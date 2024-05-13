<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tbl13th";
$Qry->selected  = "*";
$Qry->fields = "status = 0";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        $row['idpaygroup'] = getPaygroup($con,$row['idpaygroup'] );
        $row['paydate'] = getpayDate($con,$row['paydate'] );

        $data[] = $row;
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array("err"=>mysqli_error($con)));
	
}
print $return;
mysqli_close($con);

function getPaygroup($con,$id){
    if($id == 'all'){
        return 'All';
    }
    $Qry = new Query();	
    $Qry->table     = "tblpaygrp";
    $Qry->selected  = "`group`";
    $Qry->fields = "id='".$id."'";
    $rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
           return $row['group'];
        }
    }
}
function getpayDate($con,$id){
    $Qry = new Query();	
    $Qry->table     = "tblpayperiod";
    $Qry->selected  = "`pay_date`";
    $Qry->fields = "id='".$id."'";
    $rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){

           return $row['pay_date'];
        }
    }
}
?>