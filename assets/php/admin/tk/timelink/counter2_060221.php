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
$unit = !empty($param->idunit) ? $param->idunit : '';
$empname = !empty($param->info->empname) ? $param->info->empname : '';
if( !empty($param->accountid) ){
	
	$arr = getIDConf($con);
	if( !empty($arr) ){
		$Qry 			= new Query();	
		$Qry->table     = "tbltimelogs a LEFT JOIN vw_dataemployees aa ON a.acct_id = aa.empid";
		$Qry->selected  = "a.idunit AS idunit, aa.business_unit AS business_unit, aa.empname       AS empname, a.work_date      AS work_date, TIME_FORMAT(a.work_time,'%h:%i %p') AS work_time, (CASE WHEN (a.time_type = '".$arr['in1']."' OR a.time_type = '".$arr['in2']."' ) THEN 'IN' ELSE 'OUT' END) AS time_type, (SELECT COUNT(0) FROM tbltimelogs b WHERE ((a.id >= b.id) AND (CONCAT(a.acct_id,a.work_date,a.time_type) = CONCAT(b.acct_id,b.work_date,b.time_type)))) AS row_number";
		$Qry->fields    = "a.idunit > 0 AND a.work_date BETWEEN '".$param->info->sdate."' AND '".$param->info->fdate."'";
		if( empty($unit) && empty($empname)){
			$Qry->fields    = $Qry->fields    . " ORDER BY a.acct_id,a.work_date,a.work_time ASC";
		}

		if( !empty($unit) ){
			$Qry->fields    = $Qry->fields    . " AND a.idunit = '".$unit."' ";
		}
		
		if( !empty($empname) ){
			//$nameemp = $param->info->empname;
			$Qry->fields    = $Qry->fields    . " AND aa.empname = '".$empname."' ";
		}

		$rs				= $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>=1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array(
					"unit" 		=> $row['business_unit'],
					"name"  	=> $row['empname'],
					"work_date" => $row['work_date'],
					"work_time" => $row['work_time'],
					"time_type" => $row['time_type'],
					"row_num" 	=> $row['row_number'],
					"range"		=> getDateRange($con,$param->info->sdate,$param->info->fdate)
				);
			}
		}
	}
}
$return = json_encode($data);

print $return;
mysqli_close($con);

function getIDConf($con){
	$data = array();
	$Qry 			= new Query();	
	$Qry->table     = "tbltimelogs";
	$Qry->selected  = "idconf";
	$Qry->fields    = "id>0 ORDER BY id DESC LIMIT 1";
	$rs				= $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){
		if($row=mysqli_fetch_array($rs)){
			$Qry2 			= new Query();	
			$Qry2->table    = "tbltimelogsconf";
			$Qry2->selected = "ain,aout,bin,bout";
			$Qry2->fields   = "id='".$row['idconf']."'";
			$rs2			= $Qry2->exe_SELECT($con);
			if(mysqli_num_rows($rs2)>=1){
				if($row2=mysqli_fetch_array($rs2)){
					$data = array(
						"in1" 	=> $row2['ain'],
						"in2" 	=> $row2['bin'],
						"out1" 	=> $row2['aout'],
						"out2" 	=> $row2['bout']
					);
				}
			}
		}
	}
	return $data;
}

function getDateRange($con,$sdate,$fdate){
	$data = array();
	$Qry 			= new Query();	
	$Qry->table     = "vw_datatimelogswipe";
	//$Qry->selected  = "MIN(work_date) AS min_date, MAX(work_date) AS max_date";
	$Qry->selected  = "'".$sdate."' AS min_date, '".$fdate."' AS max_date";
	$Qry->fields    = "idunit>0";
	$rs				= $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){
		if($row=mysqli_fetch_array($rs)){
			$data = array(
				"min" => $row['min_date'],
				"max" => $row['max_date']
			);
		}
	}
	return $data;
}

?>