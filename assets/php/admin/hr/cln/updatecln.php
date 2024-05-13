<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));


$Qry           = new Query();
$Qry->table    = "tbldocutemp";
$Qry->selected = "name='".$param->cln->name."',
                    type='".$param->cln->type."',
                    status='".$param->cln->status."',
                    remarks='".$param->cln->remarks."',
                    content='".addslashes($param->cln->content)."'";

$Qry->fields   = "id='".$param->cln->id."'";                        
$rs = $Qry->exe_UPDATE($con);


mysqli_close($con);
?>