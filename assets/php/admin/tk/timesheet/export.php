<?php
require_once('../../../logger.php');
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 


$param = $_GET;
$data  = array();
$today_date = SysDatePadLeft();
$pay_period = getPayPeriod($con);

$search ='';
if( !empty( $param['search_acct'] ) ){ $search=$search." AND de.id 	= '".$param['search_acct']."' "; }
if( !empty( $param['search_post'] ) ){ $search=$search." AND de.idpos 	= '".$param['search_post']."' "; }
if( !empty( $param['idlvl'] ) ){ 
    $search=$search." AND de.idlvl 	= '".$param['idlvl']."' " ; }
if( !empty($param['search_dfrom']) && !empty($param['search_dto'])){
    $search=$search." AND dt.work_date BETWEEN DATE('".$param['search_dfrom']."') AND DATE('".$param['search_dto']."') ";
}


$ids='0';
//Get Managers Under person
if( !empty( $dept ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$dept);
    if( !empty( $arr["nodechild"] ) ){     
        $ids = join(',', flatten($arr['nodechild']));
    } else {
        $ids = '0';
    }
}

$name23=array();
$Qry = new Query();	
$Qry->table     = "(SELECT
 work_date, fshfname, timein, timeout, shifthrs,
 acthrs, excess, late, ut, absent,
 tid, leavetype, leaveidtype, leaveappstatus, holiday_idtype, stime, ftime, idshift
FROM vw_mng_timesheetfinal) dt LEFT JOIN
 (SELECT empname, post, id, idsuperior, idunit, etypeid FROM vw_dataemployees) de ON dt.tid = de.id";
$Qry->selected  = "
empname, post, work_date, fshfname AS shift_status, timein AS `in`,
timeout AS `out`, shifthrs AS reghrs, acthrs, excess, late,
ut, absent, tid AS empID, leavetype, leaveidtype AS idleave,
leaveappstatus AS leavestat, holiday_idtype, stime AS `shiftin`, ftime AS `shiftout`, idshift";
 //$Qry->fields    = "etypeid=1 AND dt.work_date BETWEEN '".$date."' AND '".$date1."' ".$search." ORDER BY de.empname";
//$Qry->fields    = "(de.idsuperior='".$param['idsuperior']."' or de.idunit IN (".$ids.")) AND dt.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' ".$search." ORDER BY de.empname";
if(!empty($param['tk'])){
    if(!empty($param['search_dfrom']) && !empty($param['search_dto'])){
        $Qry->fields    = "etypeid=1 AND dt.work_date BETWEEN '".$param['search_dfrom']."' AND '".$param['search_dto']."' ".$search." ORDER BY de.empname,dt.work_date";
    }else{
        $Qry->fields    = "etypeid=1 AND dt.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' ".$search." ORDER BY de.empname,dt.work_date";
    }
}else{
    if(!empty($param['search_dfrom']) && !empty($param['search_dto'])){
        $Qry->fields    = "(de.idsuperior='".$param['idsuperior']."' or de.idunit IN (".$ids.")) AND dt.work_date BETWEEN '".$param['search_dfrom']."' AND '".$param['search_dto']."' ".$search." ORDER BY de.empname,dt.work_date";
    }else{
        $Qry->fields    = "(de.idsuperior='".$param['idsuperior']."' or de.idunit IN (".$ids.")) AND dt.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' ".$search." ORDER BY de.empname,dt.work_date";
    }
}

$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){

        $break= 0;

        $Qry3           = new Query();
        $Qry3->table    = "tblshift";
        $Qry3->selected = "*";
        $Qry3->fields   = "id = '" . $row['idshift'] . "' ";
        $rs3            = $Qry3->exe_SELECT($con);
        Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
        if (mysqli_num_rows($rs3) >= 1) {
            if ($row3 = mysqli_fetch_assoc($rs3)) {
                $break = (strtotime($row3['breakout']) - strtotime($row3['breakin'])) / 3600;
            }
        }
        $wh = ((strtotime($row['shiftout']) - strtotime($row['shiftin'])) / 3600) - $break;
        
        
        if (!empty($row['holiday_id'])) {
            $row['shift_status'] = ucwords(strtolower($row['holiday_type'])) . ' Holiday';
        }

        if ($row['holiday_idtype']==1) {
            $row['shift_status'] =  'LH';
        }

        if ($row['holiday_idtype']==2) {
            $row['shift_status'] =  'SH';
        }
        if ($row['holiday_idtype']==3) {
            $row['shift_status'] = 'LSH';
        }

        //STATUS
        if ($row['shift_status'] == 'Rest Day') {
        $row['shift_status'] = 'RD';
        $status              = 'N';
        
        }elseif(((empty($row['in']) || $row['in']==null) || (empty($row['out']) || $row['out']==null)) && (empty($row['holiday_idtype']) || $row['holiday_idtype']==null)  && ($row['leavetype'] =='Unpaid' || empty($row['idleave']) || (!empty($row['idleave']) && $row['leavestat'] != 1) ) ){
            $status              = 'A';
        
        }elseif($row['holiday_idtype'] != null){
            $status              = 'N';

        }elseif($row['leavetype'] =='Paid' && $row['leavestat']==1){
            $status              = 'P';
        }else {
            $status = 'W';
        }

         //TOTAL WORK HOURS
        //  if ($row['in'] && $row['out']) {
        //     $twh =  ((strtotime($row['out']) - strtotime($row['in'])) / 3600)  - $break;
        // } else {
        //     $twh = 0;
        // }

        // //EXCESS WORK HOURS
        // if ($wh < $twh) {
        // $others = ((strtotime($row['out']) - strtotime($row['shiftin'])) / 3600) - $wh - $break;
        // if ($others < 0) {
        //         $others = 0;
        //     }
        // } else {
        //     $others = 0;
        // }


        $getChangeShift = getChangeShift($con, $row['work_date'], $row['empID']);
        $getChangeShift_name =  $getChangeShift['name'];


        $getAttendanceAdjust = getAttendanceAdjust($con, $row['work_date'], $row['empID']);
        $getAttendanceAdjust_name ='';
        if(empty($getAttendanceAdjust['stime']) && empty($getAttendanceAdjust['ftime'])){
            $getAttendanceAdjust_name='';
        }else{
            $getAttendanceAdjust_name = $getAttendanceAdjust['stime'].' - '.$getAttendanceAdjust['ftime'];
        }

        $getOvertime = getOvertime($con, $row['work_date'], $row['empID']);
        $getOvertime_planhrs = $getOvertime['planhrs'];

        $getTimesheetLeaves = getTimesheetLeaves($con, $row['work_date'], $row['empID']);
        $getTimesheetLeaves_name = '';
        if(empty($getTimesheetLeaves['name']) && empty($getTimesheetLeaves['hrs'])){
            $getTimesheetLeaves_name = '';
        }else{
            $getTimesheetLeaves_name = $getTimesheetLeaves['name'].' - '.$getTimesheetLeaves['hrs'].' hrs';
        }
  
		//mga column sa database
		$name23[] = array(
                        $status,
                        utf8_decode($row['empname']),
                        $row['post'],
						$row['work_date'],
						$row['shift_status'],
                        $row['in']? date('h:i a', strtotime($row['in'])) :'',
                        $row['out']? date('h:i a', strtotime($row['out'])) :'',
                        sprintf('%0.2f', $row['reghrs']),
                        sprintf('%0.2f', $row['acthrs']),
                        sprintf('%0.2f', $row['excess']),
                        // sprintf('%0.2f', $twh),
                        // sprintf('%0.2f', $others),
                        sprintf('%0.2f', $row['late']),
                        sprintf('%0.2f', $row['ut']),
                        sprintf('%0.2f', $row['absent']),
                        $getChangeShift_name,
                        $getAttendanceAdjust_name,
                        $getOvertime_planhrs,
                        $getTimesheetLeaves_name,
						
		);
	
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=TK_Timesheet_Detail_'.$today_date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array($param['company']));
fputcsv($output, array("Timekeeping Timesheet Details"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Status',
                        'Employee Name',
						'Position',
						'Date',
						'Shift Code',
						'In',
                        'Out',
                        'Shift Hours',
                        'Actual Hours',
                        'Excess Work Hours',
                        'Late',
                        'Undertime',
                        'Absent',
                        'Change Shift',
                        'Attendance Adjustment',
                        'Overtime',
                        'Leaves')); 
 
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}

function getChangeShift($con, $work_date, $idacct){
    $data= array(
        // "status" 	=> '',
        "name" 	    => '',
    );
    $Qry=new Query();
    $Qry->table="(SELECT idshift, `stat`, `date`, idacct, cancelby FROM tbltimeshift) `tbltimeshift` LEFT JOIN (SELECT `name`, id FROM tblshift) `tblshift` ON (tbltimeshift.idshift = tblshift.id)";
    $Qry->selected="tbltimeshift.stat, tblshift.name";
    $Qry->fields="tbltimeshift.date='".$work_date."' AND tbltimeshift.idacct='".$idacct."' AND tbltimeshift.cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getChangeShift');
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){      

             $data = array(
                              
                "status"    => $row['stat'],
                "name" 	    => $row['name'],
            );
        }
    }
    return $data;
}

function getAttendanceAdjust($con, $work_date, $idacct){
    $data= array(
        "stime" => '',
        "ftime" => '',
    );
    $Qry=new Query();
    $Qry->table="tbltimeadjustment";
    $Qry->selected="`stat`, stime, ftime";
    $Qry->fields="date='".$work_date."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getAttendanceAdjust');
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){      

            $data[] = array(
                "status"        => $row['stat'],
                "stime" 	    => date("h:i A", strtotime($row['stime'])),
                "ftime" 	    => date("h:i A", strtotime($row['ftime'])),
                // "stime" 	    => gmdate("H:i:s", $stime),
                // "ftime" 	    => gmdate("H:i:s", $ftime),
            );
        }
    }
    return $data;
}

function getOvertime($con, $work_date, $idacct){
    $data= array(
        "planhrs" => '',
    );
    $Qry=new Query();
    $Qry->table="tbltimeovertime";
    $Qry->selected="`stat`, planhrs";
    $Qry->fields="date='".$work_date."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getOvertime');
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){      
 
            $data = array(
                "status" 	    => $row['stat'],
                "planhrs" 	    => $row['planhrs']
            
            );
        }
    }
    return $data;
}

function getTimesheetLeaves($con, $work_date, $idacct){
    $data[] = array(
        "name" => '',
        "hrs"  => '',
    );
    $Qry=new Query();
    $Qry->table="(SELECT idleave, `date`, idacct, cancelby, `stat`, hrs FROM tbltimeleaves) `tbltimeleaves` LEFT JOIN (SELECT id, `name` FROM tblleaves) `tblleaves` ON (tbltimeleaves.idleave = tblleaves.id)";
    $Qry->selected="`stat`, `name`, hrs";
    $Qry->fields="tbltimeleaves.date='".$work_date."' AND tbltimeleaves.idacct='".$idacct."' AND tbltimeleaves.cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTimesheetLeaves');
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){      
 
            $data[] = array(
                "status"    => $row['stat'],
                "name" 	    => $row['name'],
                "hrs" 	    => $row['hrs'],
            
            );
        }
    }
    return $data;
}



?>