<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 


$param = json_decode(file_get_contents('php://input'));
$date_create=SysDate();
$time_create=SysTime();
// $param->application_status = '';
// $param->leave_status = '';

if(!empty($param->leave_status)){
if($param->leave_status == 'PENDING'){
            $Qry           = new Query();
            $Qry->table    = "tbltimeleaves";
            $Qry->selected = "stat='4', cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
            $Qry->fields   = "id='".$param->id."'";                        
            $rs = $Qry->exe_UPDATE($con);

            $Qry2 = new Query();	
            $Qry2->table     = "tbltimeleaves";
            $Qry2->selected  = "idacct, idleave, hrs";
            $Qry2->fields    = "id='".$param->id."'";
            $rs2 = $Qry2->exe_SELECT($con);

            if(mysqli_num_rows($rs2)>= 1){
                if($row2=mysqli_fetch_assoc($rs2)){
                    $Qry3           = new Query();
                    $Qry3->table    = "tblaccountleaves";
                    $Qry3->selected = "pending_bal = pending_bal - ".$row2['hrs']."";
                    $Qry3->fields   = "idacct='".$row2['idacct']."' AND idleave='".$row2['idleave']."'";                        
                    $Qry3->exe_UPDATE($con);
                }
            }
}
}

if(!empty($param->application_status)){
    if($param->application_status == 'PENDING'){
    
        $Qry2           = new Query();
        $Qry2->table    = "tblappcancel";
        $Qry2->selected = "status='4',cancelby='" .$param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
        $Qry2->fields   = "id='".$param->ids."'";                        
        $rs = $Qry2->exe_UPDATE($con);
    
        // $Qry2 = new Query();	
        // $Qry2->table     = "tbltimeleaves";
        // $Qry2->selected  = "idacct, idleave, hrs";
        // $Qry2->fields    = "id='".$param->id."'";
        // $rs2 = $Qry2->exe_SELECT($con);
    
        // if(mysqli_num_rows($rs2)>= 1){
        //     if($row2=mysqli_fetch_array($rs2)){
        //         $Qry3           = new Query();
        //         $Qry3->table    = "tblaccountleaves";
        //         $Qry3->selected = "pending_bal = pending_bal - ".$row2['hrs']."";
        //         $Qry3->fields   = "idacct='".$row2['idacct']."' AND idleave='".$row2['idleave']."'";                        
        //         $Qry3->exe_UPDATE($con);
        //     }
        // }
    }
}




if(!empty($param->leave_status)){
if($param->leave_status == 'APPROVED' && $param->isJapanese == false){

    $status = 3;
    $origin = 'approvedcancel';
    $approver1 = $param->idacct;
 

    function getSuperiorid( $con,$approver1){
        $Qry 			= new Query();	
        $Qry->table     = "vw_dataemployees";
        $Qry->selected  = "idsuperior";
        $Qry->fields    = "id = '".$approver1."'";
        $rs = $Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>= 1){
            return mysqli_fetch_assoc($rs)['idsuperior'];
        }
        return '';
    }
    

    $Qry2           = new Query();
    $Qry2->table    = "tblappcancel";
    $Qry2->selected = "cancelby,
                      cancel_date,
                      cancel_time,
                      idacct, 
                      docnumber,
                      idapp,
                      type,
                      status,
                      date,
                      origin,
                      idleave, 
                      approver1,
                      cancel_reason,
                      hrs
                      ";

    $Qry2->fields   = "'".$param->accountid."', 
                    '".$date_create."',
                    '".$time_create."',
                    '".$param->accountid."',
                    '".$param->ticketno."',
                    '".$param->id."',
                    '".$param->title."',
                    '".$status."',
                    '".$param->date_applied."',
                    '".$origin."',
                    '".$param->idleave."',
                    '".getSuperiorid( $con,$param->accountid)."',
                    '".$param->reason."',
                    '".$param->hrs."'
                    ";

    

    $rs2 = $Qry2->exe_INSERT($con); 
}elseif($param->leave_status == 'APPROVED' && $param->isJapanese == true){
    $Qry           = new Query();
    $Qry->table    = "tbltimeleaves";
    $Qry->selected = "stat='4', cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
    $Qry->fields   = "id='".$param->id."'";                        
    $rs = $Qry->exe_UPDATE($con);
}
}

//mysqli_close($con);
?>