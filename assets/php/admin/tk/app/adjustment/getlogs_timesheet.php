<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$idacct = !empty($param->idacct) ? $param->idacct : 0;
$empid = !empty($param->empid) ? $param->empid : getEmpid($con, $idacct);

$Qry = new Query();	
$Qry->table     = "(SELECT id, date_out, timeout, `date`, idacct FROM tbltimesheet) as a1
LEFT JOIN (SELECT idacct, `date`, stat, ftime, stime FROM `tbltimeadjustment`) `k1` ON (`k1`.`idacct` = `a1`.`idacct` AND `k1`.`date` = `a1`.`date` AND `k1`.`stat` = 1)";
$Qry->selected  = "a1.id,a1.date_out,a1.timeout,`k1`.`ftime` AS `aatimeout`,IF(CAST(`k1`.`stime` AS TIME) < CAST(`k1`.`ftime` AS TIME) 
AND (`k1`.`ftime` <> '' OR `k1`.`ftime` IS NOT NULL), `k1`.`date`,`k1`.`date` + INTERVAL 1 DAY) AS `aadate_out`";
$Qry->fields = "a1.id>0 and a1.idacct ='". $param->accountid ."' AND a1.date = '".$param->workdate."'"; 

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        if($row['date_out'] != null){
            $dateout = $row['date_out'];
        }else{
            $dateout = '';
        }
        if(!empty($row['aadate_out'])){
            $timeout = $row['aadate_out'];
        }

        if($row['timeout'] != null){
            $timeout = $row['timeout'];
        }else{
            $timeout = '';
        }
        if(!empty($row['aatimeout'])){
            $timeout = $row['aatimeout'];
        }
       
        $data[] = array(
            'id'            => $row['id'],
            "dateout"            => $dateout,
            "timeout"	        => $timeout
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