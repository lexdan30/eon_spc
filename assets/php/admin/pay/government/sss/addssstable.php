<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date = date('Y-m-d');
$msalary_ec = !empty($param->info->msalary_ec) ? $param->info->msalary_ec : 0.00;
$mpfund = !empty($param->info->mpfund) ? $param->info->mpfund : 0.00;
$emprcont = !empty($param->info->emprcont) ? $param->info->emprcont : 0.00;
$empcont = !empty($param->info->empcont) ? $param->info->empcont : 0.00;
$ecc_er = !empty($param->info->ecc_er) ? $param->info->ecc_er : 0.00;
$ecc_ee = !empty($param->info->ecc_ee) ? $param->info->ecc_ee : 0.00;
$mandatory_ee = !empty($param->info->mandatory_ee) ? $param->info->mandatory_ee : 0.00;
$mandatory_er = !empty($param->info->ecc_ee) ? $param->info->mandatory_er : 0.00;

$Qry           = new Query();
$Qry->table    = "tblcont_sss";
$Qry->selected = "description,
                    sal_creditfrom,
                    sal_creditto,
                    msalary_ec,
                    mpfund,
                    emprcont,
                    empcont,
                    ecc_er,
                    ecc_ee,
                    mandatory_er,
                    mandatory_ee,
                    yr_use";
$Qry->fields   = "'".$param->info->sal_creditfrom." - ".$param->info->sal_creditto."',
                    '".$param->info->sal_creditfrom."',
                    '".$param->info->sal_creditto."',
                    '".$msalary_ec."',
                    '".$mpfund."',
                    '".$emprcont."',
                    '".$empcont."',
                    '".$ecc_er."',
                    '".$ecc_ee."',
                    '".$mandatory_er."',
                    '".$mandatory_ee."',
                    '".$date ."'
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