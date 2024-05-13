<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$istax  = !empty($param->info->istax) ? $param->info->istax : 0;
$multi  = ($param->info->transactiontype == 1) ? 1 : -1;
$multi = $param->info->multi;
$Qry           = new Query();
$Qry->table    = "tblclass";
$Qry->selected = "description     ='".$param->info->description."',
                  istax           ='".$istax."',
                  transactiontype ='".$param->info->transactiontype."',
                  multi           ='".$multi."',
                  flags	 ='".$param->info->flags."'  
                ";
$Qry->fields   = "id='".$param->info->id."'";                        
$checke = $Qry->exe_UPDATE($con);
if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error'));
}
print $return;
mysqli_close($con);
// classname ='".$param->info->classname."',
?>