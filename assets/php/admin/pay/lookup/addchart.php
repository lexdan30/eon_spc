<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tblchartofaccount";
$Qry->selected = "code,description,type,lt,textfield";
$Qry->fields   = "'".$param->add->code."', '".$param->add->description ."', '".$param->add->type ."', '".$param->add->lt ."', '".$param->add->textfield."'";                        
$rs = $Qry->exe_INSERT($con);

mysqli_close($con);
?>