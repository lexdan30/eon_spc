<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 


$data = array( 
	"accounts" 		=> getAccounts($con,''),
	"allaccounts"	=> getAllAccounts($con,''),
	"departments"	=> getAllDepartment($con),
	"emptypes" 		=> getEmployeeType($con),
    "acctypes" 		=> getAccountType($con),
	"civilstat"		=> getCivilStatus($con),
	"positions"		=> getPositions($con),
	"empstatus"		=> getEmpStatus($con),
	"paygrps"		=> getPayGroups($con),
	"labors"		=> getLabors($con),
	"joblvl"		=> getJobLvls($con),
	"locs"			=> getJobLocation($con),
	"paygroups"		=> getPayGroups($con),
	"paystat"		=> getPayStatus($con),
	"salutation"	=> getSalutation($con),
	"suffix"		=> getSuffix($con),
	"religion"		=> getReligion($con),
	"citizenship"	=> getCitizenship($con),
	"bloodtype"		=> getBloodtype($con),
	"eyecolor"		=> getEyecolor($con),
	"haircolor"		=> getHaircolor($con),
	"skincolor"		=> getSkincolor($con),
	"buildtype"		=> getBuildtype($con),
	"banks"			=> getBanks($con),
	"docs"			=> getDocumnets($con),
	"paytype"		=> getPaytype($con),
	"govpaytype"	=> getGovPaytype($con),
	"cbatype"		=> getCBAtype($con),
	"allmethod"		=> getAllowanceMethod($con),
	"allregion"		=> getAllRegion($con),
	"period"		=> getPayPeriod($con),
	"regions"		=> getRegions($con),
	"provinces"		=> getProvinces($con),
	"municipality"	=> getMunicipality($con),
	"sites"			=> getLocations($con),
	"mainDept"		=> getAllMainDepartment($con)
);
$return = json_encode($data);

print $return;
mysqli_close($con);
?>