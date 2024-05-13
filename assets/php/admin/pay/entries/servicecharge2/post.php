<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$monthparam = strtotime($param->info->monthparam);
$monthparam = date('Y-m-d',$monthparam);

$Qry           = new Query();
$Qry->table    = "tblservicecharge";
$Qry->selected = "docnumber, payitemid, headcount, totalhours, hourlyrate, total, appliedto, monthparam, releasedate, remarks";
$Qry->fields   = "'".$param->info->docnum."',
                    '".$param->info->payitem."',
                    '".$param->info->headcount."',
                    '".$param->info->totalhrs."',
                    '".$param->info->hrlyrate."',
                    '".$param->info->total."',
                    '".$param->info->appliedto."',
                    '".$monthparam."',
                    '".$param->info->releasedate."',
                    '".$param->info->remarks."'
                ";   

$Qry->exe_INSERT($con);
$servicechargeid = mysqli_insert_id($con);

foreach($param->employees as $key=>$value){
    foreach($param->details as $key1=>$value1){
        if($value->id == $value1->empid){
            $thrs = round($value1->acthrs + $value1->ot,2);
            $amount = round($thrs * $param->info->hrlyrate,2);

            $Qry1           = new Query();
            $Qry1->table    = "tblservicechargedetails";
            $Qry1->selected = "servicechargeid,empid,hours,amount";
            $Qry1->fields   = "'".$servicechargeid."',
                                '".$value1->empid."',
                                '".$thrs."',
                                '".$amount."'
                            ";   
                            
           $checke = $Qry1->exe_INSERT($con);
        }
    }
}

if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array("status"=>"error"));
}
print $return;
mysqli_close($con);
?>