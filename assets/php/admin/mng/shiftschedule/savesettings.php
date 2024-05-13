<?php
require_once('../../../logger.php');
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php');

    $param = json_decode(file_get_contents('php://input'));


    $Qryb 			= new Query();	
    $Qryb->table 	= "tblbunits";
    if($param->accountid != getidhead($con,$param->businessunit)){
        if($param->scheduler != ''){
            $Qryb->selected = "scheduler='".$param->scheduler."'";
        }else{
            $Qryb->selected = "scheduler=NULL";
        }
        $Qryb->fields 	= "idhead='".$param->accountid."'";
    }else{
        if($param->scheduler != ''){
            $Qryb->selected = "scheduler='".$param->scheduler."'";
        }else{
            $Qryb->selected = "scheduler=NULL";
        }
        $Qryb->fields 	= "name='".$param->businessunit."'"; 
    }
    $checkentryb 	= $Qryb->exe_UPDATE($con);
    if($checkentryb){
        $return = json_encode(array('savestatus'=>'success'));
    }else{
        $return = json_encode(array('savestatus'=>'oops','err'=>mysqli_error($con)));
    }
    
print $return;
mysqli_close($con);

function getidhead( $con, $unitname ){
    $Qry 			= new Query();	
    $Qry->table     = "tblbunits";
    $Qry->selected  = "idhead";
    $Qry->fields    = "name = '".$unitname."'";
    $rs 			= $Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getidhead');
    if(mysqli_num_rows($rs)>= 1){
        return mysqli_fetch_assoc($rs)['idhead'];

    }
}
?>