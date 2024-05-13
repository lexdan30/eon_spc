<?php
require_once('../../../logger.php');
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param 		= json_decode(file_get_contents('php://input'));
$curr_date 	= SysDatePadLeft();
$yest_date  = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $curr_date ) ) ));
$tomo_date  = date('Y-m-d',(strtotime ( '+1 day' , strtotime ( $curr_date ) ) ));
$data		= array();

if(!empty($param->accountid)){
	$data = array(
		"headctr" 		=> getHeadCtr($con,$param->accountid),
		"pending_lv"	=> getPendingLeave( $con, $param->accountid, $curr_date ),
		"absnt_now"		=> getAbsentCtr( $con, $param->accountid, $curr_date),
		"absnt_yes"		=> getAbsentCtr( $con, $param->accountid, $yest_date),
		"absnt_tom"		=> getAbsentCtr( $con, $param->accountid, $tomo_date),
		
		"img_now"		=> getAbsent( $con, $param->accountid, $curr_date),
		"img_yes"		=> getAbsent( $con, $param->accountid, $yest_date),
		"img_tom"		=> getAbsent( $con, $param->accountid, $tomo_date),
		
		"curr_date"		=> $curr_date,
		"yest_date"		=> $yest_date,
		"tomo_date"		=> $tomo_date
	);
	$return = json_encode($data);
}else{
	$return = json_encode(array('status'=>'error'));
}

print $return;
mysqli_close($con);

function getAbsentCtr( $con, $idacct, $date ){
	$Qry 			= new Query();	
	$Qry->table     = "(SELECT idacct, idsuperior FROM tblaccountjob) AS a INNER JOIN (SELECT tid, absent, work_date FROM vw_emp_timesheetfinal) AS b ON (a.idacct = b.tid)";
	$Qry->selected  = "SUM(IF((b.absent > 0), 1, 0)) AS absent_ctr";
	$Qry->fields    = "a.idsuperior='".$idacct."' AND b.work_date = '".$date."'";
	$rs 			= $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getAbsentCtr');
	if(mysqli_num_rows($rs)>= 1){
		return (int)(mysqli_fetch_assoc($rs)['absent_ctr']);
	}
	return 0;
}

function getAbsent( $con, $idacct, $date ){
	$data = array();
	$Qry 			= new Query();	
	$Qry->table     = "(SELECT work_date, absent, tid FROM vw_emp_timesheetfinal) AS a INNER JOIN (SELECT id, pic, idsuperior FROM vw_dataemployees) AS b ON (a.tid = b.id)";
	$Qry->selected  = "b.pic";
	$Qry->fields    = "a.work_date = '".$date."' AND  b.idsuperior = '".$idacct."' AND (a.absent <> '0.00') ";
	$rs 			= $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getAbsent');
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_assoc($rs)){
			$path = 'assets/images/undefined.webp';
			if( !empty( $row['pic'] ) ){
				$path = 'assets/php/admin/hr/employee/pix/'.$row['pic'].'?'.time();
			}
			$data[] = array(
				"src" => $path
			);
		}
	}
	return $data;
}

function getPendingLeave( $con, $idacct, $date ){
	$Qry 			= new Query();	
	$Qry->table     = "(SELECT idacct, id, `date`, `stat` FROM tbltimeleaves) AS a INNER JOIN (SELECT idsuperior, idacct FROM tblaccountjob) AS b ON a.idacct = b.idacct";
	$Qry->selected  = "COUNT(a.id) AS pending_ctr";
	$Qry->fields    = "b.idsuperior = '".$idacct."' AND a.date = '".$date."' AND a.stat = 3";
	$rs 			= $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getPendingLeave');
	if(mysqli_num_rows($rs)>= 1){
		return mysqli_fetch_assoc($rs)['pending_ctr'];
	}
	return 0;
}

function getHeadCtr($con, $idacct){
	$Qry 			= new Query();	
	$Qry->table     = "tblaccountjob as a";
	$Qry->selected  = "a.idacct";
	$Qry->fields    = "idsuperior = '".$idacct."'  ";
	$rs 			= $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getHeadCtr');
	return mysqli_num_rows($rs);
}

function getCurrentLate($con, $work_date, $idacct){
	$Qry 			= new Query();	
	$Qry->table     = "tbltimesheet";
	$Qry->selected  = "late";
	$Qry->fields    = "work_date = '".$work_date."' AND idacct = '".$idacct."'  ";
	$rs 			= $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getCurrentLate');
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_assoc($rs)){
			if( !empty( $row['late'] ) ){
				$num = floatval( number_format($row['late'],2) ) * 60;
				if( $num > (int)$num ){
					return number_format($num,2);
				}else{
					return $num;
				}
			}else{
				return "0";
			}
		}
	}
	return "0";
}

?>