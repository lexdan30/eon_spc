<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_GET;
$data  = array();
$pay_period = getPayPeriod($con);
$date=SysDate();
$date1=SysDatePadLeft();

$search ='';

if( !empty( $param['empid'] ) ){ $search=$search." AND de.empid like 	'%".$param['empid']."%' "; }
if( !empty($param['d_from']) && empty($param['d_to'])){
    $search=$search." AND dt.date BETWEEN DATE('".$param['d_from']."') AND DATE('".$param['d_from']."') ";
}
if( !empty($param['d_from']) && !empty($param['d_to']) ){
    $search=$search." AND dt.date BETWEEN DATE('".$param['d_from']."') AND DATE('".$param['d_to']."') ";
}

//Search Department
if( !empty( $param['department'] ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$param['department']);
    array_push( $arr_id, $param['department'] );
    if( !empty( $arr["nodechild"] ) ){
        $a = getChildNode($arr_id, $arr["nodechild"]);
        if( !empty($a) ){
            foreach( $a as $v ){
                array_push( $arr_id, $v );
            }
        }
    }
    if( count($arr_id) == 1 ){
        $ids 			= $arr_id[0];
    }else{
        $ids 			= implode(",",$arr_id);
    }
    $search.=" AND idunit in (".$ids.") "; 
}


$Qry = new Query();	
$Qry->table     = "vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID=de.id";
$Qry->selected  = "*";
$Qry->fields    = "(dt.idleave IS NULL OR dt.idleave = '') AND (dt.in IS NULL OR dt.in = '') AND (dt.out IS NULL OR dt.out = '') AND dt.idshift !=4 AND (dt.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."')".$search;
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        
        //Format date for display
        $date_format=date_create($row['date']);

		$name23[] = array(
                        $row['empid'],
						utf8_decode($row['empname']),
						date_format($date_format,"m/d/Y"),
                        $row['absent'],
                        $row['concat_sup_fname_lname']
		);
	
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=AbsencesReport_'.$date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array($param['company']));
fputcsv($output, array("Timekeeping Absences Report"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Employee ID',
						'Name',
						'Date',
						'AWOL',
                        'AWOL Counter',
                        'Mananger')); 
 
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}











print $return;
mysqli_close($con);
?>