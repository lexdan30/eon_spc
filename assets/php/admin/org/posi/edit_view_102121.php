<?php
require_once('../../../activation.php');
$param = json_decode(file_get_contents('php://input'));
$conn = new connector();	
if( (int)$param->conn == 1 ){	
	$con = $conn->connect();
}else{
	$varcon = "connect".(int)$param->conn;
	$con = $conn->$varcon();
}
require_once('../../../classPhp.php'); 


$data = array();

$Qry = new Query();	
$Qry->table     = "tblposition";
$Qry->selected  = "*";
$Qry->fields    = "id='".$param->id."'";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_array($rs)){
        $data = array( 
            "id"        => $row['id'],
            "name" 	    => $row['name'],
			"idunit"	=> $row['idunit'],
            "alias" 	=> $row['alias'],
            "used" 	    => checkifUsed($con, $row['id']),
            "stat" 	    => $row['isactive']
        );
    }
}
        
$return = json_encode($data);

print $return;
mysqli_close($con);

function checkifUsed($con, $id) {
    $Qry 			= new Query();	
    $Qry->table     = "vw_dataassign";
    $Qry->selected  = "*";
    $Qry->fields    = "idpos='".$id."' and idemptype = '1'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return true;
        }
    }
    return false;
}
?>