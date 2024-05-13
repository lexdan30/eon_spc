<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php');  

$param = json_decode(file_get_contents('php://input'));

$municipality = !empty($param->info->municipality) ? $param->info->municipality : 0;

if(!empty($param->accountid)){
	if(!empty($param->info->name)){	
		if( !empty($param->info->date) && $param->info->date != "0000-00-00" && $param->info->date != "" && $param->info->date != " " ){	
			if(!empty($param->info->idtype)){		
				$param->info->name  = strtoupper(strtolower(str_replace("'","",$param->info->name))); 		
				// if( checkHolidayNames($con,$param->info->name, $param->info->id) ){
				// 	$return = json_encode(array("status"=>"exists1"));
				// 	print $return;
				// 	mysqli_close($con);
				// 	return;
				// }
				$Qry3           = new Query();
				$Qry3->table    = "tblholidays";
				$Qry3->selected = "name='".ucwords(strtolower($param->info->name),' ')."',date='".$param->info->date."',idtype='".$param->info->idtype."',regcode='".$param->info->regions."',provcode='".$param->info->provinces."',munid='".$municipality."'";
				$Qry3->fields   = "id='".$param->info->id."'";
				$checke = $Qry3->exe_UPDATE($con);
				if($checke){
					$return = json_encode(array("status"=>"success"));
				}else{
					$return = json_encode(array('status'=>'error'));
				}				
			}else{
				$return = json_encode(array('status'=>'idtype'));
			}
		}else{
			$return = json_encode(array('status'=>'date'));
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