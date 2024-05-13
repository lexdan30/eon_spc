<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));
$ids = implode(",",$param->id);

$Qry           = new Query();
$Qry->table    = "tblchartofaccount";
$Qry->selected = "*";
$Qry->fields   = "id in (".$ids.")";                      
$rs = $Qry->exe_DELETE($con);

mysqli_close($con);
?>