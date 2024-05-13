<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));

$coa = implode (",", $param->info->coa);
$flags      = !empty($param->info->flags) ? 0 : 1;

$Qry           = new Query();
$Qry->table    = "tblsapcodes";
$Qry->selected = "sapcode,description,coaids,status";
$Qry->fields   = "'".$param->info->code."',
                '".$param->info->description."',
                '".$coa."',
                '".$flags."'
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