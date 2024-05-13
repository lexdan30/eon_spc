<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date_create=SysDate();
$time_create=SysTime();
$time 	   = time();
$return = null;

$shift_cols = array("monday"	=>"idmon,mon,mon_in,mon_out,mon_brkin,mon_brkout", 
					"tuesday"	=>"idtue,tue,tue_in,tue_out,tue_brkin,tue_brkout",
					"wednesday"	=>"idwed,wed,wed_in,wed_out,wed_brkin,wed_brkout",
					"thursday"	=>"idthu,thu,thu_in,thu_out,thu_brkin,thu_brkout",
					"friday"	=>"idfri,fri,fri_in,fri_out,fri_brkin,fri_brkout",
					"saturday"	=>"idsat,sat,sat_in,sat_out,sat_brkin,sat_brkout", 
					"sunday"	=>"idsun,sun,sun_in,sun_out,sun_brkin,sun_brkout");

if(!empty($param->accountid)){
	if( $param->info->acct ){		
		if( $param->info->otdate ){
			if( $param->info->otsdate ){
				if( $param->info->otfdate ){
					if( $param->info->start_time ){
						if( $param->info->end_time ){
							$date1	= strtotime( $param->info->otsdate );
							$date2	= strtotime( $param->info->otfdate );
							if( $date1 <= $date2 ){
								
								$shift_field	= "".$shift_cols[  strtolower(''.date("l", strtotime( $param->info->otdate ) )) ];
								$shift_info 	= getDateShiftData( $con, $param->info->acct, $shift_field, $param->info->otdate  );
								$shiftin		= $shift_info[2];
								$shiftout		= $shift_info[3];
								
								$shift_fieldNXT = "".$shift_cols[  strtolower(''.date("l", strtotime( $param->info->otfdate ) )) ];
								$shift_infoNXT 	= getDateShiftData( $con, $param->info->acct, $shift_fieldNXT, $param->info->otfdate  );
								$idshiftNXT		= $shift_infoNXT[0];
								$shiftinNXT		= $shift_infoNXT[2];
								$shiftoutNXT	= $shift_infoNXT[3];
								
								
								if( strtotime($param->info->otdate) != strtotime($date_create) && strtotime($param->info->otdate) > strtotime($date_create) ){
									$return = json_encode(array('status'=>'err1','msg'=>'You can not apply overtime in advance'  ));
									print $return;
									mysqli_close($con);
									return;
								}
								
								
								if( $date1 != $date2 ){
									if( (int)$idshiftNXT != 4 ){
										if(  strtotime( $param->info->otfdate. ' ' .$param->info->end_time ) >= strtotime( $param->info->otfdate. ' ' .$shiftinNXT ) ){
											$return = json_encode(array('status'=>'err1','msg'=>'Planned OT Time Out is greater than or equal to your next Shift Time In schedule.'  ));
											print $return;
											mysqli_close($con);
											return;
										}
									}
								}
								
								
								
								if( strtotime( $shiftout ) == strtotime( $param->info->start_time ) || (int)$shift_info[0] == 4 ){								
									$docnumber		= "OT". $param->info->acct .strtotime( $date_create.$time ).$time;	
									$idacct			= $param->info->acct;
									$idshift		= $shift_info[0];
									$date			= $param->info->otdate;
									$sdate			= $param->info->otsdate;
									$fdate			= $param->info->otfdate;
									$stime			= $param->info->start_time;
									$ftime			= $param->info->end_time;	
									$planhrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.$ftime.':00' ) - strtotime( $sdate.' '.$stime.':00' )) / ( 60 * 60 )) );
									if( $planhrs >= 1 && $planhrs <= 16 ){
										$remarks 		= ucwords(strtolower(str_replace("'","",$param->info->remarks)));	
										$Qry 			= new Query();	
										$Qry->table 	= "tbltimeovertime";
										$Qry->selected 	= "creator, docnumber,  idacct, idshift,  date, sdate, fdate, stime, ftime, planhrs, date_create, id_payperiod";
										$id_period		= getTimesheetPayPeriod($con, $date);
										if( (int)$id_period	== 0 ){
											$id_period		= getLatePayPeriod($con,$date);
										}
										$Qry->fields 	= "'".$param->accountid."', '".$docnumber."','".$idacct."', '".$idshift."', '".$date."', '".$sdate."', '".$fdate."','".$stime."', '".$ftime."', '".$planhrs."', '".$date_create."', '".$id_period['id']."'";
										if( !empty( $remarks ) ){
											$Qry->selected 	= $Qry->selected . ", remarks";
											$Qry->fields 	= $Qry->fields 	 . ", '".$remarks."'";
										}
										$checke 			= $Qry->exe_INSERT($con);
										if($checke){										
											// AUTO EMAIL ??
											$return = json_encode( array('status'=>'success') );
										}else{
											$return = json_encode(array('status'=>'error'));
										}
									}else{
										$return = json_encode(array('status'=>'plantime','msg'=>'Planned OT minimum hour is 1 and maximum hour is 16.'  ));
									}
								}else{
									$return = json_encode(array('status'=>'invtime','msg'=>'Start Time should be ' . date("h:i:s a",strtotime($shiftout)), 'out'=>$shiftout,  'os'=>$param->info->start_time ));
								}
							}else{
								$return = json_encode(array('status'=>'invdate'));
							}
						}else{
							$return = json_encode(array('status'=>'endtime'));
						}
					}else{
						$return = json_encode(array('status'=>'starttime'));
					}
				}else{
					$return = json_encode(array('status'=>'enddate'));
				}
			}else{
				$return = json_encode(array('status'=>'startdate'));
			}
		}else{
			$return = json_encode(array('status'=>'shiftdate'));
		}		
	}else{
		$return = json_encode(array('status'=>'acct'));
	}
}else{
	$return = json_encode(array('status'=>'notloggedin'));
}


print $return;
mysqli_close($con);

function hasTimelogs($con, $date, $idacct){
	$Qry = new Query();	
	$Qry->table ="vw_data_timesheet AS a";
	$Qry->selected ="id";
	$Qry->fields =" a.idacct='".$idacct."' AND a.work_date = '".$date."' AND ( a.in IS NOT NULL AND a.out IS NOT NULL AND a.in<>'' AND a.out<>'' )";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){
		return true;
	}
	return false;
}

function adjustmentExists($con, $date, $idacct, $idtimeleavetype){
	$Qry = new Query();	
	$Qry->table ="tbltimeadjustment";
	$Qry->selected ="*";
	$Qry->fields =" idacct='".$idacct."' AND date='".$date."'  AND stat in ('1','3') ";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){		
		while($row=mysqli_fetch_array($rs)){
			if( $row['idtimetype'] == $idtimeleavetype ){
				return true;
			}elseif( (int)$row['idtimetype'] == 1 && ((int)$idtimeleavetype==2 || (int)$idtimeleavetype==3) ){
				return true;
			}elseif( (int)$idtimeleavetype == 1 && ((int)$row['idtimetype']==2 || (int)$row['idtimetype']==3) ){
				return true;
			}
		}
	}
	return false;
}

function hasLeave($con, $date, $idacct, $idtimeleavetype){
	$Qry = new Query();	
	$Qry->table ="tbltimeleaves";
	$Qry->selected ="*";
	$Qry->fields =" idacct='".$idacct."' AND date='".$date."' AND stat in ('1','3') ";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){
		while($row=mysqli_fetch_array($rs)){
			if( (int)$row['idtimeleavetype'] == 1 ){
				return true;
			}elseif( (int)$row['idtimeleavetype'] <= $idtimeleavetype ){
				return true;
			}elseif( (int)$row['idtimeleavetype'] == 3 && ( (int)$idtimeleavetype == 1 || (int)$idtimeleavetype == 3 ) ){
				return true;
			}
		}
	}
	return false;
}

function updateData($con,$folder_name,$lastID){	
	$Qry = new Query();	
	$Qry->table ="tbltimeadjustment";	
	$Qry->selected ="file='".$folder_name."'";
	$Qry->fields ="id='".$lastID."'";
	return $Qry->exe_UPDATE($con);
}

function getLastID($con, $ticket ){
	$return='';
	$Qry = new Query();	
	$Qry->table ="tbltimeadjustment";
	$Qry->selected ="id";
	$Qry->fields ="docnumber='".$ticket."' ORDER BY id DESC LIMIT 1";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){
		if($row=mysqli_fetch_array($rs)){
			$return = $row['id'];
		}
	}else{
		$return = '';
	}
	return $return;
}
?>