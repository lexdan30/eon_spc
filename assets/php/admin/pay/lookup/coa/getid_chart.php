<?php
require_once('../../../../activation.php');
$param = json_decode(file_get_contents('php://input'));
$conn = new connector();	
if( (int)$param->conn == 1 ){	
	$con = $conn->connect();
}else{
	$varcon = "connect".(int)$param->conn;
	$con = $conn->$varcon();
}
require_once('../../../../classPhp.php'); 


$data = array();

$Qry = new Query();	
$Qry->table     = "tblchartofaccount";
$Qry->selected  = "*";
$Qry->fields    = "id='".$param->id."'";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_array($rs)){
        $data = array( 
            'id'            => $row['id'],
            "code"          => $row['code'],
            "description"	=> $row['description'],
            "type"	        => $row['type'],
            "flags"	        => $row['flags']
        );
    }
}
        
$return = json_encode($data);

print $return;
mysqli_close($con);
?>