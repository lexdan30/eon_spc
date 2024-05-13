<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 


$param = $_GET;
$return = null;	
$date=SysDate();

$search='';
if( !empty( $param['search_acct'] ) ){ $search=$search." AND idacct 	= '".$param['search_acct']."' "; }
//Search Department
if( !empty( $param['search_dept'] ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$param['search_dept']);
    array_push( $arr_id, $param['search_dept'] );
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
  
$name23 = array();
$Qry = new Query();	
$Qry->table     = "vw_leavesummary AS al LEFT JOIN vw_dataemployees AS de ON al.idacct = de.id LEFT JOIN tblleaves AS tl ON al.idleave = tl.id";
$Qry->selected  = "al.idacct,de.empid,de.empname,de.business_unit, tl.name,al.entitle, al.used,al.balance,al.pending,al.carry_over,al.conversion";
$Qry->fields    = "de.id!=1 ".$search." order by de.empname";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        
		$name23[] = array(
                    $row['empid'],
                    utf8_decode($row['empname']),
                    $row['business_unit'],
                    $row['name'],
                    $row['entitle'],
                    $row['used'],
                    $row['balance'],
                    $row['pending'],
                    $row['carry_over'],
                    $row['conversion'],

		);
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=LeaveRecords_'.$date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array($param['company']));
fputcsv($output, array("Leave Records"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('#',
						'Employee Name',
						'Department/Section',
                        'LeaveType',
                        'entitle',
                        'used',
                        'balance',
                        'pending',
                        'carried over',
                        'converted')); 
 
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}

?>