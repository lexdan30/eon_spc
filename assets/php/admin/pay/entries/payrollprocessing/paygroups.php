<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$idpayperiod = array(  
    "period"		=> getPayPeriod($con),
);

$Qry = new Query();	
$Qry->table     = "`vw_dataemployees` AS vd LEFT JOIN tblaccount AS ta ON vd.id = ta.id";
$Qry->selected  = "COUNT(*) AS total";

if($idpayperiod['period']['type'] == 'helper'){
    $Qry->fields = "ta.idemptype = 1 and wshift is not null AND etypeid=1 AND batchnum = '6'";
}else if($idpayperiod['period']['type'] == 'jap'){
    $Qry->fields = "ta.idemptype = 1 and wshift is not null AND etypeid=1 AND batchnum = '3,4'";
}else{
    $Qry->fields = "ta.idemptype = 1 and wshift is not null AND etypeid=1 AND (batchnum != '3,4' OR batchnum != '6' )";
}

if( !empty($param->paygroup != 'all') ){
    $Qry->fields 	= $Qry->fields 	 . " AND idpaygrp = '".$param->paygroup."'";
}

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "total" 	        => $row['total']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);
?>