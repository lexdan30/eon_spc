<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblcont_ibig";
$Qry->selected  = "*";
$Qry->fields = "id>0";


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $ecc_ee1 = $row['ee'];
        $ecc_ee=($row['ee']*100);
        $ecc_er1 = $row['er'];
        $ecc_er=($row['er']*100);
        $data[] = array(
            "id" 	        => $row['id'],
            "description" 	=> $row['description'] ,
            "sal" 	        => $row['sal'] ,
            "ee" 	        => $ecc_ee,
            "er" 	        => $ecc_er
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);
?>