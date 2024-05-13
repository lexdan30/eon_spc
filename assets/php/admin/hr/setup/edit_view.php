<?php
require_once('../../../activation.php');
$param = json_decode(file_get_contents('php://input'));
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 


$data = array();

$Qry = new Query();	
$Qry->table     = "vw_preferences";
$Qry->selected  = "*";
$Qry->fields    = "id='".$param->id."'";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_array($rs)){
        $data = array( 
            "id"        => $row['id'],
			"alias" 	=> $row['alias'],
            "name"		=> $row['preference'],
			"remarks"	=> $row['remarks'],
            "idmeasure" => $row['idmeasure'],
			"value" 	=> $row['value']
        );
    }
}
        
$return = json_encode($data);

print $return;
mysqli_close($con);
?>