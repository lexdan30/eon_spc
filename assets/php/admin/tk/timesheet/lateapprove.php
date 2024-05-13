<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 



$param = json_decode(file_get_contents('php://input'));
if(!empty($param->accountid) ){
    $data = array( 
        "before"       => getbefore($con, $param->employee, $param->work_date, $param->approve_date),
        "after"        => getafter($con, $param->employee, $param->work_date, $param->approve_date),
        "work_date"        => $param->work_date,
        "approval_date"        => $param->approve_date
    );
}

$myData = array('status' => 'success', 'result' => $data);
$return = json_encode($myData);

print $return;
mysqli_close($con);

function getbefore($con, $idacct, $workdate, $dateapprove){
    $data = array();
    $Qry=new Query();
    $Qry->table="vw_emp_timesheetfinal";
    $Qry->selected="
    defaultsched, `name`, alias, absent, holiday,
    leaveidapp, acthrs, excess, npot, csstatus,
    work_date, holidaytype, timein, timeout, timein2,
    timeout2, late, ut, np, shifthrs,
    tid, cs, otstatus, obend, obstart,
    obtripstatus, obtrip, leaveappstatus, leavename, leave,
    othrs, aastatus, aastime, aaftime, aasstime, aasftime, shifttype, csname";
    $Qry->fields="tid IS NOT NULL AND `work_date` = '".$workdate."' AND tid = '".$idacct."' ORDER BY work_date";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            $row['name']   = $row['defaultsched'];

            if (!empty($row['holiday'])) {
                $row['name'] = '<p style="color:red; font-weight: 900;"> '. ucwords(strtolower($row['alias'])) . 'H ' . $row['defaultsched'] . '</p>';
            }
          
            if ($row['name'] == 'Rest Day') {
                $row['name'] = '<p class="csuccess fw9">RD</p>';
                $status              = '<p class="csuccess fw9">N</p>';
            }elseif($row['absent'] > 0){
                $status              = '<p class="csuccess fw9">A</p>';
            }elseif($row['holiday'] ){
                $status              = '<p class="csuccess fw9">N</p>';
            }elseif($row['leaveidapp']){
                $status              = '<p class="csuccess fw9">P</p>';
            }else {
                $status = '<p class="fw9">W</p>';
            }

           


            $whrs  = $row['acthrs'] - $row['excess'];
            $npot = $row['npot'];

            if($row['csstatus'] == 1){
                $row['name'] = "<strike> " . $row['name'] . "</strike>";
            }

            $data[] = array(
                "status" 	            => $status,
                "date" 	                => $row['work_date'],
                "work_date" 	        => date('D m/d/Y', strtotime($row['work_date'])),
                'shift'                 => $row['name'],
                'holiday_idtype'        => $row['holidaytype'],
                "in" 	                => $row['timein']? date('h:i a', strtotime($row['timein'])) :'',
                "out" 	                => $row['timeout']? date('h:i a', strtotime($row['timeout'])) :'',
 		        "in2" 	                => $row['timein2']? date('h:i a', strtotime($row['timein2'])) :'',
                "out2" 	                => $row['timeout2']? date('h:i a', strtotime($row['timeout2'])) :'',
                "late" 	                => sprintf('%0.2f', $row['late']),
                'undertime'             => sprintf('%0.2f', $row['ut']),
                'absent'                => sprintf('%0.2f', $row['absent']),
                'np'                     => sprintf('%0.2f', $row['np']),
                'work_hours'            => sprintf('%0.2f', $row['shifthrs']),
                'total_work_hours'      => sprintf('%0.2f',$row['acthrs']),
                'excess_hours'          => sprintf('%0.2f',$row['excess']),
                'whrs'                  => sprintf('%0.2f',$whrs),
                'npot'                  => sprintf('%0.2f',$npot),
                'getChangeShift'        => getChangeShift($con ,$row['work_date'],$row['tid'],$row['cs'],$row['csstatus'],$row['csname']),
                'getAttendanceAdjust'   => getAttendanceAdjust($con ,$row['work_date'],$row['tid'], $row['shifttype'], $row['aastatus'], $row['aastime'], $row['aaftime'], $row['aasstime'], $row['aasftime'], $row['timein'], $row['timeout'], $row['timein2'], $row['timeout2']),
                'getOvertime'           => getOvertime($con ,$row['work_date'],$row['tid'],$row['otstatus'],$row['othrs']),
                'getTimesheetLeaves'    => getTimesheetLeaves($con ,$row['work_date'], $row['tid'], $row['leaveappstatus'], $row['leavename'], $row['leave']),
                'getObTrip'             => getObTrip($con ,$row['work_date'],$row['tid'],$row['obtripstatus'],$row['obtrip'],$row['obstart'],$row['obend'])
            );
        }
    }

    return $data;
}

function getafter($con ,$idacct, $workdate, $dateapprove){
    $data = array();
    $Qry=new Query();
    $Qry->table="vw_timesheetaadjustmentfinal";
    $Qry->selected="*";
    $rs=$Qry->exe_SELECT($con);
    $Qry->fields="tid IS NOT NULL AND `work_date` = '".$workdate."' AND tid = '".$idacct."' ORDER BY work_date";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            $row['name']   = $row['defaultsched'];

            if (!empty($row['holiday'])) {
                $row['name'] = '<p style="color:red; font-weight: 900;"> '. ucwords(strtolower($row['alias'])) . 'H ' . $row['defaultsched'] . '</p>';
            }
          
            if ($row['name'] == 'Rest Day') {
                $row['name'] = '<p class="csuccess fw9">RD</p>';
                $status              = '<p class="csuccess fw9">N</p>';
            }elseif($row['absent'] > 0){
                $status              = '<p class="csuccess fw9">A</p>';
            }elseif($row['holiday'] ){
                $status              = '<p class="csuccess fw9">N</p>';
            }elseif($row['leaveidapp']){
                $status              = '<p class="csuccess fw9">P</p>';
            }else {
                $status = '<p class="fw9">W</p>';
            }

            $whrs  = $row['acthrs'] - $row['excess'];
            $npot = $row['npot'];

            if($row['csstatus'] == 1){
                $row['name'] = "<strike> " . $row['name'] . "</strike>";
            }



            $data[] = array(
                "status" 	            => $status,
                "date" 	                => $row['work_date'],
                "work_date" 	        => date('D m/d/Y', strtotime($row['work_date'])),
                'shift'                 => $row['name'],
                'holiday_idtype'        => $row['holidaytype'],
                "in" 	                => $row['timein']? date('h:i a', strtotime($row['timein'])) :'',
                "out" 	                => $row['timeout']? date('h:i a', strtotime($row['timeout'])) :'',
 		        "in2" 	                => $row['timein2']? date('h:i a', strtotime($row['timein2'])) :'',
                "out2" 	                => $row['timeout2']? date('h:i a', strtotime($row['timeout2'])) :'',
                "late" 	                => sprintf('%0.2f', $row['late']),
                'undertime'             => sprintf('%0.2f', $row['ut']),
                'absent'                => sprintf('%0.2f', $row['absent']),
                'np'                     => sprintf('%0.2f', $row['np']),
                'work_hours'            => sprintf('%0.2f', $row['shifthrs']),
                'total_work_hours'      => sprintf('%0.2f',$row['acthrs']),
                'excess_hours'          => sprintf('%0.2f', $row['excess']),
                'whrs'                  => sprintf('%0.2f',$whrs),
                'npot'                  => sprintf('%0.2f',$npot),
                'getChangeShift'        => getChangeShift($con ,$row['work_date'],$row['tid'],$row['cs'],$row['csstatus'],$row['csname']),
                'getAttendanceAdjust'   => getAttendanceAdjust($con ,$row['work_date'],$row['tid'], $row['shifttype'], $row['aastatus'], $row['aastime'], $row['aaftime'], $row['aasstime'], $row['aasftime'], $row['timein'], $row['timeout'], $row['timein2'], $row['timeout2']),
                'getOvertime'           => getOvertime($con ,$row['work_date'],$row['tid'],$row['otstatus'],$row['othrs']),
                'getTimesheetLeaves'    => getTimesheetLeaves($con ,$row['work_date'], $row['tid'], $row['leaveappstatus'], $row['leavename'], $row['leave']),
                'getObTrip'             => getObTrip($con ,$row['work_date'],$row['tid'],$row['obtripstatus'],$row['obtrip'],$row['obstart'],$row['obend'])
            );
        }
    }

    return $data;
}


function getChangeShift($con ,$work_date, $idacct,$idshift, $status, $name){
    $data = array(         
        "status" 	=> $status,
        "name" 	    => $name
    );
    return $data;
}

function getAttendanceAdjust($con ,$work_date, $idacct ,$shifttype ,$status, $stime, $ftime, $sstime, $sftime, $timein, $timeout, $timein2, $timeout2){
    $adaj = '';
 
  
        if($shifttype == 'Regular Schedule' OR $shifttype == 'Compressed Schedule'){
            if ($timein == '') {
                $in =  "<strong style='font-weight: 900 !important;'>IN</strong> ". ($stime ? date("h:i A", strtotime($stime)) : '') ."<br>"  ;
            }else{
                $in =  '';
            }
    
            if ($timeout == '') {
                $out =  "<strong style='font-weight: 900 !important;'>OUT</strong> ". ($ftime ? date("h:i A", strtotime($ftime)) : '') ."<br>"  ;
            }else{
                $out =  '';
            }
    
            if($in != '' || $out != ''){
                $adaj = $in . '' . $out;
            }
        }
        if($shifttype == 'Broken Schedule' OR $shifttype == 'Straight Schedule'){
            if ($timein == '') {
                $in =  "<strong style='font-weight: 900 !important;'>IN</strong> ". ($stime ? date("h:i A", strtotime($stime)) : '')  ."<br>"  ;
            }else{
                $in =  '';
            }
    
            if ($timeout == '') {
                $out =  " <strong style='font-weight: 900 !important;'>OUT</strong> ". ($ftime ? date("h:i A", strtotime($ftime)) : '')  ."<br>"  ;
            }else{
                $out =  '';
            }
    
            if ($timein2 == '') {
                $in2 =  " <strong style='font-weight: 900 !important;'>IN2</strong> ". ($sstime ? date("h:i A", strtotime($sstime)) : '') ."<br>"  ;
            }else{
                $in2 =  '';
            }
    
            if ($timeout2 == '') {
                $out2 =  " <strong style='font-weight: 900 !important;'>OUT2</strong> ". ($sftime ? date("h:i A", strtotime($sftime)) : '');
            }else{
                $out2 =  '';
            }
            
            if($in != '' || $out != ''  || $in2 != ''  || $out2 != ''){
                $adaj = $in . '' . $out . '' . $in2 . '' . $out2;
            }
    
        }

        $data = array(
            "status"     => $status,
            "date"     => $work_date,
            "adj" 	    => $adaj,
            "stime" 	    => $stime,
            "ftime" 	    => $ftime,
            "sstime" 	    => $sstime,
            "sftime" 	    => $sftime
        );

    
    
 
   
    return $data;
}

function getOvertime($con ,$work_date, $idacct,$status,$hrs){
    $data = array(
        "status" 	    => $status,
        "planhrs" 	    => $hrs
    );

    return $data;
}
//OB
function getObTrip($con ,$work_date, $idacct,$status,$remarks,$start,$end){

    $data = array(
        "status"    => $status,
        "remarks"    => $remarks,
        "start"    => date("h:i A", strtotime($start)),
        "end"    => date("h:i A", strtotime($end))
    );


    return $data;
}

function getTimesheetLeaves($con ,$work_date, $idacct, $status, $name, $hrs){
    $data = array(
                    "status"    => $status,
                    "name" 	    => $name,
                    "hrs" 	    => $hrs
                );
    return $data;
}

?>