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

if(!empty($param->search_month)){
	$month = $param->search_month;
}else{
	$month =  array_search(date("F"), $months) + 1;
}

if( !empty( $param->accountid ) ){

	// if( strlen($str) > 0 ){
	// 	$data1=array();
	// 	$data2=array();
	// 	$Qry 			= new Query();	
	// 	$Qry->table     = "vw_databusinessunits";
	// 	$Qry->selected  = "*";
	// 	$Qry->fields    = "unittype=3";
	// 	$rs 			= $Qry->exe_SELECT($con);
	// 	if( mysqli_num_rows($rs) >= 1 ){
			
	// 		while($row=mysqli_fetch_array($rs)){
	// 			$ids = 0;
	// 			$dept =$row['id'];
	// 			//$arr_leaves=array();

	// 			$month = $param->search_month;

	// 			//Get heirarchy
	// 			if (!empty($dept)) {
	// 				$arr_id = array();
	// 				$arr    = getHierarchy($con, $dept);
	// 				array_push($arr_id, $dept);
	// 				if (!empty($arr["nodechild"])) {
	// 					$a = getChildNode($arr_id, $arr["nodechild"]);
	// 					if (!empty($a)) {
	// 						foreach ($a as $v) {
	// 							array_push($arr_id, $v);
	// 						}
	// 					}
	// 				}
	// 				if (count($arr_id) == 1) {
	// 					$ids = $arr_id[0];
	// 				} else {
	// 					$ids = implode(",", $arr_id);
	// 				}
	// 				array_push($data2,$ids);
				
	// 				// while($month<=12){
	// 					if( $month != 12 ){
	// 						$dFrom	= $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-01";
	// 						$dTo	= $year."-".str_pad(((int)$month+1),2,"0",STR_PAD_LEFT)."-01";
	// 					}else{
	// 						$dFrom	= $year."-12-01";
	// 						$dTo	= ((int)$year+1)."-01-01";
	// 					}
				
	// 					$arr_leaves  = getCountsOT($con,  $ids, $dFrom, $dTo);
	// 					$month++;
	// 				// }
	// 				array_push($data1,$arr_leaves);
	// 			}
	// 		}
	// 	}



		
	// }

	// $depts1	= getDept1($con,  $str);
	if( $month != 12 ){
        $dFrom	= $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-01";
        $dTo	= $year."-".str_pad(((int)$month+1),2,"0",STR_PAD_LEFT)."-01";
    }else{
        $dFrom	= $year."-12-01";
        $dTo	= ((int)$year+1)."-01-01";
    }
    

	$data	 	= array(
        "status"	=>	"success",
        "overtime"	=> getEmpCountOT($con,  $str, $dFrom, $dTo),
        
        "emps"     =>  getEmp($con, $str, $dFrom, $dTo),
        
        "pp"        => $str,
	);
	// $date2 = array();
	// foreach($data as $key => $value){

	// 	if($value->$overtime != 0){
	// 		array_push($data2,$value);
	// 	}
	// }
	
	$return =  json_encode($data);
}else{
	$return = json_encode(array('status'=>'error'));
}

$return =  json_encode($data);
print $return;
mysqli_close($con);



// function getCountsOT($con,  $idunit,$dFrom, $dTo){
// 	$Qry 			= new Query();	
// 	$Qry->table     = "tblpayroll";
// 	$Qry->selected  = "COUNT(id) AS ctr";
// 	$Qry->fields    = "dept_id IN (".$idunit.") AND (pay_date >= '".$dFrom."' AND pay_date < '".$dTo."') AND idstatus=1 AND class_id=18 ";
// 	$rs 			= $Qry->exe_SELECT($con);
// 	if( mysqli_num_rows($rs) >= 1 ){
// 		if($row=mysqli_fetch_array($rs)){
// 			return $row['ctr'];
// 		}
// 	}
// 	return 0;
// }
function getCountsOT($con,  $ids, $dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "vw_overtime_approval";
	$Qry->selected  = "COUNT(idacct) AS ctr";
    $Qry->fields    = "idacct IN (".$ids.") AND (date >= '".$dFrom."' AND date < '".$dTo."') AND stat=1";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		while($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}

function getDept($con,  $idacct){
	$data=array();
	$Qry 			= new Query();	
	$Qry->table     = "vw_databusinessunits";
	$Qry->selected  = "*";
	$Qry->fields    = "unittype=3";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		while($row=mysqli_fetch_array($rs)){
			array_push($data,$row['name']);
			
		}
	}
	return $data;
}

function getEmpCountOT($con, $ids, $dFrom, $dTo){
	$data=array();
	$Qry 			= new Query();	
	$Qry->table     = "vw_dataemployees";
	$Qry->selected  = "*";
	$Qry->fields    = "id";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		while($row=mysqli_fetch_array($rs)){
			array_push($data,getCountsOT($con,  $row['id'], $dFrom, $dTo));
			
		}
	}
	return $data;
}

function getEmp($con, $ids, $dFrom, $dTo){
	$data=array();
	$Qry 			= new Query();	
	$Qry->table     = "vw_dataemployees";
	$Qry->selected  = "*";
	$Qry->fields    = "id";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		while($row=mysqli_fetch_array($rs)){
			array_push($data,$row['empname']);
		}
	}
	return $data;
}


?>