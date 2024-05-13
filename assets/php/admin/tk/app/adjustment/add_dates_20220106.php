<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

$shift_cols = array("monday"	=>"idmon,mon,mon_in,mon_out,mon_brk", 
					"tuesday"	=>"idtue,tue,tue_in,tue_out,tue_brk",
					"wednesday"	=>"idwed,wed,wed_in,wed_out,wed_brk",
					"thursday"	=>"idthu,thu,thu_in,thu_out,thu_brk",
					"friday"	=>"idfri,fri,fri_in,fri_out,fri_brk",
					"saturday"	=>"idsat,sat,sat_in,sat_out,sat_brk",  
					"sunday"	=>"idsun,sun,sun_in,sun_out,sun_brk");
					

if( !empty($param->acct) ){
		if( !empty($param->from) ){
			if( !empty($param->to) ){
				
				$Qry = new Query();	
				$Qry->table     = "vw_timesheet";
				$Qry->selected  = "*";
				$Qry->fields    = "id = '".$param->acct."' AND work_date BETWEEN '".$param->from."' AND '".$param->to."' ORDER BY work_date ASC";
				$rs = $Qry->exe_SELECT($con);
				if(mysqli_num_rows($rs)>= 1){					
					while($row=mysqli_fetch_array($rs)){
						
						$shift_field			= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];

						$shift_info 			= getDateShiftData( $con, $row['id'], $shift_field, $row['work_date'] );
						if(!empty($row['idshift'])){
							$shift_info			= getShiftIDData($con,$row['idshift']);
						}

                        if($row['csstatus'] == 1){
                            $shift_info			= getShiftIDData($con,$row['cs']);
                        }


						$idshift				= $shift_info[0];
						$shiftin				= $shift_info[2];
						$shiftout				= $shift_info[3];
						$shiftbrk			    = $shift_info[4];

						if( !empty($row['holidaytype']) ){
							$shift_info[1] = ucwords(strtolower($row['alias'])).'H ' .$shift_info[1];
						}
						//$reghrs					= sprintf('%0.2f',((strtotime($shiftout) - strtotime($shiftin))) / ( 60 * 60 )  - $shiftbrk);
						$reghrs = 0;
						//$reghrs					= sprintf('%0.2f',((strtotime("14:00") - strtotime("6:00"))) / ( 60 * 60 )  - 1);

						$drop = array();
						$val = '';
					
						// if( !empty($row['holiday_id']) ){
						// 	$shift_info[1] = $row["holiday_name"];
						// 	$reghrs = '0';
						// }

						//$shift_info[0] = 73;
						$hastwoPairs = ($shift_info[6] == 'Broken Schedule' ) ? true : false;
						//$hastwoPairs = ($shift_info[0] == 29 || $shift_info[0] == 39  || $shift_info[0] == 73  || $shift_info[0] == 74 ) ? true : false;
						$actlogs = getActLogss($con, $row['id'], $row['work_date'], $row['FPidshift']);

						//$actlogs[4] = '12:20';
						//$actlogs[5] = '16:30';
						if($hastwoPairs){
							if(!empty($row['timeout'])){
								$reghrs	= sprintf('%0.2f',((strtotime($row['timeout']) - strtotime($row['timein']))) / ( 60 * 60 ));
							}
						}
						
						if(!empty($actlogs)){
							if( strtotime($row['work_date']) != strtotime(SysDate()) ){
								if( strtotime($row['work_date']) > strtotime(SysDate()) ){
									$val = '';
									$drop = array();
									$shift_info[1] = "Can't apply in advance"; 
									$reghrs=0;
								}elseif( !empty($row['timein']) && !empty($row['timeout']) && $hastwoPairs==false){
									$val = '';
									$drop = array();
                                    $shift_info[1] = "Has In/Out data";
                                    
                                    if(strtotime($row['timeout']) < strtotime($row['timein'])){
                                        $date1 = new DateTime('2006-04-12T'.$row['timein']);
                                        $date2 = new DateTime('2006-04-12T'.$row['timeout']);
                                        
                                        $diff = $date2->diff($date1);
                                        
                                        $hours = $diff->h;
                                        $hours = $hours + ($diff->days*24);
                                        
                                        $reghrs	= $hours;
                                    }else{
                                        $reghrs	= sprintf('%0.2f',((strtotime($row['timeout']) - strtotime($row['timein']))) / ( 60 * 60 )  - $shiftbrk);
                                    }
                                  
                                

								}elseif( !empty($row['timein']) && !empty($row['timeout']) && !empty($actlogs[4]) && !empty($actlogs[5]) && $hastwoPairs==true){
									$val = '';
									$drop = array();
									$shift_info[1] = "Has In/Out data";
									$reghrs	= sprintf('%0.2f',(((strtotime($row['timeout']) - strtotime($row['timein']))) / ( 60 * 60 )) + (((strtotime($actlogs[5]) - strtotime($actlogs[4]))) / ( 60 * 60 )));
								}elseif( getHireDate($con, $param->acct) != '0' && strtotime($row['work_date']) < strtotime(getHireDate($con, $param->acct)) ){
									$val = '';
									$drop = array();
									$shift_info[1] = "Before Hire Date";
									$reghrs=0;
								}elseif(checkApplication($con, $param->acct, $row['work_date'], "vw_leave_application")){
									$val = '';
									$drop = array();
									$shift_info[1] = "Already filed for Leave Application";
									$reghrs=0;
								}
							}
							if (empty($data)) {
								$data[] = array(
									"date" 	 => $row['work_date'],
									"empID"	 => $row['id'], 
									"breakhr"=> $shiftbrk, 
									"idshift"=> $shift_info[0],
									"shift"	 => $shift_info[1],
									"doublepair"=> $hastwoPairs,
									"timein" =>	($actlogs[2] != null) ? $actlogs[2] : '',
									"timeout"=> ($actlogs[3] != null) ? $actlogs[3] : '',
									"timein2"=>	($actlogs[4] != null) ? $actlogs[4] : '',
									"timeout2"=>($actlogs[5] != null) ? $actlogs[5] : '',
									"dateeven"=>($actlogs[6] == $actlogs[7] ) ? true : false,
									'sched'  => getsched($con, $shift_info[0]),			
									"hrs"	 => (int)$reghrs,							
									"val"  	 => $val,
									"drop"	 => $drop
								);
						   }
							
						}
					}
				}
				if(!empty($data)){
					$return = json_encode($data);
				}else{
					$return = json_encode(array('status'=>'nologs'));
				}
			}else{
				$return = json_encode(array('status'=>'error'));
			}
		}else{
			$return = json_encode(array('status'=>'error'));
		}
	
}else{
	$return = json_encode(array('status'=>'error'));
}

print $return;
mysqli_close($con);


function getsched($con, $id){
    $data = array();

    $Qry = new Query();	
    $Qry->table     = "tblshift";
    $Qry->selected  = "*";
    $Qry->fields    = "id='".$id."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $data = array(
                "stime" 	 => $row['stime'],
                "ftime"	     => $row['ftime'], 
                "breakin" 	 => $row['breakin'],
                "breakout"	 => $row['breakout'], 
                "sstime" 	 => $row['sstime'],
                "sftime"	 => $row['sftime']
            );
        }
    }
    return $data;
}

function getActLogss($con, $id, $date,$idshift){
    $data = array();	
    $Qry = new Query();	
    $Qry->table     = "tbltimesheet";
    $Qry->selected  = "*";
    $Qry->fields    = "idacct='".$id."' AND `date`='".$date."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
			$data = array($row['id'],$row['idshift'],$row['timein'],$row['timeout'],$row['timein2'],$row['timeout2'],$row['date'],$row['date_out']);
        }
    }else{
        $data = array('',$idshift,'','','','','','');
    }

    return $data;
}
?>