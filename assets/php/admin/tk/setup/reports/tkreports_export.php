<?php 
require_once('../../../../activation.php');
require_once('../../../../classPhp.php');
$conn = new connector();
$con = $conn->connect();


$param = $_GET;
$pay_period = getPayPeriod($con);
$date=SysDate();
$search='';

// if( !empty( $param['tkselect'] ) ){ 
//     if($param['tkselect']!="INACTIVE") {
//         $param['tkselect']="ACTIVE";
//     }
//     $search=$search." AND EmploymentType = '".$param['tkselect']."' "; 
// }
if( !empty( $param['search_acct'] ) ){ $search=$search." AND de.id = '".$param['search_acct']."' "; }
if( !empty($param['tkdatefrom']) && empty($param['tkdateto'])){
    $search=$search." AND hdate BETWEEN DATE('".$param['tkdatefrom']."') AND DATE('".$param['tkdatefrom']."') ";
}
if( !empty($param['tkdatefrom']) && !empty($param['tkdateto']) ){
    $search=$search." AND hdate BETWEEN DATE('".$param['tkdatefrom']."') AND DATE('".$param['tkdateto']."') ";   
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
    $search.=" AND de.idunit in (".$ids.") "; 
}
// if( !empty( $param['allemployee'] ) ){ $search=$search." AND empname = '".$param['allemployee']."' "; }
// if( !empty( $param['alldepartment'] ) ){ $search=$search." AND dept_name = '".$param['alldepartment']."' "; }
// if( !empty( $param['tkgender'] ) ){ $search=$search." AND Gender = '".$param['tkgender']."' "; }
// if( !empty( $param['position'] ) ){ $search=$search." AND position_title = '".$param['position']."' "; }
// if( !empty( $param['joblocation'] ) ){ $search=$search." AND joblocation = '".$param['joblocation']."' "; }
// if( !empty( $param['paygroup'] ) ){ $search=$search." AND paygroup = '".$param['paygroup']."' "; }
// if( !empty( $param['labortype'] ) ){ $search=$search." AND LaborType = '".$param['labortype']."' "; }
// if( !empty( $param['tkdatefrom'] ) && !empty( $param['tkdateto'] ) ){ $search=$search." AND (work_date >= '".$param['tkdatefrom']."' AND work_date <= '".$param['tkdateto']."' ) "; }




$name23 = array();
$Qry = new Query();	
$Qry->table     = "vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID=de.id";
$Qry->selected  = "de.idunit,de.id,de.empid, de.empname, de. business_unit, de.post, de.hdate, de.pay_grp,
SUM(dt.acthrs) AS acthrs, SUM(dt.late) AS late, SUM(dt.ut) AS ut, SUM(dt.absent) AS absent,
SUM(CASE WHEN dt.idleave = 6 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totcompen,
SUM(CASE WHEN dt.idleave = 1 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totsl,
SUM(CASE WHEN dt.idleave = 2 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totvl,
SUM(CASE WHEN dt.idleave = 9 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totemer,
SUM(CASE WHEN dt.idleave = 10 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totmagnacar,
SUM(CASE WHEN dt.idleave = 5 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totpater,
SUM(CASE WHEN dt.idleave = 4 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totsolo,
SUM(CASE WHEN dt.idleave = 11 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totbereav,
SUM(CASE WHEN dt.idleave = 12 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totmater,
SUM(dt.reg_ot) AS reg_ot, SUM(dt.rd_ot) AS rd_ot, SUM(dt.spcl_hol) AS spcl_hol, SUM(dt.spcl_rd) AS spcl_rd, SUM(dt.legal_hol) AS legal_hol, SUM(dt.legal_rd) AS legal_rd,
SUM(dt.reg_np) AS reg_np, SUM(dt.rd_np) AS rd_np, SUM(dt.spcl_rd_np) AS spcl_rd_np, SUM(dt.spcl_np) AS spcl_np, SUM(dt.legal_np) AS legal_np,
SUM(dt.rd_ot8) rd_ot8, SUM(dt.spcl_rd8) AS spcl_rd8, SUM(dt.legal_rd8) AS legal_rd8, SUM(dt.spcl_hol8) AS spcl_hol8, SUM(dt.legal_hol8) AS legal_hol8";
$Qry->fields    = "dt.work_date IS NOT NULL ".$search." GROUP BY dt.empID" ;
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
	while($row=mysqli_fetch_array($rs)){
		$name23[] = array($row['empid'],
					utf8_decode($row['empname']),
					$row['business_unit'],
					$row['post'],
					$row['hdate'],
					$row['pay_grp'],
					$row['acthrs'],
					$row['late'],
					$row['ut'],
					$row['absent'],
					$row['reg_ot']? $row['reg_ot']:0,
					$row['rd_ot']? $row['rd_ot']:0,
					$row['spcl_hol']? $row['spcl_hol']:0,	
					$row['spcl_rd']? $row['spcl_rd']:0,		
					$row['legal_hol']? $row['legal_hol']:0,	
					$row['legal_rd']? $row['legal_rd']:0,	
					$row['reg_np']? $row['reg_np']:0,		
					$row['rd_np']? $row['rd_np']:0,			
					$row['spcl_rd_np']? $row['spcl_rd_np']:0,
					$row['spcl_np']? $row['spcl_np']:0,		
					$row['legal_np']? $row['legal_np']:0,	
					$row['rd_ot8']? $row['rd_ot8']:0,		
					$row['spcl_rd8']? $row['spcl_rd8']:0,	
					$row['legal_rd8']? $row['legal_rd8']:0,	
					$row['spcl_hol8']? $row['spcl_hol8']:0,	
					$row['legal_hol8']? $row['legal_hol8']:0,
					$row['totcompen']? $row['totcompen']:0,	
					$row['totsl']? $row['totsl']:0,			
					$row['totvl']? $row['totvl']:0,			
					$row['totemer']? $row['totemer']:0,		
					$row['totmagnacar']? $row['totmagnacar']:0,
					$row['totpater']? $row['totpater']:0,	
					$row['totsolo']? $row['totsolo']:0,		
					$row['totbereav']? $row['totbereav']:0,	
					$row['totmater']? $row['totmater']:0,	

				);

	}
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=TimesheetSummary'.$date.'.csv');
$output = fopen('php://output', 'w');
// fputcsv($output, array($param['company']));
fputcsv($output, array('New World Makati Hotel'));
fputcsv($output, array("Timesheet Summary Detials"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('EmployeeID',
						'Name',
						'Department',
						'Position',
						'Hired Date',
						'PayGroup',
						'Actual Work Hours',
						'Late',
						'UT',
						'Absent',
						'Regular Overtime',
						'RestDay Overtime',
						'Special Holiday Overtime',
						'RestDay Overtime SpecialHoliday',
						'Legal Holiday',
						'RestDay Overtime LegalHoliday',
						'Regular ND',
						'RestDay ND',
						'Special RestDay ND',
						'SpecialHoliday ND',
						'LegalHoliday ND',
						'RestDay Duty8',
						'RestDay Duty + SpecialHoliday8',
						'RestDay Duty + LegalHoliday8',
						'SpecialHoliday 8',
						'LegalHoliday 8',
						'Compensatory Leave',
						'Vacation Leave',
						'Sick Leave',
						'Emergency Leave',
						'Magna Carta Leave',
						'Paternity Leave',
						'Solo Parent Leave',
						'Bereavement Leave',
						'Maternity Leave',
					)); 
	
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}
		

?>