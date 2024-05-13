<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));
$date   = SysDatePadLeft();
$pay_period = getPayPeriod($con);

$Qry = new Query();
$Qry->table = "tblpayperiod";
$Qry->selected = "*";
$Qry->fields    = "id>0";
$rs = $Qry->exe_SELECT($con);
$data = array();
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        $n = date('Y-m-d', strtotime( $pay_period['pay_end'] . " +1 days"));
        $noplus = date('Y-m-d', strtotime( $pay_period['pay_end']));

        $diff = abs(strtotime($n) - strtotime($date));

        $years = floor($diff / (365*60*60*24));
        $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
        $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

        if($date > $noplus){
            
            $days='0';
        }

        
        $data= array( 
            'days'=>$days
        );

    }
    $return = json_encode($data);
}else{
	$return = json_encode(array());
}


print $return;
mysqli_close($con);
?>