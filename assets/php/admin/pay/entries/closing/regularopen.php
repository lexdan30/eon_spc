<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();

if($param->rp->type == 'Helper'){
    $Qry->table    = "tblpayperiod_helper";
}else if($param->rp->type == 'Japanese'){
    $Qry->table    = "tblpayperiod_japanese";
}else if($param->rp->type == 'Japanese Conversion'){
    $Qry->table    = "tblpayperiod_japaneseconversion";
}else{
    $Qry->table    = "tblpayperiod";
}

$Qry->selected = "dateclosed = null, idby= null, tkstatus = 0, tkprocess = 0, payprocess = 0";
$Qry->fields   = "id='".$param->rp->id."'";                        
$rs = $Qry->exe_UPDATE($con);

if($rs){
    $return = json_encode(array("status"=>'success'));
}else{
    $return = json_encode(array("status"=>'error',"mysqli_error" => mysqli_error($con)));
}

print($return);
mysqli_close($con);
?>