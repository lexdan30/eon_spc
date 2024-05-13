<?php
require_once('../../../activation.php');
$conn = new connector();
$con  = $conn->connect();
require_once('../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));
$return 	= null;	


$dept = getIdUnit($con,$param->accountid);
$ids=0;

//Get Managers Under person
$ids=0;if( !empty( $dept ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$dept);
    array_push( $arr_id, 0 );
    if( !empty( $arr["nodechild"] ) ){
        $a = getChildNode($arr_id, $arr["nodechild"]);
        if( !empty($a) ){
            foreach( $a as $v ){
                array_push( $arr_id, $v );
            }
        }
    }
    if( count($arr_id) == 1 ){
        $ids 			= $arr_id[0];
    }else{
        $ids 			= implode(",",$arr_id);
    }
}

$str =  $param->accountid;

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "GROUP_CONCAT(id) as idacct";
$Qry->fields    = "(idunit IN (".$ids.") OR idsuperior='".$param->accountid."') AND id != '".$param->accountid."'";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $str = $row['idacct'];
    }
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
			$arr_ot[]  = getCounts($con,  $str, "ot", $dFrom, $dTo);
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
	$Qry->table     = "vw_data_timesheet";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "idacct IN (".$idacct.") AND ".$cols." IS NOT NULL AND ".$cols." <> '0.00' AND ( work_date >= '".$dFrom."' AND work_date < '".$dTo."' ) ";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}



function getCountsLeaves($con,  $idacct, $cols, $dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tbltimeleaves";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "idacct IN (".$idacct.") AND ".$cols." IS NOT NULL AND ".$cols." <> '0.00' AND ( date >= '".$dFrom."' AND date < '".$dTo."' ) ";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}

function getCountsAA($con,  $idacct, $cols, $dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tbltimeadjustment";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "idacct IN (".$idacct.") AND ".$cols." IS NOT NULL AND ".$cols." <> '0.00' AND ( date >= '".$dFrom."' AND date < '".$dTo."' ) ";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}


function getIdUnit($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['idunit'];
        }
    }
    return null;
}

?>