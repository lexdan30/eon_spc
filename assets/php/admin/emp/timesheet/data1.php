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
    $search = $search . " AND empID = '" . $param['acct'] . "' ";
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
$Qry->table    = "vw_data_timesheet";
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
        
        if (!empty($row['holiday_id'])) {
            $row['shift_status'] = ucwords(strtolower($row['holiday_type'])) . ' Holiday';
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
        
        $today = date("Y-m-d");
        $date  = $row['work_date'];


        $dateot = new DateTime();
        $dateot->modify('+1 day');
        $otallowed = $dateot->format('Y-m-d');

        $datelv = new DateTime();
        $datelv->modify('-1 day');
        $lvallowed = $datelv->format('Y-m-d');
      
        if ($row['shift_status'] == 'Rest Day') {
            $row['shift_status'] = '<p class="csuccess fw9">RD</p>';
            $status              = '<p class="csuccess fw9">N</p>';
        } else {
            $status = '<p class="fw9">W</p>';
        }
        
        if (strtotime($row['shiftin']) < strtotime($row['in'])) {
            $in = "<p class='danger'>" . ($row['in'] ? date("h:i:s A", strtotime($row['in'])) : '') . "</p>";
        } else {
            $in = ($row['in'] ? date("h:i:s A", strtotime($row['in'])) : '');
        }
        
        if (strtotime($row['shiftout']) > strtotime($row['out'])) {
            $out = "<p class='danger'>" . ($row['out'] ? date("h:i:s A", strtotime($row['out'])) : '') . "</p>";
            
        } else {
            if (((strtotime($row['out']) - strtotime($row['shiftout'])) / 60) >= 60) {
                $out = "<p class='danger'>" . ($row['out'] ? date("h:i:s A", strtotime($row['out'])) : '') . "</p>";
            } else {
                $out = ($row['out'] ? date("h:i:s A", strtotime($row['out'])) : '');
            }
        }
        
       
        $Qry2           = new Query();
        $Qry2->table    = "tblshift";
        $Qry2->selected = "*";
        $Qry2->fields   = "id = '" . $row['idshift'] . "' ";
        $rs2            = $Qry2->exe_SELECT($con);
        if (mysqli_num_rows($rs2) >= 1) {
            if ($row2 = mysqli_fetch_array($rs2)) {
                $break = (strtotime($row2['breakout']) - strtotime($row2['breakin'])) / 3600;
            }
        }
        
        $wh = ((strtotime($row['shiftout']) - strtotime($row['shiftin'])) / 3600) - $break;
        
        $check = "<input type='checkbox' value=''>";
        $ob = $check;
        
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
            if ($row['shift_status'] == '<p class="csuccess fw9">RD</p>') {
                $cs = "<input type='checkbox' disabled>";
            } else {
                $cs = "<input type='checkbox' id='cs" . $cid . "' ng-click='changeshift(" . '$event' . "," . $cid . ",\"" . $row['work_date'] . "\"," . $row['idshift'] . ")' >";
            }
        }
        
        if ($row['in'] && $row['out']) {
            $twh = $row['acthrs'] - $break;
        } else {
            $twh = 0;
        }
        
        if ($wh < $twh) {
            $others = ((strtotime($row['out']) - strtotime($row['shiftin'])) / 3600) - $wh - $break;
            if ($others < 0) {
                $others = 0;
            }
        } else {
            $others = 0;
        }


        if ($row['adj_status'] != '') {
            if ($row['adj_status'] == 'PENDING') {
                $pending++;
                if ($row['in'] == '' && $row['out'] == '') {
                    $adaj = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . ($row['shiftin'] ? date("h:i A", strtotime($row['shiftin'])) : '') . ' - ' . ($row['shiftin'] ? date("h:i A", strtotime($row['shiftout'])) : '') . ' <a href="" ng-click="cancelapplication(\'attendance\', ' . $row['aaid'] . ')"><i class="fa fa-times danger" aria-hidden="true"></i></a> </span></div></div>';
                }else if ($row['out'] == '') {
                    $adaj = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . ($row['shiftout'] ? date("h:i A", strtotime($row['shiftout'])) : '') . '<a href="" ng-click="cancelapplication(\'attendance\', ' . $row['aaid'] . ')"><i class="fa fa-times danger" aria-hidden="true"></i> </a></span></div></div>';
                }else if ($row['in'] == '') {
                    $adaj = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . ($row['shiftin'] ? date("h:i A", strtotime($row['shiftin'])) : '') . ' <a href="" ng-click="cancelapplication(\'attendance\', ' . $row['aaid'] . ')"><i class="fa fa-times danger" aria-hidden="true"></i></a> </span></div></div>';
                }
                
            }
            if ($row['adj_status'] == 'DECLINED') {
                if ($row['in'] == '') {
                    $adaj = '<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . ($row['shiftin'] ? date("h:i:s A", strtotime($row['shiftin'])) : '') . '</span></div></div>';
                }
                if ($row['out'] == '') {
                    $adaj = '<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . ($row['shiftout'] ? date("h:i:s A", strtotime($row['shiftout'])) : '') . '</span></div></div>';
                }
            }
            if ($row['adj_status'] == 'APPROVED') {
                if ($row['in'] == '') {
                    $adaj = '<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . ($row['shiftin'] ? date("h:i:s A", strtotime($row['shiftin'])) : '') . '</span></div></div>';
                }
                if ($row['out'] == '') {
                    $adaj = '<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . ($row['shiftout'] ? date("h:i:s A", strtotime($row['shiftout'])) : '') . '</span></div></div>';
                }
            }
        } else {
            if ($row['shift_status'] == '<p class="csuccess fw9">RD</p>') {
                $adaj = "<input type='checkbox' disabled>";
            } else {
                if ($row['in'] == '' || $row['out'] == '') {
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
        

        
        if ($row['idleave']) {
            if ($row['leave_status'] == 'PENDING') {
                $pending++;
                $lv = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $row['leavename'] . ' - ' . $row['leavehrs'] . 'hrs <a href="" ng-click="cancellvapplication(' . $row['lvid'] . ')"><i class="fa fa-times danger" aria-hidden="true"></i></a> </span></div></div>';
            }
            if ($row['leave_status'] == 'DECLINED') {
                $declined++;
            }
            if ($row['leave_status'] == 'APPROVED') {
                $approved++;
            }
            $totalleave = $totalleave + $row['leavehrs'];
        }
        
        if ($row['overtime_status']) {
            if ($row['overtime_status'] == '3') {
                $pending++;
                if( $row['planhrs'] <= 1){
                    $ot = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $row['planhrs'] . ' hr <a href=""  ng-click="cancelapplication(\'overtime\', ' . $row['otid'] . ')"><i class="fa fa-times danger" aria-hidden="true"></i></a> </span></div></div>';
                }else{
                    $ot = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $row['planhrs'] . ' hrs <a href=""  ng-click="cancelapplication(\'overtime\', ' . $row['otid'] . ')"><i class="fa fa-times danger" aria-hidden="true"></i></a> </span></div></div>';
                }
             }
            if ($row['overtime_status'] == '2') {
                $declined++;
                if( $row['planhrs'] <= 1){
                    $ot = '<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $row['planhrs'] . ' hr </span></div></div>';
                }else{
                    $ot = '<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $row['planhrs'] . ' hrs </span></div></div>';
                }
            }
            if ($row['overtime_status'] == '1') {
                $approved++;
                if( $row['planhrs'] <= 1){
                    $ot = '<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $row['planhrs'] . ' hr </span></div></div>';
                }else{
                    $ot = '<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $row['planhrs'] . ' hrs </span></div></div>';
                }
            }
        }
        
        if ($row['changeshift_idshift']) {
            $Qry3           = new Query();
            $Qry3->table    = "tblshift";
            $Qry3->selected = "*";
            $Qry3->fields   = "id = '" . $row['changeshift_idshift'] . "' ";
            $rs3            = $Qry3->exe_SELECT($con);
            if (mysqli_num_rows($rs3) >= 1) {
                if ($row3 = mysqli_fetch_array($rs3)) {
                    $shiftname = $row3['name'];
                }
            }
            if ($row['changeshift_stat'] == '3') {
                $pending++;
                $cs = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $shiftname . '<a href="" ng-click="cancelapplication(\'changeshift\', ' . $row['csid'] . ')"><i class="fa fa-times danger" aria-hidden="true"></i></a> </span></div></div>';
            }
            if ($row['changeshift_stat'] == '2') {
                $declined++;
                $cs = '<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $shiftname . '</span></div></div>';
            }
            if ($row['changeshift_stat'] == '1') {
                $approved++;
                $cs = '<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . $shiftname . '</span></div></div>';
            }
        }
        
        $cid++;
        
        if (strtotime($row['shiftout']) > strtotime($row['out'])) {
            if ($row['in'] && $row['out']) {
                $ut = ((strtotime($row['shiftout']) - strtotime($row['out'])) / 3600);
            } else {
                $ut = 0;
            }
        } else {
            $ut = 0;
        }

        if ($row['shift_status'] == '<p class="csuccess fw9">RD</p>') {
            $cs = "<input type='checkbox' disabled>";
        } else {
            if (!$row['in'] || !$row['out']) {
                if($row['adj_status'] == ''){
                    //$row['absent'] =  ((strtotime($row['shiftout']) - strtotime($row['shiftin'])) / 3600)  - $break;
                }
               
            } 
        }


        $action = '<i ng-click="multi()" class="fa fa-plus-square cgreen" aria-hidden="true" style="cursor : pointer;"></i>';
        

        $totalwh        = $totalwh + $twh;
        $totalewh       = $totalewh + $others;
        $totallates     = $totallates + $row['late'];
        $totalut        = $totalut + $ut;
        $totalot        = $totalot + $row['planhrs'];
        $totalabs        = $totalabs + $row['absent'];
        $workhourstotal = $workhourstotal + $row['reghrs'];//$wh;
        $data["data"][] = array(
            'status' => $status,
            'shift' => $row['shift_status'],
            'date' => date('D m/d/Y', strtotime($row['work_date'])),
            'day' => date('l', strtotime($row['work_date'])),
            'in' => $in,
            'wh' => sprintf('%0.2f', $row['reghrs']),//$wh,
            'out' => $out,
            'late' => sprintf('%0.2f', $row['late']),
            'ut' => sprintf('%0.2f', $row['ut']),
            'absent' => sprintf('%0.2f', $row['absent']),
            'lv' => $lv,
            'cs' => $cs,
            'adaj' => $adaj,
            'ot' => $ot,
            'ob' => $ob,
            'reghrs' => sprintf('%0.2f', $twh),
            'acthrs' => sprintf('%0.2f', $row['acthrs']),
            'othrs' => sprintf('%0.2f', $others),
            'action' => $action,
            'ut' => sprintf('%0.2f', $ut),
            
            'total' => getSumRows($con, $where),
            'lvtotalhrs' => $totalleave,
            'totalwh' => sprintf('%0.2f', $totalwh),
            'totalewh' => sprintf('%0.2f', $totalewh),
            'totallates' => sprintf('%0.2f', $totallates),
            'totalut' => sprintf('%0.2f', $totalut),
            'totalot' => sprintf('%0.2f', $totalot),
            'totalabs' => sprintf('%0.2f', $totalabs),
            'workhourstotal' => sprintf('%0.2f', $workhourstotal),
            'pending' => $pending,
            'declined' => $declined,
            'approved' => $approved
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

function getTotalRows($con, $search)
{
    $Qry           = new Query();
    $Qry->table    = "vw_data_timesheet";
    $Qry->selected = "*";
    $Qry->fields   = "work_date is not null " . $search;
    $rs            = $Qry->exe_SELECT($con);
    return mysqli_num_rows($rs);
}

function getDateShiftDatas($con, $idacct, $idshift, $cols, $in, $out, $bin, $bout, $date)
{
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
                    array_push($data, $row[$in]);
                    array_push($data, $row[$out]);
                    array_push($data, $row[$bin]);
                    array_push($data, $row[$bout]);
                    array_push($data, $row[$idshift]);
                }
            }
        }
    }
    return $data;
}
?>