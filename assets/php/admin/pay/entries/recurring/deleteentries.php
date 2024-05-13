<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));


//if(checkOpenPaydate( $con, $param->paystart )){
    $Qry 			= new Query();	
    $Qry->table     = "tblrecurring";
    $Qry->fields    = "id='".$param->id."'";
    $be 			= $Qry->exe_DELETE($con);
    if($be){
        $Qry2 			= new Query();	
        $Qry2->table     = "tblrecurringdetails";
        $Qry2->fields    = "recurringid='".$param->id."'";
        $bd 			= $Qry2->exe_DELETE($con);
        if($bd){
            $return = json_encode(array("status"=>"success"));
        }else{
            $return = json_encode(array("status"=>"error2")); 
        }
    }else{
        $return = json_encode(array("status"=>"error1"));
    }
// }else{
// $return = json_encode(array("status"=>"invalid"));
// }

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