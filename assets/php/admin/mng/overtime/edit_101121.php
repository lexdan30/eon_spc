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
	$idacct			= $param->accountid;
	$approve_stat 	= $param->info->stat;
	
	$approver		= "1";
	
	
	$arr_id23 = array();
	$arr_hr1  = array();
	
	
	if( (int)$param->info->curr_appr == 1 ){ // approver 1
		$approve_hour_stat	= (int)$param->info->approve_hr;
		$arr_err = array();
		foreach( $param->info->data as $ka => $va ){
			//check if approver1 
			$Qry2 			= new Query();	
			$Qry2->table     = "vw_appmatrix";
			$Qry2->selected  = "*";
			$Qry2->fields    = "idunit = '".$va->idunit."'" ;
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
						if( (int)$approve_hour_stat == 2 ){
							if( empty($va->actual_hrs) ){
								$arr_err[] = array(
									"id"	=> $va->id,
									"date"	=> $va->date,
									"name"	=> $va->empname,
									"err"	=> "No Actual Hour Computed"
								);
								$hrs = "";
							}else{
								$hrs = sprintf('%0.2f',$va->actual_hrs);
							}
						}elseif( (int)$approve_hour_stat == 1 ){
							//$hrs = sprintf('%0.2f',$va->planned_hrs);
							$hrs = sprintf('%0.2f',$va->actual_hrs);
						}
						if( !empty( $hrs ) ){
							$splice = (explode('.', $hrs, 2));
							if($splice[1] >= 00 && $splice[1] < 25){
								$hrs = $splice[0] . '.' . '00';
							}elseif($splice[1] >= 25 && $splice[1] < 50){
								$hrs = $splice[0] . '.' . '25';
							}elseif($splice[1] >= 50 && $splice[1] < 75){
								$hrs = $splice[0] .'.' . '50';
							}elseif($splice[1] >= 75 && $splice[1] <= 99){
								$hrs = $splice[0] . '.' . '75';
							}
							$Qry3           = new Query();
							$Qry3->table    = "tbltimeovertime";		
							$Qry3->selected = "approver1='".$param->accountid."',
									   approver1_stat='".$approve_stat."',
									   approver1_date='".SysDate()."',
									   approver1_time='".SysTime()."',
									   approve_hr='".$approve_hour_stat."',
									   hrs='".$hrs."',
									   stat='".$approve_stat."'";
							if( (int)$param->info->stat == 2 ){
								$Qry3->selected = $Qry3->selected . ",stat='2',approver2_reason=NULL,approver1_reason= '".strtoupper(strtolower(str_replace("'","",$param->info->reason)))."' ";
							}	
							
						}
						$Qry3->fields   = "id = '".$va->id."' ";                  
						$checke = $Qry3->exe_UPDATE($con);
						if( !$checke ){
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
	}elseif( (int)$param->info->curr_appr == 2 ){ // approver 2
		$arr_err = array();
		foreach( $param->info->data as $ka => $va ){
			//check if approver2 
			$Qry2 			= new Query();	
			$Qry2->table     = "vw_appmatrix";
			$Qry2->selected  = "*";
			$Qry2->fields    = "(((approver_2a = '".$idacct."') OR (approver_2b = '".$idacct."') OR (approver_2c = '".$idacct."')) OR approver2_head = '".$idacct."') AND idunit = '".$va->idunit."'" ;
			$rs2 			= $Qry2->exe_SELECT($con);
			if(mysqli_num_rows($rs2) > 0){
				$Qry3           = new Query();
				$Qry3->table    = "tbltimeovertime";		
				$Qry3->selected = "stat='1',
					   approver2='".$param->accountid."',
					   approver2_stat='".$approve_stat."',
					   approver2_date='".SysDate()."',
					   approver2_time='".SysTime()."'";
				if( (int)$param->info->stat == 2 ){
					$Qry3->selected = $Qry3->selected . ",stat='2',approver1_reason=NULL,approver2_reason= '".strtoupper(strtolower(str_replace("'","",$param->info->reason)))."' ";
				}	
				$Qry3->fields   = "id = '".$va->id."' ";  			
				$checke = $Qry3->exe_UPDATE($con);
				if( !$checke ){
					$arr_err[] = array(
						"id"	=> $va->id,
						"date"	=> $va->date,
						"name"	=> $va->empname,
						"err"	=> "Error On Update"
					);
				}else{
					
					$param->info->date 	 = $va->date;
					$param->info->idacct = $va->idacct;
					
					//$param->info-idacct = $va->idacct;
					/*
					$period_start = strtotime( $va->period_start );			
					$period_end   = date("Y-m-d",strtotime('+'.$va->grace_hour.' hours', strtotime( $va->period_end ))) ;
					if( $date_approve > $period_end ){
						if( (int)$approve_stat == 1 ){				
							$timesheet_data = getTimeSheetData($con, $va->idacct, $va->date);
							if( !empty( $timesheet_data ) ){
								if( addToBackuptimesheet($con,$timesheet_data) ){
									if( (int)updateTimesheetLate($con, $param) == 1 ){
										if( !checkLateApproveExists($con, $timesheet_data['id'] ) ){									
											$curr_pay_period = getLatePayPeriod($con, $date_approve);									
											$Qry4           = new Query();
											$Qry4->table    = "tbltimeovertime";
											$Qry4->selected = "id_payperiod='".$curr_pay_period['id']."'";
											$Qry4->fields   = "id='".$va->id."'";                    
											$checke4 		= $Qry4->exe_UPDATE($con);
											if( $checke4 ){
												$return = insertLateApprove($con, $timesheet_data['id'], SysDate(), $curr_pay_period['pay_date'], $curr_pay_period['id']);
											}else{
												$arr_err[] = array(
													"id"	=> $va->id,
													"date"	=> $va->date,
													"name"	=> $va->empname,
													"err"	=> "Error On Payperiod Update"
												);
											}
										}
									}					
								}else{
									$arr_err[] = array(
										"id"	=> $va->id,
										"date"	=> $va->date,
										"name"	=> $va->empname,
										"err"	=> "Not added to backup"
									);
								}
							}
						}
					}else{*/
						if( (int)$param->info->stat == 1 ){
							$timesheet_data = getTimeSheetData($con, $va->idacct, $va->date);
							if( !empty( $timesheet_data ) ){
								if( addToBackuptimesheet($con,$timesheet_data) ){
									$return = updateTimesheetLate($con, $param);
								}
							}
						}				
					//}
				}
			}else{
				$arr_err[] = array(
					"id"	=> $va->id,
					"date"	=> $va->date,
					"name"	=> $va->empname,
					"err"	=> "You are not assigned as Approver 2"
				);
			}
		}
		$return = json_encode(array("status"=>"success","err"=>$arr_err));
	}
}else{
	$return = json_encode(array('status'=>'notloggedin'));
}


print $return;
mysqli_close($con);

function getflexinfo($con, $id, $date){
	$data = array();
	$Qry = new Query();	
	$Qry->table     = "vw_timesheet";
	$Qry->selected  = "stime,ftime,idshift,breakin,breakout";
	$Qry->fields = "id = '" .$param->accountid . "' AND work_date = '" . $param->info . "'";

	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_assoc($rs)){
		  if($row['idshift'] != '89' && $row['idshift'] != '93'){
			return false;
		  }else{
			return true;
		  }
		}
	}

	return false;
?>