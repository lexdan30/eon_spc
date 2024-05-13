<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_GET;
$return = null;
$date=SysDate();

$search='';

if( !empty( $param['shiftname'] ) ){ $search=" AND id like   '%".$param['shiftname']."%' "; }


$name23 = array();
$Qry = new Query();	
$Qry->table     = "vw_datacalendar";
$Qry->selected  = "*";
$Qry->fields    = "id>0 ".$search;
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){	

		$name23[] = array(
						$row['name'],
						$row['sun'],
						$row['mon'],
						$row['tue'],
						$row['wed'],
                        $row['thu'],
                        $row['fri'],
                        $row['sat'],

		);
	
    }
}
// print_r($name23);
// return;
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=CalendarSetup_'.$date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array($param['company']));
fputcsv($output, array("Calendar Setup"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Name',
						'Sun Shift',
                        'Mon Shift',
                        'Tue Shift',
                        'Wed Shift',
                        'Thu Shift',
                        'Fri Shift',
                        'Sat Shift')); 
 
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}



?>