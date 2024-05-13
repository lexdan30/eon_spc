<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tbloperator_master";
$Qry->selected = "emp_id     ='".$param->info->emp_id."'";

if( !empty($param->info->operators_name) ){
    $Qry->selected 	= $Qry->selected . ", operators_name='".$param->info->operators_name."'";																				
}
if( !empty($param->info->position) ){
    $Qry->selected 	= $Qry->selected . ", position='".$param->info->position."'";																				
} 
if( !empty( $param->info->stats ) ){
    if($param->info->stats == "Active") {
        $Qry->selected 	= $Qry->selected . ", stats='1'";	
    }elseif($param->info->stats == "Inactive") {	
        $Qry->selected 	= $Qry->selected . ", stats='0'";	
    }																
} 

$Qry->fields   = "emp_id='".$param->info->emp_id."'";  
           
$checke = $Qry->exe_UPDATE($con);

if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error',"selected"=>$Qry->selected,"fields"=>$Qry->fields, 'mysqli'=>mysqli_error($con)));
}
print $return;
mysqli_close($con);
?>