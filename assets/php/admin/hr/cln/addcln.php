<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));


$Qry           = new Query();
$Qry->table    = "tbldocutemp";
$Qry->selected = "name,type,status,remarks,content,idacct";
$Qry->fields   = "'".$param->cln->name."', '".$param->cln->type ."', '".$param->cln->status ."', '".$param->cln->remarks ."', '".addslashes($param->cln->content) ."', '".$param->accountid."'";                        
$rs = $Qry->exe_INSERT($con);

mysqli_close($con);
?>