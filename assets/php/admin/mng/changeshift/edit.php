<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
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
		//check if approver1 
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
				}elseif( $row['approver_1b'] == $idacct || $row['approver_1c'] == $idacct || $va->idsuperior == $idacct ){
					$Qry3           = new Query();
					$Qry3->table    = "tbltimeshift";
					$Qry3->selected = "stat='".$param->info->stat."',
									   approver1='".$param->accountid."',
									   approver1_stat='".$param->info->stat."',
									   approver1_date='".$date_approve."',
									   approver1_time='".SysTime()."'";
					
					if( (int)$param->info->stat == 2 ){
						$Qry3->selected = $Qry3->selected . ",approver1_reason= '".strtoupper(strtolower(str_replace("'","",$param->info->reason)))."' ";
					}
					$Qry3->fields   = "id = '".$va->id."' ";              
					$checke = $Qry3->exe_UPDATE($con);
					if($checke){
						$param->info->date 	 = $va->date;
						$param->info->idacct = $va->idacct;
						/*$period_start = strtotime( $va->period_start );			
						$period_end   = date("Y-m-d",strtotime('+'.$va->grace_hour.' hours', strtotime( $va->period_end ))) ;
						if( $date_approve > $period_end ){
							if( (int)$param->info->stat == 1 ){				
								$timesheet_data = getTimeSheetData($con, $va->idacct, $va->date );
								if( !empty( $timesheet_data ) ){
									if( addToBackuptimesheet($con,$timesheet_data) ){
										$param->info->date = $va->date;
										$param->info->idacct = $va->idacct;
										if( (int)updateTimesheetLate($con, $param, $va->idshift) == 1 ){
											if( !checkLateApproveExists($con, $timesheet_data['id'] ) ){
												$curr_pay_period = getLatePayPeriod($con, $date_approve);									
												$Qry4           = new Query();
												$Qry4->table    = "tbltimeadjustment";
												$Qry4->selected = "id_payperiod='".$curr_pay_period['id']."'";
												$Qry4->fields   = "id='".$va->id."'";                    
												$checke4 		= $Qry4->exe_UPDATE($con);
												if( $checke4 ){
													$return = insertLateApprove($con, $timesheet_data['id'], SysDate(), $curr_pay_period['pay_date'], $curr_pay_period['id']);
												}
											}
										}					
									}
								}
							}
						}else{*/
							if( (int)$param->info->stat == 1 ){
								$timesheet_data = getTimeSheetData($con, $va->idacct, $va->date);
								if( !empty( $timesheet_data ) ){
									if( addToBackuptimesheet($con,$timesheet_data) ){
										$return = updateTimesheetLate($con, $param, $va->idshift);
									}
								}
							}				
						//}
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

?>