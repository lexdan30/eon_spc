<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));
$flags      = !empty($param->info->flags) ? 0 : 1;

$Qry           = new Query();
$Qry->table    = "tblpaygrp";
$Qry->selected = "`code`, `group`, `period`, `flags`";
$Qry->fields   = " '".$param->info->code."',
                 '".$param->info->group."',
                 '".$param->info->period."',
                 '".$flags."'
                ";                        
$rs = $Qry->exe_INSERT($con);

if($rs){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error'));
}
print $return;
mysqli_close($con);
?>