<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date = date("Y-m-d",strtotime($param->info->monthparam));


$Qry = new Query();	
$Qry->table     = "vw_timesheet";
$Qry->selected  = "id,sum(acthrs - excess) as acthrs,sum(othrs) as ot";
$Qry->fields = "MONTH(work_date) = MONTH('" . $date  . "')
                AND YEAR(work_date) = YEAR('" . $date  . "')
                GROUP BY id";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "empid" 	=> $row['id'],
            "acthrs" 	=> $row['acthrs'] + getAdjustments($con, $date, $row),
            "ot"        => 0,
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);

function getAdjustments($con, $date, $param){
    $adjustment = 0;

    $Qry = new Query();	
    $Qry->table     = "vw_timesheet";
    $Qry->selected  = "*";
    $Qry->fields    = "id = '" . $param['id']  . "'
                        AND ( (MONTH(aaapprove) = MONTH('" . $date  . "') AND YEAR(aaapprove) = YEAR('" . $date  . "') AND aaapprove NOT BETWEEN period_start AND period_end)
                        OR ( MONTH(csapprove) = MONTH('" . $date  . "') AND YEAR(csapprove) = YEAR('" . $date  . "')  AND csapprove NOT BETWEEN period_start AND period_end)
                        OR ( MONTH(obapprove) = MONTH('" . $date  . "') AND YEAR(obapprove) = YEAR('" . $date  . "')  AND obapprove NOT BETWEEN period_start AND period_end)
                        OR ( MONTH(otapprove) = MONTH('" . $date  . "') AND YEAR(otapprove) = YEAR('" . $date  . "')  AND otapprove NOT BETWEEN period_start AND period_end)
                        OR ( MONTH(lvapprove) = MONTH('" . $date  . "') AND YEAR(lvapprove) = YEAR('" . $date  . "')  AND lvapprove NOT BETWEEN period_start AND period_end)
                        ) GROUP BY work_date";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            $Qry2 = new Query();	
            $Qry2->table     = "vw_timesheetapprove";
            $Qry2->selected  = "*";
            $Qry2->fields = "work_date = '" . $row['work_date']  . "' and id = '" . $param['id'] . "'";
            
            $rs2 = $Qry2->exe_SELECT($con);
            if(mysqli_num_rows($rs2)>= 1){
                if($row1=mysqli_fetch_assoc($rs2)){
                    if($row['absent'] != 0){
                        if($row1['leave']){
                            $val = $row1['leave'];
                        }else{
                           $late = $row['late'] - $row1['late'];
                           $ut = $row['ut'] - $row1['ut'];
                           $absent = $row['absent'] - $row1['absent'];

                           $val = $absent + $ut + $late;
                        }
                    }else{
                        $late = $row['late'] - $row1['late'];
                        $ut = $row['ut'] - $row1['ut'];

                        $val = $ut + $late;
                    }

                    $adjustment = $adjustment + $val;
                } 
            }
        } 
    }
    


    return $adjustment;
}
?>