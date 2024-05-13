<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date=SysDate();
$time=SysTime();
$return = null;	
$data = array();
if( !empty($param->accountid) ){
	$Qry 			= new Query();	
	$Qry->table     = "vw_resocenter as a LEFT JOIN vw_databusinessunits as b ON b.id = a.idunit";
	$Qry->selected  = "a.*,b.name AS unit";
	$Qry->fields    = "a.reso_date BETWEEN '".$param->info->sdate."' AND '".$param->info->fdate."' ";
	if( !empty($param->idunit) ){
		$dept = $param->idunit;
		$arr_id = array();
		$arr    = getHierarchy($con, $dept);
		array_push($arr_id, $dept);
		if (!empty($arr["nodechild"])) {
			$a = getChildNode($arr_id, $arr["nodechild"]);
			if (!empty($a)) {
				foreach ($a as $v) {
					array_push($arr_id, $v);
				}
			}
		}
		if (count($arr_id) == 1) {
			$ids = $arr_id[0];
		} else {
			$ids = implode(",", $arr_id);
		}
		$Qry->fields    = "a.idunit IN (".$ids.") AND a.reso_date BETWEEN '".$param->info->sdate."' AND '".$param->info->fdate."'";
	}
	$rs				= $Qry->exe_SELECT($con);		
	if(mysqli_num_rows($rs)>=1){
		while($row=mysqli_fetch_array($rs)){
			$path = 'assets/images/undefined.webp?'.time();
			if( !empty( $row['pic'] ) ){
				$path = 'assets/php/admin/hr/employee/pix/'.$row['pic'].'?'.time();
			}
			$data[] = array(
				"date" 		=> $row['reso_date'],
				"unit"		=> $row['unit'],
				"name"  	=> $row['empname'],
				"pic"		=> $path,
				"time" 		=> $row['reso_time'],
				"txt" 		=> $row['reso_txt'],
				"range"		=> array(
									"min" => $param->info->sdate,
									"max" => $param->info->fdate
								)
			);
		}
	}
}
$return = json_encode($data);

print $return;
mysqli_close($con);


?>