<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param  = json_decode(file_get_contents('php://input'));
$ids = implode(',', $param->employees);

$Qry=new Query();

$Qry->table    = "tblfinalpay";
$Qry->selected = "tkstatus = 0";
$Qry->fields="idacct IN (".$ids.")";                 
$Qry->exe_UPDATE($con);


$myData = array('status' => 'success');
$return = json_encode($myData);

print $return;
mysqli_close($con);

?>
