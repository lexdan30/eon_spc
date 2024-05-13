<?php
require_once('../../../logger.php');
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_GET;
$return = null;	
$date=SysDate();

$search='';
if( array_key_exists('accountid', $param) && !empty($param['accountid']) ){
	if( array_key_exists('units', $param) && !empty($param['units']) ){
		$search=$search." AND (idsuperior 	= '".$param['accountid']."' OR idsuperior in (".$param['units'].") ) "; 
	}else{
		$search=$search." AND idsuperior 	= '".$param['accountid']."' "; 
	}
}
if( !empty( $param['acct'] ) ){ $search=$search." AND idacct 	= '".$param['acct']."' "; }
if( !empty( $param['docu'] ) ){ $search=$search." AND docnumber like '%".$param['docu']."%' "; }
if( !empty( $param['appstat'] ) ){ $search=$search." AND stat = '".$param['appstat']."' "; }
if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['to']."')"; }
if( !empty( $param['from'] ) && empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['from']."')"; }



$Qry = new Query();	
$Qry->table     = "vw_shift_application";
$Qry->selected  = "empname, `date`, oldshift, newshift, remarks, shift_status";
// $Qry->fields    = "id>0 ".$search;
$Qry->fields="id>0 AND (idsuperior='".$param['idsuperior']."') ".$search." ORDER BY empname";
$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){		
		//mga column sa database
		$name23[] = array(
						utf8_decode($row['empname']),
						$row['date'],
						$row['oldshift'],
						$row['newshift'],
						$row['remarks'],
						ucwords(strtolower($row['shift_status']))
		);
    }
}
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=ChangeShift_'.$date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array($param['company']));
fputcsv($output, array("Change Shift"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Employee Name',
						'Date',
						'Current Shift',
						'Change Shift',
						'Reason',
						'Status')); 
 
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}

?>