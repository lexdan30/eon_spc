<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

if(isset($param->modal)){
    $Qry           = new Query();
    $Qry->table    = "tblaccountplan";
    $Qry->selected = "event_title='".$param->title."', type='".$param->type."'";
    $Qry->fields   = "id='".$param->id."'";                        
    $rs = $Qry->exe_UPDATE($con);
}else{
    $Qry           = new Query();
    $Qry->table    = "tblaccountplan";
    $Qry->selected = "efrom='".$param->start."', eto='".$param->end."'";
    $Qry->fields   = "id='".$param->id."'";                        
    $rs = $Qry->exe_UPDATE($con);

}

mysqli_close($con);
?>