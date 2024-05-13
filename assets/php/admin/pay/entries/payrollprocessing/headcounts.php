<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$param->id = getPayid($con, $param);

$paygroup = $param->data->paygroup;

$Qry = new Query();	
$Qry->table     = "tbltimesheetsummary";
$Qry->selected  = "COUNT(*) AS total";
$Qry->fields = "idpayperiod = '".$param->id."' AND type = '".$param->data->paytype."' AND idpaygrp ='".$paygroup."'";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_assoc($rs)){
        $data = $row['total'];
    }

	$myData = array('status' => 'success', 'result' => $data);
    $return = json_encode($myData);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);


function getPayid($con, $param){
    if($param->data->paytype == 'Local Employee'){
        $type = 'ho';
    }
    if($param->data->paytype == 'Helper'){
        $type = 'helper';
    }
    if($param->data->paytype== 'Japanese'){
        $type= 'hajap';
    }
    if($param->data->paytype == 'Japanese Conversion'){
        $type = 'hajapc';
    }


    $Qry = new Query();	
    $Qry->table     = "vw_payperiod_all";
    $Qry->selected  = "id";
    $Qry->fields = "pay_date='".$param->data->paydate."' AND type ='". $type."' ";
    $rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
           return $row['id'];
        }
    }
}
?>