<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$param 		= json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tblacctbanks";
$Qry->selected = "bank,description";
$Qry->fields   = "'".$param->info->bank."', '".$param->info->description ."'";                        
$rs = $Qry->exe_INSERT($con);


if($rs){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array("status"=>"error"));
}
      

mysqli_close($con);
?>