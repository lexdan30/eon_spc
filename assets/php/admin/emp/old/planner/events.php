<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblaccountplanevents";
$Qry->selected  = "id,title,bgcolor";
$Qry->fields   = "id='".$param->accountid."'";  


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id" 	               => $row['id'],
            "title" 	           => $row['title'],
            "backgroundColor"      => $row['bgcolor']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array('status'=>'error','w'=>$Qry->fields));
	
}
print $return;
mysqli_close($con);
?>