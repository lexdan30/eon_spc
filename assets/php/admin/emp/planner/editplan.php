<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date 	= SysDate();

// echo $date . $param->start;

if(isset($param->modal)){
    if(is_array($param->viewers)){
        $param->viewers = implode(",", $param->viewers); 
    }else{
        $param->viewers = $param->viewers; 
    }
    $Qry           = new Query();
    $Qry->table    = "tblaccountplan";
    $Qry->selected = "event_title='".$param->title."', type='".$param->type."', canview='".$param->viewers."'";
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