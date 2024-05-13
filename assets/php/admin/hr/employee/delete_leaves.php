<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry_Dept1 = new Query();
$Qry_Dept1->table="tblaccountleaves";
$Qry_Dept1->fields="id = $param->id";
$RS_Dept1=$Qry_Dept1->exe_DELETE($con);

// print($param->id); 

//Prompt Error or Success
if(!$RS_Dept1){
    $return = json_encode(array('status'=>'error'));
}
else{
    $return = json_encode(array('status'=>'success'));
}

print $return;
mysqli_close($con);

?>