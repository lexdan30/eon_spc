<?php
require_once('../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblmachine_master";
$Qry->selected  = "*";
$Qry->fields    = "id>0 order by id";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){ 
    while($row=mysqli_fetch_array($rs)){  
        $data[] = array(
            'id'           => $row['id'],
            'machine_pic'           => $row['machine_pic'],
            'machine_code'           => $row['machine_code'],
            "machine_name"         => $row['machine_name'],
            "description"	    => $row['description'],
            "locator_code"	    => $row['locator_code'],
            "location"	        => $row['location'], 
            'stats'           => $row['stats'],
        );
    }

    $myData = array('status' => 'success', 'result' => $data);
	$return = json_encode($myData);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);
?>