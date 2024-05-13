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
$shift_cols = array("monday"	=>"idmon,mon,mon_in,mon_out,mon_brkin,mon_brkout", 
					"tuesday"	=>"idtue,tue,tue_in,tue_out,tue_brkin,tue_brkout",
					"wednesday"	=>"idwed,wed,wed_in,wed_out,wed_brkin,wed_brkout",
					"thursday"	=>"idthu,thu,thu_in,thu_out,thu_brkin,thu_brkout",
					"friday"	=>"idfri,fri,fri_in,fri_out,fri_brkin,fri_brkout",
					"saturday"	=>"idsat,sat,sat_in,sat_out,sat_brkin,sat_brkout", 
					"sunday"	=>"idsun,sun,sun_in,sun_out,sun_brkin,sun_brkout");

//upload file
if( array_key_exists('file',$_FILES) ){
	$valid_formats = array("txt");	
	$name = $_FILES['file']['name'];
	if ($_FILES['file']['error'] == 4) {
		$return = json_encode(array('status'=>'error','on'=>'img_check'));
		print $return;	
		mysqli_close($con);
		return;
	}
	if ($_FILES['file']['error'] == 0) {
		if(!in_array(pathinfo(strtolower($name), PATHINFO_EXTENSION), $valid_formats) ){
			$return = json_encode(array('status'=>'error-upload-type'));
			print $return;	
			mysqli_close($con);
			return;
		}
	}
	
	$folder_path 	= $param['targetPath'];	
	$t				= strtotime($date).time();	
	$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
	$save_name		= $pay_period['pay_date'].'.'.$extMove;	
	move_uploaded_file($_FILES["file"]["tmp_name"], $folder_path.$save_name);
		
}else{
	$return = json_encode(array('status'=>'nofile','on'=>'img_check'));
	print $return;	
	mysqli_close($con);
	return;
}

/* REPROCESS ALL */
$truncData = truncateSheet($con);

$file = fopen($folder_path.$save_name,"r");
while(! feof($file)){
  $txt_file = fgets($file);
  $arr_data	= explode(" ",$txt_file); 
  $ret = insertToTimeLogs($con, $arr_data);
}
fclose($file);

//pair timelogs uploaded
$arr_idlogout = array();
$QryPair 			= new Query();	
$QryPair->table     = "tbltimelogs ";
$QryPair->selected  = "id, acct_id, work_date, MIN(work_time) AS work_time, time_type";
$QryPair->fields    = "time_type='C/I' GROUP BY acct_id, work_date ORDER BY id ASC";
$rsPair				= $QryPair->exe_SELECT($con);
if(mysqli_num_rows($rsPair)>= 1){
	while($rowPair=mysqli_fetch_array($rsPair)){
		$shift_field= "".$shift_cols[  strtolower(''.date("l", strtotime($rowPair['work_date']) )) ];		
		$shift_info = getDateShiftData( $con, $rowPair['acct_id'], $shift_field, $rowPair['work_date'] );		
		$idshift	= $shift_info[0];
		$shiftin	= $shift_info[2];
		$shiftout	= $shift_info[3];
		$shiftbrkin	= $shift_info[4];
		$shiftbrkout= $shift_info[5];
		$dateCompare= $rowPair['work_date'];		
		$idLogout	= 0;
		//if( (int)$idshift != 4 ){
			if( strtotime($shiftin) > strtotime($shiftout) ){				
				$rowPair['work_date'] = date('Y-m-d', strtotime("+1 day", strtotime( $rowPair['work_date']  )));
				$idLogout = getIDLogOut2( $con, $rowPair['acct_id'], $rowPair['work_date'], $shiftin, $shiftout );			
			}else{
				$idLogout = getIDLogOut( $con, $rowPair['acct_id'], $rowPair['work_date'] );				
				if( $idLogout == 0 ){
					$rowPair['work_date'] = date('Y-m-d', strtotime("+1 day", strtotime( $rowPair['work_date'] )));
					$idLogout = getIDLogOut2( $con, $rowPair['acct_id'], $rowPair['work_date'], $shiftin, $shiftout );
				}
			}
		//}else{
		//	$idLogout = getIDLogOut( $con, $rowPair['acct_id'], $rowPair['work_date'] );
		//}
		//UPDATE date_pair
		$QryPair2 				= new Query();	
		$QryPair2->table    	= "tbltimelogs ";
		$QryPair2->selected 	= "date_pair = '".$dateCompare."', ispaired='1'";
		$QryPair2->fields   	= "ispaired=0 AND id = '".$rowPair['id']."' ";
		if( $idLogout > 0 ){
			$QryPair2->fields   = $QryPair2->fields   . " OR id='".$idLogout."' ";
		}
		$rsPair2				= $QryPair2->exe_UPDATE($con);		
	}
}


//validate timelogs
$QryValidate 			= new Query();	
$QryValidate->table     = "vw_datatimelogs AS a LEFT JOIN vw_dataholidays AS b ON a.work_date = b.date LEFT JOIN vw_dataemployees AS c ON c.id = a.acct_id";
$QryValidate->selected  = "a.acct_id, c.empname, a.work_date, a.date_in, a.time_in, a.date_out, CASE WHEN STR_TO_DATE(CONCAT(date_out,' ', time_out), \"%M %d %Y\") < STR_TO_DATE(CONCAT(date_in,' ', time_in), \"%M %d %Y\") THEN NULL ELSE time_out END AS time_out,b.id AS holiday_id";
$QryValidate->fields    = "a.acct_id > 0 AND a.work_date between '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' ORDER BY CONCAT(a.work_date,' ', a.acct_id) ASC ";
$rsValidate 			= $QryValidate->exe_SELECT($con);
if(mysqli_num_rows($rsValidate)>= 1){
	while($rowValidate=mysqli_fetch_array($rsValidate)){
		$shift_field		= "".$shift_cols[  strtolower(''.date("l", strtotime($rowValidate['work_date']) )) ];
		$shift_info 		= getDateShiftData( $con, $rowValidate['acct_id'], $shift_field, $rowValidate['work_date'] );
		$idshift			= $shift_info[0];
		$shiftin			= $shift_info[2];
		$shiftout			= $shift_info[3];
		$shiftbrkin			= $shift_info[4];
		$shiftbrkout		= $shift_info[5];
		$date_in 			= $rowValidate['date_in'];
		$date_out 			= $rowValidate['date_out'];
		$date_shiftin 		= $rowValidate['work_date'];
		$date_shiftout 		= $rowValidate['work_date'];
		$date_shiftbrkin	= $rowValidate['work_date'];
		$date_shiftbrkout	= $rowValidate['work_date'];
		
		if( strtotime( $date_shiftin.' '.$shiftin ) > strtotime( $date_shiftout.' '.$shiftout ) ){
			$date_shiftout 	= date('Y-m-d', strtotime("+1 day", strtotime($date_shiftout)));
		}
		if( strtotime( $date_shiftbrkin.' '.$shiftbrkin ) > strtotime( $date_shiftbrkout.' '.$shiftbrkout ) ){
			$date_shiftbrkout 	= date('Y-m-d', strtotime("+1 day", strtotime( $date_out )));
		}
		
		if( (!empty($rowValidate['time_in']) && empty($rowValidate['time_out'])) || (empty($rowValidate['time_in']) && !empty($rowValidate['time_out'])) ){
			//IN or OUT is empty			
			$truncData = truncateSheet($con);
			$return = json_encode(array('status'=>'val-err1', 'info'=>$rowValidate));
			print $return;	
			mysqli_close($con);
			return;
		}		
		
		if( !empty($rowValidate['time_in']) && !empty($rowValidate['time_out']) ){			
			//HAS IN AND OUT BUT RD OR HOLIDAY BUT NO OT APPLICATION
			if( !checkApplicationTimelog( $con, $rowValidate['work_date'], $rowValidate['acct_id'], "vw_overtime_application" ) && ($idshift == 4 || !empty($rowValidate['holiday_id']) ) ){
				$truncData = truncateSheet($con);
				$return = json_encode(array('status'=>'val-err2', 'info'=>$rowValidate));
				print $return;	
				mysqli_close($con);
				return;
			}
			//HAS IN/OUT BUT FILED FOR LEAVE
			if( checkApplicationTimelog( $con, $rowValidate['work_date'], $rowValidate['acct_id'], "vw_leave_application", " AND stat in (1,3) " )  ){
				$truncData = truncateSheet($con);
				$return = json_encode(array('status'=>'val-err3', 'info'=>$rowValidate));
				print $return;	
				mysqli_close($con);
				return;
			}
			//HAS IN/OUT BUT FILED FOR AA
			if( checkApplicationTimelog( $con, $rowValidate['work_date'], $rowValidate['acct_id'], "vw_attendance_application", " AND stat in (1,3) " )  ){
				$truncData = truncateSheet($con);
				$return = json_encode(array('status'=>'val-err4', 'info'=>$rowValidate));
				print $return;	
				mysqli_close($con);
				return;
			}
			//HAS PENDING OT APPLICATION
			if( checkApplicationTimelog( $con, $rowValidate['work_date'], $rowValidate['acct_id'], "vw_overtime_application", " AND stat in (3) " )  ){
				$truncData = truncateSheet($con);
				$return = json_encode(array('status'=>'val-err5', 'info'=>$rowValidate));
				print $return;	
				mysqli_close($con);
				return;
			}
			//NEED TO CHANGE SHIFT
			$in  		= $rowValidate['time_in'];
			$out 		= $rowValidate['time_out'];
			if( $idshift != 4 ){
				if( (!empty( $shiftbrkin ) && (( (strtotime($date_out.' '.$out) - strtotime($date_in.' '.$in)) / ( 60 * 60 ) ) >= 8) && ( strtotime($date_in.' '.$in) >= strtotime($date_shiftout.' '.$shiftout) || strtotime($date_out.' '.$out) <= strtotime( $date_shiftin.' '.$shiftin ))) ||
					(empty( $shiftbrkin ) && (( (strtotime($date_out.' '.$out) - strtotime($date_in.' '.$in)) / ( 60 * 60 ) ) >= 4) && ( strtotime($date_in.' '.$in) >= strtotime($date_shiftout.' '.$shiftout) || strtotime($date_out.' '.$out) <= strtotime( $date_shiftin.' '.$shiftin )))   ){
					$truncData = truncateSheet($con);
					$return = json_encode(array('status'=>'val-err9', 'info'=>$rowValidate));
					print $return;	
					mysqli_close($con);
					return;
				}	
			}
		}else{
			//HAS PENDING LEAVE
			if( checkApplicationTimelog( $con, $rowValidate['work_date'], $rowValidate['acct_id'], "vw_leave_application", " AND stat in (3) " )  ){
				$truncData = truncateSheet($con);
				$return = json_encode(array('status'=>'val-err6', 'info'=>$rowValidate));
				print $return;	
				mysqli_close($con);
				return;
			}
			//HAS PENDING AA
			if( checkApplicationTimelog( $con, $rowValidate['work_date'], $rowValidate['acct_id'], "vw_attendance_application", " AND stat in (3) " )  ){
				$truncData = truncateSheet($con);
				$return = json_encode(array('status'=>'val-err7', 'info'=>$rowValidate));
				print $return;	
				mysqli_close($con);
				return;
			}
			//NEED TO CANCEL OT APPLICATION
			if( checkApplicationTimelog( $con, $rowValidate['work_date'], $rowValidate['acct_id'], "vw_overtime_application", " AND stat in (1,3) " )  ){
				$truncData = truncateSheet($con);
				$return = json_encode(array('status'=>'val-err8', 'info'=>$rowValidate));
				print $return;	
				mysqli_close($con);
				return;
			}
		}
	}
}

//process
$Qry = new Query();	
$Qry->table     = "vw_datacurrentworkdates2 as a LEFT JOIN vw_dataholidays AS b ON a.work_date = b.date";
$Qry->selected  = "a.work_date, a.id AS acct_id, b.id AS holiday_id";
$Qry->fields    = "a.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' ORDER BY a.work_date ASC";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
	while($row=mysqli_fetch_array($rs)){
		
		$shift_field= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];
		$shift_info = getDateShiftData( $con, $row['acct_id'], $shift_field, $row['work_date'] );

		$idacct 	= $row['acct_id'];
		$day		= date("l", strtotime($row['work_date']));
		$date		= $row['work_date'];

		$idshift	= $shift_info[0];
		$shiftin	= $shift_info[2];
		$shiftout	= $shift_info[3];
		$shiftbrkin	= $shift_info[4];
		$shiftbrkout= $shift_info[5];
		$in			= '';
		$out		= '';
		$date_in 	= $row['work_date'];
		$date_out 	= $row['work_date'];
		
		$date_shiftin 	= $row['work_date'];
		$date_shiftout 	= $row['work_date'];
		$date_shiftbrkin= $row['work_date'];
		$date_shiftbrkout= $row['work_date'];
		
		if( strtotime( $date_shiftin.' '.$shiftin ) > strtotime( $date_shiftout.' '.$shiftout ) ){
			$date_shiftout 	= date('Y-m-d', strtotime("+1 day", strtotime($date_shiftout)));
		}
		if( strtotime( $date_shiftbrkin.' '.$shiftbrkin ) > strtotime( $date_shiftbrkout.' '.$shiftbrkout ) ){
			$date_shiftbrkout 	= date('Y-m-d', strtotime("+1 day", strtotime( $date_out )));
		}
		
		$regconti=0;
		
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
		
		$acthrs		= '0';
		$excess		= '0';
		$absent		= $reghrs;
		$late		= '0';
		$ut			= '0';
		$ot 		= '0';
		$np 		= '0';
		
		//Adjustment Applications
		$aa_data = getAttendanceApplication( $con, $row['work_date'], $row['acct_id'], $pay_period['pay_start'], date("Y-m-d", strtotime('+'.$pay_period['grace_hour'].' hours', strtotime($pay_period['pay_end'])))  );
		

		//Leave Applications
		$leave_data = getLeaveApplication( $con, $row['work_date'], $row['acct_id'], $pay_period['pay_start'], date("Y-m-d", strtotime('+'.$pay_period['grace_hour'].' hours', strtotime($pay_period['pay_end'])))  );
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
			if( (int)getBeforeHoliday($con, $row['work_date'], $row['acct_id']) > 0 ){				
				$reghrs		= '0';
				if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
					$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
				}else{
					$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
				}
			}else{
				if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
					$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
					$regconti=4;
				}else{
					$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
					$regconti=5;
				}
				$absent		= '0';
			}
		}
		
		$Qry2 = new Query();	
		$Qry2->table     = "vw_datatimelogs";
		//$Qry2->selected  = "acct_id, work_date, date_in, time_in, CASE WHEN time_out < time_in THEN NULL ELSE time_out END AS time_out";
		$Qry2->selected  = "acct_id, work_date, date_in, time_in, date_out, CASE WHEN STR_TO_DATE(CONCAT(date_out,' ', time_out), \"%M %d %Y\") < STR_TO_DATE(CONCAT(date_in,' ', time_in), \"%M %d %Y\") THEN NULL ELSE time_out END AS time_out";
		$Qry2->fields    = "work_date = '".$row['work_date']."' AND acct_id='".$row['acct_id']."' ";
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
						if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
							$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
						}else{
							$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
						}
						$acthrs		= '0';
					}
				}
				
				if( !empty( $row['holiday_id'] ) ){ // DETERMINE IF HOLIDAY
					if( (int)getBeforeHoliday($con, $row['work_date'], $row['acct_id']) > 0 ){
						if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
							$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
						}else{
							$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
						}
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
					if( !empty($shiftbrkout) && strtotime( $date_in.' '.$in ) > strtotime( $date_shiftbrkout.' '.$shiftbrkout ) ){ // TIMEIN GREATER THAN BREAKOUT
						$late   = sprintf('%0.2f', (strtotime($date_in.' '.$in) - strtotime($date_shiftbrkout.' '.$shiftbrkout)) / ( 60 * 60 ));
						$absent = sprintf('%0.2f', (strtotime($date_shiftbrkin.' '.$shiftbrkin) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 )); $condi=2;
					}
					if( !empty($shiftbrkin) && !empty($shiftbrkout) && 
						((strtotime( $date_in.' '.$in ) >= strtotime( $date_shiftbrkin.' '.$shiftbrkin ) && strtotime( $date_in.' '.$in ) <= strtotime( $date_shiftbrkout.' '.$shiftbrkout )) || 
						(strtotime( $date_out.' '.$out ) >= strtotime( $date_shiftbrkin.' '.$shiftbrkin ) && strtotime( $date_out.' '.$out ) <= strtotime( $date_shiftbrkout.' '.$shiftbrkout )))  ){ // TIMEIN IN BETWEEN BREAK SCHEDULE
						$late	= '0'; $condi=3;
						if( strtotime( $date_in.' '.$in ) > strtotime( $date_shiftin.' '.$shiftin ) && strtotime( $date_in.' '.$in ) < strtotime( $date_shiftout.' '.$shiftout ) ){ // TIME IN IS GREATER THAN SHIFT IN
							$late   = sprintf('%0.2f', (strtotime($date_in.' '.$in) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 )); $condi=4;
						}						
						$absent = sprintf('%0.2f', (strtotime($date_shiftbrkin.' '.$shiftbrkin) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ));
					}					
					if( strtotime( $date_in.' '.$in ) >  strtotime( $date_shiftout.' '.$shiftout ) ){
						$absent = $reghrs;
						$acthrs	= '0';
						$excess	= '0';
					}
					
					
					
					
					//UNDERTIME
					if( strtotime( $date_shiftout.' '.$shiftout ) > strtotime( $date_out.' '.$out ) ){	// TIME OUT IS LESSER THAN SHIFT OUT
						$ut   	= sprintf('%0.2f', (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_out.' '.$out)) / ( 60 * 60 ));
					}
					if( strtotime($date_out.' '.$out) >= strtotime($date_shiftbrkin.' '.$shiftbrkin) && strtotime($date_out.' '.$out) <= strtotime($date_shiftbrkout.' '.$shiftbrkout)  ){ // TIME OUT BETWEEN BREAK SCHEDULE
						$ut   	= '0';
						
					}	
					if( strtotime( $date_shiftbrkin.' '.$shiftbrkin ) > strtotime( $date_out.' '.$out ) ){	// TIME OUT IS LESSER THAN BREAK IN
						$absent = $reghrs;
						$ut		= '0';
						$late	= '0';
						$acthrs	= '0';
						$regconti=6;
					}
					if( ( strtotime( $date_in.' '.$in ) >= strtotime( $date_shiftbrkin.' '.$shiftbrkin ) && strtotime( $date_in.' '.$in ) <= strtotime( $date_shiftbrkout.' '.$shiftbrkout )) && strtotime($date_out.' '.$out) < strtotime($date_shiftout.' '.$shiftout) ){ // TIME IN BETWEEN BREAK SCHEDULE AND TIME OUT LESS THAN SHIFT SCHED
						$absent = $reghrs;
						$ut		= '0';
						$late	= '0';
						$acthrs	= '0';
						$regconti=7;
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
			}			
			if( !empty($aa_data['id']) ){
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
					$np	= sprintf('%0.2f', ( strtotime( $date_shiftout. ' ' .$shiftout ) - strtotime($date_night_p . ' ' . $nightp)) / ( 60 * 60 ));
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
		$ot_data = getOtApplication( $con, $row['work_date'], $row['acct_id'], $pay_period['pay_start'], date("Y-m-d", strtotime('+'.$pay_period['grace_hour'].' hours', strtotime($pay_period['pay_end'])))  );
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

		$data = array();
		
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
			"date_in"		=>  $date_in,
			"date_out"		=>  $date_out
		);
		
		if( checkTimeSheetExists($con, $idacct, $date ) ){			 
			$return = updateTimeSheetRec($con, $data);			
		}else{
			$return = insertTimeSheetRec($con, $data);
		}
	}
    //ADD TO LOGS


    $Qry           = new Query();
    $Qry->table    = "tblpayperiod";
    $Qry->selected = "tkstatus ='1'";
    $Qry->fields   = "id='". $pay_period['id'] ."'";                        
    $Qry->exe_UPDATE($con);



    
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

?>