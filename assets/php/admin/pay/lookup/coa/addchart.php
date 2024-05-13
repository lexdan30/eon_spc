<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));

$type = !empty($param->info->type) ? $param->info->type : NULL;
$flags      = !empty($param->info->flags) ? 0 : 1;

$Qry           = new Query();
$Qry->table    = "tblchartofaccount";
$Qry->selected = "code,description,type,flags";
$Qry->fields   = "'".$param->info->code."',
                '".$param->info->description."',
                '".$type."',
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