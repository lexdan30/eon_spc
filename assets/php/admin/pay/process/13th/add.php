<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tbl13th";
$Qry->selected = "start,end,idpaygroup,paydate";
$Qry->fields   = "'".$param->data->start."',
                    '".$param->data->end."',
                    '".$param->data->paygroup."',
                    '".$param->data->pdr."'
                ";   

$checke = $Qry->exe_INSERT($con);

if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array("status"=>"error"));
}
                 
print $return;
mysqli_close($con);
?>