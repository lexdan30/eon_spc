<?php
require_once('../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../classPhp.php');

$param = $_GET;
$data  = array();
$date=SysDate();
$date1=SysDatePadLeft();

$search ='';

if( !empty( $param['emp'] ) ){ $search=$search." AND empid like 	'%".$param['emp']."%' "; }

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


$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees AS de LEFT JOIN vw_leavesummary AS al ON de.id=al.idacct";
$Qry->selected  = "de.id,de.empid, de.empname, de.business_unit, de.post,de.unittype,de.idunit, de.concat_sup_fname_lname AS manager,
SUM(CASE WHEN idleave = 1 THEN entitle ELSE '' END ) AS sl_entitlement,
SUM(CASE WHEN idleave = 1 THEN used ELSE '' END ) AS sl_used,
SUM(CASE WHEN idleave = 1 THEN balance ELSE '' END ) AS sl_balance,
SUM(CASE WHEN idleave = 1 THEN carry_over ELSE '' END ) AS sl_carry_over,
SUM(CASE WHEN idleave = 1 THEN conversion ELSE '' END ) AS sl_conversion,
SUM(CASE WHEN idleave = 2 THEN entitle ELSE '' END ) AS vl_entitlement,
SUM(CASE WHEN idleave = 2 THEN used ELSE '' END ) AS vl_used,
SUM(CASE WHEN idleave = 2 THEN balance ELSE '' END ) AS vl_balance,
SUM(CASE WHEN idleave = 2 THEN carry_over ELSE '' END ) AS vl_carry_over,
SUM(CASE WHEN idleave = 2 THEN conversion ELSE '' END ) AS vl_conversion,
SUM(CASE WHEN idleave = 3 THEN entitle ELSE '' END ) AS lwop_entitlement,
SUM(CASE WHEN idleave = 3 THEN used ELSE '' END ) AS lwop_used,
SUM(CASE WHEN idleave = 3 THEN balance ELSE '' END ) AS lwop_balance,
SUM(CASE WHEN idleave = 3 THEN carry_over ELSE '' END ) AS lwop_carry_over,
SUM(CASE WHEN idleave = 3 THEN conversion ELSE '' END ) AS lwop_conversion,
SUM(CASE WHEN idleave = 4 THEN entitle ELSE '' END ) AS solo_entitlement,
SUM(CASE WHEN idleave = 4 THEN used ELSE '' END ) AS solo_used,
SUM(CASE WHEN idleave = 4 THEN balance ELSE '' END ) AS solo_balance,
SUM(CASE WHEN idleave = 4 THEN carry_over ELSE '' END ) AS solo_carry_over,
SUM(CASE WHEN idleave = 4 THEN conversion ELSE '' END ) AS solo_conversion,
SUM(CASE WHEN idleave = 5 THEN entitle ELSE '' END ) AS paternity_entitlement,
SUM(CASE WHEN idleave = 5 THEN used ELSE '' END ) AS paternity_used,
SUM(CASE WHEN idleave = 5 THEN balance ELSE '' END ) AS paternity_balance,
SUM(CASE WHEN idleave = 5 THEN carry_over ELSE '' END ) AS paternity_carry_over,
SUM(CASE WHEN idleave = 5 THEN conversion ELSE '' END ) AS paternity_conversion,
SUM(CASE WHEN idleave = 6 THEN entitle ELSE '' END ) AS comp_entitlement,
SUM(CASE WHEN idleave = 6 THEN used ELSE '' END ) AS comp_used,
SUM(CASE WHEN idleave = 6 THEN balance ELSE '' END ) AS comp_balance,
SUM(CASE WHEN idleave = 6 THEN carry_over ELSE '' END ) AS comp_carry_over,
SUM(CASE WHEN idleave = 6 THEN conversion ELSE '' END ) AS comp_conversion,
SUM(CASE WHEN idleave = 7 THEN entitle ELSE '' END ) AS spcleave_entitlement,
SUM(CASE WHEN idleave = 7 THEN used ELSE '' END ) AS spcleave_used,
SUM(CASE WHEN idleave = 7 THEN balance ELSE '' END ) AS spcleave_balance,
SUM(CASE WHEN idleave = 7 THEN carry_over ELSE '' END ) AS spcleave_carry_over,
SUM(CASE WHEN idleave = 7 THEN conversion ELSE '' END ) AS spcleave_conversion,
SUM(CASE WHEN idleave = 8 THEN entitle ELSE '' END ) AS bday_entitlement,
SUM(CASE WHEN idleave = 8 THEN used ELSE '' END ) AS bday_used,
SUM(CASE WHEN idleave = 8 THEN balance ELSE '' END ) AS bday_balance,
SUM(CASE WHEN idleave = 8 THEN carry_over ELSE '' END ) AS bday_carry_over,
SUM(CASE WHEN idleave = 8 THEN conversion ELSE '' END ) AS bday_conversion,
SUM(CASE WHEN idleave = 9 THEN entitle ELSE '' END ) AS emer_entitlement,
SUM(CASE WHEN idleave = 9 THEN used ELSE '' END ) AS emer_used,
SUM(CASE WHEN idleave = 9 THEN balance ELSE '' END ) AS emer_balance,
SUM(CASE WHEN idleave = 9 THEN carry_over ELSE '' END ) AS emer_carry_over,
SUM(CASE WHEN idleave = 9 THEN conversion ELSE '' END ) AS emer_conversion,
SUM(CASE WHEN idleave = 10 THEN entitle ELSE '' END ) AS magna_entitlement,
SUM(CASE WHEN idleave = 10 THEN used ELSE '' END ) AS magna_used,
SUM(CASE WHEN idleave = 10 THEN balance ELSE '' END ) AS magna_balance,
SUM(CASE WHEN idleave = 10 THEN carry_over ELSE '' END ) AS magna_carry_over,
SUM(CASE WHEN idleave = 10 THEN conversion ELSE '' END ) AS magna_conversion,
SUM(CASE WHEN idleave = 11 THEN entitle ELSE '' END ) AS bereav_entitlement,
SUM(CASE WHEN idleave = 11 THEN used ELSE '' END ) AS bereav_used,
SUM(CASE WHEN idleave = 11 THEN balance ELSE '' END ) AS bereav_balance,
SUM(CASE WHEN idleave = 11 THEN carry_over ELSE '' END ) AS bereav_carry_over,
SUM(CASE WHEN idleave = 11 THEN conversion ELSE '' END ) AS bereav_conversion,
SUM(CASE WHEN idleave = 12 THEN entitle ELSE '' END ) AS mater_entitlement,
SUM(CASE WHEN idleave = 12 THEN used ELSE '' END ) AS mater_used,
SUM(CASE WHEN idleave = 12 THEN balance ELSE '' END ) AS mater_balance,
SUM(CASE WHEN idleave = 12 THEN carry_over ELSE '' END ) AS mater_carry_over,
SUM(CASE WHEN idleave = 12 THEN conversion ELSE '' END ) AS mater_conversion";
$Qry->fields    = "de.id!=1 AND al.entitle is not null ".$search." GROUP BY de.empid order by de.lname asc";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        if($row['unittype'] !=3 && $row['unittype'] > 3){
            $dept = getDepartment($con, $row['idunit']);
        }else{
            $dept = $row['business_unit'];
        }
		$name23[] = array(
                        $row['empid'],
                        utf8_decode($row['empname']),
                        $dept,
                        $row['post'],
                        $row['sl_entitlement']/8,
                        $row['sl_used']/8,
                        $row['sl_balance']/8,
                        $row['sl_carry_over']/8,
                        $row['sl_conversion']/8,
                        $row['vl_entitlement']/8,
                        $row['vl_used']/8,
                        $row['vl_balance']/8,
                        $row['vl_carry_over']/8,
                        $row['vl_conversion']/8,
                        $row['lwop_entitlement']/8,
                        $row['lwop_used']/8,
                        $row['lwop_balance']/8,
                        $row['lwop_carry_over']/8,
                        $row['lwop_conversion']/8,
                        $row['solo_entitlement']/8,
                        $row['solo_used']/8,
                        $row['solo_balance']/8,
                        $row['solo_carry_over']/8,
                        $row['solo_conversion']/8,
                        $row['paternity_entitlement']/8,
                        $row['paternity_used']/8,
                        $row['paternity_balance']/8,
                        $row['paternity_carry_over']/8,
                        $row['paternity_conversion']/8,
                        $row['comp_entitlement']/8,
                        $row['comp_used']/8,
                        $row['comp_balance']/8,
                        $row['comp_carry_over']/8,
                        $row['comp_conversion']/8,
                        $row['spcleave_entitlement']/8,
                        $row['spcleave_used']/8,
                        $row['spcleave_balance']/8,
                        $row['spcleave_carry_over']/8,
                        $row['spcleave_conversion']/8,
                        $row['bday_entitlement']/8,
                        $row['bday_used']/8,
                        $row['bday_balance']/8,
                        $row['bday_carry_over']/8,
                        $row['bday_conversion']/8,
                        $row['emer_entitlement']/8,
                        $row['emer_used']/8,
                        $row['emer_balance']/8,
                        $row['emer_carry_over']/8,
                        $row['emer_conversion']/8,
                        $row['magna_entitlement']/8,
                        $row['magna_used']/8,
                        $row['magna_balance']/8,
                        $row['magna_carry_over']/8,
                        $row['magna_conversion']/8,
                        $row['bereav_entitlement']/8,
                        $row['bereav_used']/8,
                        $row['bereav_balance']/8,
                        $row['bereav_carry_over']/8,
                        $row['bereav_conversion']/8,
                        $row['mater_entitlement']/8,
                        $row['mater_used']/8,
                        $row['mater_balance']/8,
                        $row['mater_carry_over']/8,
                        $row['mater_conversion']/8,
                        $row['manager'],
            
		);
	
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=LeaveBalanceReport'.$date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array("New World Makati Hotel"));
// fputcsv($output, array($param['company']));
fputcsv($output, array("Leave Balance Report"));
if( !empty($param['filterby'])){
    fputcsv($output,array($param['filterby']));
}
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Employee ID',
						'Name',
                        'Department',
						'Position',
						'SL Entitlement',
                        'SL Used',
                        'SL Balance',
                        'SL Carried Over',
                        'SL Converted',
                        'VL Entitlement',
                        'VL Used',
                        'VL Balance',
                        'VL Carried Over',
                        'VL Converted',
                        'LWOP Entitlement',
                        'LWOP Used',
                        'LWOP Balance',
                        'LWOP Carried Over',
                        'LWOP Converted',
                        'Solo Parent Entitlement',
                        'Solo Parent Used',
                        'Solo Parent Balance',
                        'Solo Carried Over',
                        'Solo Converted',
                        'Paternity Entitlement',
                        'Paternity Used',
                        'Paternity Balance',
                        'Paternity Carried Over',
                        'Paternity Converted',
                        'Compensatory Entitlement',
                        'Compensatory Used',
                        'Compensatory Balance',
                        'Compensatory Carried Over',
                        'Compensatory Converted',
                        'Special Leave Entitlement',
                        'Special Leave Used',
                        'Special Leave Balance',
                        'Special Carried Over',
                        'Special Converted',
                        'Birthday Entitlement',
                        'Birthday Used',
                        'Birthday Balance',
                        'Birthday Carried Over',
                        'Birthday Converted',
                        'Emergency Entitlement',
                        'Emergency Used',
                        'Emergency Balance',
                        'Emergency Carried Over',
                        'Emergency Converted',
                        'Magna Carta Entitlement',
                        'Magna Carta Used',
                        'Magna Carta Balance',
                        'Magna Carried Over',
                        'Magna Converted',
                        'Bereavement Entitlement',
                        'Bereavement Used',
                        'Bereavement Balance',
                        'Bereavement Carried Over',
                        'Bereavement Converted',
                        'Maternity Entitlement',
                        'Maternity Used',
                        'Maternity Balance',
                        'Maternity Carried Over',
                        'Maternity Converted',
                        'Mananger')); 
 
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}
?>