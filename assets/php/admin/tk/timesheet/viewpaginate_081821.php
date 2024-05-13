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
$where = $search;


$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "*";
$Qry->fields    = "sched is not null AND etypeid=1 ORDER BY empname ". $search . " LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){

    while($row=mysqli_fetch_array($rs)){
		if( empty($row['pic']) ){
			$row['pic'] = "undefined.webp";
		}

        $data[] = array( 
            "empID"			       => $row['id'],
            "empname"			   => $row['empname'],
            "pic"			       => $row['pic'],
            "wshift_name"		   => $row['wshift_name'],
            "post"			       => $row['business_unit'],
            "getVwTimesheet"       => getVwTimesheet($con, $row['id'], $date, $date1),
            "getTotalApproved"     => getTotalApprovedCS($con, $date, $date1, $row['id']) + getTotalApprovedAA($con, $date, $date1, $row['id']) + getTotalApprovedOT($con, $date, $date1,$row['id']) + getTotalApprovedLV($con, $date, $date1,$row['id']) + getTotalApprovedOB($con, $date, $date1,$row['id']),
            "getTotalDeclined"     => getTotalDeclinedCS($con, $date, $date1, $row['id']) + getTotalDeclinedAA($con, $date, $date1, $row['id']) + getTotalDeclinedOT($con, $date, $date1,$row['id']) + getTotalDeclinedLV($con, $date, $date1,$row['id']) + getTotalDeclinedOB($con, $date, $date1,$row['id']),
            "getTotalPending"     => getTotalPendingCS($con, $date, $date1, $row['id']) + getTotalPendingAA($con, $date, $date1, $row['id']) + getTotalPendingOT($con, $date, $date1,$row['id']) + getTotalPendingLV($con, $date, $date1,$row['id']) + getTotalPendingOB($con, $date, $date1,$row['id']),
            "twh"       => '',
            "totalwh"   => '',
            "totalewh"  =>'',
            "totalexcess"  =>'',
            "totallate" =>'',
            "totalut"  =>'',
            "totalabsent"  =>'',
            "totalnp"  =>'',
        
        );
        $myData = array('status' => 'success', 'result' => $data, 'totalItems' => getTotal($con , $where));
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

    $shift_cols = array(
        "monday" => "mon",
        "tuesday" => "tue",
        "wednesday" => "wed",
        "thursday" => "thu",
        "friday" => "fri",
        "saturday" => "sat",
        "sunday" => "sun"
    );

    $data=array();
    $Qry=new Query();
    $Qry->table="vw_timesheet";
    $Qry->selected="*";
    $Qry->fields="id='".$idacct."' AND work_date BETWEEN '".$date."' AND '".$date1."' ORDER BY CONCAT(id,work_date) ASC";
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

            // if(){

            // }else{

            // }

            $data[] = array(
                "status" 	            => $status,
                "date"                  => $row['work_date'],
                "work_date" 	        => date('D m/d/Y', strtotime($row['work_date'])),
                'shift'                 => $row['name'],
                'holiday_idtype'        => $row['holidaytype'],
                "in" 	                => $row['timein']? date('h:i a', strtotime($row['timein'])) :'',
                "out" 	                => $row['timeout']? date('h:i a', strtotime($row['timeout'])) :'',
 		        "in2" 	                => $row['timein2']? date('h:i a', strtotime($row['timein2'])) :'',
                "out2" 	                => $row['timeout2']? date('h:i a', strtotime($row['timeout2'])) :'',

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
                'np'                     => sprintf('%0.2f', $row['np']),
                'work_hours'            => sprintf('%0.2f', $row['shifthrs']),
                'total_work_hours'      => sprintf('%0.2f',$row['acthrs']),
                'excess_hours'          => sprintf('%0.2f',$row['excess']),
                'whrs'                  => sprintf('%0.2f',$whrs),
                'npot'                  => sprintf('%0.2f',$npot),
                'getChangeShift'        => getChangeShift($row, $con ,$date ,$row['work_date'],$row['id'],$row['cs'],$row['csstatus'],$row['csname']),
                'getAttendanceAdjust'   => getAttendanceAdjust($row, $con ,$date , $row['work_date'],$row['id'], $row['shifttype'], $row['aastatus'], $row['aastime'], $row['aaftime'], $row['aasstime'], $row['aasftime'], $row['timein'], $row['timeout'], $row['timein2'], $row['timeout2']),
                'getOvertime'           => getOvertime($row, $con ,$date , $row['work_date'],$row['id'],$row['otstatus'], $row['othrs']),
                'getTimesheetLeaves'    => getTimesheetLeaves($row, $con ,$date , $row['work_date'], $row['id'], $row['leaveappstatus'], $row['leavename'], $row['leave']),
                'getObTrip'             => getObTrip($row, $con ,$date , $row['work_date'],$row['id'],$row['obtripstatus'],$row['obtrip'],$row['obstart'],$row['obend'])
            );
        }
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
        ( date('Y-m-d', strtotime($row['csapprove']))  <= date('Y-m-d', strtotime($row['period_end'])) )){
    
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

        if (( date('Y-m-d', strtotime($row['aaapprove'])) >= date('Y-m-d', strtotime($row['period_start'])) ) && 
        ( date('Y-m-d', strtotime($row['aaapprove']))  <= date('Y-m-d', strtotime($row['period_end'])) )){
    
        }else{
            $adaj = $row['aaapprove']  . '<br>' .  $adaj;
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
    
        }else{
            $hrs = $row['otapprove']  . '<br>' .  $hrs;
        }
    
    }

    $data = array(
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
        ( date('Y-m-d', strtotime($row['obapprove']))  <= date('Y-m-d', strtotime($row['period_end'])) )){
    
        }else{
            $remarks = $row['obapprove']  . '<br>' .  $remarks . '<br>' .  date("h:i A", strtotime($start)) . '-' .  date("h:i A", strtotime($end));
        }
    
    }

    $data = array(
        "status"    => $status,
        "remarks"    => $remarks,
        "start"    => date("h:i A", strtotime($start)),
        "end"    => date("h:i A", strtotime($end)),
        "late"          =>getlateapproveObTrip($con ,$date , $work_date, $idacct)
    );


    return $data;
}

function getTimesheetLeaves($row, $con ,$date , $work_date, $idacct, $status, $name, $hrs){
    if($row['lvapprove']){
        if (( date('Y-m-d', strtotime($row['lvapprove'])) >= date('Y-m-d', strtotime($row['period_start'])) ) && 
        ( date('Y-m-d', strtotime($row['lvapprove']))  <= date('Y-m-d', strtotime($row['period_end'])) )){
    
        }else{
            $name = $row['lvapprove']  . '<br>' .  $name . '<br>' . $hrs . ' hrs';
        }
    
    }

    $data = array(
                    "status"    => $status,
                    "name" 	    => $name,
                    "hrs" 	    => $hrs,
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

function getTotal($con,$search){
    $Qry = new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "count(*) as total";
    $Qry->fields = "etypeid=1 ORDER BY empname";
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
    $Qry->table="tbltimeadjustment";
    $Qry->selected="*";
    $Qry->fields="idacct='".$idacct."'AND stat = 1 AND approver1_date = '".$work_date."' and `date` < '".$date."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
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
    $Qry->table="tbltimeshift as cs LEFT JOIN tblshift as ts ON cs.idshift = ts.id";
    $Qry->selected="cs.*,ts.name";
    $Qry->fields="idacct='".$idacct."'AND stat = 1 AND approver1_date = '".$work_date."' and `date` < '".$date."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
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
    $Qry->table="tbltimeovertime";
    $Qry->selected="*";
    $Qry->fields="idacct='".$idacct."'AND stat = 1 AND approver2_date = '".$work_date."' and `date` < '".$date."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
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
    $Qry->table="`tbltimeleaves` as lv LEFT JOIN tblleaves as lvs ON lv.idleave = lvs.id";
    $Qry->selected="lv.*,lvs.name";
    $Qry->fields="idacct='".$idacct."'AND stat = 1 AND date_approve = '".$work_date."' and `date` < '".$date."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
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
    $Qry->table="tbltimeobtrip";
    $Qry->selected="*";
    $Qry->fields="idacct='".$idacct."'AND stat = 1 AND date_approve = '".$work_date."' and `date` < '".$date."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){

        while($row=mysqli_fetch_array($rs)){
            $data[] = array(
                "date"       => $row['date'],
                "start"       => date("h:i A", strtotime($row['start_time'])),
                "end"       => date("h:i A", strtotime($row['end_time']))
            );
        }
    }

    return $data;
}

?>