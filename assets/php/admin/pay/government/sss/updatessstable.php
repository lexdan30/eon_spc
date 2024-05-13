<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tblcont_sss";
$Qry->selected = "description           ='".$param->info->sal_creditfrom." - ".$param->info->sal_creditto."',
                    sal_creditfrom      ='".$param->info->sal_creditfrom."',
                    sal_creditto        ='".$param->info->sal_creditto."',
                    msalary_ec          ='".$param->info->msalary_ec."',
                    mpfund	            ='".$param->info->mpfund."',
                    emprcont	        ='".$param->info->emprcont."', 
                    empcont	            ='".$param->info->empcont."', 
                    ecc_er	            ='".$param->info->ecc_er."', 
                    
                    mandatory_er	            ='".$param->info->mandatory_er."', 
                    mandatory_ee	            ='".$param->info->mandatory_ee."', 
                    yr_use	            ='".$param->info->yr_use."'    
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