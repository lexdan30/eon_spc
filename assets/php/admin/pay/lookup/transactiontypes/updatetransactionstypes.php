<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$coa = implode (",", $param->info->coa);
$entrytype = implode (",", $param->info->entrytype);




$Qry           = new Query();
$Qry->table    = "tblclasstrans";
$Qry->selected = "name     ='".$param->info->name."'";

if( !empty($param->info->isdef) ){
    $Qry->selected 	= $Qry->selected . ", isdef='".$param->info->isdef."'";																				
}
if( !empty($param->info->idclass) ){
    $Qry->selected 	= $Qry->selected . ", idclass='".$param->info->idclass."'";																				
}  
if( !empty( $param->info->flags ) ){
    if($param->info->flags == "Active") {
        $Qry->selected 	= $Qry->selected . ", flags='1'";	
    }elseif($param->info->flags == "Inactive") {	
        $Qry->selected 	= $Qry->selected . ", flags='0'";	
    }																
} 
if( !empty( $param->info->debit ) ){
    $Qry->selected 	= $Qry->selected . ", debit='".$param->info->debit."'";																			
} 
if( !empty( $param->info->credit ) ){
    $Qry->selected 	= $Qry->selected . ", credit='".$param->info->credit."'";																			
}      
if($param->info->rate != '' ){
    $Qry->selected 	= $Qry->selected . ", rate='".$param->info->rate."'";																			
}    
if( !empty( $coa ) ){
    $Qry->selected 	= $Qry->selected . ", coaids='".$coa."'";																			
}    else{
    $Qry->selected 	= $Qry->selected . ", coaids=''";
}     

if( !empty( $entrytype ) ){
    $Qry->selected 	= $Qry->selected . ", entrytype='".$entrytype."'";																			
}    else{
    $Qry->selected 	= $Qry->selected . ", entrytype=NULL";
}     



$Qry->fields   = "id='".$param->info->ids."'";  
           
$checke = $Qry->exe_UPDATE($con);

if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error', 'mysqli'=>mysqli_error($con)));
}
print $return;
mysqli_close($con);
?>