<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date_approve =  date("Y-m-d",strtotime(SysDate()));

if(!empty($param->accountid)){
	if(!empty($param->info->id)){		
		if( (int)$param->info->stat == 2 ){
			if( empty( strtoupper(strtolower(str_replace("'","",$param->info->reason))) ) ){
				$return = json_encode(array("status"=>"noreason"));
				print $return;
				mysqli_close($con);
				return;
			}
		}		
		$Qry3           = new Query();
		$Qry3->table    = "tbltimeleaves";
		$Qry3->selected = "stat='".$param->info->stat."',
						   approver1='".$param->accountid."',
						   approver1_stat='".$param->info->stat."',
						   approver1_date='".SysDate()."',
						   approver1_time='".SysTime()."'";
		
		if( (int)$param->info->stat == 2 ){
			$Qry3->selected = $Qry3->selected . ",approver1_reason= '".strtoupper(strtolower(str_replace("'","",$param->info->reason)))."' ";
		}	
		if( (int)$param->info->stat == 4 ){
			$Qry3->selected = $Qry3->selected . ",cancelby='".$param->accountid."', cancel_date='".SysDate()."', cancel_time='".SysTime()."' ";
		}
	
		
		$Qry3->fields   = "id='".$param->info->id."'";                    
		$checke = $Qry3->exe_UPDATE($con);
		if($checke){
			
			
			//UPDATE ACCOUNT LEAVES UNITS	
			$idleave	    	= $param->info->idleave;
			$idacct				= $param->info->idacct;
			$accountleave		= getAccountLeavesInfo($con,$idleave,$idacct);
			$entitle			= $accountleave['entitle'];
			$used				= getLeaveUsed($con, $idacct, $idleave);
			$balance			= (int)$entitle - (int)$used;
			$Qry22 				= new Query();	
			$Qry22->table		= "tblaccountleaves";
			$Qry22->selected 	= "entitle='".$entitle."', used='".$used."', balance='".$balance."'";
			$Qry22->fields 		= "idacct='".$idacct."' AND idleave='".$idleave."' ";
			$checke22 			= $Qry22->exe_UPDATE($con);
			
			$period_start = strtotime( $param->info->period_start );			
			$period_end   = date("Y-m-d",strtotime('+'.$param->info->grace_hour.' hours', strtotime( $param->info->period_end ))) ;
			
			if( $date_approve > $period_end ){
				if( (int)$param->info->stat == 1 ){				
					$timesheet_data = getTimeSheetData($con, $param->info->idacct, $param->info->date);
					if( !empty( $timesheet_data ) ){
						if( addToBackuptimesheet($con,$timesheet_data) ){
							if( (int)updateTimesheetLate($con, $param) == 1 ){
								if( !checkLateApproveExists($con, $timesheet_data['id'] ) ){
									$curr_pay_period = getLatePayPeriod($con, $date_approve);									
									$Qry4           = new Query();
									$Qry4->table    = "tbltimeleaves";
									$Qry4->selected = "id_payperiod='".$curr_pay_period['id']."'";
									$Qry4->fields   = "id='".$param->info->id."'";                    
									$checke4 		= $Qry4->exe_UPDATE($con);
									if( $checke4 ){
										$return = insertLateApprove($con, $timesheet_data['id'], SysDate(), $curr_pay_period['pay_date'], $curr_pay_period['id']);
									}
								}
							}					
						}else{
							$return = json_encode(array("status"=>"success","w"=>"not added to backup","e"=>$curr_pay_period));
							print $return;
							mysqli_close($con);
							return;
						}
					}
				}
			}else{	
				if( (int)$param->info->stat == 1 ){
					$timesheet_data = getTimeSheetData($con, $param->info->idacct, $param->info->date);
					if( !empty( $timesheet_data ) ){
						if( addToBackuptimesheet($con,$timesheet_data) ){
							$return = updateTimesheetLate($con, $param);
						}
					}
				}
			}
			$return = json_encode(array("status"=>"success"));
		}else{
			$return = json_encode(array('status'=>'error', 'reason'=>mysqli_error($con)));
		}	
	}else{
		$return = json_encode(array('status'=>'noid'));
	}
}else{
	 $return = json_encode(array('status'=>'notloggedin'));
}


print $return;
mysqli_close($con);
?>