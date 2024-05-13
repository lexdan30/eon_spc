<?php
require_once('../../../activation.php');
$conn = new connector();
$con  = $conn->connect();
require_once('../../../classPhp.php');

$param       = json_decode(file_get_contents('php://input'));
$date_action = date("Y-m-d", strtotime(SysDate()));

foreach ($param->info as $key => $value) {
    $obid   = $value->obid;
    $apprseq   = $value->approversequence;
    $apprcount = $value->approvercount;
     $remarks   = $value->remarks;
     $Qry        = new Query();
     $Qry->table = "tbltimeobtrip";

     if ($apprcount == $apprseq) {
        $Qry->selected = "stat = '2',
                            date_approve ='" . SysDate() . "',
                            approver3_stat='2',
                            approver3_date='" . SysDate() . "',
                            approver3_reason='" . $remarks . "',
                            approver3_time='" . SysTime() . "'";
    }else if($apprseq == 2){
        $Qry->selected = "stat = '2',
                            date_approve ='" . SysDate() . "',
                            approver2_stat='2',
                            approver2_date='" . SysDate() . "',
                            approver2_reason='" . $remarks . "',
                            approver2_time='" . SysTime() . "'";    
    }else {
       $Qry->selected = "stat = '2',
                           date_approve ='" . SysDate() . "',
                           approver1_stat='2',
                           approver1_date='" . SysDate() . "',
                           approver1_reason='" . $remarks . "',
                           approver1_time='" . SysTime() . "'";    
   }
     
     $date_approve = SysTime();

     $Qry->fields = "id='" . $obid . "'";
     $checke =  $Qry->exe_UPDATE($con);
    
}


$return = json_encode(array(
    "status" => "success"
));

print $return;
mysqli_close($con);


?>