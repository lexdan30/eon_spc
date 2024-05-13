<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));
$stats      = !empty($param->info->stats) ? 0 : 1;
$loan_mode  = !empty($param->info->loan_mode) ? $param->info->loan_mode : "Fixed Amount";
$app_first  = !empty($param->info->app_first) ? $param->info->app_first : 0;
$app_second = !empty($param->info->app_second) ? $param->info->app_second : 0;
$app_sp     = !empty($param->info->app_sp) ? $param->info->app_sp : 0;
$app_fp     = !empty($param->info->app_fp) ? $param->info->app_fp : 0;
$percentage = !empty($param->info->interest_percentage) ? $param->info->interest_percentage : 0;
$credit = !empty($param->info->credit) ? $param->info->credit : NULL;
$debit = !empty($param->info->debit) ? $param->info->debit : NULL;

$Qry           = new Query();
$Qry->table    = "tblloantype";
$Qry->selected = "`code`,`desc`,`stats`,`type`,`loan_mode`,`interest_percentage`,`app_first`,`app_second`,`app_sp`,`app_fp`,`priority`,`debit`,`credit`";
$Qry->fields   = "'".$param->info->code."',
                '".$param->info->desc."',
                '".$stats."',
                '".$param->info->type."',
                '".$loan_mode."',
                '".$percentage."',
                '".$app_first."',
                '".$app_second."',
                '".$app_sp."',
                '".$app_fp."',
                '".$param->info->priority."',
                '".$debit."',
                '".$credit."'
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