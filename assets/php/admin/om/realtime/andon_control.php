<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
 
$Qry           = new Query();
$Qry->table    = "tblmachine_master";
$Qry->selected = "control_line     ='".$param->control_no."'";

$Qry->fields   = "id='".$param->machine_id."'";  
           
$checke = $Qry->exe_UPDATE($con);

if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error', 'mysqli'=>mysqli_error($con)));
}
print $return;
mysqli_close($con);
?>
