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
$search2 =' GROUP BY empid';
if( !empty( $param['empid'] ) ){ $search=$search." AND empid like '%".$param['empid']."%' "; }

if( !empty($param['d_from']) && !empty($param['d_to'])){
    $search=$search." AND work_date BETWEEN DATE('".$param['d_from'] ."') AND DATE('".$param['d_to']."') ";
}

/*
if( !empty( $param['empid'] ) ){ $search=$search." AND de.empid like 	'%".$param['empid']."%' "; }
if( !empty($param['d_from']) && empty($param['d_to'])){
    $search=$search." AND dt.date BETWEEN DATE('".$param['d_from']."') AND DATE('".$param['d_from']."') ";
}
if( !empty($param['d_from']) && !empty($param['d_to']) ){
    $search=$search." AND dt.date BETWEEN DATE('".$param['d_from']."') AND DATE('".$param['d_to']."') ";
}
*/
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
    //$search.=" AND idunit in (".$ids.") "; 
}

/*
$Qry = new Query();	
$Qry->table     = "vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID=de.id";
$Qry->selected  = "*";
$Qry->fields    = "(dt.idleave IS NULL OR dt.idleave = '') AND (dt.in IS NULL OR dt.in = '') AND (dt.out IS NULL OR dt.out = '') AND dt.idshift !=4 AND (dt.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."')".$search;
*/

$Qry 			= new Query();	
if($param['typeemp'] == "Local Employee"){

    $Qry->table = "vw_timesheetfinal_ho";
  
  
}elseif($param['typeemp'] == "Japanese"){

    $Qry->table = "vw_timesheetfinal_japanese";

  
}elseif($param['typeemp'] == "Helper"){
  
    $Qry->table = "vw_timesheetfinal_helper";
  
  
}elseif($param['typeemp'] == "Japanesecon"){
    $Qry->table = "vw_timesheetfinal_japanesec";
}else{
    $Qry->table = "vw_timesheetfinal";
}
$Qry->selected  = "empid,empname,late, COUNT(late) AS frequency,SUM(late) AS TotalLate";
$Qry->fields    ="late NOT LIKE '0%'  ".$search.$search2;

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        
        //Format date for display
      //  $date_format=date_create($row['date']);

		$name23[] = array(
            /*
                        $row['empid'],
						utf8_decode($row['empname']),
						date_format($date_format,"m/d/Y"),
                        $row['absent'],
                        $row['concat_sup_fname_lname']*/
                      $row['empid'],
                      $row['empname'],
                      $row['frequency'],
                      round($row['TotalLate'], 2)
		);
	
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=TardinessSummaryReport_'.$date.'.csv');
$output = fopen('php://output', 'w');
//fputcsv($output, array($param['company']));
fputcsv($output, array("New World Makati Hotel"));
fputcsv($output, array("Tardiness Summary Report"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Employee ID',
						'Name',
                        'Frequency',
						'Duration'
                        )); 
 
              
if (isset($name23)) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}



mysqli_close($con);
?>