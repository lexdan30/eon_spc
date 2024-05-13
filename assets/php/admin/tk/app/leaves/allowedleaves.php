<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblaccountleaves";
$Qry->selected  = "idleave";
$Qry->fields    = "idacct = '".$param->accountid."'";
$rs = $Qry->exe_SELECT($con);

$id = array();
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        array_push($id, $row['idleave']);
    }
	$return = json_encode($id);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);
?>