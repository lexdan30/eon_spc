<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$data 		= array();

$Qry2 = new Query();	
$Qry2->table     = "tblaccountplan";
$Qry2->selected  = "efrom, eto";
$Qry2->fields    = "id='".$param->id."'";
$rs2 = $Qry2->exe_SELECT($con);
if(mysqli_num_rows($rs2)>= 1){
    while($row=mysqli_fetch_assoc($rs2)){
        $data[] = array( 
            "status" => 'success',
            "start" => $row['efrom'],
            "end"   => $row['eto']
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