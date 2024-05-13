<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$idacct = !empty($param->idacct) ? $param->idacct : 0;
$empid = !empty($param->empid) ? $param->empid : getEmpid($con, $idacct);

$Qry = new Query();	
$Qry->table     = "tbltimelogs";
$Qry->selected  = "id, acct_id, work_time";
$Qry->fields = "id>0 and acct_id ='". $empid ."' AND work_date = '".$param->workdate."'ORDER by work_time";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        
        $data[] = array(
            'id'            => $row['id'],
            "acct_id"       => $row['acct_id'],
            "work_time"	    => $row['work_time']
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