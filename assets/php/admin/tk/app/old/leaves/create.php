<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$date_create=SysDate();
$time_create=SysTime();
$return = null;

if(!empty($param['accountid'])){
	if( $param['info']['acct'] ){
		if( $param['info']['idleave'] ){
			if( $param['info']['datefrom'] ){
				if( $param['info']['dateto'] ){
					$date1	= strtotime( $param['info']['datefrom'] );
					$date2	= strtotime( $param['info']['dateto'] );
					if( $date1 <= $date2 ){

						if( array_key_exists('file',$_FILES) ){
							$valid_formats = array("jpg", "png", "jpeg", "pdf");	
							foreach ($_FILES['file']['name'] as $f => $name) { 
								if ($_FILES['file']['error'][$f] == 4) {
									$return = json_encode(array('status'=>'error','on'=>'img_check'));
									print $return;	
									mysql_close($con);
									return;
								}
								if ($_FILES['file']['error'][$f] == 0) {
									if(!in_array(pathinfo(strtolower($name), PATHINFO_EXTENSION), $valid_formats) ){
										$return = json_encode(array('status'=>'error-upload-type'));
										print $return;	
										mysql_close($con);
										return;
									}
								}
							}
						}
						
						$idleave		= $param['info']['idleave'];
						$idacct			= $param['info']['acct'];
						$remarks 		= ucwords(strtolower(str_replace("'","",$param['info']['remarks'])));
						$total_units	= 0;
						
						
						
						$reject			= array();
						$ctr=1;
						foreach( $param['info']['leave_dates']  as $keys=>$value ){
							$idshift		= $value['idshift'];
							$idtimeleavetype= $value['val'];	
							$date			= $value['date'];
							
							
							if( leaveExists($con, $date, $idacct, $idtimeleavetype) ){
								$reject[]	= array(
									"date"	=> $date,
									"msg"	=> "Already has leave application for this date."
								);
							}
							// elseif( hasTimelogs($con, $date, $idacct) ){
							// 	$reject[]	= array(
							// 		"date"	=> $date,
							// 		"msg"	=> "Time logs exists."
							// 	);
							// }
							elseif( hasAdjustment($con, $date, $idacct, $idtimeleavetype) ){
								$reject[]	= array(
									"date"	=> $date,
									"msg"	=> "Applied Attendance Adjustment."
								);
							}elseif( (int)$value['hrs'] > 0 ){
								$leave_balance  = getLeaveBalance($con, $param['info']['acct'], $param['info']['idleave']);
								$pending_bal	= getPendingBalance($con, $idleave , $idacct);								
								$hrs			= (int)$value['hrs'];
								if( $hrs <= $leave_balance ){
									$total_units 	= $total_units + (int)$value['hrs'];
									$shifttime = getShiftTime( $con, $idshift, $idtimeleavetype );
									$stime	   = $shifttime['stime'];
									$ftime	   = $shifttime['ftime'];
									$time 	   = time();
									$docnumber = "LV".$param['info']['acct'].strtotime( $date_create.$time ).$time.$ctr;
									$ctr++;
									$Qry 			= new Query();	
									$Qry->table 	= "tbltimeleaves";
									$Qry->selected 	= "creator, docnumber, idleave, idacct, idshift, idtimeleavetype, date, stime, ftime, hrs, date_create, id_payperiod";
									
									$id_period		= getTimesheetPayPeriod($con, $date);
									if( (int)$id_period	== 0 ){
										$id_period		= getLatePayPeriod($con,$date);
									}
									$Qry->fields 	= "'".$param['accountid']."', '".$docnumber."','".$idleave."', '".$idacct."', '".$idshift."', '".$idtimeleavetype."', '".$date."', '".$stime."', '".$ftime."', '".$hrs."', '".$date_create."', '".$id_period['id']."'";
									if( !empty( $remarks ) ){
										$Qry->selected 	= $Qry->selected . ", remarks";
										$Qry->fields 	= $Qry->fields 	 . ", '".$remarks."'";
									}
									$checke 			= $Qry->exe_INSERT($con);
									if($checke){										
										$lastID = getLastID($con,$docnumber);
										if( array_key_exists('file',$_FILES) ){
											$folder_path = $param['targetPath'].'req-'.$lastID;
											$folder_name = 'req-'.$lastID;
											if( !is_dir($folder_path) ){
												mkdir($folder_path);				
											}
											$check = updateData($con,$folder_name,$lastID);
											if( $check ){																	
												$fileCtr=1;
												foreach ($_FILES['file']['name'] as $f => $name) {
													$t=strtotime($date_create).time();	
													$extMove = pathinfo($name, PATHINFO_EXTENSION);
													$save_name	= $fileCtr.'-'.$t.'.'.$extMove;	
													move_uploaded_file($_FILES["file"]["tmp_name"][$f], $folder_path.'/'.$save_name);
													$fileCtr++;
												}																	
											}										
										}
										$pending_bal = $pending_bal + $hrs;
										$return = updatePendingBal($con, $idleave , $idacct, $pending_bal);
									}
								}else{
									$reject[]	= array(
										"date"	=> $date,
										"msg"	=> "No more leave balance. LB=>'".$leave_balance."' PB=>'".$pending_bal."' CH=>'".$hrs."' TOTAL=>'".$total_units."'"
									);
								}
							}
						}
						
						// AUTO EMAIL ??
						
						$return = json_encode( array('status'=>'success','reject'=>$reject) );
						
					}else{
						$return = json_encode(array('status'=>'invdate'));
					}
				}else{
					$return = json_encode(array('status'=>'dateto'));
				}
			}else{
				$return = json_encode(array('status'=>'datefrom'));
			}
		}else{
			$return = json_encode(array('status'=>'idleave'));
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

function leaveExists($con, $date, $idacct, $idtimeleavetype){
	$Qry = new Query();	
	$Qry->table ="tbltimeleaves";
	$Qry->selected ="*";
	$Qry->fields =" idacct='".$idacct."' AND date='".$date."'  AND stat in ('1','3') ";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){		
		while($row=mysqli_fetch_array($rs)){
			if( $row['idtimeleavetype'] == $idtimeleavetype ){
				return true;
			}elseif( (int)$row['idtimeleavetype'] == 1 && ((int)$idtimeleavetype==2 || (int)$idtimeleavetype==3) ){
				return true;
			}elseif( (int)$idtimeleavetype == 1 && ((int)$row['idtimeleavetype']==2 || (int)$row['idtimeleavetype']==3) ){
				return true;
			}
		}
	}
	return false;
}

function hasAdjustment($con, $date, $idacct, $idtimeleavetype){
	$Qry = new Query();	
	$Qry->table ="tbltimeadjustment";
	$Qry->selected ="*";
	$Qry->fields =" idacct='".$idacct."' AND date='".$date."' AND stat in ('1','3') ";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){
		while($row=mysqli_fetch_array($rs)){
			if( (int)$row['idtimetype'] == 1 ){
				return true;
			}elseif( (int)$row['idtimetype'] <= $idtimeleavetype ){
				return true;
			}elseif( (int)$row['idtimetype'] == 3 && ( (int)$idtimeleavetype == 1 || (int)$idtimeleavetype == 3 ) ){
				return true;
			}
		}
	}
	return false;
}

function updatePendingBal($con, $idleave , $idacct, $pending_bal){
	$Qry 			= new Query();	
	$Qry->table 	= "tblaccountleaves";	
	$Qry->selected 	= "pending_bal='".$pending_bal."'";
	$Qry->fields   	= "idacct='".$idacct."' AND idleave='".$idleave."'";
	$ch = $Qry->exe_UPDATE($con);
	if( $ch ){
		return json_encode(array('status'=>'success'));
	}else{
		return json_encode(array('status'=>'error','on'=>'pendingBal update'));
	}
}

function getPendingBalance($con, $idleave , $idacct){
	$Qry = new Query();	
	$Qry->table     = "tblaccountleaves";
	$Qry->selected  = "pending_bal";
	$Qry->fields    = "idacct='".$idacct."' AND idleave='".$idleave."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			return $row['pending_bal'];
		}
	}
	return 0;
}

function updateData($con,$folder_name,$lastID){	
	$Qry = new Query();	
	$Qry->table ="tbltimeleaves";	
	$Qry->selected ="file='".$folder_name."'";
	$Qry->fields ="id='".$lastID."'";
	return $Qry->exe_UPDATE($con);
}

function getLastID($con, $ticket ){
	$return='';
	$Qry = new Query();	
	$Qry->table ="tbltimeleaves";
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