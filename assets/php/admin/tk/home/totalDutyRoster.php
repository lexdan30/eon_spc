<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));
$pay_period = getPayPeriod($con);

$Qry = new Query();
$Qry->table = "tbldutyroster";
$Qry->selected = "COUNT(*) as total";
$Qry->fields    = "(date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND type_creator=2 AND secretary=1 AND manager=0";

$rs = $Qry->exe_SELECT($con);
$data = array();
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