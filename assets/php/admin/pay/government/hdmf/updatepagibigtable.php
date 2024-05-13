<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$date = date('Y-m-d');
$Qry           = new Query();
$Qry->table    = "tblcont_ibig";
$Qry->selected = "description     ='".$param->info->description."',
                    ee    ='".$param->info->ee."',
                    er ='".$param->info->er."',
                    yr_use	 ='".$date."'    
                    ";
$Qry->fields   = "id='".$param->info->id."'";                        
$checke = $Qry->exe_UPDATE($con);
if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error'));
}
print $return;
mysqli_close($con);
?>