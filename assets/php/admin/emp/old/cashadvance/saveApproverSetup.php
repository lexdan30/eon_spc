<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

    $param = json_decode(file_get_contents('php://input'));
    $date = SysDate();
    $time = SysTime();
    $return = null;

    if(!empty($param->accountid)){
        //UPDATE TO DATABASE
        $Qry 			=   new Query();	
        $Qry->table 	=   "tblformsetup";
        

        // if($param->sequence->approvertype1a!=""||$param->sequence->approvertype1a!=NULL){
        //     $Qry->selected  =   "approver_type_1a='".$param->sequence->approvertype1a."";
        // }

        // if($param->sequence->approvertype1b!=""||$param->sequence->approvertype1b!=NULL){
        //     $Qry->selected  =   ",approver_type_1b='".$param->sequence->approvertype1b."',";
        // }
        

        $Qry->selected 	=   "approver_1a='".$param->sequence->approver1a."',
                             approver_1b='".$param->sequence->approver1b."',
                             approver_2a='".$param->sequence->approver2a."',
                             approver_2b='".$param->sequence->approver2b."',
                             approver_3a='".$param->sequence->approver3a."',
                             approver_3b='".$param->sequence->approver3b."',
                             approver_4a='".$param->sequence->approver4a."',
                             approver_4b='".$param->sequence->approver4b."',
                             approver_5a='".$param->sequence->approver5a."',
                             approver_5b='".$param->sequence->approver5b."',
                             idstatus='".$param->sequence->status."'";
        $Qry->fields 	= "id='".$param->sequence->id."'";
        $checke 		= $Qry->exe_UPDATE($con);
        if($checke){  
            $return = json_encode(array("status"=>"success"));
        }else{
            $return = json_encode(array("status"=>"error"));
        }
        //UPDATE DATABASE
    }else{
        $return = json_encode(array('status'=>'empty'));
    }

print $return;
mysqli_close($con);

?>