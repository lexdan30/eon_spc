<?php
error_reporting(0);
date_default_timezone_set('Asia/Manila');
require_once('../../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));

$stats      = !empty($param->info->stats) ? 0 : 1; 
 
$Qry           = new Query();
$Qry->table    = "tblplannerdata_master";
$Qry->selected = "`kanban_id`,
                    `wo_no`,
                    `prod_no`,
                    `prod_qty`,
                    `stats`
                    ";
$Qry->fields   = "'".$param->info->kanban_id."', 
                    '".$param->info->wo_no."', 
                    '".$param->info->prod_no."',
                    '".$param->info->prod_qty."',
                    '".$stats."'
                    ";       
         
$rs = $Qry->exe_INSERT($con);

if($rs){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error','mysqlerror'=>mysqli_error($con)));
}
print $return;
mysqli_close($con);
?>