<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 


$data = array( 
	"templates" => getPMUploaderTemplate($con),
	"batchentries" => getBatchEntriesName($con),
	"loanentries" => getLoanName($con),
	"recurringentries" => getRecurringName($con),
	"servicechargeentries" => getScName($con),
	"getempname" => getEmpname($con)
);
$return = json_encode($data);

print $return;
mysqli_close($con);
?>