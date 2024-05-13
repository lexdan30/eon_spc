<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date_create=SysDate();
$time_create=SysTime();
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



if($param->type == 'changeshift' && $param->status == 'PENDING'){
    $Qry           = new Query();
    $Qry->table    = "tbltimeshift";
    $Qry->selected = "stat='4', cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
    $Qry->fields   = "id='".$param->id."'";                        
    $rs = $Qry->exe_UPDATE($con);

    $Qry2           = new Query();
    $Qry2->table    = "tblappcancel";
    $Qry2->selected = "status='4', cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
    $Qry2->fields   = "id='".$param->id."'";                        
    $rs = $Qry2->exe_UPDATE($con);
}

if($param->type == 'changeshift' && $param->status == 'APPROVED' && $param->isJapanese == false){
    //print_r("HI");
    $type = 'changeshift';
    $status = 3;
    $origin = 'approvedcancel';
    $idleave = 103;


    $Qry           = new Query();
    $Qry->table    = "tblappcancel";

    $Qry->selected = "cancelby,
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
                      cancel_reason
                      ";

    $Qry->fields   = "'".$param->accountid."', 
                    '".$date_create."',
                    '".$time_create."',
                    '".$param->accountid."',
                    '".$param->ticketno."',
                    '".$param->id."',
                    '".$type."',
                    '".$status."',
                    '".$param->date_applied."',
                    '".$origin."',
                    '".$idleave."',
                    '".getSuperiorid( $con,$param->accountid)."',
                    '".$param->reason."'
                    ";

    

    $rs = $Qry->exe_INSERT($con); 
}elseif($param->type == 'changeshift' && $param->status == 'APPROVED' && $param->isJapanese == true){
    $Qry           = new Query();
    $Qry->table    = "tbltimeshift";
    $Qry->selected = "stat='4', cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
    $Qry->fields   = "id='".$param->id."'";                        
    $rs = $Qry->exe_UPDATE($con);
}

if($param->type == 'attendance' && $param->status == 'PENDING'){
    $Qry           = new Query();
    $Qry->table    = "tbltimeadjustment";
    $Qry->selected = "stat='4', cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
    $Qry->fields   = "id='".$param->id."'";                        
    $rs = $Qry->exe_UPDATE($con);

    $Qry2           = new Query();
    $Qry2->table    = "tblappcancel";
    $Qry2->selected = "status='4', cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
    $Qry2->fields   = "id='".$param->id."'";                        
    $rs = $Qry2->exe_UPDATE($con);

    // $Qry3           = new Query();
    // $Qry3->table    = "tbltimeadjustment";
    // $Qry3->selected = "approvedcancelstat='0'";
    // $Qry3->fields   = "docnumber='".$param->ticketno."'";                        
    // $rs = $Qry3->exe_UPDATE($con);
}

if($param->type == 'attendance' && $param->status == 'APPROVED' && $param->isJapanese == false){
    //print_r("HI");
    $type = 'attendance';
    $status = 3;
    $originaa = 'approvedcancel';
    $idleave = 104;


    $Qry           = new Query();
    $Qry->table    = "tblappcancel";

    $Qry->selected = "cancelby,
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
                      cancel_reason
                      ";

    $Qry->fields   = "'".$param->accountid."', 
                    '".$date_create."',
                    '".$time_create."',
                    '".$param->accountid."',
                    '".$param->ticketno."',
                    '".$param->id."',
                    '".$type."',
                    '".$status."',
                    '".$param->date_applied."',
                    '".$originaa."',
                    '".$idleave."',
                    '".getSuperiorid( $con,$param->accountid)."',
                    '".$param->reason."'
                    ";

    $rs = $Qry->exe_INSERT($con); 

    // $Qry2           = new Query();
    // $Qry2->table    = "tbltimeadjustment";
    // $Qry2->selected = "approvedcancelstat='1'";
    // $Qry2->fields   = "id='".$param->id."'";                        
    // $rs2 = $Qry2->exe_UPDATE($con);
}elseif($param->type == 'attendance' && $param->status == 'APPROVED' && $param->isJapanese == true){
    $Qry           = new Query();
    $Qry->table    = "tbltimeadjustment";
    $Qry->selected = "stat='4', cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
    $Qry->fields   = "id='".$param->id."'";                        
    $rs = $Qry->exe_UPDATE($con);
}

if($param->type == 'overtime' && $param->status == 'PENDING'){
    $Qry           = new Query();
    $Qry->table    = "tbltimeovertime";
    $Qry->selected = "stat='4', cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
    $Qry->fields   = "id='".$param->id."'";                        
    $rs = $Qry->exe_UPDATE($con);

    $Qry2           = new Query();
    $Qry2->table    = "tblappcancel";
    $Qry2->selected = "status='4', cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
    $Qry2->fields   = "id='".$param->id."'";                        
    $rs = $Qry2->exe_UPDATE($con);
}

if($param->type == 'overtime' && $param->status == 'APPROVED' && $param->isJapanese == false){
    //print_r("HI");
    $type = 'overtime';
    $status = 3;
    $originot = 'approvedcancel';
    $idleave = 101;

 

    $Qry           = new Query();
    $Qry->table    = "tblappcancel";

    $Qry->selected = "cancelby,
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

    $Qry->fields   = "'".$param->accountid."', 
                    '".$date_create."',
                    '".$time_create."',
                    '".$param->accountid."',
                    '".$param->ticketno."',
                    '".$param->id."',
                    '".$type."',
                    '".$status."',
                    '".$param->date_applied."',
                    '".$originot."',
                    '".$idleave."',
                    '".getSuperiorid( $con,$param->accountid)."',
                    '".$param->reason."',
                    '".$param->hrs."'
                    ";

    

    $rs = $Qry->exe_INSERT($con); 


}elseif($param->type == 'overtime' && $param->status == 'APPROVED' && $param->isJapanese == true){
    $Qry           = new Query();
    $Qry->table    = "tbltimeovertime";
    $Qry->selected = "stat='4', cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
    $Qry->fields   = "id='".$param->id."'";                        
    $rs = $Qry->exe_UPDATE($con);
}

if($param->type == 'obtrip' && $param->status == 'PENDING'){   
	$Qry           = new Query();
	$Qry->table    = "tbltimeobtrip";
	$Qry->selected = "stat='4',cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
	$Qry->fields   = "id='".$param->id."'";                        
	$rs = $Qry->exe_UPDATE($con);

    $Qry2           = new Query();
	$Qry2->table    = "tblappcancel";
	$Qry2->selected = "status='4',cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
	$Qry2->fields   = "id='".$param->id."'";                        
	$rs = $Qry2->exe_UPDATE($con);

}



if($param->type == 'obtrip' && $param->status == 'APPROVED' && $param->isJapanese == false){

    $Qry0           = new Query();
	$Qry0->table    = "tbltimeobtrip";
	$Qry0->selected = "statcounter='5',cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
	$Qry0->fields   = "id='".$param->id."'";                        
	$rs = $Qry0->exe_UPDATE($con);


    $type = 'obtrip';
    $status = 3;
    $originobtrip = 'approvedcancel';
    $idleave = 102;


    $Qry           = new Query();
    $Qry->table    = "tblappcancel";

    $Qry->selected = "cancelby,
                      cancel_date,
                      cancel_time,
                      idacct,
                      docnumber,
                      idapp,
                      type,
                      status,
                      date,
                      start_time,
                      end_time,
                      origin,
                      idleave,
                      approver1,
                      cancel_reason
                      ";

    $Qry->fields   = "'".$param->accountid."', 
                    '".$date_create."',
                    '".$time_create."',
                    '".$param->accountid."',
                    '".$param->ticketno."',
                    '".$param->id."',
                    '".$type."',
                    '".$status."',
                    '".$param->date_applied."',
                    '".$param->start_time."',
                    '".$param->end_time."',
                    '".$originobtrip."',
                    '".$idleave."',
                    '".getSuperiorid( $con,$param->accountid)."',
                    '".$param->reason."'
                    ";
    $rs = $Qry->exe_INSERT($con);   
}elseif($param->type == 'obtrip' && $param->status == 'APPROVED' && $param->isJapanese == true){
    $Qry           = new Query();
    $Qry->table    = "tbltimeobtrip";
    $Qry->selected = "stat='4', cancelby='" . $param->accountid ."', cancel_date='".$date_create."', cancel_time='".$time_create."'";
    $Qry->fields   = "id='".$param->id."'";                        
    $rs = $Qry->exe_UPDATE($con);
}

// mysqli_close($con);
?>