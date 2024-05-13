<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param       = json_decode(file_get_contents('php://input'));
$data  = array();
$date = SysDatePadLeft();
$pay_period = getPayPeriod($con);
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

$dept = getIdUnit($con,$param->accountid);


//Get Managers Under person
$ids=0;if( !empty( $dept ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$dept);
    array_push( $arr_id, $dept );
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
}


$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "*";
$Qry->fields    = "idunit IN (".$ids.") ORDER BY empname";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){        
        //Late
        $jann = getTimesheet($con, $row['id'],$year."-".str_pad(1,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(2,2,"0",STR_PAD_LEFT)."-01");
        $febb = getTimesheet($con, $row['id'],$year."-".str_pad(2,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(3,2,"0",STR_PAD_LEFT)."-01");
        $marr = getTimesheet($con, $row['id'],$year."-".str_pad(3,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(4,2,"0",STR_PAD_LEFT)."-01");
        $aprr = getTimesheet($con, $row['id'],$year."-".str_pad(4,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(5,2,"0",STR_PAD_LEFT)."-01");
        $mayy = getTimesheet($con, $row['id'],$year."-".str_pad(5,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(6,2,"0",STR_PAD_LEFT)."-01");
        $junn = getTimesheet($con, $row['id'],$year."-".str_pad(6,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(7,2,"0",STR_PAD_LEFT)."-01");
        $jull = getTimesheet($con, $row['id'],$year."-".str_pad(7,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(8,2,"0",STR_PAD_LEFT)."-01");
        $augg = getTimesheet($con, $row['id'],$year."-".str_pad(8,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(9,2,"0",STR_PAD_LEFT)."-01");
        $sepp = getTimesheet($con, $row['id'],$year."-".str_pad(9,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(10,2,"0",STR_PAD_LEFT)."-01");
        $octt = getTimesheet($con, $row['id'],$year."-".str_pad(10,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(11,2,"0",STR_PAD_LEFT)."-01");
        $novv = getTimesheet($con, $row['id'],$year."-".str_pad(11,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(12,2,"0",STR_PAD_LEFT)."-01");
        $decc = getTimesheet($con, $row['id'],$year."-12-01",((int)$year+1)."-01-01");
        $total_latee = $jann + $febb + $marr + $aprr + $mayy + $junn + $jull + $augg + $sepp + $octt + $novv + $decc;
        //Undertime
        $jann_ut = getTimesheetCountUT($con, $row['id'],$year."-".str_pad(1,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(2,2,"0",STR_PAD_LEFT)."-01");
        $febb_ut = getTimesheetCountUT($con, $row['id'],$year."-".str_pad(2,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(3,2,"0",STR_PAD_LEFT)."-01");
        $marr_ut = getTimesheetCountUT($con, $row['id'],$year."-".str_pad(3,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(4,2,"0",STR_PAD_LEFT)."-01");
        $aprr_ut = getTimesheetCountUT($con, $row['id'],$year."-".str_pad(4,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(5,2,"0",STR_PAD_LEFT)."-01");
        $mayy_ut = getTimesheetCountUT($con, $row['id'],$year."-".str_pad(5,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(6,2,"0",STR_PAD_LEFT)."-01");
        $junn_ut = getTimesheetCountUT($con, $row['id'],$year."-".str_pad(6,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(7,2,"0",STR_PAD_LEFT)."-01");
        $jull_ut = getTimesheetCountUT($con, $row['id'],$year."-".str_pad(7,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(8,2,"0",STR_PAD_LEFT)."-01");
        $augg_ut = getTimesheetCountUT($con, $row['id'],$year."-".str_pad(8,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(9,2,"0",STR_PAD_LEFT)."-01");
        $sepp_ut = getTimesheetCountUT($con, $row['id'],$year."-".str_pad(9,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(10,2,"0",STR_PAD_LEFT)."-01");
        $octt_ut = getTimesheetCountUT($con, $row['id'],$year."-".str_pad(10,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(11,2,"0",STR_PAD_LEFT)."-01");
        $novv_ut = getTimesheetCountUT($con, $row['id'],$year."-".str_pad(11,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(12,2,"0",STR_PAD_LEFT)."-01");
        $decc_ut = getTimesheetCountUT($con, $row['id'],$year."-12-01",((int)$year+1)."-01-01");
        $total_utt = $jann_ut + $febb_ut + $marr_ut + $aprr_ut + $mayy_ut + $junn_ut + $jull_ut + $augg_ut + $sepp_ut + $octt_ut + $novv_ut + $decc_ut;
        //Absent
        $jann_absent = getTimesheetCountAbsent($con, $row['id'],$year."-".str_pad(1,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(2,2,"0",STR_PAD_LEFT)."-01");
        $febb_absent = getTimesheetCountAbsent($con, $row['id'],$year."-".str_pad(2,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(3,2,"0",STR_PAD_LEFT)."-01");
        $marr_absent = getTimesheetCountAbsent($con, $row['id'],$year."-".str_pad(3,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(4,2,"0",STR_PAD_LEFT)."-01");
        $aprr_absent = getTimesheetCountAbsent($con, $row['id'],$year."-".str_pad(4,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(5,2,"0",STR_PAD_LEFT)."-01");
        $mayy_absent = getTimesheetCountAbsent($con, $row['id'],$year."-".str_pad(5,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(6,2,"0",STR_PAD_LEFT)."-01");
        $junn_absent = getTimesheetCountAbsent($con, $row['id'],$year."-".str_pad(6,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(7,2,"0",STR_PAD_LEFT)."-01");
        $jull_absent = getTimesheetCountAbsent($con, $row['id'],$year."-".str_pad(7,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(8,2,"0",STR_PAD_LEFT)."-01");
        $augg_absent = getTimesheetCountAbsent($con, $row['id'],$year."-".str_pad(8,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(9,2,"0",STR_PAD_LEFT)."-01");
        $sepp_absent = getTimesheetCountAbsent($con, $row['id'],$year."-".str_pad(9,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(10,2,"0",STR_PAD_LEFT)."-01");
        $octt_absent = getTimesheetCountAbsent($con, $row['id'],$year."-".str_pad(10,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(11,2,"0",STR_PAD_LEFT)."-01");
        $novv_absent = getTimesheetCountAbsent($con, $row['id'],$year."-".str_pad(11,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(12,2,"0",STR_PAD_LEFT)."-01");
        $decc_absent = getTimesheetCountAbsent($con, $row['id'],$year."-12-01",((int)$year+1)."-01-01");
        $total_absentt = $jann_absent + $febb_absent + $marr_absent + $aprr_absent + $mayy_absent + $junn_absent + $jull_absent + $augg_absent + $sepp_absent + $octt_absent + $novv_absent + $decc_absent;
        //Overtime - getTimesheetCountOvertime
        $jann_overtime = getTimesheetCountOvertime($con, $row['id'],$year."-".str_pad(1,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(2,2,"0",STR_PAD_LEFT)."-01");
        $febb_overtime= getTimesheetCountOvertime($con, $row['id'],$year."-".str_pad(2,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(3,2,"0",STR_PAD_LEFT)."-01");
        $marr_overtime= getTimesheetCountOvertime($con, $row['id'],$year."-".str_pad(3,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(4,2,"0",STR_PAD_LEFT)."-01");
        $aprr_overtime= getTimesheetCountOvertime($con, $row['id'],$year."-".str_pad(4,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(5,2,"0",STR_PAD_LEFT)."-01");
        $mayy_overtime= getTimesheetCountOvertime($con, $row['id'],$year."-".str_pad(5,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(6,2,"0",STR_PAD_LEFT)."-01");
        $junn_overtime= getTimesheetCountOvertime($con, $row['id'],$year."-".str_pad(6,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(7,2,"0",STR_PAD_LEFT)."-01");
        $jull_overtime= getTimesheetCountOvertime($con, $row['id'],$year."-".str_pad(7,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(8,2,"0",STR_PAD_LEFT)."-01");
        $augg_overtime= getTimesheetCountOvertime($con, $row['id'],$year."-".str_pad(8,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(9,2,"0",STR_PAD_LEFT)."-01");
        $sepp_overtime= getTimesheetCountOvertime($con, $row['id'],$year."-".str_pad(9,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(10,2,"0",STR_PAD_LEFT)."-01");
        $octt_overtime= getTimesheetCountOvertime($con, $row['id'],$year."-".str_pad(10,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(11,2,"0",STR_PAD_LEFT)."-01");
        $novv_overtime= getTimesheetCountOvertime($con, $row['id'],$year."-".str_pad(11,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(12,2,"0",STR_PAD_LEFT)."-01");
        $decc_overtime= getTimesheetCountOvertime($con, $row['id'],$year."-12-01",((int)$year+1)."-01-01");
        $total_overtimet = $jann_overtime+ $febb_overtime+ $marr_overtime+ $aprr_overtime+ $mayy_overtime+ $junn_overtime+ $jull_overtime+ $augg_overtime+ $sepp_overtime+ $octt_overtime+ $novv_overtime+ $decc_absent;
       
            $data[] = array( 
                "empname" 	=> $row['empname'],
                 "jan"       => $jann,
                 "feb"       => $febb,
                 "mar"       => $marr,
                 "apr"       => $aprr,
                 "may"       => $mayy,
                 "jun"       => $junn,
                 "jul"       => $jull,
                 "aug"       => $augg,
                 "sep"       => $sepp,
                 "oct"       => $octt,
                 "nov"       => $novv,
                 "dec"       => $decc,
                 "total_late" =>"".$total_latee,
                 "jan_ut"       => $jann_ut,
                 "feb_ut"       => $febb_ut,
                 "mar_ut"       => $marr_ut,
                 "apr_ut"       => $aprr_ut,
                 "may_ut"       => $mayy_ut,
                 "jun_ut"       => $junn_ut,
                 "jul_ut"       => $jull_ut,
                 "aug_ut"       => $augg_ut,
                 "sep_ut"       => $sepp_ut,
                 "oct_ut"       => $octt_ut,
                 "nov_ut"       => $novv_ut,
                 "dec_ut"       => $decc_ut,
                 "total_ut"     =>$total_utt,
                 "jan_absent"       => $jann_absent,
                 "feb_absent"       => $febb_absent,
                 "mar_absent"       => $marr_absent,
                 "apr_absent"       => $aprr_absent,
                 "may_absent"       => $mayy_absent,
                 "jun_absent"       => $junn_absent,
                 "jul_absent"       => $jull_absent,
                 "aug_absent"       => $augg_absent,
                 "sep_absent"       => $sepp_absent,
                 "oct_absent"       => $octt_absent,
                 "nov_absent"       => $novv_absent,
                 "dec_absent"       => $decc_absent,
                 "total_absent"     =>$total_absentt,
                 "jan_overtime"       => $jann_overtime,
                 "feb_overtime"       => $febb_overtime,
                 "mar_overtime"       => $marr_overtime,
                 "apr_overtime"       => $aprr_overtime,
                 "may_overtime"       => $mayy_overtime,
                 "jun_overtime"       => $junn_overtime,
                 "jul_overtime"       => $jull_overtime,
                 "aug_overtime"       => $augg_overtime,
                 "sep_overtime"       => $sepp_overtime,
                 "oct_overtime"       => $octt_overtime,
                 "nov_overtime"       => $novv_overtime,
                 "dec_overtime"       => $decc_overtime,
                 "total_overtime"     =>$total_overtimet
            );
      
        $return = json_encode($data);
    }
}else{
    $return = json_encode(array());
}


print $return;
mysqli_close($con);


function getIdUnit($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['idunit'];
        }
    }
    return null;
}


function getTimesheet($con, $idacct, $dfrom, $dto){
    $Qry=new Query();
    $Qry->table="vw_data_timesheet";
    $Qry->selected="COUNT(late) AS ctr";
    $Qry->fields="empID IN (".$idacct.") AND late IS NOT NULL AND late <> '0.00' AND ( work_date >= '".$dfrom."' AND work_date < '".$dto."' )";
    $rs=$Qry->exe_SELECT($con);
    if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
    return  0;
}

function getTimesheetCountUT($con, $idacct, $dfrom, $dto){
    $Qry=new Query();
    $Qry->table="vw_data_timesheet";
    $Qry->selected="COUNT(ut) AS ctr";
    $Qry->fields="empID IN (".$idacct.") AND ut IS NOT NULL AND ut <> '0.00' AND ( work_date >= '".$dfrom."' AND work_date < '".$dto."' )";
    $rs=$Qry->exe_SELECT($con);
    if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
    return  0;
}

function getTimesheetCountAbsent($con, $idacct, $dfrom, $dto){
    $Qry=new Query();
    $Qry->table="vw_data_timesheet";
    $Qry->selected="COUNT(absent) AS ctr";
    $Qry->fields="empID IN (".$idacct.") AND absent IS NOT NULL AND absent <> '0.00' AND ( work_date >= '".$dfrom."' AND work_date < '".$dto."' )";
    $rs=$Qry->exe_SELECT($con);
    if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
    return  0;
}

function getTimesheetCountOvertime($con, $idacct, $dfrom, $dto){
    $Qry=new Query(); 
    $Qry->table="vw_data_timesheet";
    $Qry->selected="COUNT(ot) AS ctr";
    $Qry->fields="empID IN (".$idacct.") AND ot IS NOT NULL AND ot <> '0.00' AND ( work_date >= '".$dfrom."' AND work_date < '".$dto."' )";
    $rs=$Qry->exe_SELECT($con);
    if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
    return  0;
}


?>