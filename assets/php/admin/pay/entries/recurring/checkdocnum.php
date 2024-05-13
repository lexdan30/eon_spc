<?php

require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblrecurring";
$Qry->selected  = "*";
$Qry->fields = "docnum='".$param->docnum."'";
$rs = $Qry->exe_SELECT($con);

if(mysqli_num_rows($rs)>= 1){
    $return = json_encode(array("status"=>'yes'));
}else{
	$return = json_encode(array("status"=>'no'));
	
}
print $return;
mysqli_close($con);
?>