<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tblcont_bir";
$Qry->selected = "description     ='".$param->info->description."',
                    mini    ='".$param->info->mini."',
                    fix_amt ='".$param->info->fix_amt."',
                    multi	 ='".$param->info->multi."', 
                    yr_use	 ='".$param->info->yr_use."'    
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