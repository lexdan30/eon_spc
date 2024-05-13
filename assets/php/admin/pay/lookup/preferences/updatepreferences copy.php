<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
// $flags = !empty($param->info->flags) ? $param->info->flags : "Active";
if($param->info->idmeasure != '11'){
	$val = $param->info->value;
}else{
	$val = implode (",", $param->info->value);
}


$Qry           = new Query();
$Qry->table    = "tblpreference";
$Qry->selected = "  prefname     ='".$param->info->prefname."',
                    remarks      ='".$param->info->remarks."',
					value 		 ='".$val."',
					idmeasure 	 ='".$param->info->idmeasure."',
                    flags	     ='".$param->info->flags."' 
                    ";
					// if( !empty($value) ){
					// 	$Qry->selected 	= "value ='".$param->info->value."'";
					// }
					if( !empty($param->info->idmeasure) ){
						$Qry->selected 	= "idmeasure ='".$param->info->idmeasure."'";
					} 
$Qry->fields   = "id='".$param->info->id."'";                        
$checke = $Qry->exe_UPDATE($con);
if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error'));
}
print $return;
mysqli_close($con);
?>