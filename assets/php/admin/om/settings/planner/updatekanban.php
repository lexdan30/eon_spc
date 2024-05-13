<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tblplannerdata_master";
$Qry->selected = "kanban_id     ='".$param->info->ids."'";

if( !empty($param->info->wo_no) ){
    $Qry->selected 	= $Qry->selected . ", wo_no='".$param->info->wo_no."'";																				
}
if( !empty($param->info->prod_no) ){
    $Qry->selected 	= $Qry->selected . ", prod_no='".$param->info->prod_no."'";																				
}  
if( !empty( $param->info->stats ) ){
    if($param->info->stats == "Active") {
        $Qry->selected 	= $Qry->selected . ", stats='1'";	
    }elseif($param->info->stats == "Inactive") {	
        $Qry->selected 	= $Qry->selected . ", stats='0'";	
    }																
} 
if( !empty( $param->info->prod_qty ) ){
    $Qry->selected 	= $Qry->selected . ", prod_qty='".$param->info->prod_qty."'";																			
}

$Qry->fields   = "kanban_id='".$param->info->ids."'";  
           
$checke = $Qry->exe_UPDATE($con);

if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error',"selected"=>$Qry->selected,"fields"=>$Qry->fields, 'mysqli'=>mysqli_error($con)));
}
print $return;
mysqli_close($con);
?>