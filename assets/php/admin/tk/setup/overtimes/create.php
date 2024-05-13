<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

if(!empty($param->accountid)){
	if(!empty($param->info->name)){	
		if(!empty($param->info->alias)){
			if(!empty($param->info->rate)){
				
					$param->info->name  = strtoupper(strtolower(str_replace("'","",$param->info->name))); 
					$param->info->alias = strtoupper(strtolower(str_replace("'","",$param->info->alias))); 
					$param->info->rate	= "".(float)$param->info->rate;
					
					if( checkOvertimeName($con,$param->info->name, '') ){
						$return = json_encode(array("status"=>"exists1"));
						print $return;
						mysqli_close($con);
						return;
					}
					
					if( checkOvertimeAlias($con,$param->info->alias, '') ){
						$return = json_encode(array("status"=>"exists2"));
						print $return;
						mysqli_close($con);
						return;
					}
					
					$Qry3           = new Query();
					$Qry3->table    = "tblovertimes";
					$Qry3->selected = "name,alias,rate";
					$Qry3->fields   = "'".$param->info->name."',
									  '".$param->info->alias."',
									  '".$param->info->rate."'";                    
					$checke = $Qry3->exe_INSERT($con);
					if($checke){
						$return = json_encode(array("status"=>"success"));
					}else{
						$return = json_encode(array('status'=>'error'));
					}
				
			}else{
				$return = json_encode(array('status'=>'rate'));
			}
		}else{
			$return = json_encode(array('status'=>'alias'));
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