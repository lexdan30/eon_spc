<?php
require_once('../../../activation.php');
$conn = new connector();	
$param = json_decode(file_get_contents('php://input'));
if( (int)$param->conn == 1 ){	
	$con = $conn->connect();
}else{
	$varcon = "connect".(int)$param->conn;
	$con = $conn->$varcon();
}
require_once('../../../classPhp.php'); 

$data = array( 
    "unittypes" 	=> getUnitTypes($con),
    "unithead"  	=> getUnitHeads($con),
	"departments"	=> getAllDepartment($con),
	"locations"	=> getLocations($con),
);
$return = json_encode($data);

print $return;
mysqli_close($con);
?>