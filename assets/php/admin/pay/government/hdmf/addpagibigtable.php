<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$date = date('Y-m-d');
$Qry           = new Query();
$Qry->table    = "tblcont_ibig";
$Qry->selected = "description,
                    ee,
                    er,
                    yr_use";
$Qry->fields   = "'".$param->info->description."',
                    '".$param->info->ee."',
                    '".$param->info->er."',
                    '".$date."'
                    ";                        
$checke = $Qry->exe_INSERT($con);

if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error'));
}
print $return;
mysqli_close($con);
?>