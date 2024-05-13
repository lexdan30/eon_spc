<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_GET;
$return = null;	
$date=SysDate();

$search='';
if( array_key_exists('idsuperior', $param) && !empty($param['idsuperior']) ){ $search=$search." AND idsuperior 	= '".$param['idsuperior']."' ";  }
if( !empty( $param['acct'] ) ){ $search=$search." AND idacct 	= '".$param['acct']."' "; }
if( !empty( $param['docu'] ) ){ $search=$search." AND docnumber like '%".$param['docu']."%' "; }
if( !empty( $param['appstat'] ) ){ $search=$search." AND stat = '".$param['appstat']."' "; }
if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['to']."')"; }



$Qry = new Query();	
$Qry->table     = "vw_ob_application";
$Qry->selected  = "empname, `date`, location, remarks, ob_status";
$Qry->fields    = "id>0 ".$search;
$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){	
		
		$name23[] = array(
						utf8_decode($row['empname']),
						date('Y-m-d', strtotime($row['date'])),
						$row['location'],
						$row["remarks"],
						ucwords(strtolower($row['ob_status']))
		);
    }
}
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=OfficialBusinessTrip_'.$date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array($param['company']));
fputcsv($output, array("Official Business Trip"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Employee Name',
						'Date',
						'Location',
						'Activity',
						'Status')); 
 
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}

?>