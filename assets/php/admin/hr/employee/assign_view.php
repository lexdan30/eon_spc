<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

$Qry 			= new Query();	
$Qry->table     = "vw_dataassign";
$Qry->selected  = "*";
$Qry->fields    = "idacct='".$param->id."'";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_array($rs)){
        $data = array( 
            "id"        	=> $row['id'],
            "idacct"		=> $row['idacct'],
			"idpos"			=> $row['idpos'],
			"idunit"		=> $row['idunit'],
			"idlabor"		=> $row['idlabor'],
			"idsuperior"	=> $row['idsuperior'],
			"empstat"		=> $row['empstat'],
			"idloc"			=> $row['idloc'],
			"wshift"		=> $row['wshift'],
			"schedtype"		=> $row['schedtype'],
			"hdate"			=> $row['hdate'],
			"rdate"			=> $row['rdate'],
			"sdate"			=> $row['sdate'],
			"salary"		=> number_format($row['salary'],2),
			"idemptype"		=> $row['idemptype'],
			"idlvl"			=> $row['idlvl'],
			"lvl"			=> $row['lvl'],
			"acct_name"		=> $row['acct_name'],
			"pos"			=> $row['pos'],
			"unit"			=> $row['unit'],
			"labor"			=> $row['labor'],			
			"supperior"		=> $row['supperior'],
			"estat"			=> $row['estat'],
			"loc"			=> $row['loc'],
			"workshift"		=> $row['workshift'],
			"scheduleType"	=> $row['schedule'],
			
			"idpaygrp"		=> $row['idpaygrp'],
			"paygroup"		=> $row['paygroup'],
			"idpaystat"		=> $row['idpaystat'],
			"paystatus"		=> $row['paystatus'],
			"idrevenue"		=> $row['idrevenue'],
			"payrevenue"	=> $row['payrevenue'],
			"dependent"		=> $row['dependent'],
			"idrelease"		=> $row['idrelease'],
			"payrelease"	=> $row['payrelease'],
			"prev_employer"	=> $row['prev_employer'],
			
			"supperiors"	=> getAccounts($con, $row['idacct'] ),
			"estats"		=> getEmpStatus($con),
			"locs"			=> getJobLocation($con),
			"workshifts"	=> getWShifts($con),
			"schedules"		=> getSchedType($con),
			"labors"		=> getLabors($con),
			"units"			=> getBusinessUnits($con),
			"poss"			=> getPositions($con),
			
			"paygroups"		=> getPayGroups($con),
			"paystat"		=> getPayStatus($con),
			"payrevenues"	=> getPayRevenues($con),
			"payreleases"	=> getPayRelease($con),
			"leaves"		=> getAccountleaves($con, $param->id ),
			"daysmonth"		=> $row['daysmonth']
			
        );
    }
}else{
	$data = array( 
		"id"        	=> '',
		"idacct"		=> $param->id,
		"idpos"			=> '',
		"idunit"		=> '',
		"idlabor"		=> '',
		"idsuperior"	=> '',
		"empstat"		=> '',
		"idloc"			=> '',
		"wshift"		=> '',
		"schedtype"		=> '',
		"hdate"			=> '',
		"rdate"			=> '',
		"sdate"			=> '',
		"salary"		=> '',
		"idemptype"		=> getLocalEmployeeType($con,$param->id),
		
		"acct_name"		=> '',
		"pos"			=> '',
		"unit"			=> '',
		"labor"			=> '',	
		"supperior"		=> '',
		"estat"			=> '',
		"loc"			=> '',
		"workshift"		=> '',
		"scheduleType"	=> '',
		
		"idpaygrp"		=> '',
		"paygroup"		=> '',
		"idpaystat"		=> '',
		"paystatus"		=> '',
		"idrevenue"		=> '',
		"payrevenue"	=> '',
		"dependent"		=> '0',
		"idrelease"		=> '',
		"payrelease"	=> '',
		"prev_employer"	=> '',
		"daysmonth"		=> '',
		
		"supperiors"	=> getAccounts($con, $param->id),
		"estats"		=> getEmpStatus($con),
		"locs"			=> getJobLocation($con),
		"workshifts"	=> getWShifts($con),
		"schedules"		=> getSchedType($con),
		"labors"		=> getLabors($con),
		"units"			=> getBusinessUnits($con),
		"poss"			=> getPositions($con),
		"paygroups"		=> getPayGroups($con),
		"paystat"		=> getPayStatus($con),
		"payrevenues"	=> getPayRevenues($con),
		"payreleases"	=> getPayRelease($con),
		"leaves"		=> getAccountleaves($con, $param->id )
	);
}

$return = json_encode($data);
print $return;
mysqli_close($con);

function getLocalEmployeeType($con, $idacct){	
	$Qry 			= new Query();	
	$Qry->table     = "tblaccount";
	$Qry->selected  = "idemptype";
	$Qry->fields    = "id='".$idacct."' ";
	$rs 			= $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			return $row['idemptype'];
		}
	}
	return '';
}

?>