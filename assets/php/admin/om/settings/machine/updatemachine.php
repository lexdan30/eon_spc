<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));


$Qry           = new Query();
$Qry->table    = "tblmachine_master";
$Qry->selected = "machine_name     ='".$param->info->ids."'";

if( !empty($param->info->description) ){
    $Qry->selected 	= $Qry->selected . ", description='".$param->info->description."'";																				
}
if( !empty($param->info->locator_code) ){
    $Qry->selected 	= $Qry->selected . ", locator_code='".$param->info->locator_code."'";																				
}  
if( !empty($param->info->location) ){
    $Qry->selected 	= $Qry->selected . ", location='".$param->info->location."'";																				
}  
if( !empty($param->info->control_line) ){
    $Qry->selected 	= $Qry->selected . ", control_line='".$param->info->control_line."'";																				
} 
if( !empty( $param->info->auto_off ) ){
    if($param->info->auto_off == "Yes") {
        $Qry->selected 	= $Qry->selected . ", auto_off='1'";	
    }elseif($param->info->auto_off == "No") {	
        $Qry->selected 	= $Qry->selected . ", auto_off='0'";	
    }																
}  
if( !empty( $param->info->stats ) ){
    if($param->info->stats == "Active") {
        $Qry->selected 	= $Qry->selected . ", stats='1'";	
    }elseif($param->info->stats == "Inactive") {	
        $Qry->selected 	= $Qry->selected . ", stats='0'";	
    }																
} 


$Qry->fields   = "machine_name='".$param->info->ids."'";  
           
$checke = $Qry->exe_UPDATE($con);

if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error',"selected"=>$Qry->selected,"fields"=>$Qry->fields, 'mysqli'=>mysqli_error($con)));
}
print $return;
mysqli_close($con);
?>
