<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$Qry = new Query();	
$Qry->table     = "tblshift";
$Qry->selected  = "id,name";
$Qry->fields    = "id";


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id" 	    => $row['id'],
            "name"      => $row['name']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array('status'=>'error','w'=>$Qry->fields));
	
}
print $return;
mysqli_close($con);
?>