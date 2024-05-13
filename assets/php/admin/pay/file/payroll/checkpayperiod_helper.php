<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblpayperiod_helper";
$Qry->selected  = "*";
$Qry->fields = "(period_start='". $param->info->from ."' OR period_end='". $param->info->to ."' OR pay_date='". $param->info->paydate ."')";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    $return = json_encode(array("status"=>"error"));
}else{
    $return = json_encode(array("status"=>"success"));
}

print $return;
mysqli_close($con);
?>