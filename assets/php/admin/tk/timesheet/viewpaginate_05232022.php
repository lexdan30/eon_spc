<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();
$date  = $param->dfrom;
$date1 = $param->dto; 
$search='';

// $id_array = getJoblevels($con,$param->filter->idlvl);
// $ids = implode(",",$id_array);

if( !empty( $param->filter->acct ) ){ $search=" AND id =  '".$param->filter->acct."' "; }
if( !empty( $param->filter->post ) ){ $search=" AND idpos =  '".$param->filter->post."' "; }
if( !empty( $param->filter->department ) ){ $search=" AND idunit =  '".$param->filter->department."' "; }
if( !empty( $param->filter->idlvl ) ){ 
    $id_array = getJoblevels($con,$param->filter->idlvl);
    $ids = implode(",",$id_array);
    $search=" AND idlvl  IN  (". $ids .") ";
}
$where = $search;


$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "*";
$Qry->fields    = "(sdate > '".$date1."' OR sdate IS NULL) AND etypeid = 1 ". $search . " ORDER BY empname LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
//$Qry->fields    = "wshift is not null AND etypeid=1 ORDER BY empname ". $search . " LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){

    while($row=mysqli_fetch_array($rs)){
		if( empty($row['pic']) ){
			$row['pic'] = "undefined.webp";
		}

        $data[] = array( 
            "empID"			        => $row['id'],
            "empIDn"			    => $row['empid'],
            "empname"			    => $row['empname'], 
            "location"			    => $row['location'],
            "idlvl"			        => $row['idlvl'],
            "jobalias"			    => getJobalias($con,$row['idlvl']),
            "joblvl"			    => $row['joblvl'],
            "wshifttype"			=> $row['wshifttype'],
            "pic"			        => $row['pic'],
            "wshift_name"		    => $row['wshift_name'],
            "post"			        => $row['business_unit'],
            "getVwTimesheet"        => getVwTimesheet($con, $row['id'], $date, $date1),
            "getTotalApproved"      => getTotalApprovedCS($con, $date, $date1, $row['id']) + getTotalApprovedAA($con, $date, $date1, $row['id']) + getTotalApprovedOT($con, $date, $date1,$row['id']) + getTotalApprovedLV($con, $date, $date1,$row['id']) + getTotalApprovedOB($con, $date, $date1,$row['id']),
            "getTotalDeclined"      => getTotalDeclinedCS($con, $date, $date1, $row['id']) + getTotalDeclinedAA($con, $date, $date1, $row['id']) + getTotalDeclinedOT($con, $date, $date1,$row['id']) + getTotalDeclinedLV($con, $date, $date1,$row['id']) + getTotalDeclinedOB($con, $date, $date1,$row['id']),
            "getTotalPending"       => getTotalPendingCS($con, $date, $date1, $row['id']) + getTotalPendingAA($con, $date, $date1, $row['id']) + getTotalPendingOT($con, $date, $date1,$row['id']) + getTotalPendingLV($con, $date, $date1,$row['id']) + getTotalPendingOB($con, $date, $date1,$row['id']),
            "twh"                   => '',
            "totalwh"               => '',
            "totalewh"              =>'',
            "totalexcess"           =>'',
            "totallate"             =>'',
            "totalut"               =>'',
            "totalabsent"           =>'',
            "totalnp"               =>'',
            "totalot"               =>'',
        );

        $myData = array('status' => 'success', 'result' => $data, 'totalItems' => getTotal($con , $where, $date1));
        $return = json_encode($myData);
    }
}else{
    // $return = json_encode(array('status'=>'error'));
    $myData = array('status' => 'success', 'result' => $data, 'totalItems' => getTotal($con , $where));
    $return = json_encode($myData);
}


print $return;
mysqli_close($con);


function getVwTimesheet($con, $idacct, $date, $date1){
    error_reporting(0);
    $shift_cols = array( 
        "monday" => "mon", 
        "tuesday" => "tue",
        "wednesday" => "wed",
        "thursday" => "thu",
        "friday" => "fri",
        "saturday" => "sat",
        "sunday" => "sun"
    );

    $totallates = getLateCount($con, $date, $date1, $idacct);

    $data=array();
    $Qry=new Query();
    $Qry->table="vw_timesheetfinal";
    $Qry->selected="*";
    $Qry->fields=" tid='".$idacct."' AND work_date BETWEEN '".$date."' AND '".$date1."' ORDER BY work_date ASC";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            $row['name']   = $row['defaultsched'];

            if (!empty($row['holiday'])) {
                if($row['csstatus'] == 1){
                    $row['name'] = "<p style='color:red; font-weight: 900;'>". ucwords(strtolower($row['alias'])) . "H <strike>" . $row["defaultsched"] . "</strike> </p>";
                }else{
                    $row['name'] = "<p style='color:red; font-weight: 900;'> ". ucwords(strtolower($row['alias'])) . "H " . $row["defaultsched"] . "</p>";
                }
            }
          
            if ($row['name'] == 'Rest Day') {
                $row['name'] = '<p class="csuccess fw9">RD</p>';
                $status              = '<p class="csuccess fw9">N</p>';
            }else {
                $status = '<p class="fw9">W</p>';
            }

            if($row['absent'] > 0 && $row['acthrs'] < 0){
                $status              = '<p class="csuccess fw9">A</p>';
            }

            if($row['holiday'] ){
                $status              = '<p class="csuccess fw9">N</p>';


                //$row['acthrs'] = $row['acthrs'] + ($row['late'] + $row['ut']);

                $row['late'] = 0;
                $row['ut'] = 0;
            }

            if($row['leaveappstatus'] == 1){
                $status              = '<p class="csuccess fw9">P</p>';
            }

            if($row['lateref'] + $row['utref']  > 1){
               
                $row['late'] = $row['late'];
                $row['ut'] = $row['ut'];
            }else{
                if($row['lateref'] + $row['utref'] != 0){
                    //Managers add late ref to complete the  shifthours
                    $row['acthrs'] = $row['acthrs'] +  ($row['lateref'] + $row['utref']);
               
                    $row['late'] = 0;
                    $row['ut'] = 0;
                 
                } 
            }            
            
            $whrs  = $row['acthrs'] - $row['excess']; 
            $npot = $row['npot'];


            if($row['shifthrs'] < $whrs ){
                $whrs = $row['shifthrs'];
            }
            if(!empty($row['timein']) && !empty($row['timeout'])  && $row['leaveappstatus'] == 1 && $row['leave'] == 4 && $row['acthrs'] > ($row['shifthrs'] / 2)){
                $whrs = ($row['shifthrs'] / 2);
            }
            if($row['acthrs'] < $whrs){
                $whrs = $row['acthrs'];
            }

            if($row['leaveappstatus'] == 1 && $row['idtimeleavetype'] == 1){
                $whrs = $whrs + $row['leave'];
            }

            // !empty($row['timein']) && !empty($row['timeout'])  && 

            // if($row['leaveappstatus'] == 1 &&  ($row['idtimeleavetype'] == 2 || $row['idtimeleavetype'] == 3)){
            //     $whrs = $whrs + $row['leave'];
            // }
            //requested by Eunice disregard
            // if( !empty($row['holidaytype']) && empty($row['othrs']) && $row['wshifttyperef'] == 'Admin' && ($row['joblvl'] == 'Supervisor' || $row['joblvl'] == 'Rank and File' || $row['joblvl'] == 'Confidential')){
            //     $whrs = $whrs-$whrs;
            // }

            if( $row['leaveappstatus'] == 1 && ($row['leaveidtype'] == 3 || $row['leaveidtype'] == 34)){
                $whrs = $whrs-$row['leave'];
            }



            $csapprove = strtotime(date($row['csapprove']));
            $period_start = strtotime(date($row['period_start']));
            $period_endref =  strtotime(date($row['period_endref']));

            if($row['csstatus'] == 1 ){
                if($row['name'] == '<p class="csuccess fw9">RD</p>'){
                    $row['name'] = '<p class="csuccess fw9"><strike> RD</strike></p>';
                }else{
                    if( (($csapprove >= $period_start) && ($csapprove >= $period_endref)) ){
                        $row['name'] = $row['name'];
                    }else{
                        $row['name'] = "<strike> " . $row['name'] . "</strike>";
                    }
                    
                }
            }

            if($row['aastatus'] == 1){
                $row['timein'] = $row['timein']? "<strike> " . date('h:i a', strtotime($row['timein'])) . "</strike>" :'';
                $row['timeout'] = $row['timeout']? "<strike> " . date('h:i a', strtotime($row['timeout'])) . "</strike>" :'';
                $row['timein2'] = $row['timein2']? "<strike> " . date('h:i a', strtotime($row['timein2'])) . "</strike>":'';
                $row['timeout2'] = $row['timeout2']? "<strike> " . date('h:i a', strtotime($row['timeout2'])) . "</strike>":'';
            }else{
                $row['timein'] = $row['timein']?  date('h:i a', strtotime($row['timein'])) :'';
                $row['timeout'] = $row['timeout']?  date('h:i a', strtotime($row['timeout'])):'';
                $row['timein2'] = $row['timein2']?  date('h:i a', strtotime($row['timein2'])) :'';
                $row['timeout2'] = $row['timeout2']?  date('h:i a', strtotime($row['timeout2'])) :'';
            }

            $checkdate = false;
            foreach ($data as $key => $val) {
                if ($val['date'] == $row['work_date']) { 
                    $checkdate = true;
                    $data[$key]['getTimesheetLeaves'] = array_merge($val['getTimesheetLeaves'],  getTimesheetLeaves($row, $con ,$date , $row['work_date'], $row['tid'], $row['leaveappstatus'], $row['leavename'], $row['leave'], $row['idtimeleavetype'], $row['leaveidtype']));
                    $finalleavehrs =0;
                    foreach ($data[$key]['getTimesheetLeaves'] as $keys => $vals) {
                        if($vals['leavetype'] != '3' && $vals['leavetype'] != '34'){
                            $finalleavehrs += $vals['hrs'];
                            if($keys == 1){
                                $data[$key]['absent'] =  sprintf('%0.2f',  sprintf('%0.2f',  $data[$key]['absent']) - $vals['hrs'] );
                            }
                        }
                        // else{
                        //     if($keys == 1){
                        //         $data[$key]['absent'] =  sprintf('%0.2f',  sprintf('%0.2f',  $data[$key]['absent']) + $vals['hrs'] );
                        //     }
                        // }
                    }
                    $data[$key]['whrs'] = sprintf('%0.2f',$finalleavehrs);

                    if(!$row['lvapprove']){
                        $data[$key]['getOvertime'] = array_merge($val['getOvertime'],  getOvertime($row, $con ,$date , $row['work_date'],$row['tid'],$row['otstatus'], $row['othrs']));
                        $data[$key]['ot'] = $data[$key]['ot'] + $row['othrs'];
                    }
                    break;
                }
            }

            if($row['leaveappstatus'] == 1 &&  ($row['idtimeleavetype'] == 2 || $row['idtimeleavetype'] == 3)){
                    $whrs =  $whrs + $row['leave'];
            }

            if(!$checkdate){
                $data[] = array(
                    "status" 	            => $status,
                    "date"                  => $row['work_date'],
                    "work_date" 	        => date('D m/d/Y', strtotime($row['work_date'])),
                    'shift'                 => $row['name'],
                    'holiday_idtype'        => $row['holidaytype'],
                    "in" 	                => $row['timein'],
                    "out" 	                => $row['timeout'],
                    "in2" 	                => $row['timein2'],
                    "out2" 	                => $row['timeout2'],
    
                    "shiftin" 	                => $row['stime']? $row['stime'] :'',
                    "shiftout" 	                => $row['ftime']? $row['ftime'] :'',
                    "shiftin2" 	                => $row['sstime']? $row['sstime'] :'',
                    "shiftout2" 	            => $row['sftime']? $row['sftime'] :'',
    
                    
                    "wdate" 	                => $row['work_date']? date('m/d/Y', strtotime($row['work_date'])) : '',
                    "datein" 	                => $row['date_in']? date('m/d/Y', strtotime($row['date_in'])) : '',
                    "dateout" 	                => $row['date_out']? date('m/d/Y', strtotime($row['date_out'])) :'',
                     "datein2" 	                => $row['date_in2']? date('m/d/Y', strtotime($row['date_in2'])) :'',
                    "dateout2" 	                => $row['date_out2']? date('m/d/Y', strtotime($row['date_out2'])) :'',
    
                    "late" 	                => sprintf('%0.2f', $row['late']),
                    'undertime'             => sprintf('%0.2f', $row['ut']),
                    'absent'                => sprintf('%0.2f', $row['absent']),
                    'np'                    => sprintf('%0.2f', $row['np']),
                    'work_hours'            => sprintf('%0.2f', $row['shifthrs']),
                    'total_work_hours'      => sprintf('%0.2f',$row['acthrs']),
                    'excess_hours'          => sprintf('%0.2f',$row['excess']),
                    'whrs'                  => sprintf('%0.2f',$whrs),
                    'ot'                    => sprintf('%0.2f',$row['othrs']),
                    'otstat'                =>$row['otstatus'],
                    
                    'npot'                  => sprintf('%0.2f',$npot),
                    'getChangeShift'        => getChangeShift($row, $con ,$date ,$row['work_date'],$row['tid'],$row['cs'],$row['csstatus'],$row['csname']),
                    'getAttendanceAdjust'   => getAttendanceAdjust($row, $con ,$date , $row['work_date'],$row['tid'], $row['shifttype'], $row['aastatus'], $row['aastime'], $row['aaftime'], $row['aasstime'], $row['aasftime'], $row['timein'], $row['timeout'], $row['timein2'], $row['timeout2']),
                    'getOvertime'           => getOvertime($row, $con ,$date , $row['work_date'],$row['tid'],$row['otstatus'], $row['othrs']),
                    'getTimesheetLeaves'    => getTimesheetLeaves($row, $con ,$date , $row['work_date'], $row['tid'], $row['leaveappstatus'], $row['leavename'], $row['leave'], $row['idtimeleavetype'], $row['leaveidtype']),
                    'getObTrip'             => getObTrip($row, $con ,$date , $row['work_date'],$row['tid'],$row['obtripstatus'],$row['obtrip'],$row['obstart'],$row['obend'])
                );
            }
        }
    }else{
        return $data[] = array();
    }
    return $data;
}

function getDateShiftDatas($con, $idacct, $idshift, $cols, $in, $out, $b, $date){
    $data          = array();
    $col           = explode(",", $cols);
    $Qry           = new Query();
    $Qry->table    = "vw_datatimesched";
    $Qry->selected = $cols . ',' . $in . ',' . $out . ',' . $b . ',' . $idshift;
    $Qry->fields   = "idacct='" . $idacct . "' AND date_create <= '" . $date . "' ORDER BY id DESC LIMIT 1";
    $rs            = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1) {
        if ($row = mysqli_fetch_array($rs)) {
            foreach ($col as $key => $v) {
                array_push($data, $row[$col[$key]]);
                array_push($data, $row[$in]);
                array_push($data, $row[$out]);
                array_push($data, $row[$b]);
                array_push($data, $row[$idshift]);
            }
        }
    } else {
        $Qry2           = new Query();
        $Qry2->table    = "vw_datatimesched";
        $Qry2->selected = $cols . ',' . $in . ',' . $out . ',' . $b . ',' . $idshift;
        $Qry2->fields   = "idacct='" . $idacct . "' AND date_create >= '" . $date . "' ORDER BY id ASC LIMIT 1";
        $rs2            = $Qry2->exe_SELECT($con);
        if (mysqli_num_rows($rs2) >= 1) {
            if ($row2 = mysqli_fetch_array($rs2)) {
                foreach ($col as $key => $v) {
                    array_push($data, $row2[$col[$key]]);
                    array_push($data, $row2[$in]);
                    array_push($data, $row2[$out]);
                    array_push($data, $row2[$b]);
                    array_push($data, $row2[$idshift]);
                }
            }
        }
    }
    return $data;
}

function getChangeShift($row, $con ,$date , $work_date, $idacct,$idshift, $status, $name){

    if($row['csapprove']){
        if (( date('Y-m-d', strtotime($row['csapprove'])) >= date('Y-m-d', strtotime($row['period_start'])) ) && 
        ( date('Y-m-d', strtotime($row['csapprove']))  <= date('Y-m-d', strtotime($row['period_endref'])) )){
    
        }else{
            $name = $row['csapprove']  . '<br>' .  $name;
        }
    
    }

    
    $data = array(         
        "status" 	=> $status,
        "name" 	    => $name,
        "late"      =>getlateapproveChangeShift($con ,$date , $work_date, $idacct)
    );

    return $data;
}

function getAttendanceAdjust($row, $con ,$date , $work_date, $idacct ,$shifttype ,$status, $stime, $ftime, $sstime, $sftime, $timein, $timeout, $timein2, $timeout2){
    $adaj = '';
        if($shifttype == 'Regular Schedule' OR $shifttype == 'Compressed Schedule'){
            // if ($timein == '') {
            //     $in =  "<strong style='font-weight: 900 !important;'>IN</strong> ". ($stime ? date("h:i A", strtotime($stime)) : '') ."<br>"  ;
            // }else{
            //     $in =  '';
            // }
    
            // if ($timeout == '') {
            //     $out =  "<strong style='font-weight: 900 !important;'>OUT</strong> ". ($ftime ? date("h:i A", strtotime($ftime)) : '') ."<br>"  ;
            // }else{
            //     $out =  '';
            // }

            $in =  "<strong style='font-weight: 900 !important;'>IN</strong> ". ($stime ? date("h:i A", strtotime($stime)) : '') ."<br>"  ;
            $out =  "<strong style='font-weight: 900 !important;'>OUT</strong> ". ($ftime ? date("h:i A", strtotime($ftime)) : '') ."<br>"  ;

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

        if (( date('Y-m-d', strtotime($row['aaapprove'])) >= date('Y-m-d', strtotime($row['period_start'])) ) && 
        ( date('Y-m-d', strtotime($row['aaapprove']))  <= date('Y-m-d', strtotime($row['period_endref'])) )){
    
        }else{
            $adaj = $row['aaapprove']  . '' .  $adaj;
        }

        $data = array(
            "status"     => $status,
            "date"     => $work_date,
            "adj" 	    => $adaj,
            "stime" 	    => $stime,
            "ftime" 	    => $ftime,
            "sstime" 	    => $sstime,
            "sftime" 	    => $sftime,
            "late"          =>getlateapproveAttendanceAdjust($con ,$date , $work_date, $idacct)
        );

    
    
 
   
    return $data;
}

function getOvertime($row, $con ,$date , $work_date, $idacct,$status,$hrs){
    if($row['otapprove']){
        if (( date('Y-m-d', strtotime($row['otapprove'])) >= date('Y-m-d', strtotime($row['period_start'])) ) && 
        ( date('Y-m-d', strtotime($row['otapprove']))  <= date('Y-m-d', strtotime($row['period_end'])) )){
            $hrs =  number_format((float)$hrs, 2, '.', '') . ' hrs';
        }else{
            $hrs = $row['otapprove']  . '<br>' .  number_format((float)$hrs, 2, '.', '') . ' hrs';
        }
    }

    if($status == '3'){
        $hrs =  number_format((float)$hrs, 2, '.', '') . ' hrs';
    }

    $data[] = array(
        "status" 	    => $status,
        "planhrs" 	    => $hrs,
        "late"          =>getlateapproveOvertime($con ,$date , $work_date, $idacct)
    );

    return $data;
}
//OB
function getObTrip($row, $con ,$date , $work_date, $idacct,$status,$remarks,$start,$end){
    if($row['obapprove']){
        if (( date('Y-m-d', strtotime($row['obapprove'])) >= date('Y-m-d', strtotime($row['period_start'])) ) && 
        ( date('Y-m-d', strtotime($row['obapprove']))  <= date('Y-m-d', strtotime($row['period_endref'])) )){
            $remarks = $remarks . '<br>' .  date("h:i A", strtotime($start)) . '-' .  date("h:i A", strtotime($end));
        }else{
            $remarks = $row['obapprove']  . '<br>' .  $remarks . '<br>' .  date("h:i A", strtotime($start)) . '-' .  date("h:i A", strtotime($end));
        }
    
    }

    if($status == '3'){
        $remarks = $remarks . '<br>' .  date("h:i A", strtotime($start)) . '-' .  date("h:i A", strtotime($end));
    }
   
    $data = array(
        "status"    => $status,
        "remarks"   => $remarks,
        "start"     => date("h:i A", strtotime($start)),
        "end"       => date("h:i A", strtotime($end)),
        "late"      =>getlateapproveObTrip($con ,$date , $work_date, $idacct)
    );


    return $data;
}

function getTimesheetLeaves($row, $con ,$date , $work_date, $idacct, $status, $name, $hrs, $idtimeleavetype, $leavetype){
    if($row['lvapprove']){
        if ((( date('Y-m-d', strtotime($row['lvapprove'])) >= date('Y-m-d', strtotime($row['period_start'])) ) && 
        ( date('Y-m-d', strtotime($row['lvapprove']))  <= date('Y-m-d', strtotime($row['period_endref'])) )) ||
        ( date('Y-m-d', strtotime($row['lvapprove'])) < date('Y-m-d', strtotime($row['period_start'])) )){
             // $name = $name . '<br>' . date("h:i A", strtotime($row['leavestart'])) . '-'. date("h:i A", strtotime($row['leaveend']));
             $identifier = '';
             if($idtimeleavetype == 2){
                 $identifier = '1st Half';
                 $name = $name . '<br>' . $identifier;
             }
             else if($idtimeleavetype == 3){
                 $identifier = '2nd Half';
                 $name = $name . '<br>' . $identifier;
             }
             else{
                 $identifier = 'Whole Shift';
                 $name = $name . '<br>' . $identifier;
             }
            
        }else{
            $name = $row['lvapprove']  . '<br>' .  $name;
        }
    
    }

    if($status == '3'){
          // $name = $name . '<br>' . date("h:i A", strtotime($row['leavestart'])) . '-'. date("h:i A", strtotime($row['leaveend']));
          $identifier = '';
          if($idtimeleavetype == 2){
              $identifier = '1st Half';
              $name = $name . '<br>' . $identifier;
          }
          else if($idtimeleavetype == 3){
              $identifier = '2nd Half';
              $name = $name . '<br>' . $identifier;
          }
          else{
              $identifier = 'Whole Shift';
              $name = $name . '<br>' . $identifier;
          }
    }

    $data[] = array(
                    "status"    => $status,
                    "name" 	    => $name,
                    "hrs" 	    => $hrs,
                    "leavetype" 	    => $leavetype,
                    "idtimeleavetype"  => $idtimeleavetype,
                    "late"        =>getlateapproveTimesheetLeaves($con ,$date , $work_date, $idacct)
                );
           
    return $data;
}

// APPROVED
function getTotalApprovedCS($con, $date, $date1, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeshift";
    $Qry->selected="COUNT(id) as ctr";
    $Qry->fields="stat=1 AND date BETWEEN '".$date."' AND '".$date1."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
            return $row['ctr'];
        }
    }
    return '';
}

function getTotalApprovedAA($con, $date, $date1, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeadjustment";
    $Qry->selected="COUNT(id) as ctr";
    $Qry->fields="stat=1 AND date BETWEEN '".$date."' AND '".$date1."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
            return $row['ctr'];
        }
    }
    return '';
}

function getTotalApprovedOT($con, $date, $date1, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeovertime";
    $Qry->selected="COUNT(id) as ctr";
    $Qry->fields="stat=1 AND date BETWEEN '".$date."' AND '".$date1."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
            return $row['ctr'];
        }
    }
    return '';
}

function getTotalApprovedLV($con, $date, $date1, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeleaves";
    $Qry->selected="COUNT(id) as ctr";
    $Qry->fields="stat=1 AND date BETWEEN '".$date."' AND '".$date1."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
            return $row['ctr'];
        }
    }
    return '';
}

function getTotalApprovedOB($con, $date, $date1, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeobtrip";
    $Qry->selected="COUNT(id) as ctr";
    $Qry->fields="stat=1 AND date BETWEEN '".$date."' AND '".$date1."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
            return $row['ctr'];
        }
    }
    return '';
}

// DECLINED
function getTotalDeclinedCS($con, $date, $date1, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeshift";
    $Qry->selected="COUNT(id) as ctr";
    $Qry->fields="stat=2 AND date BETWEEN '".$date."' AND '".$date1."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
            return $row['ctr'];
        }
    }
    return '';
}

function getTotalDeclinedAA($con, $date, $date1, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeadjustment";
    $Qry->selected="COUNT(id) as ctr";
    $Qry->fields="stat=2 AND date BETWEEN '".$date."' AND '".$date1."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
            return $row['ctr'];
        }
    }
    return '';
}

function getTotalDeclinedOT($con, $date, $date1, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeovertime";
    $Qry->selected="COUNT(id) as ctr";
    $Qry->fields="stat=2 AND date BETWEEN '".$date."' AND '".$date1."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
            return $row['ctr'];
        }
    }
    return '';
}

function getTotalDeclinedLV($con, $date, $date1, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeleaves";
    $Qry->selected="COUNT(id) as ctr";
    $Qry->fields="stat=2 AND date BETWEEN '".$date."' AND '".$date1."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
            return $row['ctr'];
        }
    }
    return '';
}

function getTotalDeclinedOB($con, $date, $date1, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeobtrip";
    $Qry->selected="COUNT(id) as ctr";
    $Qry->fields="stat=2 AND date BETWEEN '".$date."' AND '".$date1."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
            return $row['ctr'];
        }
    }
    return '';
}

// PENDING
function getTotalPendingCS($con, $date, $date1, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeshift";
    $Qry->selected="COUNT(id) as ctr";
    $Qry->fields="stat=3 AND date BETWEEN '".$date."' AND '".$date1."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
            return $row['ctr'];
        }
    }
    return '';
}

function getTotalPendingAA($con, $date, $date1, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeadjustment";
    $Qry->selected="COUNT(id) as ctr";
    $Qry->fields="stat=3 AND date BETWEEN '".$date."' AND '".$date1."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
            return $row['ctr'];
        }
    }
    return '';
}

function getTotalPendingOT($con, $date, $date1, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeovertime";
    $Qry->selected="COUNT(id) as ctr";
    $Qry->fields="stat=3 AND date BETWEEN '".$date."' AND '".$date1."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
            return $row['ctr'];
        }
    }
    return '';
}

function getTotalPendingLV($con, $date, $date1, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeleaves";
    $Qry->selected="COUNT(id) as ctr";
    $Qry->fields="stat=3 AND date BETWEEN '".$date."' AND '".$date1."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
            return $row['ctr'];
        }
    }
    return '';
}
function getTotalPendingOB($con, $date, $date1, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeobtrip";
    $Qry->selected="COUNT(id) as ctr";
    $Qry->fields="stat=3 AND date BETWEEN '".$date."' AND '".$date1."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
            return $row['ctr'];
        }
    }
    return '';
}

function getTotal($con,$search, $date1){
    $Qry = new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "count(*) as total";
    //$Qry->fields = "wshift is not null AND etypeid=1 ORDER BY empname";
    $Qry->fields = "(sdate > '".$date1."' OR sdate IS NULL) and etypeid = 1". $search . " ORDER BY empname";
    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['total'];
			}
		}
		return 0;
}


function getlateapproveAttendanceAdjust($con ,$date , $work_date, $idacct){

    $data = array();
    $Qry=new Query();
    $Qry->table="tbltimeadjustment as app LEFT JOIN tblaccountjob as aj ON app.idacct = aj.idacct";
    $Qry->selected="*";
    $Qry->fields="app.idacct='".$idacct."' 
    AND stat = 1 AND approver1_date = '".$work_date."' 
    and `date` < '".$date."' 
    and (approver1_date > (CASE 
                                    WHEN aj.idlvl IN (2) THEN DATE_ADD('".$date."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                    ELSE DATE_ADD('".$date."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                                END)  
        OR  `date` <  (CASE 
                                WHEN aj.idlvl IN (2) THEN DATE_SUB('".$date."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                ELSE DATE_SUB('".$date."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                            END)  
        )
    AND app.id NOT IN (SELECT idapp FROM tblappcancel WHERE `type` = 'attendance' AND STATUS = 1)";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
            if($row['stime']){
                $stime = date("h:i A", strtotime($row['stime']));
            }else{
                $stime = ''; 
            }
    
            if($row['ftime']){
                $ftime = date("h:i A", strtotime($row['ftime']));
            }else{
                $stime = '';
            }
    
            if($row['sstime']){
                $sstime = date("h:i A", strtotime($row['sstime']));
            }else{
                $sstime = '';
            }
            
            if($row['sftime']){
                $sftime =  date("h:i A", strtotime($row['sftime']));
            }else{
                $sftime = '';
            }
    
                $data[] = array(
                    "date"       => $row['date'],
                    "stime"       => $stime,
                    "ftime"       => $ftime,
                    "sstime"       =>  $sstime,
                    "sftime"       => $sftime
                );
        }
    }

    return $data;
}

function getlateapproveChangeShift($con ,$date , $work_date, $idacct){
  
    $data = array();
    $Qry=new Query();
    $Qry->table="tbltimeshift as cs LEFT JOIN tblshift as ts ON cs.idshift = ts.id LEFT JOIN tblaccountjob as aj ON cs.idacct = aj.idacct";
    $Qry->selected="cs.*,ts.name";
    $Qry->fields="cs.idacct='".$idacct."' 
    AND stat = 1 
    AND approver1_date = '".$work_date."' and `date` < '".$date."' 
    and (approver1_date > (CASE 
                                WHEN aj.idlvl IN (2) THEN DATE_ADD('".$date."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                ELSE DATE_ADD('".$date."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                            END) 
          OR  `date` <  (CASE 
                                WHEN aj.idlvl IN (2) THEN DATE_SUB('".$date."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                ELSE DATE_SUB('".$date."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                            END)  
        )
    AND cs.id NOT IN (SELECT idapp FROM tblappcancel WHERE `type` = 'changeshift' AND STATUS = 1)";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
            $data[] = array(
                "date"       => $row['date'],
                "name"       => $row['name']
            );
        }
    }

    return $data;
}

function getlateapproveOvertime($con ,$date , $work_date, $idacct){

    $data = array();
    $Qry=new Query();
    $Qry->table="tbltimeovertime as app LEFT JOIN tblaccountjob as aj ON app.idacct = aj.idacct";
    $Qry->selected="*";
    $Qry->fields="app.idacct='".$idacct."' 
    AND stat = 1 
    AND IFNULL(approver2_date,approver1_date) = '".$work_date."' 
    and `date` < '".$date."' 
    and (IFNULL(approver2_date,approver1_date) > (CASE 
                                                    WHEN aj.idlvl IN (2) THEN DATE_ADD('".$date."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                                    ELSE DATE_ADD('".$date."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                                                END)  
           OR  `date` <  (CASE 
                            WHEN aj.idlvl IN (2) THEN DATE_SUB('".$date."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                            ELSE DATE_SUB('".$date."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                        END)  
        )                                        
    AND app.id NOT IN (SELECT idapp FROM tblappcancel WHERE `type` = 'overtime' AND STATUS = 1)";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
            if($row['approve_hr'] == '2'){
                if($row['hrs'] > 1){
                    $hrs = $row['hrs'] . ' hrs';
                }else{
                    $hrs = $row['hrs'] . ' hr';
                }
              
            }else{
                if($row['hrs'] > 1){
                    $hrs = $row['planhrs'] . ' hrs';
                }else{
                    $hrs = $row['planhrs'] . ' hr';
                }
            }

            $data[] = array(
                "date"       => $row['date'],
                "hrs"       => $hrs
            );
        }
    }

    return $data;
}

function getlateapproveTimesheetLeaves($con ,$date , $work_date, $idacct){

    $data = array();
    $Qry=new Query();
    $Qry->table="`tbltimeleaves` as lv LEFT JOIN tblleaves as lvs ON lv.idleave = lvs.id LEFT JOIN tblaccountjob AS aj ON lv.idacct = aj.idacct";
    $Qry->selected="lv.*,lvs.name";
    $Qry->fields="lv.idacct='".$idacct."' 
    AND stat = 1 
    AND date_approve = '".$work_date."'  
    and `date` < '".$date."' 
    and (date_approve > (CASE 
                            WHEN aj.idlvl IN (2) THEN DATE_ADD('".$date."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                            ELSE DATE_ADD('".$date."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                        END) 
            OR  `date` <  (CASE 
                                WHEN aj.idlvl IN (2) THEN DATE_SUB('".$date."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                ELSE DATE_SUB('".$date."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                            END)  
        )
    AND lv.id NOT IN (SELECT idapp FROM tblappcancel WHERE `type` = '%LEAVE%' AND STATUS = 1)";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
            $data[] = array(
                "date"       => $row['date'],
                "name"       => $row['name'],
                "hrs"       => $row['hrs']
            );
        }
    }

    return $data;
}

function getlateapproveObTrip($con ,$date , $work_date, $idacct){

    $data = array();
    $Qry=new Query();
    $Qry->table="tbltimeobtrip as app LEFT JOIN tblaccountjob as aj ON app.idacct = aj.idacct";
    $Qry->selected="*";
    $Qry->fields="app.idacct='".$idacct."' 
    AND stat = 1 
    AND date_approve = '".$work_date."' 
    and `date` < '".$date."' 
    and (date_approve > (CASE 
                            WHEN aj.idlvl IN (2) THEN DATE_ADD('".$date."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                            ELSE DATE_ADD('".$date."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                        END) 
            OR  `date` <  (CASE 
                                WHEN aj.idlvl IN (2) THEN DATE_SUB('".$date."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                ELSE DATE_SUB('".$date."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                            END)  
        ) 
    AND app.id NOT IN (SELECT idapp FROM tblappcancel WHERE `type` = 'obtrip' AND STATUS = 1)";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
            $data[] = array(
                "date"       => $row['date'],
                "start"       => date("h:i A", strtotime($row['start_time'])),
                "end"       => date("h:i A", strtotime($row['end_time']))
            );
        }
    }

    return $data;
}

function getLateCount($con, $date, $date1, $idacct){
    $count = 0;
    $Qry=new Query();
    $Qry->table="vw_timesheetfinal";
    $Qry->selected="SUM(latecount) as total";
    $Qry->fields=" tid='".$idacct."' AND work_date BETWEEN '".$date."' AND '".$date1."' ORDER BY work_date ASC";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
            $count = $row['total'];
        }
    }else{
        return $count = 0;
    }
    return $count;
}

function getJobalias($con, $id){
    $data = 0;
    $Qry=new Query();
    $Qry->table="tbljoblvl";
    $Qry->selected="*";
    $Qry->fields=" id='".$id."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        if($row=mysqli_fetch_assoc($rs)){
            $data = $row['alias'];
        }
    }else{
        return $data = 0;
    }

    return $data;

}

function getJoblevels($con, $param){
    $data = array();	
    $joblevel = implode("','",$param);

    $Qry = new Query();	
    $Qry->table     = "tbljoblvl";
    $Qry->selected  = "id";
    $Qry->fields = "type IN ('". $joblevel."')";
    $rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            array_push($data,$row['id']);
        }
    }
    return $data;
}
?>