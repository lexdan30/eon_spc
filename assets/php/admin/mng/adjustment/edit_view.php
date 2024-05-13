<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data = array();

$ids = implode(",",$param->id);

$Qry = new Query();	
$Qry->table     = "vw_attendance_application";
// $Qry->selected = "*";
$Qry->selected  = "
id, docnumber, idunit, idsuperior, idacct,
empid, empname, `date`, stime, ftime,
file, hrs, remarks, file, adj_status,
date_approve, id_payperiod, period_start, period_end, grace_hour";
$Qry->fields    = "id in (".$ids.")";
$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
	$data = array(
		"idacct"		=> '',
		"date"			=> '',
		"reason"		=> '',
		"stat"			=> '',
		"data"			=> array()
	);
    while($row=mysqli_fetch_assoc($rs)){
        $data["data"][] = array( 
            "id"        	=> $row['id'],
            "docnumber" 	=> $row['docnumber'],
			"idunit"		=> $row['idunit'],
			"idsuperior"	=> $row["idsuperior"],
            "idacct" 		=> $row['idacct'],
            "empid" 		=> $row['empid'],
            "empname" 		=> $row['empname'],
            "date" 			=> $row['date'],
            "stime" 		=> date('h:i A', strtotime($row['stime'])),
			"ftime" 		=> date('h:i A', strtotime($row['ftime'])),
			"hrs" 			=> $row['hrs'],
			"remarks" 		=> $row['remarks'],
			"file" 			=> $row['file'],
			"adj_status"	=> $row['adj_status'],
			"date_approve"	=> $row['date_approve'],
			"id_payperiod"	=> $row['id_payperiod'],
			"period_start"	=> $row['period_start'],
			"period_end"	=> $row['period_end'],
			"grace_hour"	=> $row['grace_hour']
        );
    }
}
        
$return = json_encode($data);

print $return;
mysqli_close($con);
?>