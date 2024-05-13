<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$daysno = gettotaldays($param->info->from, $param->info->to);
$monthend = date("Y-m-t", strtotime($param->info->paydate));


$Qry           = new Query();
$Qry->table    = "tblpayperiod";
$Qry->selected = "period,
                    period_start,
                    period_end,
                    pay_date,
                    month_end,
                    hascontri,
                    period_type,
                    grace_hour,
                    daysno";
$Qry->fields   = "'".$param->info->period."', 
                    '".$param->info->from ."',
                    '".$param->info->to ."',
                    '".$param->info->paydate ."',
                    '". $monthend."',
                    '".$param->info->contribution ."',
                    '".$param->info->periodtype ."',
                    '".$param->info->gracehrs ."',
                    '".$daysno ."'
                    ";

$rs = $Qry->exe_INSERT($con);


if($rs){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array("status"=>"error"));
}
      

mysqli_close($con);
function gettotaldays($start , $end ) {
    $start = new DateTime($start);
    $end = new DateTime($end );
    $end->modify('+1 day');
    $interval = $end->diff($start);
    $days = $interval->days;
    $period = new DatePeriod($start, new DateInterval('P1D'), $end);
   
    foreach($period as $dt) {
        $curr = $dt->format('D');
        if ( $curr == 'Sun') {
            $days--;
        }
        
    }
    
    return $days;
}

?>