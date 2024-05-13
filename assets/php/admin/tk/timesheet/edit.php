<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$data = array();
$pay_period = getPayPeriod($con);

if( !empty( $param->accountid ) ){
	if( !empty( $param->info->idshift ) ){		
		if( !empty( $param->info->date_in ) ){
			if( !empty( $param->info->date_out ) ){
				if( empty( $param->info->in ) && (int)$param->info->idshift != 4 ){
					$return = json_encode(array('status'=>'in'));
					print $return;	
					mysqli_close($con);
					return;
				}
				if( empty( $param->info->out ) && (int)$param->info->idshift != 4 ){
					$return = json_encode(array('status'=>'out'));
					print $return;	
					mysqli_close($con);
					return;
				}
				if( (int)$param->info->idshift != 4 && (int)$param->info->orig_idshift == 4 ){
					$return = json_encode(array('status'=>'invduty','reason'=>'Could not change because ORIGINAL SHIFT IS RD'));
					print $return;	
					mysqli_close($con);
					return;
				}
				
				if( (int)$param->info->idshift != 4 && strtotime( $param->info->date_out.' '.$param->info->out ) > strtotime( $param->info->date_in.' '.$param->info->in ) ){
					$period_end   = date("Y-m-d",strtotime('+'.$pay_period['grace_hour'].' hours', strtotime( $pay_period['pay_end'] ))) ;
					if( strtotime($param->info->work_date)  >= strtotime($pay_period['pay_start'])  && strtotime($param->info->work_date) <= strtotime($period_end) ){
						//NEED TO CANCEL LEAVE
						if( checkApplicationTimelog( $con, $param->info->work_date, $param->info->idacct, "vw_leave_application", " AND stat in (1,3) " )  ){
							$return = json_encode(array('status'=>'val-err6'));
							print $return;	
							mysqli_close($con);
							return;
						}
						//NEED TO CANCEL AA
						if( checkApplicationTimelog( $con, $param->info->work_date, $param->info->idacct, "vw_attendance_application", " AND stat in (1,3) " )  ){
							$return = json_encode(array('status'=>'val-err7'));
							print $return;	
							mysqli_close($con);
							return;
						}
						//NEED TO CANCEL OT APPLICATION
						if( checkApplicationTimelog( $con, $param->info->work_date, $param->info->idacct, "vw_overtime_application", " AND stat in (1,3) " )  ){
							$return = json_encode(array('status'=>'val-err8'));
							print $return;	
							mysqli_close($con);
							return;
						}
						
						$shift_info 		= getShiftIDData( $con, $param->info->idshift );
						$idshift			= $shift_info[0];
						$shiftin			= $shift_info[2];
						$shiftout			= $shift_info[3];
						$shiftbrkin			= $shift_info[4];
						$shiftbrkout		= $shift_info[5];
						$date_shiftin 		= $param->info->work_date;
						$date_shiftout 		= $param->info->work_date;
						$date_shiftbrkin	= $param->info->work_date;
						$date_shiftbrkout	= $param->info->work_date;
						
						
						if( strtotime( $date_shiftin.' '.$shiftin ) > strtotime( $date_shiftout.' '.$shiftout ) ){
							$date_shiftout 	= date('Y-m-d', strtotime("+1 day", strtotime($date_shiftout)));
						}
						if( strtotime( $date_shiftbrkin.' '.$shiftbrkin ) > strtotime( $date_shiftbrkout.' '.$shiftbrkout ) ){
							$date_shiftbrkout 	= date('Y-m-d', strtotime("+1 day", strtotime( $date_out )));
						}
						
						$shift_info2 		= getShiftIDData( $con, $param->info->orig_idshift );
						$date_shiftout2 	= $param->info->work_date;
						if( strtotime( $date_shiftin.' '.$shift_info2[2] ) > strtotime( $date_shiftin.' '.$shift_info2[3] ) ){
							$date_shiftout2 	= date('Y-m-d', strtotime("+1 day", strtotime($date_shiftout2)));
						}
						
						if( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) != (strtotime($date_shiftout2.' '.$shift_info2[3]) - strtotime($date_shiftin.' '.$shift_info2[2])) / ( 60 * 60 ) ){
							$return = json_encode(array('status'=>'invduty'));
							print $return;	
							mysqli_close($con);
							return;
						}
						
						//NEED TO CHANGE SHIFT
						$date_in 			= $param->info->date_in;
						$date_out 			= $param->info->date_out;								
						$in  				= $param->info->in;
						$out 				= $param->info->out;
						if( (!empty( $shiftbrkin ) && (( (strtotime($date_out.' '.$out) - strtotime($date_in.' '.$in)) / ( 60 * 60 ) ) >= 8) && ( strtotime($date_in.' '.$in) >= strtotime($date_shiftout.' '.$shiftout) || strtotime($date_out.' '.$out) <= strtotime( $date_shiftin.' '.$shiftin ))) ||
							(empty( $shiftbrkin ) && (( (strtotime($date_out.' '.$out) - strtotime($date_in.' '.$in)) / ( 60 * 60 ) ) >= 4) && ( strtotime($date_in.' '.$in) >= strtotime($date_shiftout.' '.$shiftout) || strtotime($date_out.' '.$out) <= strtotime( $date_shiftin.' '.$shiftin )))   ){
							$return = json_encode(array('status'=>'invduty'));
							print $return;	
							mysqli_close($con);
							return;
						}
						
						//REMOVE PAIRING FOR WORKDATE IN TIMELOGS								
						$QryPair2 				= new Query();	
						$QryPair2->table    	= "tbltimelogs ";
						$QryPair2->selected 	= "date_pair=NULL, ispaired=0";
						$QryPair2->fields   	= "acct_id = '".$param->info->idacct."' AND date_pair='".$param->info->work_date."' ";								
						$rsPair2				= $QryPair2->exe_UPDATE($con);
						
						//INSERT IN tbltimelogs								
						$arr_data = array( 
										array($param->info->idacct,$param->info->date_in, $param->info->in,'C/I',  $param->info->work_date),
										array($param->info->idacct,$param->info->date_out,$param->info->out,'C/O', $param->info->work_date)
						);
						$ret = insertToTimeLogs($con, $arr_data[0]);
						$ret = insertToTimeLogs($con, $arr_data[1]);
						
						//process
						$Qry = new Query();	
						$Qry->table     = "vw_datacurrentworkdates2 as a LEFT JOIN vw_dataholidays AS b ON a.work_date = b.date";
						$Qry->selected  = "a.work_date, a.id AS acct_id, b.id AS holiday_id";
						$Qry->fields    = "a.work_date = '".$param->info->work_date."' AND a.id='".$param->info->idacct."' ORDER BY a.work_date ASC";
						$rs = $Qry->exe_SELECT($con);
						if(mysqli_num_rows($rs)>= 1){
							while($row=mysqli_fetch_array($rs)){										
								
								$idacct 	= $row['acct_id'];
								$day		= date("l", strtotime($row['work_date']));
								$date		= $row['work_date'];

								$shift_info = getShiftIDData( $con, $param->info->idshift );
								$idshift	= $shift_info[0];
								$shiftin	= $shift_info[2];
								$shiftout	= $shift_info[3];
								$shiftbrkin	= $shift_info[4];
								$shiftbrkout= $shift_info[5];
								$date_in 	= $param->info->date_in;
								$date_out 	= $param->info->date_out;
								
								$date_shiftin 	= $param->info->date_in;
								$date_shiftout 	= $param->info->date_out;
								$date_shiftbrkin= $param->info->work_date;
								$date_shiftbrkout= $param->info->work_date;
								
								
								if( strtotime( $date_shiftin.' '.$shiftin ) > strtotime( $date_shiftout.' '.$shiftout ) ){
									$date_shiftout 	= date('Y-m-d', strtotime("+1 day", strtotime( $param->info->date_out )));
								}
								if( strtotime( $date_shiftbrkin.' '.$shiftbrkin ) > strtotime( $date_shiftbrkout.' '.$shiftbrkout ) ){
									$date_shiftbrkout 	= date('Y-m-d', strtotime("+1 day", strtotime( $param->info->date_out )));
								}
								
								$in			= '';
								$out		= '';										
								
								$regconti=0;
								
								if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
									$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
								}else{
									$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin))) / ( 60 * 60 ));											
								}
								
								//echo $date_shiftbrkout.' '.$shiftbrkout.' | '.$date_shiftbrkin.' '.$shiftbrkin.' | '.$reghrs; return;
								
								if( strtotime($date_shiftout.' '.$shiftout) < strtotime($date_shiftin.' '.$shiftin) ){
									//$date_out = date('Y-m-d', strtotime("+1 day", strtotime( $row['work_date']  )));
									$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin))) / ( 60 * 60 ));
									
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
										
									}else{
										$absent		= $leave_data['hrs'];
										$acthrs		= '0';
										$reghrs		= '0';				
									}
								}
								if( !empty( $row['holiday_id'] ) ){ // DETERMINE IF HOLIDAY
									if( (int)getBeforeHoliday($con, $row['work_date'], $row['acct_id']) > 0 ){				
										$reghrs		= '0';
										if( ((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin .' '. $shiftin)) / ( 60 * 60 ) ) >= 9 ){
											$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
										}else{
											$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
										}
									}else{
										if( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) >= 9 ){
											$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
											
										}else{
											$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
											
										}
										$absent		= '0';
									}
								}
								
								$Qry2 = new Query();	
								$Qry2->table     = "vw_datatimelogs";
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
												if( ((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)/ ( 60 * 60 ) )) >= 9 ){
													$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
												}else{
													$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
												}
												$acthrs		= '0';
											}
										}
										
										if( !empty( $row['holiday_id'] ) ){ // DETERMINE IF HOLIDAY
											if( (int)getBeforeHoliday($con, $row['work_date'], $row['acct_id']) > 0 ){
												if(((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)/ ( 60 * 60 ) )) >= 9 ){
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
												$absent = sprintf('%0.2f', (strtotime($date_shiftin.' '.$shiftbrkin) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 )); $regconti=2;
											}
											if( !empty($shiftbrkin) && !empty($shiftbrkout) && 
												((strtotime( $date_in.' '.$in ) >= strtotime( $date_shiftbrkin.' '.$shiftbrkin ) && strtotime( $date_in.' '.$in ) <= strtotime( $date_shiftbrkout.' '.$shiftbrkout )) || 
												(strtotime( $date_out.' '.$out ) >= strtotime( $date_shiftbrkin.' '.$shiftbrkin ) && strtotime( $date_out.' '.$out ) <= strtotime( $date_shiftbrkout.' '.$shiftbrkout )))  ){ // TIMEIN IN BETWEEN BREAK SCHEDULE
												$late	= '0'; $condi=3;
												if( strtotime( $date_in.' '.$in ) > strtotime( $date_shiftin.' '.$shiftin ) && strtotime( $date_in.' '.$in ) < strtotime( $date_shiftout.' '.$shiftout ) ){ // TIME IN IS GREATER THAN SHIFT IN
													$late   = sprintf('%0.2f', (strtotime($date_in.' '.$in) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 )); $condi=4;
												}						
												$absent = sprintf('%0.2f', (strtotime($date_shiftbrkin.' '.$shiftbrkin) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ));$regconti=3;
											}
											
											if( strtotime( $date_in.' '.$in ) >  strtotime( $date_shiftout.' '.$shiftout ) ){
												$absent = $reghrs;
												$acthrs	= '0';
												$excess	= '0';
												$regconti=4;
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
												$regconti=5;
											}
											if( ( strtotime( $date_in.' '.$in ) >= strtotime( $date_shiftbrkin.' '.$shiftbrkin ) && strtotime( $date_in.' '.$in ) <= strtotime( $date_shiftbrkout.' '.$shiftbrkout )) && strtotime($date_out.' '.$out) < strtotime($date_shiftout.' '.$shiftout) ){ // TIME IN BETWEEN BREAK SCHEDULE AND TIME OUT LESS THAN SHIFT SCHED
												$absent = $reghrs;
												$ut		= '0';
												$late	= '0';
												$acthrs	= '0';
												$regconti=6;
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
								$np	= 0;
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
							$return = inserLogs($con, $param->accountid, "Updated Timesheet of ".getEmployeeName($con, $idacct)." on ".$date);
							$return = json_encode(array('status'=>'success','test'=>$date_out. ' ' .$out));
						}else{
							$return = json_encode(array('status'=>'error'));
						}
					}else{
						$return = json_encode(array('status'=>'notcovered','period'=>$pay_period));
					}
				}elseif( (int)$param->info->idshift == 4 ){
					if( !empty( $param->info->in ) ){
						$return = json_encode(array('status'=>'in'));
						print $return;	
						mysqli_close($con);
						return;
					}
					if( !empty( $param->info->out ) ){
						$return = json_encode(array('status'=>'out'));
						print $return;	
						mysqli_close($con);
						return;
					}					
					if( $param->info->date_in != $param->info->date_out ){
						$return = json_encode(array('status'=>'invaliddate'));
						print $return;	
						mysqli_close($con);
						return;
					}
					if( !empty($param->info->orig_timein) || !empty($param->info->orig_timeout) ){
						$return = json_encode(array('status'=>'invduty'));
						print $return;	
						mysqli_close($con);
						return;
					}
					$reghrs	= '0';
					$acthrs	= '0';
					$excess	= '0';
					$absent	= '0';
					$late	= '0';
					$ut		= '0';
					$np 	= '0';
					$ot		= '0';
					$idacct = $param->info->idacct;
					$day	= date("l", strtotime($param->info->work_date));
					$date	= $param->info->work_date;
					$data = array();
					$data = array(
						"idacct" 		=>	$idacct, 	
						"day"			=>	$day,		
						"date"			=>	$date,		
						"idshift"		=>	$param->info->idshift,	
						"shiftin"		=>	'',	
						"shiftout"		=>	'',	
						"in"			=>	'',			
						"out"			=>	'',				
						"reghrs"		=>	sprintf('%0.2f',$reghrs),		
						"acthrs"		=>	sprintf('%0.2f',$acthrs),		
						"excess"		=>	sprintf('%0.2f',$excess),		
						"absent"		=>	sprintf('%0.2f',$absent),
						"late"			=>  sprintf('%0.2f',$late),
						"ut"			=>  sprintf('%0.2f',$ut),
						"ot"			=>  sprintf('%0.2f',$ot),
						"np"			=>  sprintf('%0.2f',$np),
						"id_payperiod"  =>  $pay_period['id'],
						"date_in"		=>  $param->info->work_date,
						"date_out"		=>  $param->info->work_date
					);
					
					if( checkTimeSheetExists($con, $idacct, $date ) ){			 
						$return = updateTimeSheetRec($con, $data);			
					}else{
						$return = insertTimeSheetRec($con, $data);
					}
					$return = json_encode(array('status'=>'success'));
				}else{
					$return = json_encode(array('status'=>'invaliddate'));
				}	
			}else{
				$return = json_encode(array('status'=>'date_out'));
			}					
		}else{
			$return = json_encode(array('status'=>'date_in'));
		}
	}else{
		$return = json_encode(array('status'=>'idshift'));
	}
}else{
	$return = json_encode(array('status'=>'notloggedin'));
}
        

print $return;
mysqli_close($con);

function insertToTimeLogs($con, $arr_data){	
	$txt_acct = trim($arr_data[0]);
    $txt_date = trim($arr_data[1]);
    $txt_time = trim($arr_data[2]);
    $txt_type = trim($arr_data[3]);
	$txt_pair = trim($arr_data[4]);
	
	$Qry3           = new Query();
	$Qry3->table    = "tbltimelogs";
	$Qry3->selected = "acct_id, work_date, work_time, time_type, date_pair, ispaired";
	$Qry3->fields   = "'".$txt_acct."',
					   '".$txt_date."',
					   '".$txt_time."',
					   '".$txt_type."',
					   '".$txt_pair."',
					   '1'";					   
			   
	$checke = $Qry3->exe_INSERT($con);
	if($checke){
		return  json_encode(array("status"=>"success"));
	}else{
		return  json_encode(array('status'=>'error',"err"=>mysqli_error($con)));
	}
}


?>