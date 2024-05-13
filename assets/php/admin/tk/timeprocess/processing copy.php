<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$date=SysDate();
$time=SysTime();
$return = null;	

$pay_period = getPayPeriod($con);
$shift_cols = array("monday"	=>"idmon,mon,mon_in,mon_out,mon_brk,mon_hrs", 
					"tuesday"	=>"idtue,tue,tue_in,tue_out,tue_brk,tue_hrs",
					"wednesday"	=>"idwed,wed,wed_in,wed_out,wed_brk,wed_hrs",
					"thursday"	=>"idthu,thu,thu_in,thu_out,thu_brk,thu_hrs",
					"friday"	=>"idfri,fri,fri_in,fri_out,fri_brk,fri_hrs",
					"saturday"	=>"idsat,sat,sat_in,sat_out,sat_brk,sat_hrs", 
					"sunday"	=>"idsun,sun,sun_in,sun_out,sun_brk,sun_hrs");

//Validate parameters
if( empty($param['info']['sdate']) ){
	$return = json_encode(array('status'=>'sdate'));
	print $return;	
	mysqli_close($con);
	return;
}
if( empty($param['info']['fdate']) ){
	$return = json_encode(array('status'=>'fdate'));
	print $return;	
	mysqli_close($con);
	return;
}
if( ( strtotime( $param['info']['sdate'] ) > strtotime( $param['info']['fdate'] ) ) ||
	( strtotime( $param['info']['sdate'] ) < strtotime( $pay_period['pay_start'] ) ) ||
	( strtotime( $param['info']['fdate'] ) > strtotime( $pay_period['pay_end']   ) )
){
	$return = json_encode(array('status'=>'invdates'));
	print $return;	
	mysqli_close($con);
	return;
}
$ids=0;
if( !empty( $param['info']['classi'] ) ){
	$dept = $param['info']['classi'];
	if (!empty($dept)) {
		$arr_id = array();
		$arr    = getHierarchy($con, $dept);
		array_push($arr_id, $dept);
		if (!empty($arr["nodechild"])) {
			$a = getChildNode($arr_id, $arr["nodechild"]);
			if (!empty($a)) {
				foreach ($a as $v) {
					array_push($arr_id, $v);
				}
			}
		}
		if (count($arr_id) == 1) {
			$ids = $arr_id[0];
		} else {
			$ids = implode(",", $arr_id);
		}
	}
}
$sdate  = $param['info']['sdate'];
$fdate  = date('Y-m-d', strtotime("+1 day", strtotime($param['info']['fdate'])));
$arr_reso = array();
//resolution
$QryDel           = new Query();
$QryDel->table    = "tblresocenter";
if( $ids== 0 ){
	$QryDel->fields= "reso_date between '".$sdate."' AND '".$fdate."'";
}else{
	$QryDel->fields= "idunit in (".$ids.") AND (reso_date between '".$sdate."' AND '".$fdate."')";
}
$checkDEl         = $QryDel->exe_DELETE($con);

$QryValidate 			= new Query();	
$QryValidate->table     = "vw_datatimelogs AS a  LEFT JOIN vw_dataholidays AS b ON a.work_date = b.date LEFT JOIN tblshift AS d ON a.idshift = d.id";
$QryValidate->selected  = "a.*,b.id AS holiday_id, d.stime AS shiftin, d.ftime AS shiftout,d.break, d.stype, d.reghrs";
if( $ids== 0 ){
	$QryValidate->fields= "a.acct_id > 0 AND a.work_date between '".$sdate."' AND '".$fdate."' ORDER BY CONCAT(a.work_date,' ', a.acct_id) ASC ";
}else{
	$QryValidate->fields= "a.idunit in (".$ids.") AND a.work_date between '".$sdate."' AND '".$fdate."' ORDER BY CONCAT(a.work_date,' ', a.acct_id) ASC ";
}
$rsValidate 			= $QryValidate->exe_SELECT($con);
if(mysqli_num_rows($rsValidate)>= 1){
	while($rowValidate=mysqli_fetch_array($rsValidate)){
		$shift_field		= "".$shift_cols[  strtolower(''.date("l", strtotime($rowValidate['work_date']) )) ];
		$shift_info 		= getDateShiftData( $con, $rowValidate['acct_id'], $shift_field, $rowValidate['work_date'] );
		$idshift			= $rowValidate['idshift'];
		$shiftin			= $rowValidate['shiftin'];
		$shiftout			= $rowValidate['shiftout'];
		$shiftbrk			= $rowValidate['break'];
		
		$date_in 			= $rowValidate['date_in'];
		$date_out 			= $rowValidate['date_out'];
		$date_shiftin 		= $rowValidate['work_date'];
		$date_shiftout 		= $rowValidate['work_date'];
		$date_shiftbrk	= $rowValidate['work_date'];
		$in  				= $rowValidate['time_in'];
		$out 				= $rowValidate['time_out'];
		
		$concat_data		= $rowValidate['acct_id']."/".$rowValidate['work_date'];
		
		if( strtotime( $date_shiftin.' '.$shiftin ) > strtotime( $date_shiftout.' '.$shiftout ) ){
			$date_shiftout 	= date('Y-m-d', strtotime("+1 day", strtotime($date_shiftout)));
		}
		
		
		$reso = array(
			"idacct" 	=> $rowValidate['acct_id'],
			"idunit" 	=> $rowValidate['idunit'],
			"reso_date"	=> $rowValidate['work_date'],
			"reso_time"	=> date("h:mA",strtotime($in)) ."-". date("h:mA",strtotime($out)),
			"reso_txt"	=> ""
		);
		
		if( !empty($rowValidate['time_in']) && empty($rowValidate['time_out']) ){
			if( checkApplicationTimelog( $con, $rowValidate['work_date'], $rowValidate['acct_id'], "vw_attendance_application", " AND stat in (1) " ) ){
			}else{
				array_push($arr_reso,$concat_data);
				$reso["reso_time"] = date("h:mA",strtotime($in)) ."-";
				$reso["reso_txt"] = "No Time Out";
			}
		}elseif( empty($rowValidate['time_in']) && !empty($rowValidate['time_out']) ){
			array_push($arr_reso,$concat_data);
			$reso["reso_txt"] = "No Time In";
			$reso["reso_time"] = "-". date("h:mA",strtotime($out));
		}elseif( !empty($rowValidate['time_in']) && !empty($rowValidate['time_out']) ){
			if( !checkApplicationTimelog( $con, $rowValidate['work_date'], $rowValidate['acct_id'], "vw_overtime_application" ) && ($idshift == 4 || !empty($rowValidate['holiday_id']) ) ){
				array_push($arr_reso,$concat_data);
				$reso["reso_txt"] = "Need to file OT Application";
			}elseif( checkApplicationTimelog( $con, $rowValidate['work_date'], $rowValidate['acct_id'], "vw_leave_application", " AND stat in (1,3) " )  ){
				array_push($arr_reso,$concat_data);
				$reso["reso_txt"] = "Need to Cancel Leave Application";
			}elseif( checkApplicationTimelog( $con, $rowValidate['work_date'], $rowValidate['acct_id'], "vw_attendance_application", " AND stat in (1,3) " )  ){
				array_push($arr_reso,$concat_data);
				$reso["reso_txt"] = "Need to Cancel Attendance Adjustment Application";
			}elseif( checkApplicationTimelog( $con, $rowValidate['work_date'], $rowValidate['acct_id'], "vw_overtime_application", " AND stat in (3) " )  ){
				array_push($arr_reso,$concat_data);
				$reso["reso_txt"] = "Has Pending OT Application";
			}elseif( $idshift != 4 ){
				if( (!empty( $shiftbrkin ) && (( (strtotime($date_out.' '.$out) - strtotime($date_in.' '.$in)) / ( 60 * 60 ) ) >= 8) && ( strtotime($date_in.' '.$in) >= strtotime($date_shiftout.' '.$shiftout) || strtotime($date_out.' '.$out) <= strtotime( $date_shiftin.' '.$shiftin ))) ||
					(empty( $shiftbrkin ) && (( (strtotime($date_out.' '.$out) - strtotime($date_in.' '.$in)) / ( 60 * 60 ) ) >= 4) && ( strtotime($date_in.' '.$in) >= strtotime($date_shiftout.' '.$shiftout) || strtotime($date_out.' '.$out) <= strtotime( $date_shiftin.' '.$shiftin )))   ){
					array_push($arr_reso,$concat_data);
					$reso["reso_txt"] = "Need to file Change Shift";
				}else{
					if( strtotime( $date_in.' '.$in ) > strtotime( $date_shiftin.' '.$shiftin ) ){
						$late   = sprintf('%0.2f', (strtotime($date_in.' '.$in) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ));
						if( $late > 2.00 && $late < 3.00 ){
							array_push($arr_reso,$concat_data);
							$reso["reso_txt"] = "2 or more Hours Late";
						}elseif( $late > 3.00 ){
							array_push($arr_reso,$concat_data);
							$reso["reso_txt"] = "3 or more Hours Late";
						}
					}elseif( strtotime( $date_shiftout.' '.$shiftout ) > strtotime( $date_out.' '.$out ) ){
						$ut   	= sprintf('%0.2f', (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_out.' '.$out)) / ( 60 * 60 ));
						if( $ut > 2.00 && $ut < 3.00 ){
							array_push($arr_reso,$concat_data);
							$reso["reso_txt"] = "2 or more Hours Undertime";
						}elseif( $ut > 3.00 ){
							array_push($arr_reso,$concat_data);
							$reso["reso_txt"] = "3 or more Hours Undertime";
						}
					}
				}	
			}
		}else{
			$reso["reso_time"] =" ";
			if( checkApplicationTimelog( $con, $rowValidate['work_date'], $rowValidate['acct_id'], "vw_leave_application", " AND stat in (3) " )  ){
				array_push($arr_reso,$concat_data);
				$reso["reso_txt"] = "Has Pending Leave Application";
			}elseif( checkApplicationTimelog( $con, $rowValidate['work_date'], $rowValidate['acct_id'], "vw_attendance_application", " AND stat in (3) " )  ){
				array_push($arr_reso,$concat_data);
				$reso["reso_txt"] = "Has Pending Attendance Adjustment Application";
			}elseif( checkApplicationTimelog( $con, $rowValidate['work_date'], $rowValidate['acct_id'], "vw_overtime_application", " AND stat in (1,3) " )  ){
				array_push($arr_reso,$concat_data);
				$reso["reso_txt"] = "Need to Cancel OT Application";
			}else{
				array_push($arr_reso,$concat_data);
				$reso["reso_txt"] = "No In and Out";
			}
		}
		if( !empty($reso["reso_txt"]) ){
			if( !notInsertedReso($con,$reso["idacct"],$reso["reso_date"]) ){
				$Qry4           = new Query();
				$Qry4->table    = "tblresocenter";
				$Qry4->selected = "idacct,idunit,reso_date,reso_time,reso_txt,id_payperiod,idprocessby,create_date,create_time";
				$Qry4->fields   = "'".$reso["idacct"]."',
								   '".$reso["idunit"]."',
								   '".$reso["reso_date"]."',
								   '".$reso["reso_time"]."',
								   '".$reso["reso_txt"]."',
								   '".$pay_period['id']."',
								   '".$param['accountid']."',
								   '".SysDate()."',
								   '".SysTime()."'";	
				$Qry4->exe_INSERT($con);
			}
		}
	}
}

//process
$Qry = new Query();	
$Qry->table     = "vw_data_timesheet AS a LEFT JOIN vw_dataemployees AS b ON a.empID = b.id";
$Qry->selected  = "b.idunit,a.*";
if( $ids== 0 ){
	$Qry->fields    = "a.work_date BETWEEN '".$sdate."' AND '".$fdate."' ORDER BY CONCAT(a.empID,a.work_date) ASC";
}else{
	$Qry->fields    = "idunit in (".$ids.") AND (a.work_date BETWEEN '".$sdate."' AND '".$fdate."') ORDER BY CONCAT(a.empID,a.work_date) ASC";
}

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
	while($row=mysqli_fetch_array($rs)){
		
		if( empty($row['idshift']) ){
			$shift_field= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];
			$shift_info = getDateShiftData( $con, $row['empID'], $shift_field, $row['work_date'] );
		}else{
			$shift_info = getShiftIDData( $con, $row['idshift'] );
		}
		
		$idacct 	= $row['empID'];
		$idunit		= $row['idunit'];
		$day		= date("l", strtotime($row['work_date']));
		$date		= $row['work_date'];

		$idshift	= $shift_info[0];
		$shiftin	= $shift_info[1];
		$shiftout	= $shift_info[2];
		$shiftbrk	= $shift_info[3];
		$shifthrs	= $shift_info[4];
		
		$in			= $row['in'] ? $row['in'] : '';
		$out		= $row['out'] ? $row['out'] : '';
		$date_in 	= $row['work_date'];
		$date_out 	= $row['work_date'];
		$regconti	= 0;
		
		$date_shiftin 	= $row['work_date'];
		$date_shiftout 	= $row['work_date'];
		$date_shiftbrk= $row['work_date'];
		
		if( strtotime( $date_shiftin.' '.$shiftin ) > strtotime( $date_shiftout.' '.$shiftout ) ){
			$date_shiftout 	= date('Y-m-d', strtotime("+1 day", strtotime($date_shiftout)));
		}
		
		
		
		/*
		if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
			$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
			$regconti=1;
		}else{
			$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin))) / ( 60 * 60 ));
			$regconti=2;
		}
		
		if( strtotime($date_shiftout.' '.$shiftout) < strtotime($date_shiftin.' '.$shiftin) ){
			$date_out = date('Y-m-d', strtotime("+1 day", strtotime( $row['work_date']  )));
			$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin))) / ( 60 * 60 ));
			$regconti=99;
		}		
		*/
		
		$reghrs		= $shifthrs;
		$acthrs		= '0';
		$excess		= '0';
		$absent		= $reghrs;
		$late		= '0';
		$ut			= '0';
		$ot 		= '0';
		$np 		= '0';
		
		$concat_data= $row['empID']."/".$row['work_date'];
		if( !in_array($concat_data,$arr_reso) ){
			//Adjustment Applications
			$aa_data = getAttendanceApplication( $con, $row['work_date'], $row['empID'], $pay_period['pay_start'], date("Y-m-d", strtotime('+'.$pay_period['grace_hour'].' hours', strtotime($pay_period['pay_end'])))  );
			//Leave Applications
			$leave_data = getLeaveApplication( $con, $row['work_date'], $row['empID'], $pay_period['pay_start'], date("Y-m-d", strtotime('+'.$pay_period['grace_hour'].' hours', strtotime($pay_period['pay_end'])))  );
			if( !empty($leave_data['id']) ){
				if( (int)$leave_data['idtype'] == 1 ){ //PAID LEAVE
					$reghrs		= $leave_data['hrs'];
					$absent		= $reghrs - $acthrs ;
					$regconti=3;
				}else{
					$absent		= $leave_data['hrs'];
					$acthrs		= '0';
					$reghrs		= '0';				
				}
			}
			if( !empty( $row['holiday_id'] ) ){ // DETERMINE IF HOLIDAY
				if( (int)getBeforeHoliday($con, $row['work_date'], $row['empID']) > 0 ){				
					$reghrs		= '0';
					/*if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
						$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
					}else{
						$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
					}*/
				}else{
					/*if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
						$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
						$regconti=4;
					}else{
						$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
						$regconti=5;
					}*/
					$absent		= '0';
				}
			}
			
			$Qry2 = new Query();	
			$Qry2->table     = "vw_datatimelogs";
			//$Qry2->selected  = "acct_id, work_date, date_in, time_in, CASE WHEN time_out < time_in THEN NULL ELSE time_out END AS time_out";
			$Qry2->selected  = "acct_id, work_date, date_in, time_in, date_out, CASE WHEN STR_TO_DATE(CONCAT(date_out,' ', time_out), \"%M %d %Y\") < STR_TO_DATE(CONCAT(date_in,' ', time_in), \"%M %d %Y\") THEN NULL ELSE time_out END AS time_out";
			$Qry2->fields    = "work_date = '".$row['work_date']."' AND acct_id='".$row['empID']."' ";
			$rs2 = $Qry2->exe_SELECT($con);
			if(mysqli_num_rows($rs2)>= 1){
				$filter_acthrs=0;
				if($row2=mysqli_fetch_array($rs2)){	
					$date_in	= $row2['date_in'];
					$date_out	= $row2['date_out'];
					if( !empty($aa_data['id']) ){
						if( empty( $row2['time_in'] ) ){
							$in			= $aa_data['stime'];
							$date_in	= $aa_data['date'];
						}else{
							$in			= $row2['time_in'];
							$date_in	= $row2['date_in'];
						}					
						if( empty( $row2['time_out'] ) ){
							$out		= $aa_data['ftime'];
							$date_out	= $aa_data['date'];
						}else{						
							$out		= $row2['time_out'];
							$date_out	= $row2['date_out'];						
						}
						if( !empty($out) && !empty($in) ){
							$acthrs		= sprintf('%0.2f', (strtotime($date_out.' '.$out) - strtotime($date_in.' '.$in)) / ( 60 * 60 ));
						}
						$absent		= '0';						
					}
					
					
					
					if( empty($leave_data['id']) &&  empty($aa_data['id']) ){	
						$in			= $row2['time_in'];
						$out		= $row2['time_out'];
						$date_in	= $row2['date_in'];
						$date_out	= $row2['date_out'];
						if( !empty($out) && !empty($in) ){
							$acthrs		= sprintf('%0.2f', (strtotime($date_out.' '.$out) - strtotime($date_in.' '.$in)) / ( 60 * 60 ));
							$absent		= '0';		
						}else{
							/*if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
								$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
							}else{
								$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
							}*/
							$absent		= $shifthrs;
							$acthrs		= '0';
						}
					}
					
					if( !empty( $row['holiday_id'] ) ){ // DETERMINE IF HOLIDAY
						if( (int)getBeforeHoliday($con, $row['work_date'], $row['empID']) > 0 ){
							/*if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
								$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
							}else{
								$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
							}*/
							$absent		= $shifthrs;
							$reghrs		= '0';
							$acthrs		= '0';
							$excess		= '0';						
							$late		= '0';
							$ut			= '0';
						}else{
							$absent		= '0';
							$reghrs		= '0';
							$acthrs		= '0';
							$excess		= '0';						
							$late		= '0';
							$ut			= '0';
						}
					}
					
					
					if( empty($leave_data['id'])  ){
						//LATES
						$condi=0;
						if( strtotime( $date_in.' '.$in ) > strtotime( $date_shiftin.' '.$shiftin ) && strtotime( $date_in.' '.$in ) < strtotime( $date_shiftout.' '.$shiftout ) ){ // TIME IN IS GREATER THAN SHIFT IN
							$late   = sprintf('%0.2f', (strtotime($date_in.' '.$in) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 )); $condi=1;
						}
										
						if( strtotime( $date_in.' '.$in ) >  strtotime( $date_shiftout.' '.$shiftout ) ){
							//$absent = $reghrs;
							$absent		= $shifthrs;
							$acthrs	= '0';
							$excess	= '0';
						}
						
						
						
						
						//UNDERTIME
						if( strtotime( $date_shiftout.' '.$shiftout ) > strtotime( $date_out.' '.$out ) ){	// TIME OUT IS LESSER THAN SHIFT OUT
							$ut   	= sprintf('%0.2f', (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_out.' '.$out)) / ( 60 * 60 ));
						}
						
						
						
					}
				}
			}else{
				if( !empty($aa_data['id']) ){
					$in			= $aa_data['stime'];
					$out		= $aa_data['ftime'];
					$date_in	= $aa_data['date'];
					$date_out	= $aa_data['date'];
					if( !empty($out) && !empty($in) ){
						$acthrs		= sprintf('%0.2f', (strtotime($date_out.' '.$out) - strtotime($date_in.' '.$in)) / ( 60 * 60 ));
					}
					$absent		= '0';	
					if( strtotime( $date_in.' '.$in ) > strtotime( $date_shiftin.' '.$shiftin ) ){
						$late   = sprintf('%0.2f', (strtotime($date_in.' '.$in) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ));
					}
					if( strtotime( $date_shiftout.' '.$shiftout ) > strtotime( $date_out.' '.$out ) ){
						$ut   	= sprintf('%0.2f', (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_out.' '.$out)) / ( 60 * 60 ));
					}
				}	
			}
			
			if( strtotime( $acthrs ) > strtotime($reghrs) ){
				$excess	= sprintf('%0.2f', (strtotime($date_out.' '.$out) - strtotime($date_shiftout.' '.$shiftout)) / ( 60 * 60 ));			
			}
			if( $excess < 0 ){ $excess=0; }
			
			// REG HOURS MINUS LATE, UT, ABSENT
			if( empty($leave_data['id'])  ){
				if( $reghrs > 0 ){
					$reghrs	= $reghrs - ( $late + $ut + $absent );
				}			
				$reghrs = sprintf('%0.2f', $reghrs );
			}
			
			// NIGHT PREMIUM
			$nightp = getNightPremium($con);
			$workd  = $row['work_date'];
			$date_shift_out = $date_shiftout;
			$date_night_p   = $date_shiftin;		
			$np	= '0';
			if( !empty( $date_out ) && !empty( $date_in ) && $absent == '0'){			
				if( strtotime( $date_out. ' ' .$out ) > strtotime($date_night_p . ' ' . $nightp) ){
					if( strtotime( $date_shiftout. ' ' .$shiftout ) > strtotime($date_out. ' ' .$out) ){
						$np	= sprintf('%0.2f', ( strtotime( $date_out. ' ' .$out ) - strtotime($date_night_p . ' ' . $nightp)) / ( 60 * 60 ));
					}else{						
						if( strtotime( $date_shiftout. ' ' .$shiftout ) > strtotime($date_night_p . ' ' . $nightp) ){
							$np	= sprintf('%0.2f', ( strtotime( $date_shiftout. ' ' .$shiftout ) - strtotime($date_night_p . ' ' . $nightp)) / ( 60 * 60 ));
						}else{
							$np	= sprintf('%0.2f', ( strtotime( $date_out. ' ' .$out ) - strtotime($date_night_p . ' ' . $nightp)) / ( 60 * 60 ));
						}						
					}
				}
			}

			if( $idshift == 4  ){	// REST DAY ID
				$reghrs	= '0';
				$acthrs	= '0';
				$excess	= '0';
				$absent	= '0';
				$late	= '0';
				$ut		= '0';
				$np 	= '0';
			}	

			// OT HOUR CALCULATION
			$ot_data = getOtApplication( $con, $row['work_date'], $row['empID'], $pay_period['pay_start'], date("Y-m-d", strtotime('+'.$pay_period['grace_hour'].' hours', strtotime($pay_period['pay_end'])))  );
			$ot_type = "W";
			if( !empty($ot_data['id']) ){
				$ot = $ot_data['hrs'];
				//Check Holiday in vw_dataholidays using ot_date and get ot_type
				$ot_type = getHolidayAlias($con,$ot_data['date']);
				if(  $ot_data['shift_id'] == 4 ){				
					if($ot_type == 'L'){
						$ot_type = 'A';
					}elseif($ot_type == 'S'){
						$ot_type = 'B';
					}elseif($ot_type == 'Y'){
						$ot_type = 'D';
					}else{
						$ot_type = 'R';
					}				
				}
				//CASE WHEN and get id,rate, compute ot hours in tblclasstrans
				$classtrans = getClassTrans ($con,$ot_type);

				foreach($classtrans as $key => $value){
					if ($value['determine'] == 'N') {
						//himo functio to insert in tblotbreakdown
						if($ot <= 8){
							insertOtRate($con,$ot_data['id'],$ot,$value['rate'],'N');						
						}else{
							insertOtRate($con,$ot_data['id'],8,$value['rate'],'N');						
						}					
					}

					if($value['determine'] == 'G'){
						if($ot <= 8){						
							insertOtRate ($con,$ot_data['id'],0,$value['rate'],'G');					
						}else{
							insertOtRate($con,$ot_data['id'],$ot-8,$value['rate'],'G');
						}
					}

					if($value['determine'] == 'P'){			
						$ot_start = $ot_data['sdate']." ".$ot_data['overtime_stime'];
						$ot_end   = $ot_data['fdate']." ".$ot_data['overtime_ftime'];
						$ot_mins  = ((strtotime($ot_end) - strtotime($ot_start)) / 60);
						$npStart = $ot_data['sdate']." ".$nightp;
						
						$nphours = (strtotime( date('Y-m-d H:i:s', strtotime($ot_start . " +".$ot_mins." minutes")) ) - strtotime($npStart)) / 3600;
						if( floatval($nphours) > 0 ){
							if( floatval($nphours) >= 8 ){
								$nphours = 8;
							}
						}else{
							$nphours = 0;
						}
						insertOtRate ($con,$ot_data['id'],$nphours,$value['rate'],'P');
					}			 
				}
			}
		}
		
		$data = array(
			"idacct" 		=>	$idacct, 	
			"day"			=>	$day,		
			"date"			=>	$date,		
			"idshift"		=>	$idshift,	
			"shiftin"		=>	$shiftin,	
			"shiftout"		=>	$shiftout,	
			"in"			=>	$in,			
			"out"			=>	$out,				
			"reghrs"		=>	sprintf('%0.2f',$reghrs),		
			"acthrs"		=>	sprintf('%0.2f',$acthrs),		
			"excess"		=>	sprintf('%0.2f',$excess),		
			"absent"		=>	sprintf('%0.2f',$absent),
			"late"			=>  sprintf('%0.2f',$late),
			"ut"			=>  sprintf('%0.2f',$ut),
			"ot"			=>  sprintf('%0.2f',$ot),
			"np"			=>  sprintf('%0.2f',$np),
			"id_payperiod"  =>  $pay_period['id'],
			"date_in"		=>  !empty($in) ? $date_in   : '',
			"date_out"		=>  !empty($out) ? $date_out : ''
		);
		
		if( $absent > 0 && empty($in) && empty($out) ){
			if( !notInsertedReso($con,$idacct,$date) ){
				$Qry4           = new Query();
				$Qry4->table    = "tblresocenter";
				$Qry4->selected = "idacct,idunit,reso_date,reso_time,reso_txt,id_payperiod,idprocessby,create_date,create_time";
				$Qry4->fields   = "'".$idacct."',
								   '".$idunit."',
								   '".$date."',
								   ' ',
								   'No Time In and Out',
								   '".$pay_period['id']."',
								   '".$param['accountid']."',
								   '".SysDate()."',
								   '".SysTime()."'";	
				$Qry4->exe_INSERT($con);
			}
		}
		
		if( checkTimeSheetExists($con, $idacct, $date ) ){			 
			$return = updateTimeSheetRec($con, $data);			
		}else{
			$return = insertTimeSheetRec($con, $data);
		}
    }
    

    $Qry           = new Query();
    $Qry->table    = "tblpayperiod";
    $Qry->selected = "tkstatus ='1'";
    $Qry->fields   = "id='". $pay_period['id'] ."'";                        
    $Qry->exe_UPDATE($con);
	//ADD TO LOGS
	$return = inserLogs($con, $param['accountid'], "Uploaded and Proccessed TimeLogs");
	$return = json_encode($data);

}else{
	$return = json_encode(array('status'=>'error'));
}



print $return;
mysqli_close($con);


function insertToTimeLogs($con, $arr_data){	
	$txt_acct = trim($arr_data[0]);
    $txt_date = trim($arr_data[1]);
    $txt_time = trim($arr_data[2]);
    $txt_type = trim($arr_data[3]);
	
	
	
	/* FOR INDIVIDUAL PROCESS
	$Qry           = new Query();
	$Qry->table    = "tbltimelogs";
	$Qry->fields   = "acct_id='".$txt_acct."' AND work_date='".$txt_date."' AND time_type='".$txt_type."' AND work_time='".$txt_time."' ";
	$check         = $Qry->exe_DELETE($con);
	*/	
	
	$Qry3           = new Query();
	$Qry3->table    = "tbltimelogs";
	$Qry3->selected = "acct_id,work_time,time_type";
	$Qry3->fields   = "'".$txt_acct."',
					   '".$txt_time."',
					   '".$txt_type."'";
					   
	if( !empty( $txt_date ) ){
		$arr = explode("-",$txt_date);
		$txt_date = $arr[2]."-".$arr[0]."-".$arr[1];
		$Qry3->selected = $Qry3->selected." ,work_date, date_pair";
		$Qry3->fields   = $Qry3->fields  .",'".$txt_date."','".$txt_date."'";
	}				   
					   
	$checke = $Qry3->exe_INSERT($con);
	if($checke){
		return  json_encode(array("status"=>"success"));
	}else{
		return  json_encode(array('status'=>'error',"err"=>mysqli_error($con)));
	}
}

function getIDLogOut($con, $acct_id, $work_date){
	$QryPair 			= new Query();	
	$QryPair->table     = "tbltimelogs ";
	$QryPair->selected  = "MAX(id) as id, acct_id, work_date, MAX(work_time) AS work_time, time_type";
	$QryPair->fields    = "ispaired=0 AND time_type='C/O' AND work_date='".$work_date."' AND  acct_id='".$acct_id."'";
	$rsPair				= $QryPair->exe_SELECT($con);
	if(mysqli_num_rows($rsPair)>= 1){
		while($rowPair=mysqli_fetch_array($rsPair)){
			return $rowPair['id'];
		}
	}
	return 0;
}

function getIDLogOut2( $con, $acct_id, $work_date, $shiftin, $shiftout ){
	$QryPair 			= new Query();	
	$QryPair->table     = "tbltimelogs ";
	$QryPair->selected  = "MAX(id) as id, acct_id, work_date, MAX(work_time) AS work_time, time_type";
	$QryPair->fields    = "ispaired=0 AND time_type='C/O' AND work_date = '".$work_date."' AND work_time < '".$shiftin."' ";
	if( getTimeInForTheDay($con, $work_date, $acct_id) ){
		$QryPair->fields    = $QryPair->fields." AND work_time <= '".getTimeInForTheDay($con, $work_date,$acct_id)."'";
	}
	$rsPair				= $QryPair->exe_SELECT($con);
	if(mysqli_num_rows($rsPair)>= 1){
		while($rowPair=mysqli_fetch_array($rsPair)){
			return $rowPair['id'];
		}
	}
	return 0;
}

function getTimeInForTheDay($con, $work_date, $acct_id){
	$QryPair 			= new Query();	
	$QryPair->table     = "tbltimelogs ";
	$QryPair->selected  = "MIN(work_time) AS work_time";
	$QryPair->fields    = "time_type='C/I' AND work_date='".$work_date."' AND  acct_id='".$acct_id."'";
	$rsPair				= $QryPair->exe_SELECT($con);
	if(mysqli_num_rows($rsPair)>= 1){
		while($rowPair=mysqli_fetch_array($rsPair)){
			return $rowPair['work_time'];
		}
	}
	return '';
}

function truncateSheet($con){
	$QryTrunc      		= new Query();
	$QryTrunc->table	= "tbltimelogs";
	$checkTrucn			= $QryTrunc->exe_TRUNCATE($con);
	return $checkTrucn;
}

function notInsertedReso($con,$idacct,$reso_date){
	$QryPair 			= new Query();	
	$QryPair->table     = "tblresocenter";
	$QryPair->selected  = "id";
	$QryPair->fields    = "idacct='".$idacct."' and reso_date='".$reso_date."'";
	$rsPair				= $QryPair->exe_SELECT($con);
	if(mysqli_num_rows($rsPair)>= 1){
		return true;
	}
	return false;
}

?>