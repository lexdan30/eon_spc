<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "vw_classtranstype";
$Qry->selected  = "*";
if($param->type == '3'){
    $Qry->fields = "id>0";
}else{
    if($param->type == 'Service Charge'){
        $Qry->fields = "pay_item='".$param->type."' AND FIND_IN_SET('S',entrytype)";
    }else{
        $Qry->fields = "transactiontype='".$param->type."' AND FIND_IN_SET('".$param->requestor."',entrytype)";
    }
}

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id" 	     => $row['id'],
            "name" 	     => $row['pay_item'],
            "alias"      => $row['transactiontype'],
	        "period"		=> getPayPeriod($con),
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array("err"=>mysqli_error($con)));
	
}
print $return;
mysqli_close($con);
?>