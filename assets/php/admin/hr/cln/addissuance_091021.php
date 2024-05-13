<?php
require_once('../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tbldocuissued";
$Qry->selected = "idacct,docuid,templateid,depid,empid,approver1,approver2,approver3,remarks,date_created,date_issued,apply_date";
$Qry->fields   = "'".$param->accountid."',
                    '".$param->issued->id."', 
                    '".$param->issued->template."',
                    '".$param->issued->depid."',
                    '".$param->issued->empid."',
                    '".$param->issued->approver1."',
                    '".$param->issued->approver2."',
                    '".$param->issued->approver3."',
                    '".$param->issued->remarks."',
                    '".$param->issued->date_created."',
                    '".$param->issued->dateissued."',
                    '".$param->issued->apply_date."'";                        
$rs = $Qry->exe_INSERT($con);
if( !$rs ){
	echo mysqli_error($con);
}

mysqli_close($con);
?>