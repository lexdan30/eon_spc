<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php');

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

$dept = getIdUnit($con,$param->accountid);
$ids=0;
//Get Managers Under person
if( !empty( $dept ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$dept);
    if( !empty( $arr["nodechild"] ) ){     
        $ids = join(',', flatten($arr['nodechild']));
    } else {
        $ids = '0';
    }
}

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "GROUP_CONCAT(id) as idacct";
$Qry->fields    = "(idunit IN (".$ids.") OR idsuperior='".$param->accountid."') AND id != '".$param->accountid."'";
$rs 			= $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    $str = mysqli_fetch_assoc($rs)['idacct'];
}

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

            $arr_OTyear[]  = getCountsOTbyYear($con,  $str, $dFrom, $dTo, $ids);
			
			$month++;
			
		}
    }
	
	
	$data	 	= array(
        "status"	=>	"success",
        "OTyear"	=> $arr_OTyear,
		// "OTyear"	=>	getCountsOTbyYear($con, $dFrom, $dTo, $ids),
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

function getIdUnit($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getIdUnit');
    if(mysqli_num_rows($rs)>=1){
		return mysqli_fetch_assoc($rs)['idunit'];
    }
    return null;
}

// function getCountsOTbyYear($con, $idacct, $dFrom, $dTo, $ids){
// 	$Qry 			= new Query();	
// 	$Qry->table     = "tblpayroll";
// 	$Qry->selected  = "COUNT(id) AS ctr";
// 	$Qry->fields    = "(pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND class_id=18 AND dept_id IN (".$ids.")";
// 	$rs 			= $Qry->exe_SELECT($con);
// 	if( mysqli_num_rows($rs) >= 1 ){
// 		if($row=mysqli_fetch_array($rs)){
// 			return $row['ctr'];
// 		}
// 	}
// 	return 0;
// }

function getCountsOTbyYear($con, $idacct, $dFrom, $dTo, $ids){
	$Qry 			= new Query();	
	$Qry->table     = "vw_overtime_approval";
	$Qry->selected  = "COUNT(idacct) AS ctr";
	$Qry->fields    = "(date >= '".$dFrom."' AND date < '".$dTo."') AND stat=1 AND idacct IN (".$idacct.")";
	$rs 			= $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getCountsOTbyYear');
	if( mysqli_num_rows($rs) >= 1 ){
		return mysqli_fetch_assoc($rs)['ctr'];
	}
	return 0;
}

function getYear($con){
	$date_arr = array();
	$Qry 		   = new Query();	
	$Qry->table    = "tblpayroll";
	$Qry->selected = "IFNULL(MIN(YEAR(pay_date)),2018) AS min_year, IFNULL(MAX(YEAR(pay_date)), YEAR(CURDATE())) AS max_year";
	$Qry->fields   = "id>0";
	$rs 		   = $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getYear');
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_assoc($rs)){
			//For Loop
			for ($x = (int)$row['min_year']; $x <= (int)$row['max_year']; $x++) {
				array_push($date_arr,(int)$x);
			}
		}
	}
	return $date_arr;
}


?>