<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data = array();

$Qry = new Query();	
$Qry->table     = "tblleaves AS a";
$Qry->selected  = "a.id,a.name AS leave_type,
				   ( SELECT COUNT(aa.id) FROM vw_leave_application AS aa WHERE aa.idleave = a.id AND aa.stat=3 AND aa.date BETWEEN '".$param->start_date."' AND '".$param->end_date."' ) AS pending_ctr,
				   ( SELECT COUNT(aa.id) FROM vw_leave_application AS aa WHERE aa.idleave = a.id AND aa.stat=1 AND aa.date BETWEEN '".$param->start_date."' AND '".$param->end_date."' ) AS approve_ctr,
				   ( SELECT COUNT(aa.id) FROM vw_leave_application AS aa WHERE aa.idleave = a.id AND aa.stat=2 AND aa.date BETWEEN '".$param->start_date."' AND '".$param->end_date."' ) AS decline_ctr";
$Qry->fields    = "a.name is not null ORDER BY a.id ASC";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
	while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
			"id"				=> $row['id'],
			"name"				=> $row['leave_type'],
			"pending"			=> $row['pending_ctr'],
			"approve"			=> $row['approve_ctr'],
			"decline"			=> $row['decline_ctr'],
			"df"				=> $param->start_date,
			"dt"				=> $param->end_date
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array('status'=>'error'));
	
}
print $return;
mysqli_close($con);
?>