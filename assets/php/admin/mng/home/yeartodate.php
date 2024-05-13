<?php
require_once('../../../logger.php');
require_once('../../../activation.php');
$conn = new connector();
$con  = $conn->connect();
require_once('../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));
$return 	= null;	


$dept = getIdUnit($con,$param->accountid);

$ids='0';
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

$str =  $param->accountid;

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "GROUP_CONCAT(id) as idacct";
$Qry->fields    = "(idunit IN (".$ids.") OR idsuperior='".$param->accountid."') AND id != '".$param->accountid."'";
$rs 			= $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
	$str = mysqli_fetch_assoc($rs)['idacct'];
}

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
	
	$arr_late = array();
	$arr_ut	  = array();
    $arr_abs  = array();
    $arr_ot  = array();
    $arr_leaves  = array();
    $arr_aa  = array();
	if( strlen($str) > 0 ){
		while($month<=12){
			if( $month != 12 ){
				$dFrom	= $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-01";
				$dTo	= $year."-".str_pad(((int)$month+1),2,"0",STR_PAD_LEFT)."-01";
			}else{
				$dFrom	= $year."-12-01";
				$dTo	= ((int)$year+1)."-01-01";
			}
			
			$arr_late[] = getCounts($con,  $str, "late", $dFrom, $dTo);
			$arr_ut[]   = getCounts($con,  $str, "ut", $dFrom, $dTo);
			$arr_abs[]  = getCounts($con,  $str, "absent", $dFrom, $dTo);
			$arr_ot[]  = getotCounts($con,  $str, "ot", $dFrom, $dTo);
			$arr_leaves[]  = getCountsLeaves($con,  $str, "id", $dFrom, $dTo);
			$arr_aa[]  = getCountsAA($con,  $str, "id", $dFrom, $dTo);
			
			 
			
			$month++;
		}
	}
	
	$data	 	= array(
		"status"	=>	"success",
		"late"		=>	$arr_late,
		"ut"		=>	$arr_ut,
        "absent"	=>	$arr_abs,
        "ot"	    =>	$arr_ot,
        "leaves1"	=>	$arr_leaves,
        "aa"	     =>	$arr_aa,
        "pp"        => $str,
	);
	
	$return =  json_encode($data);
}else{
	$return = json_encode(array('status'=>'error')); 
}

$return =  json_encode($data);
print $return;
mysqli_close($con);

function getCounts($con,  $idacct, $cols, $dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "vw_mng_timesheetfinal";
	$Qry->selected  = "COUNT(tid) AS ctr";
	$Qry->fields    = "tid IN (".$idacct.") AND ".$cols." IS NOT NULL AND ".$cols." <> '0' AND work_date BETWEEN '".$dFrom."' AND '".$dTo."'";
	$rs 			= $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getCounts');
	if( mysqli_num_rows($rs) >= 1 ){
		return mysqli_fetch_assoc($rs)['ctr'];
	}
	return 0;
}

function getotCounts($con,  $idacct, $cols, $dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "vw_overtime_approval";
	$Qry->selected  = "COUNT(idacct) AS ctr";
	$Qry->fields    = "idacct IN (".$idacct.") AND stat = '1' AND date BETWEEN '".$dFrom."' AND '".$dTo."'";
	$rs 			= $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getotCounts');
	if( mysqli_num_rows($rs) >= 1 ){
		return mysqli_fetch_assoc($rs)['ctr'];
	}
	return 0;
}


function getCountsLeaves($con,  $idacct, $cols, $dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tbltimeleaves";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "idacct IN (".$idacct.") AND ".$cols." IS NOT NULL AND ".$cols." <> '0.00' AND date BETWEEN '".$dFrom."' AND '".$dTo."'";
	$rs 			= $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getCountsLeaves');
	if( mysqli_num_rows($rs) >= 1 ){
		return mysqli_fetch_assoc($rs)['ctr'];
	}
	return 0;
}




function getCountsAA($con,  $idacct, $cols, $dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tbltimeadjustment";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "idacct IN (".$idacct.") AND ".$cols." IS NOT NULL AND ".$cols." <> '0.00' AND date BETWEEN '".$dFrom."' AND '".$dTo."'  ";
	$rs 			= $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getCountsAA');
	if( mysqli_num_rows($rs) >= 1 ){
		return mysqli_fetch_assoc($rs)['ctr'];
	}
	return 0;
}


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
?>
