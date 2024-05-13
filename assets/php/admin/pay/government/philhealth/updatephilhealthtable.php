<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

// salfrom    ='".$param->info->salfrom."',
// salto ='".$param->info->salto."',
// ee	 ='".$param->info->ee."',
// er	 ='".$param->info->er."',
// fix_amt	 ='".$param->info->fix_amt."', 
// yr_use	 ='".$param->info->yr_use."',
$premium=($param->info->prem_rate/100);
$Qry           = new Query();
$Qry->table    = "tblcont_health";
$Qry->selected = "description     ='".$param->info->description."',

                    monthly_prem	 ='".$param->info->monthly_prem."', 
                    prem_rate	 ='".$premium."'     
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