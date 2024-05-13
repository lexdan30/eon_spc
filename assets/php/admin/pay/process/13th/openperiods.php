<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblpayperiod";
$Qry->selected  = "*";
$Qry->fields = "stat = 0";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        $data[] = $row;
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array("err"=>mysqli_error($con)));
	
}
print $return;
mysqli_close($con);
?>