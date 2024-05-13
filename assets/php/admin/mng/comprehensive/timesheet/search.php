<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();
$date  = $param->dfrom;
$date1 = $param->dto;

$search ='';

if( !empty( $param->search_acct ) ){ $search=$search." AND id 	= '".$param->search_acct."' "; }
if( !empty( $param->search_post ) ){ $search=$search." AND idpos 	= '".$param->search_post."' "; }

$dept = getIdUnit($con, $param->idsuperior);
$ids=0;
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

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "*";
$Qry->fields    = "id != '".$param->idsuperior."' AND (idsuperior='".$param->idsuperior."' or idunit IN (".$ids.")) ".$search." ORDER BY empname";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){

    while($row=mysqli_fetch_array($rs)){


        $data[] = array( 
            "empID"			       => $row['id'],
            "empname"			   => $row['empname'],
            "pic"			       => $row['pic'],
            "wshift_name"		   => $row['wshift_name'],
            "post"			       => $row['business_unit'],
            "getVwTimesheet"       => getVwTimesheet($con, $row['id'], $date, $date1),
            "getVwTimesheet"       => getVwTimesheet($con, $row['id'], $date, $date1),
            "getTotalApproved"     => getTotalApprovedCS($con, $date, $date1, $row['id']) + getTotalApprovedAA($con, $date, $date1, $row['id']) + getTotalApprovedOT    ($con, $date, $date1,$row['id']) + getTotalApprovedLV($con, $date, $date1,$row['id']) + getTotalApprovedOB($con, $date, $date1,$row['id']),
            "getTotalDeclined"     => getTotalDeclinedCS($con, $date, $date1, $row['id']) + getTotalDeclinedAA($con, $date, $date1, $row['id']) + getTotalDeclinedOT($con, $date, $date1,$row['id']) + getTotalDeclinedLV($con, $date, $date1,$row['id']) + getTotalDeclinedOB($con, $date, $date1,$row['id']),
            "getTotalPending"     => getTotalPendingCS($con, $date, $date1, $row['id']) + getTotalPendingAA($con, $date, $date1, $row['id']) + getTotalPendingOT($con, $date, $date1,$row['id']) + getTotalPendingLV($con, $date, $date1,$row['id']) + getTotalPendingOB($con, $date, $date1,$row['id']),
            "twh"                  => '',
            "totalwh"              => '',
            "totalewh"             => '',
            "totallate"            => '',
            "totalut"              => '',
            "totalabsent"          => '',
            
        
        );
        $return = json_encode($data);
    }
}else{
    // $return = json_encode(array('status'=>'error'));
    $return = json_encode($data);
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
    $Qry->table="vw_data_timesheet";
    $Qry->selected="*";
    $Qry->fields="empID='".$idacct."' AND work_date BETWEEN '".$date."' AND '".$date1."' ORDER BY CONCAT(empID,work_date) ASC";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
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

            if (empty($row['shift_status'])) {
                $shift_fieldid   = "id" . $shift_cols[strtolower('' . date("l", strtotime($row['work_date'])))];
                $shift_field     = "" . $shift_cols[strtolower('' . date("l", strtotime($row['work_date'])))];
                $shift_fieldin   = "" . $shift_cols[strtolower('' . date("l", strtotime($row['work_date'])))] . "_in";
                $shift_fieldout  = "" . $shift_cols[strtolower('' . date("l", strtotime($row['work_date'])))] . "_out";
                $shift_fieldbin  = "" . $shift_cols[strtolower('' . date("l", strtotime($row['work_date'])))] . "_brkin";
                $shift_fieldbout = "" . $shift_cols[strtolower('' . date("l", strtotime($row['work_date'])))] . "_brkout";
                
                $shift_info = getDateShiftDatas($con, $row['empID'], $shift_fieldid, $shift_field, $shift_fieldin, $shift_fieldout, $shift_fieldbin, $shift_fieldbout, $row['work_date']);
                
                $row['shift_status'] = $shift_info[0];
                $row['shiftin']      = $shift_info[1];
                $row['shiftout']     = $shift_info[2];
                $row2['breakin']     = $shift_info[3];
                $row2['breakout']    = $shift_info[4];
                $row['idshift']      = $shift_info[5];
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

            //IN
            // if (strtotime($row['shiftin']) < strtotime($row['in'])) {
            //     // $in1 = "<p class='danger'>" . ($row['in'] ? date("h:i:s A", strtotime($row['in'])) : '') . "</p>";
            //     $in = ($row['in'] ? date("h:i:s A", strtotime($row['in'])) : '');
            //     $late = strtotime($row['in']) - strtotime($row['shiftin']);
                
            // } else {
            //     $in = ($row['in'] ? date("h:i:s A", strtotime($row['in'])) : '');
            //     $late = 0;
            // }

            //OUT
            // if ((strtotime($row['shiftout']) > strtotime($row['out'])) && $row['out'] != '') {

            //     $out = ($row['out'] ? date("h:i:s A", strtotime($row['out'])) : '');
            //     $undertime = strtotime($row['shiftout']) - strtotime($row['out']);
                
            // } else {
            //     // if (((strtotime($row['out']) - strtotime($row['shiftout'])) / 60) >= 60) {
            //     //     $out = "<p class='excdclr'>" . ($row['out'] ? date("h:i:s A", strtotime($row['out'])) : '') . "</p>";
            //     // } else {
            //         $out = ($row['out'] ? date("h:i:s A", strtotime($row['out'])) : '');
            //         $undertime = 0;
            //     // }
            // }

            //TOTAL WORK HOURS
            // if ($row['in'] && $row['out']) {
            //     $twh =  ((strtotime($row['out']) - strtotime($row['in'])) / 3600)  - $break;
            // } else {
            //     $twh = 0;
            // }
            
            //EXCESS WORK HOURS
            // if ($wh < $twh) {
            //     $others = ((strtotime($row['out']) - strtotime($row['shiftin'])) / 3600) - $wh - $break;
            //     if ($others < 0) {
            //         $others = 0;
            //     }
            // } else {
            //     $others = 0;
            // }


            $data[] = array(

                "status" 	          => $status,
                "work_date" 	      => date('D m/d/Y', strtotime($row['work_date'])),
                'shift'               => $row['shift_status'],
                'holiday_idtype'      => $row['holiday_idtype'],
                // "in" 	        => $in,
                // "out" 	        => $out,
                "in" 	              => $row['in']? date('h:i a', strtotime($row['in'])) :'',
                "out" 	              => $row['out']? date('h:i a', strtotime($row['out'])) :'',
                "late" 	              => sprintf('%0.2f', $row['late']),
                'undertime'           => sprintf('%0.2f', $row['ut']),
                'absent'              => sprintf('%0.2f', $row['absent']),
                'work_hours'          => sprintf('%0.2f', $row['reghrs']),
                'total_work_hours'  => sprintf('%0.2f', $row['acthrs']),
                'excess_work_hours'   => sprintf('%0.2f', $row['excess']),
                // 'total_work_hours'    => sprintf('%0.2f', $twh),
                // 'excess_work_hours'   => sprintf('%0.2f', $others),
                
                'getChangeShift'      => getChangeShift($con, $row['work_date'],$row['empID']),
                'getAttendanceAdjust' => getAttendanceAdjust($con, $row['work_date'],$row['empID']),
                'getOvertime'         => getOvertime($con, $row['work_date'],$row['empID']),
                'getTimesheetLeaves'  => getTimesheetLeaves($con, $row['work_date'],$row['empID']),
                'getObTrip'           => getObTrip($con, $row['work_date'],$row['empID']),
                

            );

        }
    }
    return $data;
}
function getDateShiftDatas($con, $idacct, $idshift, $cols, $in, $out, $bin, $bout, $date){
    $data          = array();
    $col           = explode(",", $cols);
    $Qry           = new Query();
    $Qry->table    = "vw_datatimesched";
    $Qry->selected = $cols . ',' . $in . ',' . $out . ',' . $bin . ',' . $bout . ',' . $idshift;
    $Qry->fields   = "idacct='" . $idacct . "' AND date_create <= '" . $date . "' ORDER BY id DESC LIMIT 1";
    $rs            = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1) {
        if ($row = mysqli_fetch_array($rs)) {
            foreach ($col as $key => $v) {
                array_push($data, $row[$col[$key]]);
                array_push($data, $row[$in]);
                array_push($data, $row[$out]);
                array_push($data, $row[$bin]);
                array_push($data, $row[$bout]);
                array_push($data, $row[$idshift]);
            }
        }
    } else {
        $Qry2           = new Query();
        $Qry2->table    = "vw_datatimesched";
        $Qry2->selected = $cols . ',' . $in . ',' . $out . ',' . $bin . ',' . $bout . ',' . $idshift;
        $Qry2->fields   = "idacct='" . $idacct . "' AND date_create >= '" . $date . "' ORDER BY id ASC LIMIT 1";
        $rs2            = $Qry2->exe_SELECT($con);
        if (mysqli_num_rows($rs2) >= 1) {
            if ($row2 = mysqli_fetch_array($rs2)) {
                foreach ($col as $key => $v) {
                    array_push($data, $row2[$col[$key]]);
                    array_push($data, $row2[$in]);
                    array_push($data, $row2[$out]);
                    array_push($data, $row2[$bin]);
                    array_push($data, $row2[$bout]);
                    array_push($data, $row2[$idshift]);
                }
            }
        }
    }
    return $data;
}
function getChangeShift($con, $work_date, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeshift LEFT JOIN tblshift ON tbltimeshift.idshift = tblshift.id";
    $Qry->selected="tbltimeshift.*, tblshift.name";
    $Qry->fields="tbltimeshift.date='".$work_date."' AND tbltimeshift.idacct='".$idacct."' AND tbltimeshift.cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      

             $data = array(
                              
                "status" 	    => $row['stat'],
                "name" 	    => $row['name'],
            );
        }
    }
    return $data;
}
function getAttendanceAdjust($con, $work_date, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeadjustment";
    $Qry->selected="*";
    $Qry->fields="date='".$work_date."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      

            // $stime = strtotime($row['stime']);
            // $ftime = strtotime($row['ftime']);
             
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
    $data= array();
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
    $data= array();
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
//OB
function getObTrip($con, $work_date, $idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="tbltimeobtrip";
    $Qry->selected="*";
    $Qry->fields="date='".$work_date."' AND idacct='".$idacct."' AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){      
 
            $data = array(
                "status"    => $row['stat'],
                "remarks"    => $row['remarks'],
            
            );
        }
    }
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


?>