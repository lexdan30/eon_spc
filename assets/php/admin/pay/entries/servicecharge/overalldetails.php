<?php 
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
if(!empty($param->accountid)){
$Qry = new Query();	
$Qry->table     = "tblservicechargedetails";
$Qry->selected  = "*";
$Qry->fields = "servicechargeid = '".$param->id."'";


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id" 	            => $row['id'],
            "empid" 	        => $row['empid'],
            "hours"             => $row['hours'],
            "backpay_hrs"       => $row['backpay_hrs'],
            "amount"            => $row['amount']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array("err"=>mysqli_error($con)));
	
}
print $return;
 mysqli_close($con);
}else{
	mysqli_close($con);
	header("Location: https://peninsula.mydhris.com/mph/");  // unAuth back to login page
}
?>