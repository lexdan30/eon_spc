<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));
$return 	= null;	

$str =  $param->accountid;


$year = date("Y");
$month = 1;
$months = array(
	'January',
	'February',
	'March',
	'April',
	'May',
	'June',
	'July ',
	'August',
	'September',
	'October',
	'November',
	'December',
);

if( !empty( $param->accountid ) ){
	
    $arr_regot  = array();
    $arr_regotg8  = array();
    $arr_swhdot  = array();
    $arr_swhdotg8  = array();
    $arr_lhwdot  = array();
    $arr_lhwdotg8  = array();
    $arr_rdot  = array();
    $arr_rdotg8  = array();
    $arr_lhrdot  = array();
    $arr_lhrdotg8  = array();
    $arr_shrdot  = array();
    $arr_shrdotg8  = array();
    $arr_lshot  = array();
    $arr_lshotg8  = array();
    
    

    if( strlen($str) > 0 ){
		while($month<=12){
			if( $month != 12 ){
				$dFrom	= $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-01";
				$dTo	= $year."-".str_pad(((int)$month+1),2,"0",STR_PAD_LEFT)."-01";
			}else{
				$dFrom	= $year."-12-01";
				$dTo	= ((int)$year+1)."-01-01";
			}


            $arr_regot[]  = getCountsREGOT($con,  $str, $dFrom, $dTo);
            $arr_regotg8[]  = getCountsREGOTG8($con,  $str, $dFrom, $dTo);
            $arr_swhdot[]  = getCountsSHWDOT($con,  $str, $dFrom, $dTo);
            $arr_swhdotg8[]  = getCountsSHWDOTG8($con,  $str, $dFrom, $dTo);
            $arr_lhwdot[]  = getCountsLHWDOT($con,  $str, $dFrom, $dTo);
            $arr_lhwdotg8[]  = getCountsLHWDOTG8($con,  $str, $dFrom, $dTo);
            $arr_rdot[]  = getCountsRDOT($con,  $str, $dFrom, $dTo);
            $arr_rdotg8[]  = getCountsRDOTG8($con,  $str, $dFrom, $dTo);
            $arr_lhrdot[]  = getCountsLHRDOT($con,  $str, $dFrom, $dTo);
            $arr_lhrdotg8[]  = getCountsLHRDOTG8($con,  $str, $dFrom, $dTo);
            $arr_shrdot[]  = getCountsSHRDOT($con,  $str, $dFrom, $dTo);
            $arr_shrdotg8[]  = getCountsSHRDOTG8($con,  $str, $dFrom, $dTo);
            $arr_lshot[]  = getCountsLSHOT($con,  $str, $dFrom, $dTo);
            $arr_lshotg8[]  = getCountsLSHOTG8($con,  $str, $dFrom, $dTo);
			$month++;
			
		}
	}
	
	
	$data	 	= array(
		"status"	    =>	"success",
        "regot"	        =>	$arr_regot,
        "regotg8"       =>	$arr_regotg8,
        "swhdot"        =>	$arr_swhdot,
        "swhdotg8"      =>	$arr_swhdotg8,
        "lhwdot"        =>	$arr_lhwdot,
        "lhwdotg8"      =>	$arr_lhwdotg8,
        "arr_rdot"      =>	$arr_rdot,
        "arr_rdotg8"    =>	$arr_rdotg8,
        "arr_lhrdot"    =>	$arr_lhrdot,
        "arr_lhrdotg8"  =>	$arr_lhrdotg8,
        "arr_shrdot"    =>	$arr_shrdot,
        "arr_shrdotg8"  =>	$arr_shrdotg8,
        "arr_lshot"     =>	$arr_lshot,
        "arr_lshotg8"   =>	$arr_lshotg8,   
        "pp"            => $str,
	);
	
	$return =  json_encode($data);
}else{
	$return = json_encode(array('status'=>'error')); 
}

$return =  json_encode($data);
print $return;
mysqli_close($con);


function getCountsREGOT($con,  $idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tblpayroll";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND trans_id=1";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
function getCountsREGOTG8($con,$idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tblpayroll";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND trans_id=2";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
function getCountsSHWDOT($con,$idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tblpayroll";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND trans_id=3";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
function getCountsSHWDOTG8($con,$idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tblpayroll";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND trans_id=4";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
function getCountsLHWDOT($con,$idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tblpayroll";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND trans_id=5";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
function getCountsLHWDOTG8($con,$idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tblpayroll";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND trans_id=6";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
function getCountsRDOT($con,$idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tblpayroll";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND trans_id=7";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
function getCountsRDOTG8($con,$idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tblpayroll";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND trans_id=8";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
function getCountsLHRDOT($con,$idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tblpayroll";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND trans_id=9";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
function getCountsLHRDOTG8($con,$idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tblpayroll";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND trans_id=10";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
function getCountsSHRDOT($con,$idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tblpayroll";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND trans_id=11";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
function getCountsSHRDOTG8($con,$idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tblpayroll";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND trans_id=12";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
function getCountsLSHOT($con,$idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tblpayroll";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND trans_id=13";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
function getCountsLSHOTG8($con,$idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tblpayroll";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND trans_id=14";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}



?>