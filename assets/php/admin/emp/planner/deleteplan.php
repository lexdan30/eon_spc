<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));


$data 		= array();

$Qry2 = new Query();	
$Qry2->table     = "tblaccountplan";
$Qry2->selected  = "id";
$Qry2->fields    = "id='".$param->id."'";
$rs2 = $Qry2->exe_SELECT($con);
if(mysqli_num_rows($rs2)>= 1){
    while($row=mysqli_fetch_array($rs2)){

        $Qry = new Query();	
        $Qry->table     = "tblaccountplan";
        $Qry->fields    = "id='".$param->id."'";
        $rs = $Qry->exe_DELETE($con);

        $data[] = array( 
            "status" => 'success'
        );
    }
	$return = json_encode($data);
}else{
    $data[] = array( 
        "status" => 'error'
    );
	$return = json_encode($data);
}


print $return;
mysqli_close($con);
?>