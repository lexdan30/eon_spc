<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

$Qry 			= new Query();	
$Qry->table     = "tblshift";
$Qry->selected  = "*";
$Qry->fields    = "id='".$param->id."'";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_array($rs)){
        $data = array( 
            "id"        => $row['id'],
			"name"		=> $row['name'],
			"stime"		=> $row['stime'],
            "ftime"		=> $row['ftime'],
            "breakin" 	=> $row['breakin'],
            "breakout" 	=> $row['breakout'],
            "sstime"	=> $row['sstime'],
            "sftime"	=> $row['sftime'],
            "stype" 	=> $row['stype']
        );
    }
}

$return = json_encode($data);
print $return;
mysqli_close($con);
?>