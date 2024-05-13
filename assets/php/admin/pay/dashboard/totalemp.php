<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));

$Qry = new Query();
$Qry->table = "vw_data_timesheet AS a";
$Qry->selected = "COUNT(a.empID) AS total";
$Qry->fields    = "a.work_date = '".SysDatePadLeft()."' AND a.idacct IS NOT NULL AND (a.in IS NOT NULL || a.out IS NOT NULL)";

$rs = $Qry->exe_SELECT($con);

if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data= array( 'total'  => $row['total'] );
    }
    $return = json_encode($data);
}else{
	$return = json_encode(array());
}


print $return;
mysqli_close($con);
?>