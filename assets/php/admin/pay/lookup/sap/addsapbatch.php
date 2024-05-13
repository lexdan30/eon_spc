<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));

$pd = implode (",", $param->info->pd);
$sc = implode (",", $param->info->sc);
$flags      = !empty($param->info->flags) ? 0 : 1;

$Qry           = new Query();
$Qry->table    = "sapbatch";
$Qry->selected = "batchnum,sapcodes,paydate";
$Qry->fields   = "'".$param->info->bn."',
                '".$sc."',
                '".$pd."'
                ";                        
$rs = $Qry->exe_INSERT($con);

if($rs){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error', 'code' => mysqli_error($con)));
}

print $return;
mysqli_close($con);
?>