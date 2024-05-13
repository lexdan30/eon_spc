<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data = array();

$Qry = new Query();	
$Qry->table     = "vw_dataemployees AS a INNER JOIN vw_data_timesheet AS b ON a.id = b.empID";
$Qry->selected  = "a.empid, a.empname";
$Qry->fields    = "b.work_date BETWEEN '".$param->start_date."' AND '".$param->end_date."' GROUP BY empid";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
		$data[] = array( 
			"empid"				=> $row['empid'],
			"empname"			=> $row['empname']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array('status'=>'error'));
	
}
print $return;
mysqli_close($con);
?>