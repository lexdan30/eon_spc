<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tblloans";
$Qry->selected = "docnumber,
                    empid,
                    loanid,
                    status,
                    interest,
                    loandate,
                    firstpaydate,
                    entrydate,
                    noa,
                    begginingbalance,
                    systemamortization,
                    useramortization,
                    totalamount
                    ";
$Qry->fields   = "'".$param->info->docnumber."',
                    '".$param->info->employee."',
                    '".$param->info->type."',
                    '".$param->info->status."',
                    '".$param->info->interest."',
                    '".$param->info->loandate."',
                    '".$param->info->firstpay."',
                    '". SYSDATE() ."',
                    '".$param->info->noa."',
                    '".$param->info->bbalance."',
                    '".$param->info->samor."',
                    '".$param->info->uamor."',
                    '".$param->info->totalamount."'
                ";   

$checke =  $Qry->exe_INSERT($con);

if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array("status"=>"error"));
}

mysqli_close($con);
?>