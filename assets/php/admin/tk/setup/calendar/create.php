<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

if(!empty($param->accountid)){
	if(!empty($param->info->name)){	
		if(!empty($param->info->shiftsun)){	
			if(!empty($param->info->shiftmon)){	
				if(!empty($param->info->shifttue)){	
					if(!empty($param->info->shiftwed)){	
						if(!empty($param->info->shiftthu)){									
							if(!empty($param->info->shiftfri)){									
								if(!empty($param->info->shiftsat)){		
									$param->info->name  = strtoupper(strtolower(str_replace("'","",$param->info->name))); 		
									if( checkCalendarName($con,$param->info->name, '') ){
										$return = json_encode(array("status"=>"exists1"));
										print $return;
										mysqli_close($con);
										return;
									}
									$Qry3           = new Query();
									$Qry3->table    = "tblcalendar";
									$Qry3->selected = "name,shiftsun,shiftmon,shifttue,shiftwed,shiftthu,shiftfri,shiftsat";
									$Qry3->fields   = "'".$param->info->name."',
													   '".$param->info->shiftsun."',
													   '".$param->info->shiftmon."',
													   '".$param->info->shifttue."',
													   '".$param->info->shiftwed."',
													   '".$param->info->shiftthu."',
													   '".$param->info->shiftfri."',
													   '".$param->info->shiftsat."'";
									$checke = $Qry3->exe_INSERT($con);
									if($checke){
										$return = json_encode(array("status"=>"success"));
									}else{
										$return = json_encode(array('status'=>'error'));
									}
								}else{
									$return = json_encode(array('status'=>'shiftsat'));
								}
							}else{
								$return = json_encode(array('status'=>'shiftfri'));
							}
						}else{
							$return = json_encode(array('status'=>'shiftthu'));
						}
					}else{
						$return = json_encode(array('status'=>'shiftwed'));
					}
				}else{
					$return = json_encode(array('status'=>'shifttue'));
				}
			}else{
				$return = json_encode(array('status'=>'shiftmon'));
			}
		}else{
			$return = json_encode(array('status'=>'shiftsun'));
		}
	}else{
		$return = json_encode(array('status'=>'name'));
	}
}else{
	 $return = json_encode(array('status'=>'notloggedin'));
}


print $return;
mysqli_close($con);
?>