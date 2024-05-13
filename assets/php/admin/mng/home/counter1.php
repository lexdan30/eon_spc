<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data = array();

$Qry = new Query();	
$Qry->table     = "vw_dataemployees AS a INNER JOIN vw_data_timesheet AS b ON a.id = b.empID";
$Qry->selected  = "a.empid, a.empname, 
				   SUM(IF(b.late > 0 , 1, 0)) AS lte_ctr, ( SELECT GROUP_CONCAT(CONCAT(work_date,' ',late)) FROM  vw_data_timesheet WHERE empID=b.empID AND late > 0 AND work_date BETWEEN '".$param->start_date."' AND '".$param->end_date."') AS late_stamp,
				   SUM(IF(b.absent > 0 && ISNULL(b.leavehrs) , 1, 0)) AS absent_ctr, ( SELECT GROUP_CONCAT(CONCAT(work_date,' ',absent)) FROM  vw_data_timesheet WHERE empID=b.empID AND (absent > 0 AND ISNULL(leavehrs)) AND work_date BETWEEN '".$param->start_date."' AND '".$param->end_date."') AS absent_stamp";
$Qry->fields    = "a.idsuperior='".$param->accountid."' AND b.work_date BETWEEN '".$param->start_date."' AND '".$param->end_date."' GROUP BY empid";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
	$late_ctr=0;
	$abs_ctr=0;
    while($row=mysqli_fetch_array($rs)){
        
		$late_date	= array();
		$late_hrs	= array();
		$absent_date= array();
		$absent_hrs	= array();
		
		if( (int)$row['lte_ctr'] > 0 ){
			$late_ctr = $late_ctr + (int)$row['lte_ctr'];
			$arr_lates	= explode(",",$row['late_stamp']);
			foreach( $arr_lates as $v1 ){
				$t1	= explode(" ",$v1);
				array_push($late_date,$t1[0]);
				array_push($late_hrs,$t1[1]);
			}
		}
		
		if( (int)$row['absent_ctr'] > 0 ){
			$abs_ctr = $abs_ctr + (int)$row['absent_ctr'];
			$arr_absnt	= explode(",",$row['absent_stamp']);			
			foreach( $arr_absnt as $v2 ){
				$t1	= explode(" ",$v2);
				array_push($absent_date,$t1[0]);
				array_push($absent_hrs,$t1[1]);
			}
		}
		
		$data[] = array( 
			"empid"				=> $row['empid'],
			"empname"			=> $row['empname'],
			"lte_ctr"			=> $row['lte_ctr'],
			"lte_dates"			=> $late_date,
			"lte_hrs"			=> $late_hrs,
			"late_hours"		=> array_sum($late_hrs),
			"absent_ctr"		=> $row['absent_ctr'],
			"absent_dates"		=> $absent_date,
			"absent_hrs"		=> $absent_hrs,
			"absent_hours"		=> array_sum($absent_hrs),
			"total_late_ctr"	=> 0,
			"total_absent_ctr"	=> 0
        );
    }
	$data[0]['total_late_ctr'] = $late_ctr;
	$data[0]['total_absent_ctr'] = $abs_ctr;
	$return = json_encode($data);
}else{
	$return = json_encode(array('status'=>'error'));
	
}
print $return;
mysqli_close($con);
?>