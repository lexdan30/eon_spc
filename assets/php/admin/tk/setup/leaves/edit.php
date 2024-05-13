<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

if(!empty($param->accountid)){
	if(!empty($param->info->name)){
		if( (int)$param->info->hours > 0 ){
			if(!empty($param->info->idtype)){
				if(!empty($param->info->isconvertible)){
					if(!empty($param->info->active)){
						$param->info->name     = strtoupper(strtolower(str_replace("'","",$param->info->name))); 
						if( checkLeaveName($con, $param->info->name, $param->info->id) ){
							$return = json_encode(array("status"=>"exists"));
							print $return;
							mysqli_close($con);
							return;
						}
						$Qry3           = new Query();
						$Qry3->table    = "tblleaves";
						$Qry3->selected = "name='".$param->info->name."',
										   hours='".(int)$param->info->hours."',
										   idtype='".$param->info->idtype."',										   
										   isconvertible='".$param->info->isconvertible."',
										   active='".$param->info->active."'";
						$Qry3->fields   = "id='".$param->info->id."'";                    
						$checke = $Qry3->exe_UPDATE($con);
						if($checke){
							$return = json_encode(array("status"=>"success"));
						}else{
							$return = json_encode(array('status'=>'error'));
						}
					}else{
						$return = json_encode(array('status'=>'isconvertible'));
					}
				}else{
					$return = json_encode(array('status'=>'isconvertible'));
				}
			}else{
				$return = json_encode(array('status'=>'idtype'));
			}
		}else{
			$return = json_encode(array('status'=>'hours'));
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