<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
// $flags = !empty($param->info->flags) ? $param->info->flags : "Active";


$Qry           = new Query();
$Qry->table    = "tblclasstrans";
$Qry->selected = "name     ='".$param->info->name."',
                    idclass    ='".$param->info->idclass."',
                    debit	 ='".$param->info->debit."', 
                    credit	 ='".$param->info->credit."',
                    rate	 ='".$param->info->rate."' ,
                    isdef	 ='".$param->info->isdef."' ,
                    flags	 ='".$param->info->flags."'   
                    ";
$Qry->fields   = "id='".$param->info->id."'";                        
$checke = $Qry->exe_UPDATE($con);

if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error'));
}
print $return;
mysqli_close($con);
// classname ='".$param->info->classname."',
?>