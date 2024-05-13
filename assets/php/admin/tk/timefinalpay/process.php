<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param  = json_decode(file_get_contents('php://input'));

$Qry=new Query();
$Qry->table="vw_dataemployees";
$Qry->selected="*";
$Qry->fields="sdate IS NOT NULL AND YEAR(sdate) = 2022 AND id NOT IN(SELECT idacct FROM `tblfinalpay` WHERE tkstatus = 1) ORDER by sdate DESC";
$rs=$Qry->exe_SELECT($con);

if(mysqli_num_rows($rs)>=1){
    while($row=mysqli_fetch_assoc($rs)){ 
        $row['covered'] = getPeriodcovered($con,$row);
        if($row['batchnum'] == '5'){
            $row['type'] = 'Helper';
        }elseif($row['batchnum'] == '3,4'){
            $row['type'] = 'Japanese';
        }else{
            $row['type'] = 'Local Employee';
        }
        $data[] = $row;
    }
   
    $myData = array('status' => 'success', 
                    'result' => $data
                );
    $return = json_encode($myData);
}else{
    $return = json_encode(array('error' => mysqli_error($con), 'query' => $Qry));
}

print $return;
mysqli_close($con);

function getPeriodcovered($con,$prow){
    $Qry=new Query();

    if($prow['batchnum'] == '5'){
        $Qry->table="vw_timesheetfinal_helper";
    }elseif($prow['batchnum'] == '3,4'){
        $Qry->table="vw_timesheetfinal_japanese";
    }else{
        $Qry->table="vw_timesheetfinal_ho";
    }
  

    $Qry->selected="period_start,separationdate";
    $Qry->fields="tid = '".$prow['id']."' AND work_date = '".$prow['sdate']."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        if($row=mysqli_fetch_assoc($rs)){
            if($row['period_start']){
                return $row['period_start'] . ' to ' .  $row['separationdate'];
            }
        }
    }else{
        return '';
    }

    
}
?>