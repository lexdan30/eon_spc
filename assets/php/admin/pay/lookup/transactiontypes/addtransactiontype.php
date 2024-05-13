<?php
error_reporting(0);
date_default_timezone_set('Asia/Manila');
require_once('../../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));

$rate = !empty($param->info->rate) ? $param->info->rate : NULL;
$credit = !empty($param->info->credit) ? $param->info->credit : NULL;
$debit = !empty($param->info->debit) ? $param->info->debit : NULL;
$flags      = !empty($param->info->flags) ? 0 : 1;
$isdef      = 1;
$coa = implode (",", $param->info->coa);

if($param->info->entrytype){
    $entrytype = implode (",", $param->info->entrytype);
}else{
    $entrytype = '';
}


$Qry           = new Query();
$Qry->table    = "tblclasstrans";
$Qry->selected = "`idclass`,
                    `name`,
                    `alias`,
                    `rate`,
                    `isdef`,
                    `flags`,
                    `coaids`
                    ";
$Qry->fields   = "'".$param->info->idclass."', 
                    '".$param->info->name."', 
                    '".strtoupper($param->info->alias)."',
                    '".$rate."',
                    '".$isdef."',
                    '".$flags."',
                    '".$coa."'
                    ";  

if( !empty( $param->info->debit ) ){
    $Qry->selected 	= $Qry->selected . ", debit";
    $Qry->fields 	= $Qry->fields 	 . ",'".$debit."'";
}  
if( !empty( $param->info->credit ) ){
    $Qry->selected 	= $Qry->selected . ", credit";
    $Qry->fields 	= $Qry->fields 	 . ",'".$credit."'";
}         
if( $entrytype != '' ){
    $Qry->selected 	= $Qry->selected . ", entrytype";
    $Qry->fields 	= $Qry->fields 	 . ",'".$entrytype."'";
}         
         
$rs = $Qry->exe_INSERT($con);

if($rs){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error','mysqlerror'=>mysqli_error($con)));
}
print $return;
mysqli_close($con);
?>