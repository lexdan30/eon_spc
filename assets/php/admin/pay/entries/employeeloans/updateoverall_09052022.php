<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$totalamount = (int)$param->info->loanamount + (int)$param->info->interest;

$Qry           = new Query();
$Qry->table    = "tblloans";
$Qry->selected = "loandate     ='".$param->info->loandate."',
                    firstpaydate     ='".$param->info->firstpaydate."',
                    interest     ='".$param->info->interest."',
                    noa     ='".$param->info->noa."',
                    systemamortization     ='".$param->info->systemamortization."',
                    useramortization     ='".$param->info->useramortization."',
                    totalamount     ='". $totalamount."',
                    begginingbalance     ='". $totalamount."'
                    ";
$Qry->fields   = "id='".$param->info->id."'";                        
$checke =  $Qry->exe_UPDATE($con);

if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error'));
}
print $return;
mysqli_close($con);
?>