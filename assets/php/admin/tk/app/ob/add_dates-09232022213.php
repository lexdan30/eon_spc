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
						//$reghrs					= sprintf('%0.2f',((strtotime($shiftout) - strtotime($shiftin))) / ( 60 * 60 )  - $shiftbrk);
						$date1 = $row['work_date'];
						$date2 = $row['work_date'];
						

						if((strtotime( $shiftout ) < strtotime( $shiftin )) && $shiftin != '00:00:00'){
							$plusday = new DateTime($date1);
							$plusday->modify('+1 day');
							$plusday = date_format($plusday,"Y-m-d");
							$date2 = $plusday;
						}

						$main_start_time = '';
						$main_end_time = '';
						$first_start_time = '';
						$first_end_time = '';
						$second_start_time = '';
						$second_end_time = '';
						$reghr2 = 0;
						$reghr3 = 0;
						$reghrs		= sprintf('%0.2f', ((strtotime( $date2.' '.$shiftout.'' ) - strtotime( $date1.' '.$shiftin.'' )) / ( 60 * 60 ) - $shiftbrk) );
						
						if(!empty($shift_info[7]) && !empty($shift_info[8])){
							$reghrs		= sprintf('%0.2f', ((strtotime( $date2.' '.$shiftout.'' ) - strtotime( $date1.' '.$shiftin.'' )) / ( 60 * 60 ) + (strtotime( $date2.' '.$shift_info[8].'' ) - strtotime( $date1.' '.$shift_info[7].'' )) / ( 60 * 60 )) );
							// $reghr2 = sprintf('%0.2f', ((strtotime( $date2.' '.$shiftout.'' ) - strtotime( $date1.' '.$shiftin.'' )) / ( 60 * 60 )) );
							// $reghr3 = sprintf('%0.2f', ((strtotime( $date2.' '.$shift_info[8].'' ) - strtotime( $date1.' '.$shift_info[7].'' )) / ( 60 * 60 )) );
							if($reghrs > 8){
								$reghrs = 8;
							}
							$reghr2 = ($reghrs/2);
							$reghr3 = ($reghrs/2);
							$main_start_time = $shiftin;
							$main_end_time = $shift_info[8];
							$first_start_time = $shiftin;
							$first_end_time = $shiftout;
							$second_start_time = $shift_info[7];
							$second_end_time = $shift_info[8];
						}else{
							// if($reghrs > 8){ you may add second condition if not compress
							// 	$reghrs = 8;
							// }
							$reghr2 = ($reghrs/2);
							$reghr3 = ($reghrs/2);
							$main_start_time = $shiftin;
							$main_end_time = $shiftout;
							$first_start_time = $shiftin;
							$first_end_time = $shiftout;
							$second_start_time = $shiftin;
							$second_end_time = $shiftout;
						}


						$drop = array();
						$val = '';
						
						if( (int)$idshift != 4 && empty($row['holiday']) ){
							if( $reghrs == 4 ){
								$drop[0] = array(
									"id" 	=> "1",
									"hr"	=> 4,
									"valz"	=> "Whole Shift",
									"start_time"	=> '',
									"end_time"	=> ''
									
								);
								$val = '1';
							}else{
								$drop[0] = array(
									"id" 	=> "1",
									"hr"	=> (int)$reghrs,
									"valz"	=> "Whole Shift",
									"start_time"	=> $main_start_time,
									"end_time"	=> $main_end_time
								);
								$drop[1] = array(
									"id" 	=> "2",
									"hr"	=> $reghr2,
									"valz"	=> "1st Half Shift",
									"start_time"	=> $first_start_time,
									"end_time"	=> $first_end_time
								);
								$drop[2] = array(
									"id" 	=> "3",
									"hr"	=> $reghr3,
									"valz"	=> "2nd Half Shift",
									"start_time"	=> $second_start_time,
									"end_time"	=> $second_end_time
                                );

								if($param->from == $param->to){
									$drop[3] = array(
										"id" 	=> "4",
										"hr"	=> 0,
										"valz"	=> "User Defined",
										"start_time"	=> '',
										"end_time"	=> ''
									);
								}
								$val = '1';
							}
						}
						if( !empty($row['holiday']) ){
							$shift_info[1] = $row["holiday"];
							$reghrs = '0';
						}
						// if( strtotime($row['work_date']) != strtotime(SysDate()) ){
						// 	if( strtotime($row['work_date']) > strtotime(SysDate()) ){
						// 		$val = '';
						// 		$drop = array();
						// 		$shift_info[1] = "Can't apply in advance";
						// 		$reghrs=0;
						// 	}elseif( !empty($row['in']) && !empty($row['out']) ){
						// 		$val = '';
						// 		$drop = array();
						// 		$shift_info[1] = "Has In/Out data";
						// 		$reghrs=0;
						// 	}elseif( getHireDate($con, $param->acct) != '0' && strtotime($row['work_date']) < strtotime(getHireDate($con, $param->acct)) ){
						// 		$val = '';
						// 		$drop = array();
						// 		$shift_info[1] = "Before Hire Date";
						// 		$reghrs=0;
						// 	}elseif(checkApplication($con, $param->acct, $row['work_date'], "vw_leave_application")){
						// 		$val = '';
						// 		$drop = array();
						// 		$shift_info[1] = "Already filed for Leave Application";
						// 		$reghrs=0;
						// 	}
                        // }
						if((int)$idshift == 89 || (int)$idshift == 93){
							$flexi = true;
						}else{
							$flexi = false;
						}

						
						$data[] = array(
							"date" 	 => $row['work_date'],
							"empID"	 => $row['id'], 
							"idshift"=> $shift_info[0],
							"shift"	 => $shift_info[1],
                            "hrs"	 => (int)$reghrs,
							"orig_hrs"=> (int)$reghrs,		
                            'sched'  => getsched($con, $shift_info[0]),				
							"val"  	 => $val,
							"drop"	 => $drop,
							"flexi"	 => $flexi
						);
					}
				}
				$return = json_encode($data);
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

function getsched1($con, $id){

    $Qry = new Query();	
    $Qry->table     = "tblshift";
    $Qry->selected  = "*";
    $Qry->fields    = "id='".$id."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
           return $row['stime'];
        }
    }
    return '';
}

function getsched2($con, $id){

    $Qry = new Query();	
    $Qry->table     = "tblshift";
    $Qry->selected  = "*";
    $Qry->fields    = "id='".$id."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
           return $row['ftime'];
        }
    }
    
}

?>