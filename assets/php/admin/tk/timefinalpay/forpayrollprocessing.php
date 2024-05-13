<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param  = json_decode(file_get_contents('php://input'));

$Qry=new Query();
$Qry->table="`tblfinalpay` AS fp LEFT JOIN tblaccount AS a ON fp.idacct = a.id LEFT JOIN tblaccountjob AS aj ON fp.idacct = aj.idacct";
$Qry->selected="fp.*, a.empid,CONCAT(`a`.`lname`,IFNULL(CONCAT(' ',`a`.`suffix`),''),', ',`a`.`fname`,' ',SUBSTR(`a`.`mname`,1,1),'. ') AS `empname`, aj.batchnum";
$Qry->fields="fp.tkstatus = 1 AND fp.prstatus = 0";
$rs=$Qry->exe_SELECT($con);
if (mysqli_num_rows($rs) >= 1){
    while ($row = mysqli_fetch_array($rs)){
        if($row['batchnum'] == '5'){
            $row['type'] = 'Helper';
        }elseif($row['batchnum'] == '3,4'){
            $row['type'] = 'Japanese';
        }else{
            $row['type'] = 'Local Employee';
        }
        $data[] = $row;
    }
    $myData = array('status' => 'success','result' => $data);
    $return = json_encode($myData);
}else{
    $return = json_encode(array('error' => mysqli_error($con)));
}


print $return;
mysqli_close($con);

?>
