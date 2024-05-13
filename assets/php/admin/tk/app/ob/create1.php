<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param       = json_decode(file_get_contents('php://input'));
$date_create=SysDate();
$time_create=SysTime();

$begin = new DateTime($param->from);
$end = new DateTime($param->to);


$approver1 = getSuperior($con, $param->acct);
$approver2 = getHrid($con);

$ctr=1;
for($i = $begin; $i <= $end; $i->modify('+1 day')){
    $date =   $i->format("Y-m-d");
    $time 	   = time();
    
    $docnumber = "OB".$param->acct.strtotime( $date_create.$time ).$time.$ctr;
    $ctr++;

    $Qry 			= new Query();	
    $Qry->table 	= "tbltimeobtrip";
    $Qry->selected 	= "creator,
                        docnumber, 
                        idacct,
                        date,
                        remarks, 
                        approver1, 
                        approver2, 
                        date_create,
                        id_payperiod";
    $id_period		= getTimesheetPayPeriods($con, $date);
    if( (int)$id_period	== 0 ){
        $id_period		= getLatePayPeriod($con,$date);
    }
    $Qry->fields 	= "'".$param->acct."', 
                        '".$docnumber."', 
                        '".$param->acct."', 
                        '". $date ."',
                        '". $param->remarks ."',
                        '". $approver1 ."',
                        '". $approver2 ."',
                        '".$date_create."', 
                        '".$id_period['id']."'";
    if( !empty( $remarks ) ){
        $Qry->selected 	= $Qry->selected . ", remarks";
        $Qry->fields 	= $Qry->fields 	 . ", '".$remarks."'";
    }
    $checke 			= $Qry->exe_INSERT($con);
}

$return = json_encode( array('status'=>'success') );

print $return;
mysqli_close($con);


function getTimesheetPayPeriods( $con, $date ){
    $data = array();	
    $Qry = new Query();	
    $Qry->table     = "tbltimesheet";
    $Qry->selected  = "id_payperiod";
    $Qry->fields    = "date='".$date."' ORDER BY id ASC limit 1";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $data = array( 
                "id"        => $row['id_payperiod'],
        
            );
        }
        return $data;
    }
    return 0;
}

function getHrid($con){
    $Qry = new Query();	
	$Qry->table ="tblaccount";	
	$Qry->selected ="id";
	$Qry->fields ="idaccttype='4'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['id'];
        }
    }
}


function getSuperior($con, $idacct){	
	$Qry = new Query();	
	$Qry->table ="vw_dataassign";	
	$Qry->selected ="idsuperior";
	$Qry->fields ="idacct='".$idacct."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['idsuperior'];
        }
	}
}
?>