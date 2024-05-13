<?php
require_once('../../../activation.php');
$param = json_decode(file_get_contents('php://input'));

$conn = new connector();
if(empty($param->accountid)){
	header("Location: http://192.168.2.50/eon_spc/#/");
}

if( !empty($param->conn) && (int)$param->conn > 1 ){	
	$varcon = "connect".(int)$param->conn; 
	$con = $conn->$varcon();
}else{
	$con = $conn->connect();
}

require_once('../../../classPhp.php'); 
// require_once('../../../evaluateOT.php'); 
 
switch (!isset($param->request) ? '' : $param->request) {
	// Start-Block Employees Portal
	case 'EPTimekeeping':
	case 'EPAppLeave':
		$data = array( 
			"accounts"		=> getAccounts($con,''),
			"leaves"		=> getLeaves($con),
			"period"		=> getPayPeriodts($con),
			"prev_period"	=> getPayPeriod($con,getPayPeriod($con)['id']-1),
			"server" 		=> $_SERVER
		);
		break;

	case 'EPTimesheet':
		$data = array( 
			"accounts"		=> getAccounts($con,''),
			"period"		=> getPayPeriodts($con),
			"departments"	=> getAllDepartment($con),
			"shifttypes"	=> getShiftsTypes($con),
			"server" 		=> $_SERVER
		);
		break;

	case 'EPDashboard':
		$data = array( 
			"accounts"		=> getAccounts($con,''),
			"period"		=> getPayPeriodts($con),
			"server" 		=> $_SERVER
		);
		break;

	case 'EPPayslip':
		$data = array( 
			"allaccounts"	=> getAllAccounts($con,''),
			"period"		=> getPayPeriodts($con),
			"server" 		=> $_SERVER
		);
		break;

	case 'EPAnnouncement':
		$data = array( 
			"period"		=> getPayPeriodts($con),
			"server" 		=> $_SERVER
		);
		break;

	case 'EPProfile':
		$data = array( 
			"accounts"		=> getAccounts($con,''),
			"server" 		=> $_SERVER
		);
		break;		
	// End-Block Employees Portal
	// Start-Block Managers Portal
	case 'MNGHome':
		$data = array( 
			"accounts"		=> getAccounts($con,''),
			"period"		=> getPayPeriodts($con),
			"leaves"		=> getLeaves($con),
			"joblocation"	=> getJobLocation($con),
			"server" 		=> $_SERVER
		);
		break;

	case 'MNGAttendanceToday':
	case 'MNGMyTeam':
		$data = array( 
			"accounts"		=> getAccounts($con,''),
			"leaves"		=> getLeaves($con),
			"server" 		=> $_SERVER
		);
		break;

	case 'MNGTimesheetRecord':
		$data = array( 
			"period"		=> getPayPeriodts($con),
			"accounts"		=> getAccounts($con,''),
			"underaccounts"	=> getUnderAccounts($con,$param->accountid),
			"departments"	=> getAllDepartment($con),
			"shifttypes"	=> getShiftsTypes($con),
			"server" 		=> $_SERVER
		);
		break;

	case 'MNGAppChangeShift':
	case 'MNGTardinessReport':			
	case 'MNGResoCenter':
	case 'MNGYTDASR':
		$data = array( 
			"accounts"		=> getAccounts($con,''),
			"leaves"		=> getLeaves($con),
			"period"		=> getPayPeriodts($con),
			"server" 		=> $_SERVER
		);
		break;

	case 'MNGAppAdjustment':
	case 'MNGAppOvertime':
	case 'MNGAppOB':
	case 'MNGAppLeave':
	case 'MNGAppWithdrawal':
		$data = array( 
			"accounts"		=> getAccounts($con,''),
			"period"		=> getPayPeriodts($con),
			"server" 		=> $_SERVER
		);
		break;

	case 'MNGSchedulerShiftSched':
		$data = array( 
			"accounts"		=> getAccounts($con,''),
			"period"		=> getPayPeriodts($con),
			//"underaccounts"	=> getUnderAccounts($con,$param->accountid),
			"server" 		=> $_SERVER
		);
		break;

	case 'MNGLeaveBalanceReport':
		$data = array( 
			"accounts"		=> getAccounts($con,''),
			"leaves"		=> getLeaves($con),
			"period"		=> getPayPeriodts($con),
			"joblvl"		=> getJobLvls($con),
			"departments"	=> getAllDepartment($con),
			"server" 		=> $_SERVER
		);
		break;

	case 'MNGTimeFilling':
		$data = array( 
			"accounts"		=> getAccounts($con,''),
			"work_dates"	=> getMinMax($con),
			"leaves"		=> getLeaves($con),
			"period"		=> getPayPeriodts($con),
			"server" 		=> $_SERVER
		);
		break;

	case 'MNGLeaveFilling':
		$data = array( 
			"accounts"		=> getAccounts($con,''),
			"work_dates"	=> getMinMax($con),
			"leaves"		=> getLeaves($con),
			"period"		=> getPayPeriodts($con),
			"prev_period"	=> getPayPeriod($con,getPayPeriod($con)['id']-1),
			"server" 		=> $_SERVER
		);
		break;
	case 'MNGSchedulerperSection':
		$data = array(
			"accounts"		=> getAccounts($con,''),
			"unittypes" 	=> getUnitTypes($con),
			"unithead"  	=> getUnitHeads($con),
			"locations" 	=> getLocations($con),
			"server" 		=> $_SERVER
		);
		break;
	// End-Block Managers Portal

	default:
		$data = array( 
			"leavetype" 	=> getLeaveTypes($con),
			"allaccounts"	=> getAllAccounts($con,''),
			"leaves"		=> getLeaves($con),
			"shifttypes"	=> getShiftsTypes($con),
			"holidaytypes"	=> getHolidayTypes($con),
			"regions"		=> getRegions($con),
			"provinces"		=> getProvinces($con),
			"municipality"	=> getMunicipality($con),
			"accounts"		=> getAccounts($con,''),
			"business"		=> getBusinessDept($con,''),
			"jaccounts"		=> getJAccounts($con,''),
			"periodall"		=> getPayPeriod($con),
			"period"		=> getPayPeriodts($con),
			"prev_period"	=> getPayPeriod($con,getPayPeriod($con)['id']-1),
			"departments"	=> getAllDepartment($con),
			"departmentstk"	=> getAllDepartmentTK($con),
			"positions"		=> getPositions($con),
			"joblocation"	=> getJobLocation($con),
			"paygroup"		=> getPayGroups($con),
			"labortype"		=> getLabors($con),
			"joblvl"		=> getJobLvls($con),
			"work_dates"	=> getMinMax($con),
			"measures"		=> getMeasures($con),
			"timeconf"		=> getTimeLogsConf($con),
			"workshifts" 	=> getWShifts($con),
			"holidays" 		=> getHoliday($con),
			"orgchart" 		=> getAccountsOrg($con,''),
			"coa" 			=> getchartsofaccounts($con),
			"locations" 	=> getLocations($con),
			"maindepartments" 	=> getMainDepartment($con),
			"company_name" 	=> getCompanyName1($con)
			// "evaluateOT" 	=> processOtEvaluation($con)
		);
		break;
}

$return = json_encode($data);

print $return;
mysqli_close($con);
?>