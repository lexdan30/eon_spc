<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param 		= json_decode(file_get_contents('php://input'));

$Qry 			= new Query();
$Qry->table 	= "vw_dataemployees AS a";
$Qry->selected 	= "COUNT(a.empID) AS total";
$Qry->fields    = "a.etypeid='1' AND a.hdate <= '".$param->hdate."'";

$rs = $Qry->exe_SELECT($con);
$data = array();
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data= array( 'total'  => $row['total'] );
    }
    $return = json_encode($data);
}else{
	$return = json_encode(array( 'total'  => 0 ));
}


print $return;
mysqli_close($con);
?>