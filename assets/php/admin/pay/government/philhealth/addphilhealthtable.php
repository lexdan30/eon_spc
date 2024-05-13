<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

// <!-- 
// '".$param->info->salfrom."',
// '".$param->info->salto."',
// '".$param->info->ee."',
// '".$param->info->er."',
// '".$param->info->fix_amt."',
// '".$param->info->yr_use."', -->

// salfrom,
// salto,
// ee,
// er,
// fix_amt,
// yr_use,

$param = json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tblcont_health";
$Qry->selected = "description,

                    monthly_prem,
                    prem_rate";
$Qry->fields   = "'".$param->info->description."',

                    '".$param->info->monthly_prem."',
                    '".$param->info->prem_rate."'
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
