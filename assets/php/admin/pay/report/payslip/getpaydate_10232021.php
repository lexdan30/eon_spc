<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "vw_netpaydata";
$Qry->selected  = "*";
$Qry->fields = "id>0 group by pay_date order by pay_date";


$rs = $Qry->exe_SELECT($con); 
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array(
            'id'		=> $row['id'], 
			'id_acct'	=> $row['id_acct'],
			'empid'		=> $row['empid'],
            'emp'   	=> $row['empname'], 
			'id_paydate'=> $row['id_paydate'],
            'pay_date'  => $row['pay_date'],
			'amt'		=> number_format($row['amt'],2)
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);
?>

            