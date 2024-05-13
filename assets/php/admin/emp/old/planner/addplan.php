<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$param->viewers = implode(",", $param->viewers); 

$Qry           = new Query();
$Qry->table    = "tblaccountplan";
$Qry->selected = "idcreator, event_title, efrom, eto, type, canview";
$Qry->fields   = "'".$param->accountid."', '".$param->title."', '".date($param->start)."', '".date($param->end)."', '".$param->type."', '".$param->viewers."'";                        
$rs = $Qry->exe_INSERT($con);


mysqli_close($con);
?>