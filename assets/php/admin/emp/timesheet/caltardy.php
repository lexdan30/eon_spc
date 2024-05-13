<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param 		= json_decode(file_get_contents('php://input'));
$return 	= null;	

$year = date("Y");
$month = 1;
$months = array(
	'January',
	'February',
	'March',
	'April',
	'May',
	'June',
	'July ',
	'August',
	'September',
	'October',
	'November',
	'December',
);

if( !empty( $param->accountid ) ){
	
	$arr_late = array();
	$arr_ut	  = array();
	$arr_abs  = array();
	
	while($month<=12){
		if( $month != 12 ){
			$dFrom	= $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-01";
			$dTo	= $year."-".str_pad(((int)$month+1),2,"0",STR_PAD_LEFT)."-01";
		}else{
			$dFrom	= $year."-12-01";
			$dTo	= ((int)$year+1)."-01-01";
		}
		
		list($late, $ut, $abs) = getCounts($con,  $param->accountid, $dFrom, $dTo);
		array_push($arr_late, $late);
		array_push($arr_ut, $ut);
		array_push($arr_abs, $abs);
		
		$month++;
	}
	
	
	$data	 	= array(
		"status"	=>	"success",
		"late"		=>	$arr_late,
		"ut"		=>	$arr_ut,
		"absent"	=>	$arr_abs
	);
	
	$return =  json_encode($data);
}else{
	$return = json_encode(array('status'=>'error'));
}

$return =  json_encode($data);
print $return;
mysqli_close($con);

function getCounts($con,  $idacct, $dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "vw_emp_timesheetfinal";
	$Qry->selected  = "
	COUNT(CASE WHEN ut IS NOT NULL AND ut <> '0.00' THEN tid ELSE NULL END) AS `ut`,
	COUNT(CASE WHEN late IS NOT NULL AND late <> '0.00' THEN tid ELSE NULL END) AS `late`,
	COUNT(CASE WHEN absent IS NOT NULL AND absent <> '0.00' THEN tid ELSE NULL END) AS `absent`";
	$Qry->fields    = "tid = '".$idacct."' AND work_date BETWEEN '".$dFrom."' AND '".$dTo."'";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_assoc($rs)) {
			return array($row['ut'], $row['late'], $row['absent']);
		}
	}
	return 0;
}


?>