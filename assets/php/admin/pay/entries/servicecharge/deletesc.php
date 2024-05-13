<?php 
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
if(!empty($param->accountid)){
$id = $param->id;
$docnumber = getDocnum($con,$id);
$Qry1 = new Query();	
$Qry1->table     = "tblservicecharge";
$Qry1->fields    = "id = '".$id."'";
$Qry1->exe_DELETE($con);

$Qry2 = new Query();	
$Qry2->table     = "tblservicechargedetails";
$Qry2->fields    = "servicechargeid = '".$id."'";
$Qry2->exe_DELETE($con);

$return = json_encode(array("status"=>"success","docnum"=> $docnumber));
print $return;
 mysqli_close($con);
}else{
	mysqli_close($con);
	header("Location: https://peninsula.mydhris.com/mph/");  // unAuth back to login page
}
function getDocnum($con,$id){
    $share = '';
	$Qry = new Query();	
	$Qry->table ="tblservicecharge";
	$Qry->selected ="`docnumber`";
	$Qry->fields = "id = '".$id."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $share = $row['docnumber'];	
        }
    }
    return $share;
}
?>