<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

// $param = json_decode(file_get_contents('php://input'));
$param = $_GET;
$data  = array();
$today_date = SysDatePadLeft();
$date  = $param['dfrom'];
$date1 = $param['dto'];
// $date  = '2020-08-11';
// $date1 = '2020-08-25';


$search ='';

if( !empty( $param['acctt'] ) ){ $search=$search." AND de.id 	= '".$param['acctt']."' "; }
if( !empty( $param['postt'] ) ){ $search=$search." AND de.idpos 	= '".$param['postt']."' "; }
if( !empty($param['dfrom']) && !empty($param['dto'])){
    $search=$search." AND dt.work_date BETWEEN DATE('".$param['dfrom']."') AND DATE('".$param['dto']."') ";
}

$ids=0;
$dept = getIdUnit($con, $param['idsuperior']);

//Get Managers Under person
$ids=0;if( !empty( $dept ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$dept);
    array_push( $arr_id, 0 );
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

$name23=array();
$Qry = new Query();	
$Qry->table     = "vw_data_timesheet dt LEFT JOIN vw_dataemployees de ON dt.idacct = de.id";
$Qry->selected  = "*";
$Qry->fields    = "de.id != '".$param['idsuperior']."' AND (de.idsuperior='".$param['idsuperior']."' or de.idunit IN (".$ids.")) AND dt.work_date BETWEEN '".$date."' AND '".$date1."' ".$search." ORDER BY de.empname";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        $break= 0;

        $Qry3           = new Query();
        $Qry3->table    = "tblshift";
        $Qry3->selected = "*";
        $Qry3->fields   = "id = '" . $row['idshift'] . "' ";
        $rs3            = $Qry3->exe_SELECT($con);
        if (mysqli_num_rows($rs3) >= 1) {
            if ($row3 = mysqli_fetch_array($rs3)) {
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
                        // sprintf('%0.2f', $twh),
                        // sprintf('%0.2f', $others),
                        sprintf('%0.2f', $row['acthrs']),
                        sprintf('%0.2f', $row['excess']),
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
header('Content-Disposition: attachment; filename=myTeam_Timesheet_Detail_'.$today_date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array($param['company']));
fputcsv($output, array("myTeam Timesheet Details"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Status',
                        'Employee Name',
						'Position',
						'Date',
						'Shift Code',
						'In',
                        'Out',
                        'Work Hours',
                        'Total Work Hours',
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


function getChangeShift($con, $work_date, $idacct){
    $data= array(
        // "status" 	=> '',
        "name" 	    => '',
    );
    $Qry=new Query();
    $Qry->table="tbltimeshift LEFT JOIN tblshift ON tbltimeshift.idshift = tblshift.id";
    $Qry->selected="tbltimeshift.*, tblshift.name";
    $Qry->fields="tbltimeshift.date='".$work_date."' AND tbltimeshift.idacct='".$idacct."' AND tbltimeshift.cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      

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
    $Qry->selected="*";
    $Qry->fields="date='".$work_date."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      

            $data = array(
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
    $Qry->selected="*";
    $Qry->fields="date='".$work_date."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
 
            $data = array(
                "status" 	    => $row['stat'],
                "planhrs" 	    => $row['planhrs']
            
            );
        }
    }
    return $data;
}

function getTimesheetLeaves($con, $work_date, $idacct){
    $data= array(
        "name" => '',
        "hrs"  => '',
    );
    $Qry=new Query();
    $Qry->table="tbltimeleaves LEFT JOIN tblleaves ON tbltimeleaves.idleave = tblleaves.id";
    $Qry->selected="*";
    $Qry->fields="tbltimeleaves.date='".$work_date."' AND tbltimeleaves.idacct='".$idacct."' AND tbltimeleaves.cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
 
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