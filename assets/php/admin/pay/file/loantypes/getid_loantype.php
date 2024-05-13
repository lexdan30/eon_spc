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
$Qry->table     = "tblloantype";
$Qry->selected  = "*";
$Qry->fields    = "id='".$param->id."'";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_array($rs)){
        $data = array( 
            'id'                    => $row['id'],
            "code"                  => $row['code'],
            "desc"         	        => $row['desc'],
            "stats"         	    => $row['stats'],
            "type"         	        => $row['type'],
            "loan_mode"	            => $row['loan_mode'],
            "interest_percentage"	=> $row['interest_percentage'],
            "app_first"	            => $row['app_first'],
            "app_second"	        => $row['app_second'],
            "app_sp"	            => $row['app_sp'],
            "app_fp"	            => $row['app_fp'],
            "priority"	            => $row['priority']
        );
    }
}
        
$return = json_encode($data);

print $return;
mysqli_close($con);
?>