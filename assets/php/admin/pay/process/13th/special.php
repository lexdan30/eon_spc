<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblbonuses";
$Qry->selected  = "*";
$Qry->fields = "status = 0 and type = 13";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_assoc($rs)){
        $data = getdetails($con,$row['id']);
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array("err"=>mysqli_error($con)));
	
}
print $return;
mysqli_close($con);

function getdetails($con,$id){
    $data = array();
    $Qry = new Query();	
    $Qry->table     = "tblbonusesdetails";
    $Qry->selected  = "*";
    $Qry->fields = "bonusid='".$id."'";
    $rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $data[] = $row;
        }
    }
    return $data;
}
?>