<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

if(!empty($param->accountid)){
	if(!empty($param->info->id)){		
		if( (int)$param->info->stat == 4 ){
			if( (int)checkCurrentStatus($con, "tbltimeadjustment", $param->info->id) == 3 ){
				$Qry3           = new Query();
				$Qry3->table    = "tbltimeadjustment";
				$Qry3->selected = "stat='".$param->info->stat."',
								   cancelby='".$param->accountid."',
								   cancel_date='".SysDate()."',
								   cancel_time='".SysTime()."'";
				$Qry3->fields   = "id='".$param->info->id."'";                    
				$checke = $Qry3->exe_UPDATE($con);
				if($checke){			
					$return = json_encode(array("status"=>"success"));
				}else{
					$return = json_encode(array('status'=>'error', 'reason'=>mysqli_error($con)));
				}
			}else{
				$return = json_encode(array("status"=>"success"));
			}
		}else{
			$return = json_encode(array('status'=>'inv'));
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