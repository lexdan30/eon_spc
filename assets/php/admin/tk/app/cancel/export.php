<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_GET;
$return = null;	
$pay_period = getPayPeriod($con);
$date=SysDate();

if( !empty( $param['search_depts'] ) ){
    $id_array = getLocationsbunits($con,$param['search_depts']);
    $ids = implode(",",$id_array);
}

$search='';

if( !empty( $param['search_acct'] ) ){ $search=$search." AND idacct 	= '".$param['search_acct']."' "; }
if( !empty( $param['search_depts'] ) ){ $search = $search . " AND idunit IN   (". $ids .") "; }
if( !empty($param['_from']) && empty($param['_to'])){
    $search=$search." AND date BETWEEN DATE('".$param['_from']."') AND DATE('".$param['_from']."') ";
}
if( !empty($param['_from']) && !empty($param['_to']) ){
    $search=$search." AND date BETWEEN DATE('".$param['_from']."') AND DATE('".$param['_to']."') ";   
}


$Qry = new Query();	
$Qry->table     = "vw_cancel_application";
$Qry->selected  = "*";
// $Qry->fields    = "date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."'".$search;
$Qry->fields    = "id>0".$search;
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){

	$count=1;

    while($row=mysqli_fetch_array($rs)){		
		$name23[] = array(
						$count,
                        utf8_decode($row['empname']),
                        $row['business_unit'],
						date('Y-m-d', strtotime($row['date'])),
						//$row['location'],
						$row['remarks'],
                        $row['ob_status']
		);
	$count++;
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=OB_'.$date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array($param['company']));
fputcsv($output, array("Timekeeping & Leave Cancelled Applications"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('No.',
						'Employee Name',
                        'Department',
						'Date',
						//'Location',
						'Reason',
						'Status')); 
 
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}

?>