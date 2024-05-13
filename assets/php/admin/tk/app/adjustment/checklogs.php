<?php
require_once('../../../../activation.php');
require_once('../../../../logger.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));


if(!empty($param->filing)){
    $idacct = !empty($param->idacct) ? $param->idacct : 0;
}else{
    $idacct = !empty($param->accountid) ? $param->accountid : 0;
}
//$empid = !empty($param->empid) ? $param->empid : getEmpid($con, $idacct);

$Qry = new Query();	 
$Qry->table     = "tbltimesheet";
$Qry->selected  = "id, timein, timeout";
$Qry->fields = "id>0 and idacct ='". $idacct ."' AND date = '".$param->workdate."'";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_assoc($rs)){
        if(!empty($row['timein']) && !empty($row['timeout'])){
            $status = true;
        }else{
            $status = false;
        }

        $data = array(
            "id"           => $row['id'],
            "timein"       => $row['timein'],
            "timeout"	    => $row['timeout'],
            "completelogs"   => $status
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
}

print $return;
mysqli_close($con);

function getEmpid($con,$id){
    $Qry = new Query();	
    $Qry->table     = "tblaccount";
    $Qry->selected  = "empid";
    $Qry->fields    = "id='".$id."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        return mysqli_fetch_assoc($rs)['empid'];
    }
    return '0';
}
?>