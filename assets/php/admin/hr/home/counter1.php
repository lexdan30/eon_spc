<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data = array();

$Qry = new Query();	
$Qry->table     = "vw_dataemployees AS a INNER JOIN vw_data_timesheet AS b ON a.id = b.empID";
$Qry->selected  = " (SELECT COUNT(aa.id) FROM vw_dataemployees AS aa) AS staff,
					SUM(IF(b.absent = 0, 1, 0)) AS present_ctr,
					SUM(IF(b.late > 0 , 1, 0)) AS lte_ctr, 
					SUM(IF(b.absent > 0 && ISNULL(b.leavehrs) , 1, 0)) AS absent_ctr,
					SUM(IF(b.idleave = 2 && b.leavestat = 1, 1, 0)) AS vl_ctr,
					SUM(IF(b.idleave = 1 && b.leavestat = 1, 1, 0)) AS sl_ctr,
					SUM(IF(b.idleave = 3 && b.leavestat = 1, 1, 0)) AS lwop_ctr,
					DATEDIFF('".$param->end_date."','".$param->start_date."') + 1 AS dayz";
$Qry->fields    = "b.work_date BETWEEN '".$param->start_date."' AND '".$param->end_date."'";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
	$late_ctr=0;
	$abs_ctr=0;
    if($row=mysqli_fetch_array($rs)){
        $data = array( 
			"staff"				=> $row['staff'],
			"present_ctr"		=> $row['present_ctr'],
			"lte_ctr"			=> $row['lte_ctr'],
			"absent_ctr"		=> $row['absent_ctr'],
			"vl_ctr"			=> $row['vl_ctr'],
			"sl_ctr"			=> $row['sl_ctr'],
			"lwop_ctr"			=> $row['lwop_ctr'],
			"dayz"				=> $row['dayz']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);
?>