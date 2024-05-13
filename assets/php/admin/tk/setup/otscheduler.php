<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
$param = json_decode(file_get_contents('php://input'));
if(empty($param->accountid)){
	header("Location: http://192.168.2.50/eon_spc/#/");
}

require_once('../../../evaluateOT.php'); 
require_once('../../../classPhp.php'); 
$data = array(  
	"evaluateOT" 	=> processOtEvaluation($con)
);
$return = json_encode($data);

print $return;
mysqli_close($con);
?> 