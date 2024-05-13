<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_GET;
$return = null;	
$date=SysDate();
$date1 = SysDatePadLeft();

    $search ='';

    if( !empty( $param['emp'] ) ){ $search=$search." AND de.empid like '%".$param['emp']."%' "; } 
    if( !empty( $param['emptemp'] ) ){ $search=$search." AND dt.temp = '".$param['emptemp']."' "; }

    if( !empty($param['dfrom']) && empty($param['dto'])){
        $search=$search." AND dt.date BETWEEN DATE('".$param['dfrom']."') AND DATE('".$param['dfrom']."') ";
    }
    
    if( !empty($param['dfrom']) && !empty($param['dto']) ){
        $search=$search." AND dt.date BETWEEN DATE('".$param['dfrom']."') AND DATE('".$param['dto']."') ";
    }

    if( empty($param['dfrom']) && empty($param['dto']) ){
        $search=$search." AND dt.date = '".$date1."' ";
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
$search.=" ORDER BY de.empname ASC";
$name23 = array();
$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees AS de LEFT JOIN vw_data_timesheet AS dt ON de.id = dt.idacct";
$Qry->selected  = "de.id, de.empid, de.empname, de.business_unit,dt.temp,dt.date";
$Qry->fields    = "(dt.temp IS NOT NULL OR dt.temp != '')".$search;
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){	

		$name23[] = array(
                        $row['date'],
                        $row['empid'],
						utf8_decode($row['empname']),
						$row['temp'],
						$row['business_unit'],

		);
	
    }
}
// print_r($name23);
// return;
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=EmployeeTemperature'.$date.'.csv');
$output = fopen('php://output', 'w');
// fputcsv($output, array($param['company']));
fputcsv($output, array("New World Makati Hotel"));
fputcsv($output, array("Employee's Daily Temperature"));
if( !empty( $param['filterby'] ) ){
    fputcsv($output, array($param['filterby']));
}
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Date',
                        'EmpID',
                        'Employee Name',
                        'Temperature',
						'Department/Section')); 
 
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}

?>