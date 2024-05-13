<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

$Qry 			= new Query();	
$Qry->table     = "tblleaves";
$Qry->selected  = "*";
$Qry->fields    = "id='".$param->id."'";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_array($rs)){
        $data = array( 
            "id"        	=> $row['id'],
			"name"			=> $row['name'],
			"hours"			=> $row['hours'],
			"idtype"		=> $row['idtype'],
			"isconvertible"	=> $row['isconvertible'],
			"active"		=> $row['active']
        );
    }
}

$return = json_encode($data);
print $return;
mysqli_close($con);
?>