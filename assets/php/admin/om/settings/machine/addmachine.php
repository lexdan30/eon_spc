<?php
error_reporting(0);
date_default_timezone_set('Asia/Manila');
require_once('../../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));

$stats      = !empty($param->info->stats) ? 0 : 1; 
$auto_off      = !empty($param->info->auto_off) ? 0 : 1; 
 
$Qry           = new Query();
$Qry->table    = "tblmachine_master";
$Qry->selected = "`machine_pic`,
                    `machine_code`,
                    `machine_name`,
                    `description`,
                    `locator_code`,
                    `location`,
                    `control_line`,
                    `auto_off`,
                    `stats`
                    ";
$Qry->fields   = "'".$param->info->machine_pic."', 
                   '".$param->info->machine_code."', 
                    '".$param->info->machine_name."', 
                    '".$param->info->description."',
                    '".$param->info->locator_code."',
                    '".$param->info->location."',
                    '".$param->info->control_line."',
                    '".$auto_off."',
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