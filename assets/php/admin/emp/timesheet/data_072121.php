<?php
require_once('../../../activation.php');
$conn = new connector();
$con  = $conn->connect();
require_once('../../../classPhp.php');

$param  = $_POST;
$return = null;

$shift_cols = array(
    "monday" => "mon",
    "tuesday" => "tue",
    "wednesday" => "wed",
    "thursday" => "thu",
    "friday" => "fri",
    "saturday" => "sat",
    "sunday" => "sun"
);
$search     = '';


if (!empty($param['acct'])) {
    $search = $search . " AND id = '" . $param['acct'] . "' ";
}
if (!empty($param['dfrom']) && empty($param['dto'])) {
    $search = $search . " AND work_date =DATE('" . $param['dfrom'] . "') ";
}
if (!empty($param['dfrom']) && !empty($param['dto'])) {
    $search = $search . " AND work_date BETWEEN DATE('" . $param['dfrom'] . "') AND DATE('" . $param['dto'] . "') ";
}

$where = $search;



if (empty($param['acct']) || empty($param['dfrom'])) {
    $data   = array(
        "draw" => $param['draw'],
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => array(),
        "qry" => ''
    );
    $return = json_encode($data);
    print $return;
    mysqli_close($con);
    return;
}

$search = $search . " ORDER BY work_date ASC ";

if ($param['length'] != '') {
    $search = $search . " LIMIT " . $param['length'];
}
if ($param['start'] != '') {
    $search = $search . " OFFSET " . $param['start'];
}

$Qry           = new Query();
$Qry->table    = "vw_timesheet";
$Qry->selected = "*";
$Qry->fields   = "work_date is not null " . $search . "";
$rs            = $Qry->exe_SELECT($con);

$recFiltered = getTotalRows($con, $where);
if (mysqli_num_rows($rs) >= 1) {
    $data = array(
        "draw" => $param['draw'],
        "recordsTotal" => mysqli_num_rows($rs),
        "recordsFiltered" => $recFiltered,
        "qry" => $Qry->fields,
        "data" => array()
    );
    
    $pending    = 0;
    $declined   = 0;
    $approved   = 0;
    $totalleave = 0;
    $totalwh    = 0;
    $totalewh   = 0;
    $totallates = 0;
    $totalut    = 0;
    $totalot    = 0;
    $totalabs   = 0;
    $cid        = 0;
    $disable    = '';
    $workhourstotal = 0;
    while ($row = mysqli_fetch_array($rs)) {
        $row['name']   = $row['defaultsched'];



        if (!empty($row['holiday'])) {
            $row['name'] = '<p style="color:red; font-weight: 900;">'. ($row['alias']) . 'H ' . $row['defaultsched'] . '</p>';
        }

        // if ($row['type']==1) {
        //     $row['name'] = '<p style="color:red; font-weight: 900;"> LH' .'</p>';
        // }

        // if ($row['type']==2) {
        //     $row['name'] = '<p style="color:red; font-weight: 900;"> SH' .'</p>';
        // }
        // if ($row['type']==3) {
        //     $row['name'] = '<p style="color:red; font-weight: 900;"> LSH' .'</p>';
        // }
        
        // if (empty($row['name'])) {
        //     $shift_fieldid   = "id" . $shift_cols[strtolower('' . date("l", strtotime($row['work_date'])))];
        //     $shift_field     = "" . $shift_cols[strtolower('' . date("l", strtotime($row['work_date'])))];
        //     $shift_fieldin   = "" . $shift_cols[strtolower('' . date("l", strtotime($row['work_date'])))] . "_in";
        //     $shift_fieldout  = "" . $shift_cols[strtolower('' . date("l", strtotime($row['work_date'])))] . "_out";
        //     $shift_fieldb  = "" . $shift_cols[strtolower('' . date("l", strtotime($row['work_date'])))] . "_brk";
            
        //     $shift_info = getDateShiftDatas($con, $row['idacct'], $shift_fieldid, $shift_field, $shift_fieldin, $shift_fieldout, $shift_fieldb, $row['work_date']);
            
        //     $row['name'] = $shift_info[0];
        //     $row['shiftin']      = $shift_info[1];
        //     $row['shiftout']     = $shift_info[2];
        //     $row2['break']     = $shift_info[3];
        //     $row['idshift']      = $shift_info[4];
        // }
        
        $today = date("Y-m-d");
        $date  = $row['work_date'];


        $dateot = new DateTime();
        $dateot->modify('+1 day');
        $otallowed = $dateot->format('Y-m-d');

        $datelv = new DateTime();
        $datelv->modify('-1 day');
        $lvallowed = $datelv->format('Y-m-d');

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

        if ($row['defaultschedid'] == 4) {
            $row['name'] = '<p class="csuccess fw9">'. $row['name'] . '</p>';
        }
      

        // if (strtotime($row['shiftin']) < strtotime($row['in'])) {
        //     $in = "<p class='danger'>" . ($row['in'] ? date("h:i A", strtotime($row['in'])) : '') . "</p>";
        // } else {
        //     $in = ($row['in'] ? date("h:i A", strtotime($row['in'])) : '');
        // }
        
        // if (strtotime($row['shiftout']) > strtotime($row['out'])) {
        //     $out = "<p class='danger'>" . ($row['out'] ? date("h:i A", strtotime($row['out'])) : '') . "</p>";
            
        // } else {
        //     $out = ($row['out'] ? date("h:i A", strtotime($row['out'])) : '');
        // }
        
       
     
        $wh = $row['acthrs'];
        
    
        
        if ($date == $today || $date == $otallowed) {
            $ot = "<input type='checkbox' id='ot" . $cid . "' ng-click='overtime(" . '$event' . "," . $cid . ",\"" . $row['work_date'] . "\"," . $row['idshift'] . ")' >";
        } else {
            $ot = "<input type='checkbox' disabled>";
        }
        
          
        if ($date >= $lvallowed) {
            $lv = "<input type='checkbox' id='lv" . $cid . "' ng-click='leaves(" . '$event' . "," . $cid . ",\"" . $row['work_date'] . "\"," . $row['idshift'] . ")' >";
        } else {
            $lv = "<input type='checkbox' disabled>";
        }
        
        
        if ($date < $today) {
            $cs = "<input type='checkbox' disabled>";
        } else {
            if ($row['name'] == '<p class="csuccess fw9">RD</p>') {
                $cs = "<input type='checkbox' disabled>";
            } else {
                $cs = "<input type='checkbox' id='cs" . $cid . "' ng-click='changeshift(" . '$event' . "," . $cid . ",\"" . $row['work_date'] . "\"," . $row['idshift'] . ")' >";
            }
        }
        
        // if ($row['in'] && $row['out']) {
        //     $twh =  ((strtotime($row['out']) - strtotime($row['in'])) / 3600)  - $break;
        // } else {
        //     $twh = 0;
        // }
        
        // if ($wh < $twh) {
        //     $others = ((strtotime($row['out']) - strtotime($row['shiftin'])) / 3600) - $wh - $break;
        //     if ($others < 0) {
        //         $others = 0;
        //     }
        // } else {
        //     $others = 0;
        // }


        if ($row['aastatus'] != '') {
            if ($row['aastatus'] == '3') {
                $pending++;
                if($row['shifttype'] == 'Regular Schedule'){
                    if ($row['timein'] == '' && $row['timeout'] == '') {
                        $adaj = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . ($row['aastime'] ? date("h:i A", strtotime($row['aastime'])) : '') . ' - ' . ($row['aaftime'] ? date("h:i A", strtotime($row['aaftime'])) : '') . ' <a href="" ng-click="cancelapplication(\'attendance\', ' . $row['aaid'] . ')"><i class="fa fa-times danger" aria-hidden="true"></i></a> </span></div></div>';
                    }else if ($row['timeout'] == '') {
                        $adaj = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>OUT ' . ($row['aaftime'] ? date("h:i A", strtotime($row['aaftime'])) : '') . '<a href="" ng-click="cancelapplication(\'attendance\', ' . $row['aaid'] . ')"><i class="fa fa-times danger" aria-hidden="true"></i> </a></span></div></div>';
                    }else if ($row['timein'] == '') {
                        $adaj = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . ($row['aastime'] ? date("h:i A", strtotime($row['aastime'])) : '') . ' <a href="" ng-click="cancelapplication(\'attendance\', ' . $row['aaid'] . ')"><i class="fa fa-times danger" aria-hidden="true"></i></a> </span></div></div>';
                    }
                }else{
                    if ($row['timein'] == '') {
                        $in =  'IN '. ($row['aastime'] ? date("h:i A", strtotime($row['aastime'])) : '') .'<br>'  ;
                    }else{
                        $in =  '';
                    }

                    if ($row['timeout'] == '') {
                        $out =  'OUT '. ($row['aaftime'] ? date("h:i A", strtotime($row['aaftime'])) : '') .'<br>'  ;
                    }else{
                        $out =  '';
                    }

                    if ($row['timein2'] == '') {
                        $in2 =  'IN2 '. ($row['aasstime'] ? date("h:i A", strtotime($row['aasstime'])) : '').'<br>'  ;
                    }else{
                        $in2 =  '';
                    }

                    if ($row['timeout2'] == '') {
                        $out2 =  'OUT2 '. ($row['aasftime'] ? date("h:i A", strtotime($row['aasftime'])) : '');
                    }else{
                        $out2 =  '';
                    }
                    
                    if($in != '' || $out != ''  || $in2 != ''  || $out2 != ''){
                        $adaj = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>'
                        . $in . '' . $out . '' . $in2 . '' . $out2 . 
                        ' <a href="" ng-click="cancelapplication(\'attendance\', ' . $row['aaid'] . ')"><i class="fa fa-times danger" aria-hidden="true"></i></a> </span></div></div>';
                
                    }
                 
                       
                }

            }
             if ($row['aastatus'] == '2') {
                $declined++;
                if($row['shifttype'] == 'Regular Schedule'){
                    if ($row['timein'] == '') {
                        $in =  'IN '. ($row['aastime'] ? date("h:i A", strtotime($row['aastime'])) : '') .'<br>'  ;
                    }else{
                        $in =  '';
                    }

                    if ($row['timeout'] == '') {
                        $out =  'OUT '. ($row['aaftime'] ? date("h:i A", strtotime($row['aaftime'])) : '') .'<br>'  ;
                    }else{
                        $out =  '';
                    }

                    if($in != '' || $out != ''){
                        $adaj = '<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' 
                        . $in . '' . $out .
                        '</span></div></div>';
                    }
                }else{
                    if ($row['timein'] == '') {
                        $in =  'IN '. ($row['aastime'] ? date("h:i A", strtotime($row['aastime'])) : '') .'<br>'  ;
                    }else{
                        $in =  '';
                    }

                    if ($row['timeout'] == '') {
                        $out =  'OUT '. ($row['aaftime'] ? date("h:i A", strtotime($row['aaftime'])) : '') .'<br>'  ;
                    }else{
                        $out =  '';
                    }

                    if ($row['timein2'] == '') {
                        $in2 =  'IN2 '. ($row['aasstime'] ? date("h:i A", strtotime($row['aasstime'])) : '').'<br>'  ;
                    }else{
                        $in2 =  '';
                    }

                    if ($row['timeout2'] == '') {
                        $out2 =  'OUT2 '. ($row['aasftime'] ? date("h:i A", strtotime($row['aasftime'])) : '');
                    }else{
                        $out2 =  '';
                    }
                    
                    if($in != '' || $out != ''  || $in2 != ''  || $out2 != ''){
                        $adaj = '<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' 
                        . $in . '' . $out . '' . $in2 . '' . $out2 . 
                        '</span></div></div>';
                    }

                }
            }
            if ($row['aastatus'] == '1') {
                $approved++;
                if($row['shifttype'] == 'Regular Schedule'){
                    if ($row['timein'] == '') {
                        $in =  'IN '. ($row['aastime'] ? date("h:i A", strtotime($row['aastime'])) : '') .'<br>'  ;
                    }else{
                        $in =  '';
                    }

                    if ($row['timeout'] == '') {
                        $out =  'OUT '. ($row['aaftime'] ? date("h:i A", strtotime($row['aaftime'])) : '') .'<br>'  ;
                    }else{
                        $out =  '';
                    }

                 
                    
                    if($in != '' || $out != ''){
                        $adaj = '<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' 
                        . $in . '' . $out . 
                        '</span></div></div>';
                    }
                }else{
                    if ($row['timein'] == '') {
                        $in =  'IN '. ($row['aastime'] ? date("h:i A", strtotime($row['aastime'])) : '') .'<br>'  ;
                    }else{
                        $in =  '';
                    }

                    if ($row['timeout'] == '') {
                        $out =  'OUT '. ($row['aaftime'] ? date("h:i A", strtotime($row['aaftime'])) : '') .'<br>'  ;
                    }else{
                        $out =  '';
                    }

                    if ($row['timein2'] == '') {
                        $in2 =  'IN2 '. ($row['aasstime'] ? date("h:i A", strtotime($row['aasstime'])) : '').'<br>'  ;
                    }else{
                        $in2 =  '';
                    }

                    if ($row['timeout2'] == '') {
                        $out2 =  'OUT2 '. ($row['aasftime'] ? date("h:i A", strtotime($row['aasftime'])) : '');
                    }else{
                        $out2 =  '';
                    }
                    
                    if($in != '' || $out != ''  || $in2 != ''  || $out2 != ''){
                        $adaj = '<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' 
                        . $in . '' . $out . '' . $in2 . '' . $out2 . 
                        '</span></div></div>';
                    }
                }
            }
        } else {
            if ($row['name'] == '<p class="csuccess fw9">RD</p>') {
                $adaj = "<input type='checkbox' disabled>";
            } else {
                if ($row['timein'] == '' || $row['timeout'] == '') {
                    if ($date < $today) {
                        $adaj = "<input type='checkbox' id='adaj" . $cid . "' ng-click='adaj(" . '$event' . "," . $cid . ",\"" . $row['work_date'] . "\"," . $row['idshift'] . ")'>";
                    } else {
                        $adaj = "<input type='checkbox' )' disabled>";
                    }
                } else {
                    $adaj = "<input type='checkbox' disabled>";
                }
            }
        }
        
        $ob = "<input type='checkbox' disabled>";
        if ($row['obtrip']) {
            if ($row['obtripstatus'] == '3') {
                $pending++;
                $ob = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span> ' 
                . $row['obtrip'] .
                '<br>'
                .  date("h:i A", strtotime($row['obstart'])) .
                ' - '
                .  date("h:i A", strtotime($row['obend'])) .
                 '<a href="" ng-click="cancelapplication(\'obtrip\', ' . $row['aaid'] . ')"><i class="fa fa-times danger" aria-hidden="true"></i> </a> </span></div></div>';
            }
            if ($row['obtripstatus'] == '2') {
                $declined++;
                $ob = '<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span> ' 
                . $row['obtrip']  .
                '<br>'
                .  date("h:i A", strtotime($row['obstart'])) .
                ' - '
                .  date("h:i A", strtotime($row['obend'])) .
                 '</span></div></div>';
            }
            if ($row['obtripstatus'] == '1') {
                $approved++;
                $ob = '<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span> ' 
                . $row['obtrip'] .
                '<br>'
                .  date("h:i A", strtotime($row['obstart'])) .
                ' - '
                .  date("h:i A", strtotime($row['obend'])) .
                 '</span></div></div>';
            }
        }
        
        if ($row['leaveidapp']) {
            if ($row['leaveappstatus'] == '3') {
                $pending++;
                $lv = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $row['leavename'] . ' - ' . $row['leave'] . 'hrs <a href="" ng-click="cancellvapplication(' . $row['leaveidapp'] . ')"><i class="fa fa-times danger" aria-hidden="true"></i></a> </span></div></div>';
            }
            if ($row['leaveappstatus'] == '2') {
                $lv = '<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $row['leavename'] . ' - ' . $row['leave'] . 'hrs </span></div></div>';
            
                $declined++;
            }
            if ($row['leaveappstatus'] == '1') {
                $lv = '<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $row['leavename'] . ' - ' . $row['leave'] . 'hrs </span></div></div>';
            
                $approved++;
            }
            $totalleave = $totalleave + $row['leave'];
        }
        
        if ($row['otid']) {
            if ($row['otstatus'] == '3') {
                $pending++;
                if( $row['othrs'] <= 1){
                    $ot = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $row['othrs'] . ' hr <a href=""  ng-click="cancelapplication(\'overtime\', ' . $row['otid'] . ')"><i class="fa fa-times danger" aria-hidden="true"></i></a> </span></div></div>';
                }else{
                    $ot = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $row['othrs'] . ' hrs <a href=""  ng-click="cancelapplication(\'overtime\', ' . $row['otid'] . ')"><i class="fa fa-times danger" aria-hidden="true"></i></a> </span></div></div>';
                }
             }
            if ($row['otstatus'] == '2') {
                $declined++;
                if( $row['othrs'] <= 1){
                    $ot = '<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $row['othrs'] . ' hr </span></div></div>';
                }else{
                    $ot = '<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $row['othrs'] . ' hrs </span></div></div>';
                }
            }
            if ($row['otstatus'] == '1') {
                $approved++;
                if( $row['othrs'] <= 1){
                    $ot = '<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $row['othrs'] . ' hr </span></div></div>';
                }else{
                    $ot = '<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $row['othrs'] . ' hrs </span></div></div>';
                }
            }
        }
        
        if ($row['cs']) {
            $Qry3           = new Query();
            $Qry3->table    = "tblshift";
            $Qry3->selected = "*";
            $Qry3->fields   = "id = '" . $row['cs'] . "' ";
            $rs3            = $Qry3->exe_SELECT($con);
            if (mysqli_num_rows($rs3) >= 1) {
                if ($row3 = mysqli_fetch_array($rs3)) {
                    $shiftname = $row3['name'];
                }
            }
            if ($row['csstatus'] == '3') {
                $pending++;
                $cs = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $shiftname . '<a href="" ng-click="cancelapplication(\'changeshift\', ' . $row['csid'] . ')"><i class="fa fa-times danger" aria-hidden="true"></i></a> </span></div></div>';
            }
            if ($row['csstatus'] == '2') {
                $declined++;
                $cs = '<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $shiftname . '</span></div></div>';
            }
            if ($row['csstatus'] == '1') {
                $approved++;
                $cs = '<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $shiftname . '</span></div></div>';
            }
        }
        
        
        $cid++;
        
        // if (strtotime($row['shiftout']) > strtotime($row['out'])) {
        //     if ($row['in'] && $row['out']) {
        //         $ut = ((strtotime($row['shiftout']) - strtotime($row['out'])) / 3600);
        //     } else {
        //         $ut = 0;
        //     }
        // } else {
        //     $ut = 0;
        // }

        if ($row['name'] == '<p class="csuccess fw9">RD</p>') {
          
        } else {
            if (!$row['timein'] || !$row['timeout']) {
                if($row['aastatus'] == ''){
                    //$row['absent'] =  ((strtotime($row['shiftout']) - strtotime($row['shiftin'])) / 3600)  - $break;
                }
               
            } 
        }


        $action = '<i ng-click="multi()" class="fa fa-plus-square cgreen" aria-hidden="true" style="cursor : pointer;"></i>';
        
        
        // if ($row['name'] == 'Special Holiday') {
        //     $row['name'] = '<p class="cdanger fw9">SH</p>';
        // }
        if($row['shifttype'] == 'Regular Schedule'){
            if($row['facthrs'] - $row['fshift'] > 0){
                $excess = $row['facthrs'] - $row['fshift'];
            }else{
                $excess = 0;
            }
        }else{
            if($row['facthrs'] - $row['fshift'] > 0){
                $excess = $row['facthrs'] - $row['fshift'];
            }else{
                $excess = 0;
            }

            if($row['sacthrs'] - $row['sshift'] > 0){
                $excess = $excess + $row['sacthrs'] - $row['sshift'];
            }else{
                $excess = $excess +  0;
            }
        }


   

        // $totalwh        = $totalwh + $twh;
        $totalwh        = $totalwh + $row['acthrs'];
        // $totalewh       = $totalewh + $others;
        $totalewh       = $totalewh + sprintf('%0.2f', $excess);
        $totallates     = $totallates + $row['late'];
        // $totalut        = $totalut + $ut;
        $totalut        = $totalut + sprintf('%0.2f', $row['ut']);
        
        $totalot        = $totalot + $row['othrs'];
        $totalabs        = $totalabs + $row['absent'];
        $workhourstotal = $workhourstotal + $row['shifthrs'];//$wh;
        $data["data"][] = array(
            'status'    => $status,
            'shift'     => $row['name'],
            'temp'     => $row['temp'],
            'csstatus'    => $row['csstatus'],
            'holiday_idtype' => $row['holidaytype'],
            'idleave' => $row['leaveidapp'],
            'date'      => date('D m/d/Y', strtotime($row['work_date'])),
            'day'       => date('l', strtotime($row['work_date'])),
            // 'in' => $in,
            // 'out' => $out,
            'in'	    => $row['timein']? date('h:i a', strtotime($row['timein'])) :'',
            'out' 	    => $row['timeout']? date('h:i a', strtotime($row['timeout'])) :'',
            'in2'	    => $row['timein2']? date('h:i a', strtotime($row['timein2'])) :'',
            'out2' 	    => $row['timeout2']? date('h:i a', strtotime($row['timeout2'])) :'',
            'wh'        => sprintf('%0.2f', $row['shifthrs']),            
            'late'      => sprintf('%0.2f', $row['late']),
            'ut'        => sprintf('%0.2f', $row['ut']),
            'absent'    => sprintf('%0.2f', $row['absent']),
            'np'        => sprintf('%0.2f', $row['np']),
            'npot'    => sprintf('%0.2f', $row['npot']),
            'np'        => '1',
            'lv'        => $lv,
            'cs'        => $cs,
            'adaj'      => $adaj,
            'ot'        => $ot,
            'ob'        => $ob,
            // 'reghrs' => sprintf('%0.2f', $twh),
            'reghrs'    => sprintf('%0.2f', $row['shifthrs']),
            'acthrs'    => sprintf('%0.2f', $row['acthrs']),


            'fshift'    => sprintf('%0.2f', $row['fshift']),
            'sshift'    => sprintf('%0.2f', $row['sshift']),
            'facthrs'    => sprintf('%0.2f', $row['facthrs']),
            'sacthrs'    => sprintf('%0.2f', $row['sacthrs']),



            // 'othrs' => sprintf('%0.2f', $others),
            'othrs'     => sprintf('%0.2f', $excess ),
            'action'    => $action,
            // 'ut' => sprintf('%0.2f', $ut),
            //'total'      => getTotalRows($con, $where),
            'lvtotalhrs' => $totalleave,
            'totalwh'   => sprintf('%0.2f', $totalwh),
            'totalewh'   => sprintf('%0.2f', $totalewh),
            'totallates' => sprintf('%0.2f', $totallates),
            'totalut'   => sprintf('%0.2f', $totalut),
            'totalot'   => sprintf('%0.2f', $totalot),
            'totalabs'  => sprintf('%0.2f', $totalabs),
            'workhourstotal' => sprintf('%0.2f', $workhourstotal),
            'pending'   => $pending,
            'declined'  => $declined,
            'approved'  => $approved
        );
    }
    $return = json_encode($data);
} else {
    $data   = array(
        "draw" => $param['draw'],
        "recordsTotal" => mysqli_num_rows($rs),
        "recordsFiltered" => mysqli_num_rows($rs),
        "data" => array(),
        "qry" => $Qry->fields
    );
    $return = json_encode($data);
}

print $return;
mysqli_close($con);

function getTotalRows($con, $where)
{
    $Qry           = new Query();
    $Qry->table    = "vw_timesheet";
    $Qry->selected = "*";
    $Qry->fields   = "work_date is not null " . $where . "";
    $rs            = $Qry->exe_SELECT($con);
    return mysqli_num_rows($rs);
}

function getDateShiftDatas($con, $idacct, $idshift, $cols, $in, $out, $b, $date)
{
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
?>