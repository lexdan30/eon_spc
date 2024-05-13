<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_GET;
$return = null;
$date=SysDate();

$search='';

if( !empty( $param['name'] ) ){ $search=" AND id like   '%".$param['name']."%' "; }
if( !empty( $param['alias'] ) ){ $search=" AND idtype like   '%".$param['alias']."%' "; }


$name23 = array();
$Qry = new Query();	
$Qry->table     = "vw_dataholidays";
$Qry->selected  = "*";
$Qry->fields    = "id>0 ".$search;
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){	

		$name23[] = array(
						$row['name'],
						$row['date'],
						$row['type'],
						$row['location'],
		);
	
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=HolidaySetup_'.$date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array($param['company']));
fputcsv($output, array("Holiday Setup"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Name',
						'Holiday Date',
                        'Holiday Type',
                        'Location')); 
 
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}



?>