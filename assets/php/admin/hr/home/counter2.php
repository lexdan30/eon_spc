<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data = array();

$Qry = new Query();	
$Qry->table     = "vw_dataemployees AS a";
$Qry->selected  = "a.`idunit`, a.`business_unit`";
$Qry->fields    = "a.`idunit` > 0 GROUP BY a.`idunit`";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
	while($row=mysqli_fetch_array($rs)){
        $arr = getAttendanceCtr($con, $row['idunit'], $param);
		$data[] = array( 
			"idunit"			=> $row['idunit'],
			"unit"				=> $row['business_unit'],
			"present_ctr"		=> $arr['present'],
			"lte_ctr"			=> $arr['lte'],
			"absent_ctr"		=> $arr['absent'],
			"vl_ctr"			=> $arr['vl'],
			"sl_ctr"			=> $arr['sl'],
			"lwop_ctr"			=> $arr['lwop']			
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);

function getAttendanceCtr($con, $idunit, $param){
	$data = array( 
		"present"		=> 0,
		"lte"			=> 0,
		"absent"		=> 0,
		"vl"			=> 0,
		"sl"			=> 0,
		"lwop"			=> 0	
	);
	$Qry 			= new Query();	
	$Qry->table     = " vw_dataemployees AS a INNER JOIN vw_data_timesheet AS b ON a.id = b.empID";
	$Qry->selected  = " SUM(IF(b.absent = 0, 1, 0)) AS present_ctr,
						SUM(IF(b.late > 0 , 1, 0)) AS lte_ctr, 
						SUM(IF(b.absent > 0 && ISNULL(b.leavehrs) , 1, 0)) AS absent_ctr,
						SUM(IF(b.idleave = 2 && b.leavestat = 1, 1, 0)) AS vl_ctr,
						SUM(IF(b.idleave = 1 && b.leavestat = 1, 1, 0)) AS sl_ctr,
						SUM(IF(b.idleave = 3 && b.leavestat = 1, 1, 0)) AS lwop_ctr";
	$Qry->fields    = "a.`idunit`='".$idunit."' AND b.work_date BETWEEN '".$param->start_date."' AND '".$param->end_date."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			$data = array( 
				"present"		=> $row['present_ctr'],
				"lte"			=> $row['lte_ctr'],
				"absent"		=> $row['absent_ctr'],
				"vl"			=> $row['vl_ctr'],
				"sl"			=> $row['sl_ctr'],
				"lwop"			=> $row['lwop_ctr']			
			);
		}
	}
	return $data;
}

?>