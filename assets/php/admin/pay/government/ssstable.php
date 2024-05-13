<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblcont_sss";
$Qry->selected  = "*";
$Qry->fields = "id>0 ORDER by id";


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array(
            "id" 	            => $row['id'],
            "description" 	    => $row['description'],
            "sal_creditfrom" 	=> $row['sal_creditfrom'] ,
            "sal_creditto" 	    => $row['sal_creditto'] ,
            "mpfund" 	        => $row['mpfund'],
            "empcont" 	        => $row['empcont'],
            "emprcont" 	        => $row['emprcont'],
            "ecc" 	            => $row['ecc']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);
?>