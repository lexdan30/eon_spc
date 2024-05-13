<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "empid, empname, post";
$Qry->fields    = "empstat = 7";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
		$data[] = array( 
            "id" 			    => $row['empid'],
			"name" 			    => $row['empname'],
			"post"          	=> $row['post'],
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array('w'=>$Qry->fields));
	
}
print $return;
mysqli_close($con);
?>