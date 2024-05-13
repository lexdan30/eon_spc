<?php
require_once('../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
// echo $param->accountid;
$Qry           = new Query();
$Qry->table    = "tbldocuissued";
$Qry->selected = "idacct,docuid,templateid,depid,empid,remarks,date_created,date_issued,apply_date";
$Qry->fields   = "'".$param->accountid."',
                    '".$param->issued->id."', 
                    '".$param->issued->template."',
                    '".$param->issued->depid."',
                    '".$param->issued->empid."',
                    '".$param->issued->remarks."',
                    '".$param->issued->date_created."',
                    '".$param->issued->dateissued."',
                    '".$param->issued->apply_date."'";     
                    if( !empty($param->issued->approver1) ){
                        $Qry->selected 	= $Qry->selected . ", `approver1`";
                        $Qry->fields 	= $Qry->fields 	 . ", '".$param->issued->approver1."'";
                    }
                    if( !empty($param->issued->approver2) ){
                        $Qry->selected 	= $Qry->selected . ", `approver2`";
                        $Qry->fields 	= $Qry->fields 	 . ", '".$param->issued->approver2."'";
                    }
                    if( !empty($param->issued->approver3) ){
                        $Qry->selected 	= $Qry->selected . ", `approver3`";
                        $Qry->fields 	= $Qry->fields 	 . ", '".$param->issued->approver3."'";
                    }                   
$rs = $Qry->exe_INSERT($con);
if( !$rs ){
	echo mysqli_error($con);
}

mysqli_close($con);
?>