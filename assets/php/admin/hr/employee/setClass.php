<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$return= array();

if( !empty($param->idpos) ){
$arr = array();
$arr = getclass( $con, $param->idpos, $param->idloc );
$return =  json_encode($arr);
}

print $return;
mysqli_close($con);
?>