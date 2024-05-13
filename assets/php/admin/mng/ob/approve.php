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
    $approvenext = $value->approvenext;//lex autoapprove for testing
    $applevel = $value->applevel;//lex autoapprove for testing
 
    if ($apprcount == $apprseq) {
        $Qry        = new Query();
        $Qry->table = "tbltimeobtrip";
        $Qry->selected = "stat = '1',
                            date_approve ='" . SysDate() . "',
                            approver".$apprcount."_stat='1',
                            approver".$apprcount."_date='" . SysDate() . "',
                            approver".$apprcount."_time='" . SysTime() . "'";   

        $Qry->fields = "id='" . $obid . "'";
        $Qry->exe_UPDATE($con);
    }else if($apprseq == 3){
        $Qry        = new Query();
        $Qry->table = "tbltimeobtrip";
        $Qry->selected = "approver3_stat='1',
                        approver3_date='" . SysDate() . "',
                        approver3_time='" . SysTime() . "'";
        $Qry->fields = "id='" . $obid . "'";
        $Qry->exe_UPDATE($con);

        if($approvenext){//lex autoapprove for testing
            $Qry2        = new Query();
            $Qry2->table = "tbltimeobtrip";
            $Qry2->selected = "approver4_stat='1',
                            approver4_date='" . SysDate() . "',
                            approver4_time='" . SysTime() . "'";
            $Qry2->fields = "id='" . $obid . "'";
            $Qry2->exe_UPDATE($con);

            if ($apprcount == ($apprseq + 1)) {//lex autoapprove for testing
                $Qry3        = new Query();
                $Qry3->table = "tbltimeobtrip";
                $Qry3->selected = "stat = '1',
                                    date_approve ='" . SysDate() . "'";
                $Qry3->fields = "id='" . $obid . "'";
                $Qry3->exe_UPDATE($con);
            }
        }
    }else if($apprseq == 2){
        $Qry        = new Query();
        $Qry->table = "tbltimeobtrip";
        $Qry->selected = "approver2_stat='1',
                        approver2_date='" . SysDate() . "',
                        approver2_time='" . SysTime() . "'";
        $Qry->fields = "id='" . $obid . "'";
        $Qry->exe_UPDATE($con);

        if($approvenext){//lex autoapprove for testing
            $Qry2        = new Query();
            $Qry2->table = "tbltimeobtrip";
            $Qry2->selected = "approver3_stat='1',
                            approver3_date='" . SysDate() . "',
                            approver3_time='" . SysTime() . "'";
            $Qry2->fields = "id='" . $obid . "'";
            $Qry2->exe_UPDATE($con);

            if ($apprcount == ($apprseq + 1)) {//lex autoapprove for testing
                $Qry3        = new Query();
                $Qry3->table = "tbltimeobtrip";
                $Qry3->selected = "stat = '1',
                                    date_approve ='" . SysDate() . "'";
                $Qry3->fields = "id='" . $obid . "'";
                $Qry3->exe_UPDATE($con);
            }
        }
    }else{
        $Qry        = new Query();
        $Qry->table = "tbltimeobtrip";
        $Qry->selected = "
                        approver1_stat='1',
                        approver1_date='" . SysDate() . "',
                        approver1_time='" . SysTime() . "'";
        $Qry->fields = "id='" . $obid . "'";
        $Qry->exe_UPDATE($con);

        if($approvenext){//lex autoapprove for testing
            $Qry2        = new Query();
            $Qry2->table = "tbltimeobtrip";
            $Qry2->selected = "approver2_stat='1',
                            approver2_date='" . SysDate() . "',
                            approver2_time='" . SysTime() . "'";
            $Qry2->fields = "id='" . $obid . "'";
            $Qry2->exe_UPDATE($con);

            if ($apprcount == ($apprseq + 1)) {//lex autoapprove for testing
                $Qry3        = new Query();
                $Qry3->table = "tbltimeobtrip";
                $Qry3->selected = "stat = '1',
                                    date_approve ='" . SysDate() . "'";
                $Qry3->fields = "id='" . $obid . "'";
                $Qry3->exe_UPDATE($con);
            }
        }
    }
   
}


$return = json_encode(array(
    "status" => "success"
));

print $return;
mysqli_close($con);

function getEmail($con,$idacct){
    $Qry = new Query();	
    $Qry->table ="tblaccount";	
    $Qry->selected ="email";
    $Qry->fields ="id='".$idacct."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        return mysqli_fetch_assoc($rs)['email'];
    }
    return '';
}

function getOBTrip($con,$obid){
    $data = array();
    $Qry = new Query();	
    $Qry->table ="tbltimeobtrip";	
    $Qry->selected ="`date`, start_time, end_time, docnumber, idacct";
    $Qry->fields ="id='".$obid."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            $data = array(
                "date"	=>$row['date'],
                "stime"	=>$row['start_time'],
                "etime"	=>$row['end_time'],
                "docnumber"	=>$row['docnumber'],
                "idacct"	=>$row['idacct']
            );
        }
    }
    return $data;
}
?>