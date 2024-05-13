<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));

$date = date('Y-m-d');
$remarks = !empty($param->info->remarks) ? $param->info->remarks : NULL;
$idmeasure = !empty($param->info->idmeasure) ? $param->info->idmeasure : NULL;
$value = !empty($param->info->value) ? $param->info->value : "0";
$flags      = !empty($param->info->flags) ? 0 : 1;

if($param->info->idmeasure != '11'){
	$value  = $value;
}else{
	$value  = implode (",", $param->info->value);
}

$Qry           = new Query();
$Qry->table    = "tblpreference";
$Qry->selected = "`alias`,
                    `prefname`,
                    `remarks`,
                    `date_create`,
                    `createdby`,
                    `flags`";
$Qry->fields   = "'".$param->info->alias."',
                    '".$param->info->prefname."', 
                    '".$remarks."', 
                    '".$date."',
                    '".$param->accountid."',
                    '".$flags."'
                    ";             

                if( !empty($value) ){
                $Qry->selected 	= $Qry->selected . ", `value`";
                $Qry->fields 	= $Qry->fields 	 . ", '".$value."'";
                }
                if( !empty($idmeasure) ){
                    $Qry->selected 	= $Qry->selected . ", `idmeasure`";
                    $Qry->fields 	= $Qry->fields 	 . ", '".$idmeasure."'";
                } 

                // '".$idmeasure."',
                // '".$value."',

$rs = $Qry->exe_INSERT($con);

if($rs){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error'));
}
print $return;
mysqli_close($con);
?>