<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_GET;
$data  = array();
$date = SysDatePadLeft();
$year = date("Y");
$month = 1;
$months = array(
	'January',
	'February',
	'March',
	'April',
	'May',
	'June',
	'July ',
	'August',
	'September',
	'October',
	'November',
	'December',
);

$search ='';

if( !empty( $param['search_acct'] ) ){ $search=$search." AND id 	= '".$param['search_acct']."' "; }


$dept = getIdUnit($con,$param['idsuperior']);


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


$Qry 			= new Query();	
$Qry->table="(SELECT suffix, id, lname, fname, mname, idemptype FROM tblaccount) as a Left join (SELECT idacct, idsuperior FROM tblaccountjob) as b ON (a.id = b.idacct)";
$Qry->selected="id, CONCAT(
    `a`.`lname`,
    IFNULL(CONCAT(' ', `a`.`suffix`), ''),
    ', ',
    `a`.`fname`,
    ' ',
    SUBSTR(`a`.`mname`, 1, 1),
    '. '
  ) AS `empname` ";
$Qry->fields="a.idemptype = 1 AND (b.idsuperior='".$param['idsuperior']."')".$search." ORDER BY a.lname";
$rs 			= $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){   

        //Late
        $jann = getTimesheet($con, $row['id'],date('Y-01-01'),getEndDate($year,'01'));
        $febb = getTimesheet($con, $row['id'],date('Y-02-01'),getEndDate($year,'02'));
        $marr = getTimesheet($con, $row['id'],date('Y-03-01'),getEndDate($year,'03'));
        $aprr = getTimesheet($con, $row['id'],date('Y-04-01'),getEndDate($year,'04'));
        $mayy = getTimesheet($con, $row['id'],date('Y-05-01'),getEndDate($year,'05'));
        $junn = getTimesheet($con, $row['id'],date('Y-06-01'),getEndDate($year,'06'));
        $jull = getTimesheet($con, $row['id'],date('Y-07-01'),getEndDate($year,'07'));
        $augg = getTimesheet($con, $row['id'],date('Y-08-01'),getEndDate($year,'08'));
        $sepp = getTimesheet($con, $row['id'],date('Y-09-01'),getEndDate($year,'09'));
        $octt = getTimesheet($con, $row['id'],date('Y-10-01'),getEndDate($year,'10'));
        $novv = getTimesheet($con, $row['id'],date('Y-11-01'),getEndDate($year,'11'));
        $decc = getTimesheet($con, $row['id'],date('Y-12-01'),getEndDate($year,'12'));
        $total_latee = $jann + $febb + $marr + $aprr + $mayy + $junn + $jull + $augg + $sepp + $octt + $novv + $decc;
        //Undertime
        $jann_ut = getTimesheetCountUT($con, $row['id'],date('Y-01-01'),getEndDate($year,'01'));
        $febb_ut = getTimesheetCountUT($con, $row['id'],date('Y-02-01'),getEndDate($year,'02'));
        $marr_ut = getTimesheetCountUT($con, $row['id'],date('Y-03-01'),getEndDate($year,'03'));
        $aprr_ut = getTimesheetCountUT($con, $row['id'],date('Y-04-01'),getEndDate($year,'04'));
        $mayy_ut = getTimesheetCountUT($con, $row['id'],date('Y-05-01'),getEndDate($year,'05'));
        $junn_ut = getTimesheetCountUT($con, $row['id'],date('Y-06-01'),getEndDate($year,'06'));
        $jull_ut = getTimesheetCountUT($con, $row['id'],date('Y-07-01'),getEndDate($year,'07'));
        $augg_ut = getTimesheetCountUT($con, $row['id'],date('Y-08-01'),getEndDate($year,'08'));
        $sepp_ut = getTimesheetCountUT($con, $row['id'],date('Y-09-01'),getEndDate($year,'09'));
        $octt_ut = getTimesheetCountUT($con, $row['id'],date('Y-10-01'),getEndDate($year,'10'));
        $novv_ut = getTimesheetCountUT($con, $row['id'],date('Y-11-01'),getEndDate($year,'11'));
        $decc_ut = getTimesheetCountUT($con, $row['id'],date('Y-12-01'),getEndDate($year,'12'));
        $total_utt = $jann_ut + $febb_ut + $marr_ut + $aprr_ut + $mayy_ut + $junn_ut + $jull_ut + $augg_ut + $sepp_ut + $octt_ut + $novv_ut + $decc_ut;
        //Absent
        $jann_absent = getTimesheetCountAbsent($con, $row['id'],date('Y-01-01'),getEndDate($year,'01'));
        $febb_absent = getTimesheetCountAbsent($con, $row['id'],date('Y-02-01'),getEndDate($year,'02'));
        $marr_absent = getTimesheetCountAbsent($con, $row['id'],date('Y-03-01'),getEndDate($year,'03'));
        $aprr_absent = getTimesheetCountAbsent($con, $row['id'],date('Y-04-01'),getEndDate($year,'04'));
        $mayy_absent = getTimesheetCountAbsent($con, $row['id'],date('Y-05-01'),getEndDate($year,'05'));
        $junn_absent = getTimesheetCountAbsent($con, $row['id'],date('Y-06-01'),getEndDate($year,'06'));
        $jull_absent = getTimesheetCountAbsent($con, $row['id'],date('Y-07-01'),getEndDate($year,'07'));
        $augg_absent = getTimesheetCountAbsent($con, $row['id'],date('Y-08-01'),getEndDate($year,'08'));
        $sepp_absent = getTimesheetCountAbsent($con, $row['id'],date('Y-09-01'),getEndDate($year,'09'));
        $octt_absent = getTimesheetCountAbsent($con, $row['id'],date('Y-10-01'),getEndDate($year,'10'));
        $novv_absent = getTimesheetCountAbsent($con, $row['id'],date('Y-11-01'),getEndDate($year,'11'));
        $decc_absent = getTimesheetCountAbsent($con, $row['id'],date('Y-12-01'),getEndDate($year,'12'));
        $total_absentt = $jann_absent + $febb_absent + $marr_absent + $aprr_absent + $mayy_absent + $junn_absent + $jull_absent + $augg_absent + $sepp_absent + $octt_absent + $novv_absent + $decc_absent;
        //Overtime - getTimesheetCountOvertime
        $jann_overtime = getTimesheetCountOvertime($con, $row['id'],date('Y-01-01'),getEndDate($year,'01'));
        $febb_overtime= getTimesheetCountOvertime($con, $row['id'],date('Y-02-01'),getEndDate($year,'02'));
        $marr_overtime= getTimesheetCountOvertime($con, $row['id'],date('Y-03-01'),getEndDate($year,'03'));
        $aprr_overtime= getTimesheetCountOvertime($con, $row['id'],date('Y-04-01'),getEndDate($year,'04'));
        $mayy_overtime= getTimesheetCountOvertime($con, $row['id'],date('Y-05-01'),getEndDate($year,'05'));
        $junn_overtime= getTimesheetCountOvertime($con, $row['id'],date('Y-06-01'),getEndDate($year,'06'));
        $jull_overtime= getTimesheetCountOvertime($con, $row['id'],date('Y-07-01'),getEndDate($year,'07'));
        $augg_overtime= getTimesheetCountOvertime($con, $row['id'],date('Y-08-01'),getEndDate($year,'08'));
        $sepp_overtime= getTimesheetCountOvertime($con, $row['id'],date('Y-09-01'),getEndDate($year,'09'));
        $octt_overtime= getTimesheetCountOvertime($con, $row['id'],date('Y-10-01'),getEndDate($year,'10'));
        $novv_overtime= getTimesheetCountOvertime($con, $row['id'],date('Y-11-01'),getEndDate($year,'11'));
        $decc_overtime= getTimesheetCountOvertime($con, $row['id'],date('Y-12-01'),getEndDate($year,'12'));
        $total_overtimet = $jann_overtime+ $febb_overtime+ $marr_overtime+ $aprr_overtime+ $mayy_overtime+ $junn_overtime+ $jull_overtime+ $augg_overtime+ $sepp_overtime+ $octt_overtime+ $novv_overtime+ $decc_overtime;


        
            $name23[] = array(
                            utf8_decode($row['empname']),
                            $jann,
                            $febb,
                            $marr,
                            $aprr,
                            $mayy,
                            $junn,
                            $jull,
                            $augg,
                            $sepp,
                            $octt,
                            $novv,
                            $decc,
                           $total_latee,
                            $jann_ut,
                            $febb_ut,
                            $marr_ut,
                            $aprr_ut,
                            $mayy_ut,
                            $junn_ut,
                            $jull_ut,
                            $augg_ut,
                            $sepp_ut,
                            $octt_ut,
                            $novv_ut,
                            $decc_ut,
                            $total_utt,
                            $jann_absent,
                            $febb_absent,
                            $marr_absent,
                            $aprr_absent,
                            $mayy_absent,
                            $junn_absent,
                            $jull_absent,
                            $augg_absent,
                            $sepp_absent,
                            $octt_absent,
                            $novv_absent,
                            $decc_absent,
                            $total_absentt,
                            $jann_overtime,
                            $febb_overtime,
                            $marr_overtime,
                            $aprr_overtime,
                            $mayy_overtime,
                            $junn_overtime,
                            $jull_overtime,
                            $augg_overtime,
                            $sepp_overtime,
                            $octt_overtime,
                            $novv_overtime,
                            $decc_overtime,
                            $total_overtimet,
            );
 
    }
}


// print_r($name23);
// return;


header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=YTDReport'.$date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array($param['company']));
fputcsv($output, array("Attendance Today"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Employee Name',
                        'Jan Late',
                        'Feb Late',
                        'Mar Late',
                        'Apr Late',
                        'May Late',
                        'Jun Late',
                        'Jul Late',
                        'Aug Late',
                        'Sep Late',
                        'Oct Late',
                        'Nov Late',
                        'Dec Late',
                        'Total Late',
                        'Jan UT',
                        'Feb UT',
                        'Mar UT',
                        'Apr UT',
                        'May UT',
                        'Jun UT',
                        'Jul UT',
                        'Aug UT',
                        'Sep UT',
                        'Oct UT',
                        'Nov UT',
                        'Dec UT',
                        'Total UT',
                        'Jan Absent',
                        'Feb Absent',
                        'Mar Absent',
                        'Apr Absent',
                        'May Absent',
                        'Jun Absent',
                        'Jul Absent',
                        'Aug Absent',
                        'Sep Absent',
                        'Oct Absent',
                        'Nov Absent',
                        'Dec Absent',
                        'Total Absent',
                        'Jan Overtime',
                        'Feb Overtime',
                        'Mar Overtime',
                        'Apr Overtime',
                        'May Overtime',
                        'Jun Overtime',
                        'Jul Overtime',
                        'Aug Overtime',
                        'Sep Overtime',
                        'Oct Overtime',
                        'Nov Overtime',
                        'Dec Overtime',
                        'Total Overtime',
                    
                    )); 
 
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}


function getIdUnit($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getIdUnit');
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['idunit'];
    }
    return null;
}


function getTimesheet($con, $idacct, $dfrom, $dto){
    $Qry=new Query();
    $Qry->table = "vw_mng_timesheetfinal";
    $Qry->selected="COUNT(late) as ctr";
    $Qry->fields="tid IN (".$idacct.") AND late <> '0.00' AND late <> '0' AND (work_date BETWEEN '".$dfrom."' AND '".$dto."')";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTimesheet');
    if( mysqli_num_rows($rs) >= 1 ){
        return mysqli_fetch_assoc($rs)['ctr'];
	}
    return  0;
}

function getTimesheetCountUT($con, $idacct, $dfrom, $dto){
    $Qry=new Query();
    $Qry->table = "vw_mng_timesheetfinal";
    $Qry->selected="COUNT(ut) as ctr";
    $Qry->fields="tid IN (".$idacct.") AND ut <> '0.00' AND ut <> '0' AND (work_date BETWEEN '".$dfrom."' AND '".$dto."')";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTimesheetCountUT');
    if( mysqli_num_rows($rs) >= 1 ){
        return mysqli_fetch_assoc($rs)['ctr'];
	}
    return  0;
}

function getTimesheetCountAbsent($con, $idacct, $dfrom, $dto){
    $Qry=new Query();
    $Qry->table = "vw_mng_timesheetfinal";
    $Qry->selected="COUNT(absent) as ctr";
    $Qry->fields="tid IN (".$idacct.") AND absent IS NOT NULL AND absent <> '0' AND (work_date BETWEEN '".$dfrom."' AND '".$dto."')";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTimesheetCountAbsent');
    if( mysqli_num_rows($rs) >= 1 ){
        return mysqli_fetch_assoc($rs)['ctr'];
	}
    return  0;
}

function getTimesheetCountOvertime($con, $idacct, $dfrom, $dto){
    $Qry=new Query(); 
    $Qry->table = "vw_mng_timesheetfinal";
    $Qry->selected="COUNT(othrs) as ctr";
    $Qry->fields="tid IN (".$idacct.") AND othrs IS NOT NULL AND (work_date BETWEEN '".$dfrom."' AND '".$dto."')";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTimesheetCountOvertime');
    if( mysqli_num_rows($rs) >= 1 ){
        return mysqli_fetch_assoc($rs)['ctr'];
	}
    return  0;
}

function getEndDate($yr, $mnth) {
    $date = $yr.'-'.$mnth;
    $d = date_create_from_format('Y-m',$date);
    $last_day = date_format($d, 't');
    return $date.'-'.$last_day;
}

?>