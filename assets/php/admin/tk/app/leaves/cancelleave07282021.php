<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date_create=SysDate();
$time_create=SysTime();

$Qry           = new Query();
$Qry->table    = "tbltimeleaves";
$Qry->selected = "stat='4', cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
$Qry->fields   = "id='".$param->id."'";                        
$rs = $Qry->exe_UPDATE($con);

$Qry2 = new Query();	
$Qry2->table     = "tbltimeleaves";
$Qry2->selected  = "idacct, idleave, hrs";
$Qry2->fields    = "id='".$param->id."'";
$rs2 = $Qry2->exe_SELECT($con);

if(mysqli_num_rows($rs2)>= 1){
    if($row2=mysqli_fetch_array($rs2)){
        $Qry3           = new Query();
        $Qry3->table    = "tblaccountleaves";
        $Qry3->selected = "pending_bal = pending_bal - ".$row2['hrs']."";
        $Qry3->fields   = "idacct='".$row2['idacct']."' AND idleave='".$row2['idleave']."'";                        
        $Qry3->exe_UPDATE($con);
    }
}


mysqli_close($con);
?>