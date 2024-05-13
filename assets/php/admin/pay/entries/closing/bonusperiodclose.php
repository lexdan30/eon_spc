<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tblbonuses";
$Qry->selected = "prclose = NOW(), pidby = '" . $param->accountid . "', prstat = 1";
$Qry->fields   = "id='".$param->ppid."'";                        
$rs = $Qry->exe_UPDATE($con);

if($rs){
    $return = json_encode(array("status"=>'success'));
}else{
    $return = json_encode(array("status"=>'error'));
}

mysqli_close($con);
?>