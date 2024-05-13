<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tblrecurring";
$Qry->selected = "total ='".$param->beupdate->total."' ";
$Qry->fields   = "id='".$param->id."'";                        
$Qry->exe_UPDATE($con);

foreach($param->details as $key=>$value){
    if($value->id){
        $Qry           = new Query();
        $Qry->table    = "tblrecurringdetails";
        $Qry->selected = "amount ='".$value->amount."'";
        $Qry->fields   = "id='".$value->id."'";
        $checke =  $Qry->exe_UPDATE($con);
    }
}

foreach($param->remove as $key=>$value){
    $Qry = new Query();	
    $Qry->table     = "tblrecurringdetails";
    $Qry->fields    = "id='".$value->id."'";
    $rs = $Qry->exe_DELETE($con);
}

foreach($param->addod as $key=>$value){
    $Qry2           = new Query();
    $Qry2->table    = "tblrecurringdetails";
    $Qry2->selected = "recurringid,empid,amount";
    $Qry2->fields   = "'".$param->id."',
                        '".$value->id."',
                        '".$value->amount."'
                    ";   
    $Qry2->exe_INSERT($con);
}

if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error'));
}
print $return;
mysqli_close($con);
?>