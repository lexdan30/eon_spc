<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();
$currdate = SysDate();
$bday = getBirthdate($con, $param->acct);

$shift_cols = array("monday"	=>"idmon,mon,mon_in,mon_out,mon_brk", 
					"tuesday"	=>"idtue,tue,tue_in,tue_out,tue_brk",
					"wednesday"	=>"idwed,wed,wed_in,wed_out,wed_brk",
					"thursday"	=>"idthu,thu,thu_in,thu_out,thu_brk",
					"friday"	=>"idfri,fri,fri_in,fri_out,fri_brk",
					"saturday"	=>"idsat,sat,sat_in,sat_out,sat_brk", 
					"sunday"	=>"idsun,sun,sun_in,sun_out,sun_brk");
					

if( !empty($param->acct) ){
	if( !empty($param->idleave) ){
		if( !empty($param->from) ){
			if( !empty($param->to) ){
				
				$Qry = new Query();	
				$Qry->table     = "vw_data_timesheet";
				$Qry->selected  = "work_date, empID, holiday_id, holiday_name,idshift";
				$Qry->fields    = "empID = '".$param->acct."' AND work_date BETWEEN '".$param->from."' AND '".$param->to."' ORDER BY work_date ASC";
				$rs = $Qry->exe_SELECT($con);
				if(mysqli_num_rows($rs)>= 1){
					$total_unit				= 0;
					while($row=mysqli_fetch_array($rs)){
						
						$shift_field			= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];
						$shift_info 			= getDateShiftData( $con, $row['empID'], $shift_field, $row['work_date'] );
						if(!empty($row['idshift'])){
							$shift_info			= getShiftIDData($con,$row['idshift']);
						}
						if (empty($shift_info)) {
							$shift_info = getCalendarSched($con,$param->acct,$row['work_date'],$shift_field);
						}
						
						$idshift				= $shift_info[0];
						$shiftin				= $shift_info[2];
						$shiftout				= $shift_info[3];
						$shiftbrk				= $shift_info[4]; 

						// if($shiftbrk == 0){ // we can trap here a condition if a positionlvl have certain break since its not in db
						// 	$shiftbrk = 1;
						// }

						$plusperiod = new DateTime($row['work_date']);
						$plusperiod->modify('+1 day');
						$tomorrow = date_format($plusperiod,"Y-m-d"); 

						//$reghrs					= sprintf('%0.2f',((strtotime($shiftout) - strtotime($shiftin)) ) / ( 60 * 60 ) - $shiftbrk );
						if($shiftout < $shiftin){
							$reghrs					= sprintf('%0.2f',((strtotime( $tomorrow.' '.$shiftout.'' ) - strtotime( $row['work_date'].' '.$shiftin.'' ))/ ( 60 * 60 )) -$shiftbrk );
						}else{
							$reghrs					= sprintf('%0.2f',((strtotime( $row['work_date'].' '.$shiftout.'' ) - strtotime( $row['work_date'].' '.$shiftin.'' ))/ ( 60 * 60 )) -$shiftbrk );
						}

						
						
						if(!empty($shift_info[7]) && !empty($shift_info[8])){
							if($shiftout < $shiftin){
								$reghrs0					= sprintf('%0.2f',((strtotime( $tomorrow.' '.$shiftout.'' ) - strtotime( $row['work_date'].' '.$shiftin.'' ))/ ( 60 * 60 )));
							}else{
								$reghrs0					= sprintf('%0.2f',((strtotime( $row['work_date'].' '.$shiftout.'' ) - strtotime( $row['work_date'].' '.$shiftin.'' ))/ ( 60 * 60 )) );
							}
							if($shift_info[8] < $shift_info[7]){
								$reghrs1					= sprintf('%0.2f',((strtotime( $tomorrow.' '.$shift_info[8].'' ) - strtotime( $row['work_date'].' '.$shift_info[7].'' ))/ ( 60 * 60 )));
							}else{
								$reghrs1					= sprintf('%0.2f',((strtotime( $row['work_date'].' '.$shift_info[8].'' ) - strtotime( $row['work_date'].' '.$shift_info[7].'' ))/ ( 60 * 60 )) );
							}
							$reghrs	= sprintf('%0.2f', $reghrs0 + $reghrs1);

							// for confirmation unit for broken - uncomment when ready and adjust 1st and 2nd half
							if($reghrs > 8){
								$reghrs = 8;
							}
							
							// $reghr2 = $reghrs0;
							// $reghr3 = $reghrs1;
						}

						
						// if($reghrs >= 7 && $reghrs < 8){
						// 	$reghrs = $shiftbrk + $reghrs;
						// }

						if($reghrs > 8){
							$reghrs = 8;
						}

						
						$reghr2 = ($reghrs/2);
						$reghr3 = ($reghrs/2);
						//get reghrs didrectly
						//$reghrs					= getReghrs($con,$idshift);

						$drop = array();
						$val = '';
						// if( (int)$idshift != 4 && empty($row['holiday_id']) ){
							if( $reghrs == 4 ){
								$drop[0] = array(
									"id" 	=> "1",
									"hr"	=> 4,
									"valz"	=> "Whole Shift"
								);
								$val = '1';
							}else{
								$drop[0] = array(
									"id" 	=> "1",
									"hr"	=> (int)$reghrs,
									"valz"	=> "Whole Shift"
								);
								$drop[1] = array(
									"id" 	=> "2",
									"hr"	=> number_format($reghr2, 2),
									"valz"	=> "1st Half Shift"
								);
								$drop[2] = array(
									"id" 	=> "3",
									"hr"	=> number_format($reghr3, 2),
									"valz"	=> "2nd Half Shift"
								);
								$val = '1';
							}
						//}
						// if( !empty($row['holiday_id']) ){
						// 	$shift_info[1] = $row["holiday_name"];
						// 	$reghrs = '0';
						// }
                        if( !empty($row['in']) && !empty($row['out']) ){
                            $val = '';
                            $drop = array();
                            $shift_info[1] = "Has In/Out data";
                            $reghrs=0;
                        }
                        if( getHireDate($con, $param->acct) != '0' && strtotime($row['work_date']) < strtotime(getHireDate($con, $param->acct)) ){
                            $val = '';
                            $drop = array();
                            $shift_info[1] = "Before Hire Date";
                            $reghrs=0;
						}
						
                        // if(checkApplication($con, $param->acct, $row['work_date'], "vw_attendance_application")){
                        //     $val = '';
                        //     $drop = array();
                        //     $shift_info[1] = "Already filed for Adjustment Application";
                        //     $reghrs=0; 
                        // }
                        $plusdayperiod = new DateTime($currdate);
						$plusdayperiod->modify('+1 day');
						$plusday = date_format($plusdayperiod,"Y-m-d"); 
						
						$total_unit		= $total_unit + (int)$reghrs;
						$latefiled = false;
						$allowbirthdayfile = false;

						

						if(!empty($bday)){
							$plus6days = new DateTime($bday);
							$plus6days->modify('+6 day');
							$plus6days = date_format($plus6days,"Y-m-d"); 
	
							$minus7days = new DateTime($bday);
							$minus7days->modify('-7 day');
							$minus7days = date_format($minus7days,"Y-m-d"); 

							if($param->idleave == 16){ //if birthday leave = 16
								if(((strtotime( $row['work_date'].' '.'00:00'.'' ) <= strtotime( $plus6days.' '.'00:00'.'' )) && (strtotime( $row['work_date'].' '.'00:00'.'' ) >= strtotime( $minus7days.' '.'00:00'.'' )))){
									$allowbirthdayfile = true;
								}
							}
						}

						if((strtotime( $row['work_date'].' '.'00:00'.'' ) <= strtotime( $plusday.' '.'00:00'.'' )) && ($param->idleave != '1')){
							$latefiled = true;
						}

						if( !empty($row['holiday_id']) ){
							$holiday = true;
						}else{
							$holiday = false;
						}

						$data[] = array(
							"date" 	 => $row['work_date'],
							"latefiled" => $latefiled,
							"allowbirthdayfile" => $allowbirthdayfile,
							"empID"	 => $row['empID'], 
							"idshift"=> $shift_info[0],
							"shift"	 => $shift_info[1],
							"hrs"	 => (int)$reghrs,
							"bal"	 => getLeaveBalance($con, $param->acct, $param->idleave),
							"val"  	 => $val,
							"drop"	 => $drop,
							"unit"	 => $total_unit,
							"holiday"=> $holiday
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
}else{
	$return = json_encode(array('status'=>'error'));
}

print $return;
mysqli_close($con);

function getReghrs($con,$idshift){
	$hrs = 0;
	$Qry = new Query();	
	$Qry->table     = "tblshift";
	$Qry->selected  = "reghrs";
	$Qry->fields    = "id = '".$idshift."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
	
	}
	return $hrs;
}


?>