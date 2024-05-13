<?php 
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_GET;
// if(!empty($param['accountid'])){
$data  = array();
$today_date = SysDatePadLeft();

if(!empty( $param['search']) ){
    $time = strtotime($param['search']);
    $param['search'] = date('Y-m-d',$time);
}else{
    $param['search'] = SysDate();//'2022-01-31';//SysDate();
}

$search ='';
if( !empty( $param['search'] ) ){ $search =$search." YEAR(period) = YEAR('" .$param['search']. "') AND MONTH(period) = MONTH('" . $param['search'] . "') "; }

$name23=array();
$Qry = new Query();	
$Qry->table     = "vw_fte_records";
$Qry->selected  = "*";
$Qry->fields = $search." ORDER BY classification " ;
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
            $name23[] = array(
              $row['period'],
              $row['classification'],
              $row['costcenter'],
              $row['unit_type'],
              $row['no_staffs'],
              $row['absences'],
              $row['vl'],
              $row['sl'],
              $row['othrs_lv'],
              $row['ot'],
              $row['extra_off'],
              $row['backpay'],
              $row['total'],
              $row['equiv_hrs'],
              $row['fte_factor'],
              $row['fte_factor_diff'], 
              $row['net_fte']
              
            );
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=FTE_Report_Detail_'.$today_date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array("FTE Report"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Period','Classification',
                        'Cost Center',
                        'Unit Type',
                        'No. of Staff',
                        'Absences',
                        'VL',
                        'SL',
						'Other Leaves',
						'Overtime',
						'Extra Off',
                        'Backpay',
                        'Total',
                        'Equivalent Hrs',
                        'FTE Factor',
                        '',
                        'Net FTE')); 
 
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}
// }else{
// 	header("Location: https://peninsula.mydhris.com/mph/");  // unAuth back to login page
// }
?> 