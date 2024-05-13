<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));



    $Qry 			= new Query();	
    $Qry->table     = "tblloans";
    $Qry->fields    = "id='".$param->id."'";
    $be 			= $Qry->exe_DELETE($con);
    if($be){ 
            $return = json_encode(array("status"=>"success"));
    }else{
        $return = json_encode(array("status"=>"error"));
    }

print $return;
mysqli_close($con);

function checkOpenPaydate( $con, $paystart ){
$Qry = new Query();	
$Qry->table     = "tblpayperiod";
$Qry->selected  = "*";
$Qry->fields    = "period_start<='".$paystart."' AND period_end>='".$paystart."' AND tkstatus = '0' AND stat = '0'";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    return true;
}
return false;
}
?>