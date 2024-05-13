<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date_create=SysDate();
$time_create=SysTime();

if($param->type == 'changeshift'){
    $Qry           = new Query();
    $Qry->table    = "tbltimeshift";
    $Qry->selected = "stat='4', cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
    $Qry->fields   = "id='".$param->id."'";                        
    $rs = $Qry->exe_UPDATE($con);
}
if($param->type == 'attendance'){
    $Qry           = new Query();
    $Qry->table    = "tbltimeadjustment";
    $Qry->selected = "stat='4', cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
    $Qry->fields   = "id='".$param->id."'";                        
    $rs = $Qry->exe_UPDATE($con);
}
if($param->type == 'overtime'){
    $Qry           = new Query();
    $Qry->table    = "tbltimeovertime";
    $Qry->selected = "stat='4', cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
    $Qry->fields   = "id='".$param->id."'";                        
    $rs = $Qry->exe_UPDATE($con);
}
if($param->type == 'obtrip'){
	$Qry           = new Query();
	$Qry->table    = "tbltimeobtrip";
	$Qry->selected = "stat='4',cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
	$Qry->fields   = "id='".$param->id."'";                        
	$rs = $Qry->exe_UPDATE($con);
}
// mysqli_close($con);
?>