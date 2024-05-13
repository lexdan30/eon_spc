<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tblcont_bir";
$Qry->selected = "description,
                    mini,
                    fix_amt,
                    multi,
                    yr_use";
$Qry->fields   = "'".$param->info->description."',
                    '".$param->info->mini."',
                    '".$param->info->fix_amt."',
                    '".$param->info->multi."',
                    '".$param->info->yr_use."'
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