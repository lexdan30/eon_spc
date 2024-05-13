<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));
$return 	= null;	

$str =  $param->accountid;


$year = $param->search_year;
if(!empty($param->search_year)){
	$year = $param->search_year;
}else{
	$year = (new DateTime)->format("Y");
}

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
	
	$arr_OTyear  = array();

	if( strlen($str) > 0 ){
		while($month<=12){
			if( $month != 12 ){
				$dFrom	= $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-01";
				$dTo	= $year."-".str_pad(((int)$month+1),2,"0",STR_PAD_LEFT)."-01";
			}else{
				$dFrom	= $year."-12-01";
				$dTo	= ((int)$year+1)."-01-01";
			}


            $arr_OTyear[]  = getCountsOTbyYear($con,  $str, $dFrom, $dTo);
			
			$month++;
			
		}
	}
	
	
	$data	 	= array(
		"status"	=>	"success",
		"OTyear"	=>	$arr_OTyear,
		"yearss"	=> getYear($con,$str, $dFrom, $dTo),
        "pp"        => $str,
	);
	
	$return =  json_encode($data);
}else{
	$return = json_encode(array('status'=>'error'));
}

$return =  json_encode($data);
print $return;
mysqli_close($con);


// function getCountsOTbyYear($con,  $idacct,$dFrom, $dTo){
// 	$Qry 			= new Query();	
// 	$Qry->table     = "tblpayroll";
// 	$Qry->selected  = "COUNT(id) AS ctr";
// 	$Qry->fields    = "(pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND class_id=18";
// 	$rs 			= $Qry->exe_SELECT($con);
// 	if( mysqli_num_rows($rs) >= 1 ){
// 		if($row=mysqli_fetch_array($rs)){
// 			return $row['ctr'];
// 		}
// 	}
// 	return 0;
// }
function getCountsOTbyYear($con, $idacct, $dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "vw_overtime_approval";
	$Qry->selected  = "COUNT(idacct) AS ctr";
	$Qry->fields    = "(date >= '".$dFrom."' AND date < '".$dTo."') AND stat=1 ";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}

function getYear($con){
	$date_arr=array();
	$Qry 			= new Query();	
	$Qry->table     = "tblpayroll";
	$Qry->selected  = "IFNULL(MIN(YEAR(pay_date)),2018) AS min_year, IFNULL(MAX(YEAR(pay_date)), YEAR(CURDATE())) AS max_year";
	$Qry->fields    = "id>0";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			//For Loop
			for ($x = (int)$row['min_year']; $x <= (int)$row['max_year']; $x++) {
				array_push($date_arr,(int)$x);
			}
		}
	}
	return $date_arr;
}


?>