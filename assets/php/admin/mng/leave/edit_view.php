<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data = array();

$Qry = new Query();	
$Qry->table     = "vw_leave_application";
$Qry->selected  = "
id, docnumber, idleave, leave_name, leave_type,
idtype, idacct, empid, empname, `date`,
stime, ftime, hrs, remarks, file,
stat, leave_status, date_approve, id_payperiod, period_start, period_end, grace_hour";
$Qry->fields    = "id='".$param->id."'";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_assoc($rs)){
        $data = array( 
            "id"        	=> $row['id'],
            "docnumber" 	=> $row['docnumber'],
			"idleave" 		=> $row['idleave'],
			"leave_name" 	=> $row['leave_name'],
			"leave_type" 	=> $row['leave_type'],
			"idtype" 		=> $row['idtype'],
            "idacct" 		=> $row['idacct'],
            "empid" 		=> $row['empid'],			
            "empname" 		=> $row['empname'],
            "date" 			=> $row['date'],
            "stime" 		=> date('h:i A', strtotime($row['stime'])),
			"ftime" 		=> date('h:i A', strtotime($row['ftime'])),
			"hrs" 			=> $row['hrs'],
			"remarks" 		=> $row['remarks'],
			"file" 			=> $row['file'],
			"curr_stat"		=> $row['stat'],
			"stat" 			=> $row['stat'],
			"leave_status"	=> $row['leave_status'],
			"date_approve"	=> $row['date_approve'],
			"id_payperiod"	=> $row['id_payperiod'],
			"period_start"	=> $row['period_start'],
			"period_end"	=> $row['period_end'],			
			"grace_hour"	=> $row['grace_hour'],
			"reason"		=> ''
        );
    }
}
        
$return = json_encode($data);

print $return;
mysqli_close($con);
?>