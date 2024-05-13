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

if( !empty( $param['emp'] ) ){ $search=$search." AND de.empid like 	'%".$param['emp']."%' "; }

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
$Qry->selected  = "de.id,de.empid, de.empname, de.post, SUM(dt.absent) AS awol, COUNT(dt.absent) AS awolCounter, de.concat_sup_fname_lname AS manager,de.idunit";
$Qry->fields    = "(dt.idleave is null or dt.idleave = '') and (dt.in IS NULL OR dt.in = '') and (dt.out IS NULL OR dt.out = '') and dt.idshift !=4
and (dt.work_date between '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') ".$search." group by dt.empID";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

		$name23[] = array(
                        $row['empid'],
						utf8_decode($row['empname']),
                        $row['post'],
                        $row['awol'],
                        $row['awolCounter'],
                        $row['manager']
            
		);
	
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=AbsenteeismReport_'.$date.'.csv');
$output = fopen('php://output', 'w');
// fputcsv($output, array($param['company']));
fputcsv($output, array("New World Makati Hotel"));
fputcsv($output, array("Timekeeping Absenteeism Report"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Employee ID',
						'Name',
						'Position',
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