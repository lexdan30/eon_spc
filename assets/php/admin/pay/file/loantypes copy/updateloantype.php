<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
// $stats      = !empty($param->info->stats) ? 0 : 1;
$loan_mode  = !empty($param->info->loan_mode) ? $param->info->loan_mode : "Fixed Amount";
$app_first  = !empty($param->info->app_first) ? $param->info->app_first : 0;
$app_second = !empty($param->info->app_second) ? $param->info->app_second : 0;
$app_sp     = !empty($param->info->app_sp) ? $param->info->app_sp : 0;
$app_fp     = !empty($param->info->app_fp) ? $param->info->app_fp : 0;
$percentage = !empty($param->info->interest_percentage) ? $param->info->interest_percentage : 0;

$Qry           = new Query();
$Qry->table    = "tblloantype";
$Qry->selected = "`desc`                  ='".$param->info->desc."',
                  loan_mode             ='".$param->info->loan_mode."', 
                  type                  ='".$param->info->type."',
                  stats                 ='".$param->info->stats."', 
                  interest_percentage   ='".$percentage."',
                  app_first	            ='".$app_first."',
                  app_second	        ='".$app_second."',
                  app_sp	            ='".$app_sp."',
                  app_fp	            ='".$app_fp."',
                  priority              ='".$param->info->priority."',
                  debit	                ='".$param->info->debit."', 
                  credit	            ='".$param->info->credit."'
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