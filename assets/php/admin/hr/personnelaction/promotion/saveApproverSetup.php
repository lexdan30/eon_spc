<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

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
        

        $Qry->selected 	=   "approver_type_1a='".(int)$param->sequence->approvertype1a."',
                             approver_type_1b='".(int)$param->sequence->approvertype1b."',
                             approver_type_2a='".(int)$param->sequence->approvertype2a."',
                             approver_type_2b='".(int)$param->sequence->approvertype2b."',
                             approver_type_3a='".(int)$param->sequence->approvertype3a."',
                             approver_type_3b='".(int)$param->sequence->approvertype3b."',
                             approver_type_4a='".(int)$param->sequence->approvertype4a."',
                             approver_type_4b='".(int)$param->sequence->approvertype4b."',
                             approver_type_5a='".(int)$param->sequence->approvertype5a."',
                             approver_type_5b='".(int)$param->sequence->approvertype5b."',
                             approver_type_6a='".(int)$param->sequence->approvertype6a."',
                             approver_type_6b='".(int)$param->sequence->approvertype6b."',
                             approver_type_7a='".(int)$param->sequence->approvertype7a."',
                             approver_type_7b='".(int)$param->sequence->approvertype7b."',
                             approver_1a='".(int)$param->sequence->approver1a."',
                             approver_1b='".(int)$param->sequence->approver1b."',
                             approver_2a='".(int)$param->sequence->approver2a."',
                             approver_2b='".(int)$param->sequence->approver2b."',
                             approver_3a='".(int)$param->sequence->approver3a."',
                             approver_3b='".(int)$param->sequence->approver3b."',
                             approver_4a='".(int)$param->sequence->approver4a."',
                             approver_4b='".(int)$param->sequence->approver4b."',
                             approver_5a='".(int)$param->sequence->approver5a."',
                             approver_5b='".(int)$param->sequence->approver5b."',
                             approver_6a='".(int)$param->sequence->approver6a."',
                             approver_6b='".(int)$param->sequence->approver6b."',
                             approver_7a='".(int)$param->sequence->approver7a."',
                             approver_7b='".(int)$param->sequence->approver7b."',
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