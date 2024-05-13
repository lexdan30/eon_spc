<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

$Qry 			= new Query();	
$Qry->table     = "tblcalendar";
$Qry->selected  = "*";
$Qry->fields    = "id='".$param->id."'";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_array($rs)){
        $data = array( 
            "id"        => $row['id'],
			"name"		=> $row['name'],
			"shiftmon"	=> $row['shiftmon'],
			"shifttue"	=> $row['shifttue'],
			"shiftwed"	=> $row['shiftwed'],
			"shiftthu"	=> $row['shiftthu'],
			"shiftfri"	=> $row['shiftfri'],
			"shiftsat"	=> $row['shiftsat'],
			"shiftsun"	=> $row['shiftsun']
        );
    }
}

$return = json_encode($data);
print $return;
mysqli_close($con);
?>