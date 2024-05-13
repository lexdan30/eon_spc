<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

if(empty($param->accountid)){
	header("Location: http://192.168.2.50/eon_spc/#/");
}

$data = array( 
	"emptypes" => getEmployeeType($con),
    "acctypes" => getAccountType($con),
	"civilstat"=> getCivilStatus($con),
	"coa" => getchartsofaccounts($con),
	"period"		=> getPayPeriod($con),
	"periodts"		=> getPayPeriodts($con),
	"allaccounts"	=> getAllAccounts($con,''),
	"company_name" 	=> getCompanyName1($con)
);
$return = json_encode($data); 

print $return;
mysqli_close($con);
?> 