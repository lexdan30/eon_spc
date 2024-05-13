<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblbatchentriesdetails";
$Qry->selected  = "*";
$Qry->fields = "batchentriesid = '".$param->id."'";


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id" 	            => $row['id'],
            "empid" 	        => $row['empid'],
            "hour"               => $row['hour'],
            "unit"             => $row['unit'],
            "amount"             => $row['amount'],
            "departmentid"             => $row['departmentid'],
            "joblevelid"            => $row['joblevelid'],
            "accountsid"           => $row['accountsid']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array("err"=>mysqli_error($con)));
	
}
print $return;
mysqli_close($con);
?>