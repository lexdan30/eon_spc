<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));

// $pay_item = !empty($param->info->pay_item) ? $param->info->pay_item : NULL;
$rate = !empty($param->info->rate) ? $param->info->rate : '';
$credit = !empty($param->info->credit) ? $param->info->credit : '';
$debit = !empty($param->info->debit) ? $param->info->debit : '';
$flags      = !empty($param->info->flags) ? 0 : 1;
$isdef      = 1;


$Qry           = new Query();
$Qry->table    = "tblclasstrans";
$Qry->selected = "`idclass`,
                    `name`,
                    `alias`,
                    `credit`, 
                    `debit`,
                    `rate`,
                    `isdef`,
                    `flags`";
$Qry->fields   = "
                    '".$param->info->idclass."', 
                    '".($param->info->name)."', 
                    '".($param->info->alias)."',
                    '".($credit)."',
                    '".($debit)."',
                    '".$rate."',
                    '".$isdef."',
                    '".$flags."'
                    ";    
                    
                    
$rs = $Qry->exe_INSERT($con);

if($rs){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error','mysqlerror'=>mysqli_error($con)));
}
print $return;
mysqli_close($con);
// "isdef"	        => $row['isdef'],
// "determine"	    => $row['determine'],
// "ottype"	    => $row['ottype'],
?>