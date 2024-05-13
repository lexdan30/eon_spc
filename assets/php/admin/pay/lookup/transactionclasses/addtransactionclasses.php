<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));
$flags      = !empty($param->info->flags) ? 0 : 1;
$istax  = !empty($param->info->istax) ? $param->info->istax : 0;
$multi  = ($param->info->transactiontype  == 1) ? 1 : -1;
// $transactiontype  = !empty($param->info->transactiontype) ? $param->info->transactiontype : NULL;

print(mysqli_error($con));

$Qry           = new Query();
$Qry->table    = "tblclass";
$Qry->selected =    "`alias`,
                    `description`,
                    `istax`,
                    `transactiontype`,
                    `multi`,
                    `flags`";
$Qry->fields   = "'".$param->info->code."',
                    '".$param->info->description."', 
                    '".$istax."',
                    '".$param->info->transactiontype."', 
                    '".$multi."', 
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