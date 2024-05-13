<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_GET;
$return = null;	
$date=SysDate();

$search='';
if( array_key_exists('idsuperior', $param) && !empty($param['idsuperior']) ){ 
	if( array_key_exists('units', $param) && !empty($param['units']) ){
		$search=$search." AND (idsuperior 	= '".$param['idsuperior']."' OR idsuperior in (".$param['units'].") ) ";  
	}else{
		$search=$search." AND idsuperior 	= '".$param['idsuperior']."' ";  
	}
}
if( !empty( $param['acct'] ) ){ $search=$search." AND idacct 	= '".$param['acct']."' "; }
if( !empty( $param['docu'] ) ){ $search=$search." AND docnumber like '%".$param['docu']."%' "; }
if( !empty( $param['appstat'] ) ){ $search=$search." AND stat = '".$param['appstat']."' "; }
if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['to']."')"; }



$Qry = new Query();	
$Qry->table     = "vw_overtime_application";
$Qry->selected  = "start_time, end_time, `date`, ot_status, empname, actual_hrs, remakrs";
$Qry->fields    = "id>0 ".$search;
$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){		
		//mga column sa database
		
		$ot_s = date('Y-m-d h:i A', strtotime($row['start_time']));
		$arr_s = explode(" ",$row['start_time']);
		if( $arr_s[0] == $row['date'] ){
			$ot_s = date('h:i A', strtotime($arr_s[1]));
		}
		
		$ot_f = date('Y-m-d h:i A', strtotime($row['end_time']));
		$arr_f = explode(" ",$row['end_time']);
		if( $arr_f[0] == $row['date'] ){
			$ot_f = date('h:i A', strtotime($arr_f[1]));
		}
		
		$name23[] = array(
						utf8_decode($row['empname']),
						date('Y-m-d', strtotime($row['date'])),
						$ot_s. ' to ' .$ot_f,
						$row['actual_hrs'],
						$row['remarks'],
						ucwords(strtolower($row['ot_status']))
		);
    }
}
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Overtime_'.$date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array($param['company']));
fputcsv($output, array("Overtime"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Employee Name',
						'Date',
						'OT Application',
						'Units',
						'Reason',
						'Status')); 
 
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}

?>