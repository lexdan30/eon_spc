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

if( !empty( $param['acct'] ) ){ $search=$search." AND idacct 	= '".$param['acct']."' "; }
if( !empty( $param['search_depts'] ) ){ $search = $search . " AND idunit IN   (". $ids .") "; }
if( !empty($param['dfrom']) && empty($param['dto'])){
    $search=$search." AND date BETWEEN DATE('".$param['dfrom']."') AND DATE('".$param['dfrom']."') ";
}
if( !empty($param['dfrom']) && !empty($param['dto']) ){
    $search=$search." AND date BETWEEN DATE('".$param['dfrom']."') AND DATE('".$param['dto']."') ";   
}


$Qry = new Query();	
$Qry->table     = "vw_overtime_approval";
$Qry->selected  = "*";
// $Qry->fields    = "date BETWEEN '".$param['dfrom']."' AND '".$param['dto']."'".$search;
$Qry->fields    = "id>0".$search;
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){

	$count=1;

    while($row=mysqli_fetch_array($rs)){		
		//mga column sa database

        $units = sprintf('%0.2f',($row['planned_hrs'] / 60));

		$name23[] = array(
						$count,
                        utf8_decode($row['empname']),
                        $row['business_unit'],
						date('Y-m-d', strtotime($row['date'])),
						$row['app_time'],
						$units,
						$row['remarks'],
						$row['ot_status']
		);
	$count++;
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Overtime_'.$date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array($param['company']));
fputcsv($output, array("Overtime"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array(	'#',
						'Employee Name',
                        'Department',
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