<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$date=date_create($param->bdate);
$bdate = DATE_FORMAT($date, "mdY");
$nwmh_def='dels@n1991';

$Qry1 = new Query();	
$Qry1->table = "tblaccount";
$Qry1->selected ="password='".md5($nwmh_def)."'";
$Qry1->fields = "id='".$param->id."'";
$check = $Qry1->exe_UPDATE($con);

if(!$check ){
    $return = json_encode(array('status'=>'error'));
}
else{
    $return = json_encode(array('status'=>'success'));
}

print $return;
mysqli_close($con);

?>