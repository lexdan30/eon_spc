<?php
require_once('../../../activation.php');
$param = json_decode(file_get_contents('php://input'));

$conn = new connector();	

if( !empty($param->conn) && (int)$param->conn > 1 ){	
	
	$varcon = "connect".(int)$param->conn; 
	$con = $conn->$varcon();
}else{
	$con = $conn->connect();
}

require_once('../../../classPhp.php'); 
// require_once('../../../evaluateOT.php'); 
if(empty($param->accountid)){
	header("Location: http://192.168.2.50/eon_spc/#/");
}

$data = array( 
	
	"accounts"		=> getAccounts($con,''),
	"period"		=> getPayPeriodts($con)
	
);
$return = json_encode($data);

print $return;
mysqli_close($con);
?>