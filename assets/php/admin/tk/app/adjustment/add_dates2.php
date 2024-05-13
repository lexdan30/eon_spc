<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

$shift_cols = array("monday"	=>"idmon,mon,mon_in,mon_out,mon_brkin,mon_brkout", 
					"tuesday"	=>"idtue,tue,tue_in,tue_out,tue_brkin,tue_brkout",
					"wednesday"	=>"idwed,wed,wed_in,wed_out,wed_brkin,wed_brkout",
					"thursday"	=>"idthu,thu,thu_in,thu_out,thu_brkin,thu_brkout",
					"friday"	=>"idfri,fri,fri_in,fri_out,fri_brkin,fri_brkout",
					"saturday"	=>"idsat,sat,sat_in,sat_out,sat_brkin,sat_brkout", 
					"sunday"	=>"idsun,sun,sun_in,sun_out,sun_brkin,sun_brkout");
					

if( !empty($param->acct) ){
		if( !empty($param->from) ){
			if( !empty($param->to) ){
				
				$Qry = new Query();	
				$Qry->table     = "vw_data_timesheet";
				$Qry->selected  = "*";
				$Qry->fields    = "empID in (".implode(",",$param->acct).") AND work_date BETWEEN '".$param->from."' AND '".$param->to."' ORDER BY work_date ASC";
				$rs = $Qry->exe_SELECT($con);
				if(mysqli_num_rows($rs)>= 1){	
					$arr_dates = array();
					while($row=mysqli_fetch_array($rs)){
						
						$shift_field			= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];
						$shift_info 			= getDateShiftData( $con, $row['empID'], $shift_field, $row['work_date'] );
						if(!empty($row['idshift'])){
							$shift_info			= getShiftIDData($con,$row['idshift']);
						}
						$idshift				= $shift_info[0];
						$shiftin				= $shift_info[2];
						$shiftout				= $shift_info[3];
						$shiftbrkin				= $shift_info[4];
						$shiftbrkout			= $shift_info[5];
						$reghrs					= sprintf('%0.2f',((strtotime($shiftout) - strtotime($shiftin)) - (strtotime($shiftbrkout) - strtotime($shiftbrkin))) / ( 60 * 60 ));
						
						$drop = array();
						$val = '';
						if( (int)$idshift != 4 && empty($row['holiday_id']) ){
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
									"hr"	=> (int)($reghrs/2),
									"valz"	=> "1st Half Shift"
								);
								$drop[2] = array(
									"id" 	=> "3",
									"hr"	=> (int)($reghrs/2),
									"valz"	=> "2nd Half Shift"
								);
								$val = '1';
							}
						}
						if( !empty($row['holiday_id']) ){
							$shift_info[1] = $row["holiday_name"];
							$reghrs = 0;
						}
						if( strtotime($row['work_date']) != strtotime(SysDate()) ){
							if( strtotime($row['work_date']) > strtotime(SysDate()) ){
								$val = '';
								$drop = array();
								$shift_info[1] = "Can't apply in advance";
								$reghrs=0;
							}elseif( !empty($row['in']) && !empty($row['out']) ){
								$val = '';
								$drop = array();
								$shift_info[1] = "Has In/Out data";
								$reghrs=0;
							}elseif( getHireDate($con, $row['empID']) != '0' && strtotime($row['work_date']) < strtotime(getHireDate($con, $row['empID'])) ){
								$val = '';
								$drop = array();
								$shift_info[1] = "Before Hire Date";
								$reghrs=0;
							}elseif(checkApplication($con, $row['empID'], $row['work_date'], "vw_leave_application")){
								$val = '';
								$drop = array();
								$shift_info[1] = "Already filed for Leave Application";
								$reghrs=0;
							}
						}
						if($reghrs!=0 || count($param->acct) == 1){
							if( !in_array($row['work_date'],$arr_dates) ){
								$data[] = array(
									"date" 	 => $row['work_date'],
									"empID"	 => $row['empID'], 
									"idshift"=> $shift_info[0],
									"shift"	 => $shift_info[1],
									"hrs"	 => (int)$reghrs,							
									"val"  	 => $val,
									"drop"	 => $drop
								);
							}
						}
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


?>