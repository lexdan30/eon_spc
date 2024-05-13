<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));
$data = array();


$Qry7 = new Query();	
$Qry7->table     = "vw_dataemployees vd LEFT JOIN vw_data_timesheet vt ON vt.idacct = vd.id";
$Qry7->selected  = "vd.empname,vd.empid,vt.temp";
$Qry7->fields    = "vt.work_date = CURDATE() ";
$rs7 = $Qry7->exe_SELECT($con);

while($row=mysqli_fetch_array($rs7)){
    if($row['temp'] > 37.4){
        $data[] = array( 
            "id" 			    => $row['empid'],
            "name" 			    => $row['empname'],
            "empactiontaken" 	=> '<p style="color : red">High Temperature : ' . $row['temp'] .  ' &#8451;</p>',
        );
    }
}

$return = json_encode($data);

print $return;
mysqli_close($con);
?>