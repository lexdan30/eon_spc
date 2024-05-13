<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry 			= new Query();	
$Qry->table     = "tblpayperiod";
$Qry->selected  = "*";
$Qry->fields    = "id>0";
$rs 			= $Qry->exe_SELECT($con);


if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[]  = array( 
            "id"        	=> $row['id'],
            "pay_start"		=> $row['period_start'],
            "pay_end"		=> $row['period_end'],
            "pay_date"		=> $row['pay_date'],
            "grace_hour"	=> $row['grace_hour'],
            "hascontri" 	=> $row['hascontri'],
            "pay_stat"		=> $row['stat'],
            "tk_stat"		=> $row['tkstatus'],
            "period_type" 	=> $row['period_type']
        );
    }
    $return = json_encode($data);
}else{
    $return = json_encode(array());
}

print $return;
mysqli_close($con);
?>