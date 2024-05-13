<?php 
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 
require_once('../../../email/emailFunction.php');
$param = json_decode(file_get_contents('php://input'));
if( !empty($param->accountid)){
$date_approve =  date("Y-m-d",strtotime(SysDate()));

if(!empty($param->accountid)){
			
	if( (int)$param->info->stat == 2 ){
		if( empty( strtoupper(strtolower(str_replace("'","",$param->info->reason))) ) ){
			$return = json_encode(array("status"=>"noreason"));
			print $return;
			mysqli_close($con);
			return;
		}
	}		
	
	$idacct	 = $param->accountid;
	$arr_err = array();
	foreach( $param->info->data as $ka => $va ){
		
		$Qry2 			= new Query();	
		$Qry2->table    = "vw_appmatrix";
		$Qry2->selected = "*";
		$Qry2->fields   = " idunit = '".$va->idunit."' " ;
		$rs2 			= $Qry2->exe_SELECT($con);
		if( mysqli_num_rows($rs2) > 0 ){
			if($row=mysqli_fetch_array($rs2)){ 
				if( (($row['approver_1b'] == $idacct || $row['approver_1c'] == $idacct) && $va->idacct == $idacct) || 
					(( $row['approver_1b'] == $va->idacct || $row['approver_1c'] == $va->idacct ) && $va->idsuperior != $idacct ) ){
					$arr_err[] = array(
						"id"	=> $va->id,
						"date"	=> $va->date,
						"name"	=> $va->empname,
						"err"	=> "You can not approve this request"
					);
				}elseif(( $row['approver_1b'] == $idacct || $row['approver_1c'] == $idacct || $va->idsuperior == $idacct || $row['approver1_head'] == $idacct) && isApprover1Empty($con,$va->id)  ){
					$Qry3           = new Query();
					$Qry3->table    = "tbltimeadjustment";
					$Qry3->selected = "
									   approver1='".$param->accountid."',
									   approver1_stat='".$param->info->stat."',
									   approver1_date='".$date_approve."',
									   approver1_time='".SysTime()."'";
					if( (int)$param->info->stat == 2 ){
						$Qry3->selected = $Qry3->selected . ",stat= '".$param->info->stat."'";
						$Qry3->selected = $Qry3->selected . ",approver1_reason= '".strtoupper(strtolower(str_replace("'","",$param->info->reason)))."' ";
					}else{ 
							if($row['ctr_approver'] == 1){
								$Qry3->selected = $Qry3->selected . ",stat= '".$param->info->stat."'";
								$Qry3->selected = $Qry3->selected . ",date_approve= '".$date_approve."'";
							}elseif(empty($row['approver_2a']) && empty($row['approver_2b']) && empty($row['approver_2c'])){
								$Qry3->selected = $Qry3->selected . ",stat= '".$param->info->stat."'";
								$Qry3->selected = $Qry3->selected . ",date_approve= '".$date_approve."'";
							}elseif($row['approver_2a'] == $idacct){
								$Qry3->selected = $Qry3->selected . ",approver2= '".$param->accountid."'";
								$Qry3->selected = $Qry3->selected . ",approver2_stat= '".$param->info->stat."'";
								$Qry3->selected = $Qry3->selected . ",approver2_date= '".$date_approve."'";
								$Qry3->selected = $Qry3->selected . ",approver2_time= '".SysTime()."'";

								if($row['ctr_approver'] == 2){
									$Qry3->selected = $Qry3->selected . ",stat= '".$param->info->stat."'";
									$Qry3->selected = $Qry3->selected . ",date_approve= '".$date_approve."'";
								}
							}
					}

					$Qry3->selected = str_replace("\r\n",'', $Qry3->selected);
					$Qry3->selected = str_replace("\t",'', $Qry3->selected);	

					$Qry3->fields   = "id = '".$va->id."' ";     
					$checke = $Qry3->exe_UPDATE($con);
					if($checke){
						$param->info->date 	 = $va->date;
						$param->info->idacct = $va->idacct;
						
							if( (int)$param->info->stat == 1 ){
								$timesheet_data = getTimeSheetData($con, $va->idacct, $va->date);
								if( !empty( $timesheet_data ) ){
									if( addToBackuptimesheet($con,$timesheet_data) ){
										$return = updateTimesheetLate($con, $param);
									}
								}
							}				

						$mailSubject = "TPM HRIS 2.0 - Attendance Adjustment Application"; 
						$mailBody = "<h4>Timekeeping Application - Attendance Adjustment(AA)</h4>";
						$mailBody .= "Date Applied: ".$va->date;
						$mailBody .= "<br />Document number: ".$va->docnumber;
						if($param->info->stat == '1'){
							$mailBody .= "<br />Status: <span style='color: green'>Approved</span>";
							$mailBody .= "<br />Date Approved: ".$date_approve;
						}else{
							$mailBody .= "<br />Status: <span style='color: red'>Disapproved</span>";
							$mailBody .= "<br />Reason: <span style='color: red'>".strtoupper(strtolower(str_replace("'","",$param->info->reason)))."</span>";
						    $mailBody .= "<br />Date Disapproved: ".$date_approve;
						}

						$mailBody .="<br /><br />This is a system generated notification.<br />";
						$return1 = _EMAILDIRECT_APPLICATIONAPPR(getEmail($con,$va->idacct),$mailSubject, $mailBody,$va->idacct);

					}else{
						$arr_err[] = array(
							"id"	=> $va->id,
							"date"	=> $va->date,
							"name"	=> $va->empname,
							"err"	=> "Error On Update"
						);
					}
				}elseif($row['approver_2a'] == $idacct){
					$Qry3           = new Query();
					$Qry3->table    = "tbltimeadjustment";
					$Qry3->selected = "
									   approver2='".$param->accountid."',
									   approver2_stat='".$param->info->stat."',
									   approver2_date='".$date_approve."',
									   approver2_time='".SysTime()."'";
					if( (int)$param->info->stat == 2 ){
						$Qry3->selected = $Qry3->selected . ",stat= '".$param->info->stat."'";
						$Qry3->selected = $Qry3->selected . ",approver2_reason= '".strtoupper(strtolower(str_replace("'","",$param->info->reason)))."' ";
					}else{
						if($row['ctr_approver'] == 2){
							$Qry3->selected = $Qry3->selected . ",stat= '".$param->info->stat."'";
							$Qry3->selected = $Qry3->selected . ",date_approve= '".$date_approve."'";
						}elseif(empty($row['approver_3a']) && empty($row['approver_3b']) && empty($row['approver_3c'])){
							$Qry3->selected = $Qry3->selected . ",stat= '".$param->info->stat."'";
							$Qry3->selected = $Qry3->selected . ",date_approve= '".$date_approve."'";
						}
					}	
					$Qry3->fields   = "id = '".$va->id."' ";     
					$checke = $Qry3->exe_UPDATE($con);
					if($checke){
						$param->info->date 	 = $va->date;
						$param->info->idacct = $va->idacct;
						
							if( (int)$param->info->stat == 1 ){
								$timesheet_data = getTimeSheetData($con, $va->idacct, $va->date);
								if( !empty( $timesheet_data ) ){
									if( addToBackuptimesheet($con,$timesheet_data) ){
										$return = updateTimesheetLate($con, $param);
									}
								}
							}				

						$mailSubject = "TPM HRIS 2.0 - Attendance Adjustment Application"; 
						$mailBody = "<h4>Timekeeping Application - Attendance Adjustment(AA)</h4>";
						$mailBody .= "Date Applied: ".$va->date;
						$mailBody .= "<br />Document number: ".$va->docnumber;
						if($param->info->stat == '1'){
							$mailBody .= "<br />Status: <span style='color: green'>Approved</span>";
							$mailBody .= "<br />Date Approved: ".$date_approve;
						}else{
							$mailBody .= "<br />Status: <span style='color: red'>Disapproved</span>";
							$mailBody .= "<br />Reason: <span style='color: red'>".strtoupper(strtolower(str_replace("'","",$param->info->reason)))."</span>";
						    $mailBody .= "<br />Date Disapproved: ".$date_approve;
						}

						$mailBody .="<br /><br />This is a system generated notification.<br />";
						$return1 = _EMAILDIRECT_APPLICATIONAPPR(getEmail($con,$va->idacct),$mailSubject, $mailBody,$va->idacct);

					}else{
						$arr_err[] = array(
							"id"	=> $va->id,
							"date"	=> $va->date,
							"name"	=> $va->empname,
							"err"	=> "Error On Update"
						);
					}
				}elseif($row['approver_3a'] == $idacct){
					$Qry3           = new Query();
					$Qry3->table    = "tbltimeadjustment";
					$Qry3->selected = "
									   approver3='".$param->accountid."',
									   approver3_stat='".$param->info->stat."',
									   approver3_date='".$date_approve."',
									   approver3_time='".SysTime()."'";
					if( (int)$param->info->stat == 2 ){
						$Qry3->selected = $Qry3->selected . ",stat= '".$param->info->stat."'";
						$Qry3->selected = $Qry3->selected . ",approver3_reason= '".strtoupper(strtolower(str_replace("'","",$param->info->reason)))."' ";
					}else{
						if($row['ctr_approver'] == 3){
							$Qry3->selected = $Qry3->selected . ",stat= '".$param->info->stat."'";
							$Qry3->selected = $Qry3->selected . ",date_approve= '".$date_approve."'";
						}elseif(empty($row['approver_4a']) && empty($row['approver_4b']) && empty($row['approver_4c'])){
							$Qry3->selected = $Qry3->selected . ",stat= '".$param->info->stat."'";
							$Qry3->selected = $Qry3->selected . ",date_approve= '".$date_approve."'";
						}
					}	
					$Qry3->fields   = "id = '".$va->id."' ";     
					$checke = $Qry3->exe_UPDATE($con);
					if($checke){
						$param->info->date 	 = $va->date;
						$param->info->idacct = $va->idacct;
						
							if( (int)$param->info->stat == 1 ){
								$timesheet_data = getTimeSheetData($con, $va->idacct, $va->date);
								if( !empty( $timesheet_data ) ){
									if( addToBackuptimesheet($con,$timesheet_data) ){
										$return = updateTimesheetLate($con, $param);
									}
								}
							}				

						$mailSubject = "TPM HRIS 2.0 - Attendance Adjustment Application"; 
						$mailBody = "<h4>Timekeeping Application - Attendance Adjustment(AA)</h4>";
						$mailBody .= "Date Applied: ".$va->date;
						$mailBody .= "<br />Document number: ".$va->docnumber;
						if($param->info->stat == '1'){
							$mailBody .= "<br />Status: <span style='color: green'>Approved</span>";
							$mailBody .= "<br />Date Approved: ".$date_approve;
						}else{
							$mailBody .= "<br />Status: <span style='color: red'>Disapproved</span>";
							$mailBody .= "<br />Reason: <span style='color: red'>".strtoupper(strtolower(str_replace("'","",$param->info->reason)))."</span>";
						    $mailBody .= "<br />Date Disapproved: ".$date_approve;
						}

						$mailBody .="<br /><br />This is a system generated notification.<br />";
						$return1 = _EMAILDIRECT_APPLICATIONAPPR(getEmail($con,$va->idacct),$mailSubject, $mailBody,$va->idacct);

					}else{
						$arr_err[] = array(
							"id"	=> $va->id,
							"date"	=> $va->date,
							"name"	=> $va->empname,
							"err"	=> "Error On Update"
						);
					}
				}elseif($row['approver_4a'] == $idacct){
					$Qry3           = new Query();
					$Qry3->table    = "tbltimeadjustment";
					$Qry3->selected = "
									   approver4='".$param->accountid."',
									   approver4_stat='".$param->info->stat."',
									   approver4_date='".$date_approve."',
									   approver4_time='".SysTime()."'";
					if( (int)$param->info->stat == 2 ){
						$Qry3->selected = $Qry3->selected . ",stat= '".$param->info->stat."'";
						$Qry3->selected = $Qry3->selected . ",approver4_reason= '".strtoupper(strtolower(str_replace("'","",$param->info->reason)))."' ";
					}else{
						if($row['ctr_approver'] == 4){
							$Qry3->selected = $Qry3->selected . ",stat= '".$param->info->stat."'";
							$Qry3->selected = $Qry3->selected . ",date_approve= '".$date_approve."'";
						}
					}
					$Qry3->fields   = "id = '".$va->id."' ";     
					$checke = $Qry3->exe_UPDATE($con);
					if($checke){
						$param->info->date 	 = $va->date;
						$param->info->idacct = $va->idacct;
						
							if( (int)$param->info->stat == 1 ){
								$timesheet_data = getTimeSheetData($con, $va->idacct, $va->date);
								if( !empty( $timesheet_data ) ){
									if( addToBackuptimesheet($con,$timesheet_data) ){
										$return = updateTimesheetLate($con, $param);
									}
								}
							}				

						$mailSubject = "TPM HRIS 2.0 - Attendance Adjustment Application"; 
						$mailBody = "<h4>Timekeeping Application - Attendance Adjustment(AA)</h4>";
						$mailBody .= "Date Applied: ".$va->date;
						$mailBody .= "<br />Document number: ".$va->docnumber;
						if($param->info->stat == '1'){
							$mailBody .= "<br />Status: <span style='color: green'>Approved</span>";
							$mailBody .= "<br />Date Approved: ".$date_approve;
						}else{
							$mailBody .= "<br />Status: <span style='color: red'>Disapproved</span>";
							$mailBody .= "<br />Reason: <span style='color: red'>".strtoupper(strtolower(str_replace("'","",$param->info->reason)))."</span>";
						    $mailBody .= "<br />Date Disapproved: ".$date_approve;
						}

						$mailBody .="<br /><br />This is a system generated notification.<br />";
						$return1 = _EMAILDIRECT_APPLICATIONAPPR(getEmail($con,$va->idacct),$mailSubject, $mailBody,$va->idacct);

					}else{
						$arr_err[] = array(
							"id"	=> $va->id,
							"date"	=> $va->date,
							"name"	=> $va->empname,
							"err"	=> "Error On Update"
						);
					}
				}
			}
		}else{
			$arr_err[] = array(
				"id"	=> $va->id,
				"date"	=> $va->date,
				"name"	=> $va->empname,
				"err"	=> "You are not assigned as Approver 1"
			);
		}
	}
	$return = json_encode(array("status"=>"success","err"=>$arr_err));	
}else{
	$return = json_encode(array('status'=>'notloggedin'));
}


print $return;
 mysqli_close($con);
}else{
	mysqli_close($con);
	header("Location: https://peninsula.mydhris.com/mph/");  // unAuth back to login page
}

function getEmail($con,$idacct){
    $Qry = new Query();	
    $Qry->table ="tblaccount";	
    $Qry->selected ="email";
    $Qry->fields ="id='".$idacct."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['email'];
        }
    }
    return '';
}
function isApprover1Empty($con,$id){
    $Qry = new Query();	
    $Qry->table ="tbltimeadjustment";	
    $Qry->selected ="approver1_stat";
    $Qry->fields ="id='".$id."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
			if(!empty($row['approver1_stat'])){
				return false;
			}
        }
    }
    return true;
}
?>