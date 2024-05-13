<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry_Dept = new Query();
$Qry_Dept->table="tblacctallowance";
$Qry_Dept->fields="id = $param->id";
$RS_Dept=$Qry_Dept->exe_DELETE($con);


//Prompt Error or Success 
if(!$RS_Dept){
    $return = json_encode(array('status'=>'error'));
}
else{
    $return = json_encode(array('status'=>'success'));
}

print $return;
mysqli_close($con);

?>