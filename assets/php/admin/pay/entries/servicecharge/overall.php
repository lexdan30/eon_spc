<?php 
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
if(!empty($param->accountid)){
$Qry = new Query();	
$Qry->table     = "tblservicecharge";
$Qry->selected  = "*";
$Qry->fields = "id>0";


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id" 	            => $row['id'],
            "docnum" 	        => $row['docnumber'],
            "payitemid"         => $row['payitemid'],
            "headcount"             => $row['headcount'],
            "totalhours"             => $row['totalhours'],
            "hourlyrate"          => $row['hourlyrate'],
            "total"             => $row['total'],
            "appliedto"            => $row['appliedto'],
            "monthparam"           => $row['monthparam'],
            "period_coverage"           =>  getallPayperiod($con,$row['period_coverage']),
            "releasedate"           => $row['releasedate'],
            "remarks"              => $row['remarks'],
            "backpay_hrs"              => $row['backpay_hrs'],
            "prev_hourlyrate"              => $row['prev_hourlyrate'],
            "tot_bp_amount"              => $row['tot_bp_amount'],
            "net_sc_amount"              => $row['net_sc_amount']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
	
}
print $return;
 mysqli_close($con);
}else{
	mysqli_close($con);
	header("Location: https://peninsula.mydhris.com/mph/");  // unAuth back to login page
}
function getallPayperiod($con,$idperiod){
    $data = array();
    $Qry = new Query();	
    $Qry->table     = "vw_payperiod_all";
    $Qry->selected  = "*";
    $Qry->fields = "tkprocess = 1 AND (id) = '" . $idperiod . "'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $data = array( 
                "id" 	            => $row['id'],
                "period_start" 	        => $row['period_start'],
                "period_end"         => $row['period_end'],
                "pay_date"             => $row['pay_date']
            );
        }
    }
    return $data;
}
?>