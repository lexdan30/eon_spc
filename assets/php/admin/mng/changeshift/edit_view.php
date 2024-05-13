<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data = array();

$ids = implode(",",$param->id);

$Qry = new Query();	
$Qry->table     = "vw_shift_application";
// $Qry->selected = "*";
$Qry->selected  = "
id, docnumber, creator, idacct, empid,
empname, idsuperior, idunit ,`date`, remarks,
file, stat, oldidshift, idshift, newshift,
oldshift, shift_status, date_create, approver1, approver1_name,
approver1_reason, date_approve, id_payperiod, period_start, period_end, grace_hour";
$Qry->fields    = "id in (".$ids.")";
$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
	$data = array(
		"reason"		=> '',
		"stat"			=> '',
		"data"			=> array()
	);
    while($row=mysqli_fetch_assoc($rs)){
        $data["data"][] = array( 
            "id"				=>	$row["id"],
			"docnumber"			=>	$row["docnumber"],
			"creator"			=>	$row["creator"],
			"idacct"			=>	$row["idacct"],
			"empid"				=>	$row["empid"],
			"empname"			=>	$row["empname"],
			"idsuperior"		=>	$row["idsuperior"],
			"idunit"			=>	$row["idunit"],
			"date"				=>	$row["date"],
			"remarks"			=>	$row["remarks"],
			"file"				=>	$row["file"],
			"stat"				=>	$row["stat"],
			"oldidshift"		=>	$row["oldidshift"],
			"idshift"			=>	$row["idshift"],
			"newshift"			=>	$row["newshift"],
			"oldshift"			=>	$row["oldshift"],
			"shift_status"		=>	$row["shift_status"],
			"date_create"		=>	$row["date_create"],
			"approver1"			=>	$row["approver1"],
			"approver1_name"	=>	$row["approver1_name"],
			"approver1_reason"	=>	$row["approver1_reason"],
			"date_approve"		=>	$row["date_approve"],
			"id_payperiod"		=>	$row["id_payperiod"],
			"period_start"		=>	$row["period_start"],
			"period_end"		=>	$row["period_end"],
			"grace_hour"		=>	$row["grace_hour"]
        );
    }
}
        
$return = json_encode($data);

print $return;
mysqli_close($con);
?>