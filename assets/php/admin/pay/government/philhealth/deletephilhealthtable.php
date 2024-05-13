<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$ids = implode(",",$param->ids);

$Qry           = new Query();
$Qry->table    = "tblcont_health";
$Qry->fields    =  "id in (".$ids.")";
$checke = $Qry->exe_DELETE($con);

if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error'));
}
print $return;
mysqli_close($con);
?>