<?php
require_once('../../../activation.php');
$conn = new connector();
$con  = $conn->connect();
require_once('../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));
$return 	= null;	


$str =  $param->accountid;

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "GROUP_CONCAT(id) as idacct";
$Qry->fields    = "id>0";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $str = $row['idacct'];
    }
}

$year = $param->saptoday ;
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
	
	$arr_ob = array();
	$arr_aa	  = array();
    $arr_ot  = array();
    $arr_leaves  = array();
    $arr_cs  = array();
	$arr_dr  = array();
	$wholeArr  = array();
	$totalGrossInc=0;
	$totalDeduct=0;
	$totalNetGross=0;
	if( strlen($str) > 0 ){
		while($month<=12){
			if( $month != 12 ){
				$dFrom	= $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-01";
				$dTo	= $year."-".str_pad(((int)$month+1),2,"0",STR_PAD_LEFT)."-01";
			}else{
				$dFrom	= $year."-12-01";
				$dTo	= ((int)$year+1)."-01-01";
			}

		/*
			$arr_ob[] = getCountsOB($con,  $str, $dFrom, $dTo);
			$arr_aa[]   = getCountsAA($con,  $str, $dFrom, $dTo);
			$arr_ot[]  = getCountsOT($con,  $str, $dFrom, $dTo);
            $arr_leaves[]  = getCountsLV($con,  $str, $dFrom, $dTo);
			$arr_cs[]  = getCountsCS($con,  $str, $dFrom, $dTo);
			$arr_dr[]  = getCountsDR($con,  $str, $dFrom, $dTo);
			*/
			$wholeArr =getTimesheet($con, $dFrom, $dTo , $param->typeemp);
			$arr_ob[] = $wholeArr[2];
			$arr_aa[]   = $wholeArr[1];
	
			$arr_ot[]  = $wholeArr[0];
			$totalGrossInc= $totalGrossInc + $wholeArr[2];
			$totalDeduct= $totalDeduct + $wholeArr[1];
			$totalNetGross= $totalNetGross + $wholeArr[0];
			$month++;
		}
	}
	
	
	$data	 	= array(
		"status"	=>	"success",
		"ob"		=>	$arr_ob,
		"aa"		=>	$arr_aa,
		"ot"	    =>	$arr_ot,
		"totalGrossInc"		=>	$totalGrossInc,
		"totalDeduct"		=>	$totalDeduct,
		"totalNetGross"	    =>	$totalNetGross,
		
      //  "leaves"	=>	$arr_leaves,
      //  "cs"	    =>	$arr_cs,
       // "dr"	    =>	$arr_dr,
      //  "pp"        => $str,
	);
	
	$return =  json_encode($data);
}else{
	$return = json_encode(array('status'=>'error'));
}

$return =  json_encode($data);
print $return;
mysqli_close($con);
function getTimesheet($con,  $dfrom, $dto,$typeemp){
  
    $Qry=new Query();
    
    if($typeemp == 'Local Employee'){
        $Qry->table     = "tblpayreg AS de LEFT JOIN tblpayperiod AS dt ON de.idpayperiod=dt.period";
    }else if($typeemp == 'Japanese'){
        $Qry->table     = "tblpayreg AS de LEFT JOIN tblpayperiod_japanese AS dt ON de.idpayperiod=dt.period";
    }else if($typeemp == 'Helper'){
        $Qry->table     = "tblpayreg AS de LEFT JOIN tblpayperiod_helper AS dt ON de.idpayperiod=dt.period";
    }else if($typeemp == 'Japanesecon'){
        $Qry->table     = "tblpayreg AS de LEFT JOIN tblpayperiod_japaneseconversion AS dt ON de.idpayperiod=dt.period";
    }else{
        $Qry->table     = "tblpayreg AS de LEFT JOIN vw_payperiod_all AS dt ON de.idpayperiod=dt.id";
    }


	$Qry->selected  = "gross_amount, total_ded,net_amount";



    if(empty($typeemp) ){
        $Qry->fields    = " ( dt.pay_date >= '".$dfrom."' AND dt.pay_date < '".$dto."' ) GROUP BY 'type'";
    }else{
        $Qry->fields    = " type = '".$typeemp."' AND ( dt.pay_date >= '".$dfrom."' AND dt.pay_date < '".$dto."' ) GROUP BY type";
    }

	$rs=$Qry->exe_SELECT($con);

    if( mysqli_num_rows($rs) > 0 ){
		if($row=mysqli_fetch_array($rs)){
			return array($row['net_amount'],$row['total_ded'],$row['gross_amount']);
		}
	}
    return  0;
}
/*
function getCountsOB($con,  $idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "vw_ob_application";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(date >= '".$dFrom."' AND date < '".$dTo."') AND stat=1 ";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}

function getCountsAA($con,  $idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "vw_attendance_application";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(date >= '".$dFrom."' AND date < '".$dTo."') AND stat=1";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}

function getCountsOT($con,  $idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "vw_overtime_application";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(date >= '".$dFrom."' AND date < '".$dTo."') AND stat=1";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}

function getCountsLV($con,  $idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "vw_leave_application";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(date >= '".$dFrom."' AND date < '".$dTo."') AND stat=1";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
function getCountsCS($con,  $idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "vw_shift_application";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(date >= '".$dFrom."' AND date < '".$dTo."') AND stat=1";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
function getCountsDR($con,  $idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "tbldutyroster";
	$Qry->selected  = "COUNT(id) AS ctr";
	$Qry->fields    = "(date >= '".$dFrom."' AND date < '".$dTo."') AND type_creator=2 AND secretary=1 AND manager=1";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
*/

?>