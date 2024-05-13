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

$shift_cols = array("monday"	=>"idmon,mon,mon_in,mon_out,mon_brk", 
					"tuesday"	=>"idtue,tue,tue_in,tue_out,tue_brk",
					"wednesday"	=>"idwed,wed,wed_in,wed_out,wed_brk",
					"thursday"	=>"idthu,thu,thu_in,thu_out,thu_brk",
					"friday"	=>"idfri,fri,fri_in,fri_out,fri_brk",
					"saturday"	=>"idsat,sat,sat_in,sat_out,sat_brk", 
					"sunday"	=>"idsun,sun,sun_in,sun_out,sun_brk");

if(!empty($param->accountid)){
	if( $param->accountid ){		
		if( $param->info->otsdate ){
			if( $param->info->otsdate ){
				if( $param->info->otfdate ){
					if( $param->info->start_time ){
						if( $param->info->end_time ){
							$date1	= strtotime( $param->info->otsdate );
							$date2	= strtotime( $param->info->otfdate );
							if( $date1 <= $date2 ){
								$arr_acct		= explode(",",$param->info->emp);
								foreach( $arr_acct  as $kk=>$vv ){
									
									$shift_field	= "".$shift_cols[  strtolower(''.date("l", strtotime( $param->info->otsdate ) )) ];
									$shift_info 	= getDateShiftData( $con, $vv, $shift_field, $param->info->otsdate  );
									if( !empty( hasTimeSheetShift( $con, $param->info->otsdate, $param->accountid ) ) ){
										$shift_info = getShiftIDData($con, hasTimeSheetShift( $con, $param->info->otsdate, $param->accountid ) );
									}
									$shiftin		= $shift_info[2];
									$shiftout		= $shift_info[3];
									
									$shift_fieldNXT = "".$shift_cols[  strtolower(''.date("l", strtotime( $param->info->otfdate ) )) ];
									$shift_infoNXT 	= getDateShiftData( $con, $vv, $shift_fieldNXT, $param->info->otfdate  );
									if( !empty( hasTimeSheetShift( $con, $param->info->otfdate, $param->accountid ) ) ){
									 $shift_infoNXT = getShiftIDData($con, hasTimeSheetShift( $con, $param->info->otfdate, $param->accountid ) );
									}
									$idshiftNXT		= $shift_infoNXT[0];
									$shiftinNXT		= $shift_infoNXT[2];
									$shiftoutNXT	= $shift_infoNXT[3];
									
									
									if( strtotime($param->info->otsdate) != strtotime($date_create) && strtotime($param->info->otsdate) > strtotime($date_create) ){
										$return = json_encode(array('status'=>'err1','msg'=>'You can not apply overtime in advance'  ));
										break;
									}elseif( $date1 != $date2 ){
										if( (int)$idshiftNXT != 4 ){
											if(  strtotime( $param->info->otfdate. ' ' .$param->info->end_time ) >= strtotime( $param->info->otfdate. ' ' .$shiftinNXT ) ){
												$return = json_encode(array('status'=>'err1','msg'=>'Planned OT Time Out is greater than or equal to your next Shift Time In schedule.'  ));
												break;
											}
										}
									}elseif( strtotime( $shiftout ) == strtotime( $param->info->start_time ) || (int)$shift_info[0] == 4 || checkIfHoliday($con,$param->info->otsdate) ){								
										$docnumber		= "OT". $param->accountid .strtotime( $date_create.$time ).$time;	
										$idacct			= $vv;
										$idshift		= $shift_info[0];
										$date			= $param->info->otsdate;
										$sdate			= $param->info->otsdate;
										$fdate			= $param->info->otfdate;
										$stime			= $param->info->start_time;
										$ftime			= $param->info->end_time;	
										$planhrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.$ftime.':00' ) - strtotime( $sdate.' '.$stime.':00' )) / ( 60 * 60 )) );
										if( $planhrs >= 1 && $planhrs <= 16 ){
											$remarks 		= ucwords(strtolower(str_replace("'","",$param->info->remarks)));	
											$Qry 			= new Query();	
											$Qry->table 	= "tbltimeovertime";
                                            $Qry->selected 	= "creator, docnumber,  idacct, idshift,  date, sdate, fdate, stime, ftime, planhrs, date_create";
                                            
											$id_period		= getTimesheetPayPeriods($con, $date);
											if( (int)$id_period	== 0 ){
												$id_period		= getLatePayPeriod($con,$date);
											}
											$Qry->fields 	= "'".$param->accountid."', '".$docnumber."','".$idacct."', '".$idshift."', '".$date."', '".$sdate."', '".$fdate."','".$stime."', '".$ftime."', '".$planhrs."', '".$date_create."'";
											if( !empty( $remarks ) ){
												$Qry->selected 	= $Qry->selected . ", remarks";
												$Qry->fields 	= $Qry->fields 	 . ", '".$remarks."'";
											}
											$reject			= array();
											// if( hasTimelogs($con, $date, $idacct) ){
											// 	$checke 			= $Qry->exe_INSERT($con);
											// }else{
											// 	$reject[]	= array(
											// 		"date"	=> $date,
											// 		"msg"	=> "No Time logs exists."
											// 	);
                                            // }
                                            
                                            $checke 			= $Qry->exe_INSERT($con);

											$return = json_encode( array('status'=>'success','reject'=>$reject) );
										}else{
											$return = json_encode(array('status'=>'plantime','msg'=>'Planned OT minimum hour is 1 and maximum hour is 16.'  ));
										}
									}else{
										$return = json_encode(array('status'=>'invtime','msg'=>'Start Time should be ' . date("h:i:s a",strtotime($shiftout)), 'out'=>$shiftout,  'os'=>$param->info->start_time ));
									}
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

function hasLeave($con, $date, $idacct){
	$Qry = new Query();	
	$Qry->table ="tbltimeleaves";
	$Qry->selected ="*";
	$Qry->fields =" idacct='".$idacct."' AND date='".$date."' AND stat in ('1','3')  AND cancelby is null";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){
		return true;
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

function hasTimelogs($con, $date, $idacct){
	$Qry = new Query();	
	$Qry->table ="vw_data_timesheet AS a";
	$Qry->selected ="id";
	$Qry->fields =" a.idacct='".$idacct."' AND a.work_date = '".$date."' AND ( ( a.in IS NOT NULL AND a.out IS NOT NULL AND a.in<>'' AND a.out<>'' ) || idshift='4' || (holiday_id IS NOT NULL || holiday_id<>'') )";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){
		return true;
	}
	return false;
}

function getTimesheetPayPeriods( $con, $date ){
    $data = array();	
    $Qry = new Query();	
    $Qry->table     = "tbltimesheet";
    $Qry->selected  = "id_payperiod";
    $Qry->fields    = "date='".$date."' ORDER BY id ASC limit 1";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
           
            $data = array( 
                "id"        => $row['id_payperiod']
            );

            return $data;
        }
    }
    return 0;
}

?>
