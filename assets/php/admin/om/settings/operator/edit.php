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
$Qry->table    = "tbloperator_master";
$Qry->selected = "`emp_id`,
                    `operators_name`,
                    `position`,
                    `stats`
                    ";
$Qry->fields   = "'".$param->info->emp_id."', 
                    '".$param->info->operators_name."', 
                    '".$param->info->position."',
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