<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 


$data = array( 
	"templates" => getHRUploaderTemplate($con)
);
$return = json_encode($data);

print $return;
mysqli_close($con);
?>