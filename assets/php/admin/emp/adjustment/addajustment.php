<?php
require_once('../../../activation.php');
$conn = new connector();
$con  = $conn->connect();
require_once('../../../classPhp.php');
session_start();


$param       = $_POST;
$date_create = SysDate();
$time_create = SysTime();
$return      = null;

if (array_key_exists('file', $_FILES)) {
    $valid_formats = array(
        "jpg",
        "png",
        "jpeg",
        "pdf"
    );
    foreach ($_FILES['file']['name'] as $f => $name) {
        if ($_FILES['file']['error'][$f] == 4) {
            $return = json_encode(array(
                'status' => 'error',
                'on' => 'img_check'
            ));
            print $return;
            mysql_close($con);
            return;
        }
        if ($_FILES['file']['error'][$f] == 0) {
            if (!in_array(pathinfo(strtolower($name), PATHINFO_EXTENSION), $valid_formats)) {
                $return = json_encode(array(
                    'status' => 'error-upload-type'
                ));
                print $return;
                mysql_close($con);
                return;
            }
        }
    }
}

$idacct      = $param['accountid'];
$remarks     = ucwords(strtolower(str_replace("'", "", $param['remarks'])));
$reject      = array();
$ctr         = 1;
$fileuploads = 0;
foreach ($param['info']['leave_dates'] as $keys => $value) {
    $idshift         = $value['idshift'];
    $idtimeleavetype = $value['val'];
    $date            = $value['date'];
    
    
    if (adjustmentExists($con, $date, $idacct, $idtimeleavetype)) {
        $reject[] = array(
            "date" => $date,
            "msg" => "Already has adjustment application for this date."
        );
    } elseif (hasTimelogs($con, $date, $idacct)) {
        $reject[] = array(
            "date" => $date,
            "msg" => "Time logs exists."
        );
    } elseif (hasLeave($con, $date, $idacct, $idtimeleavetype)) {
        $reject[] = array(
            "date" => $date,
            "msg" => "Applied Leave."
        );
    } elseif ((int) $value['hrs'] > 0) {
        
        $hrs       = (int) $value['hrs'];
        $shifttime = getShiftTime($con, $idshift, $idtimeleavetype);
        $stime     = $shifttime['stime'];
        $ftime     = $shifttime['ftime'];
        $time      = time();
        $docnumber = "AA" . $param['info']['acct'] . strtotime($date_create . $time) . $time . $ctr;
        $ctr++;
        $Qry           = new Query();
        $Qry->table    = "tbltimeadjustment";
        $Qry->selected = "creator, 
                            docnumber, 
                            idacct, 
                            idshift, 
                            idtimetype,
                            date, 
                            stime, 
                            ftime, 
                            hrs,
                            date_create, 
                            id_payperiod";
        $id_period     = getTimesheetPayPeriods($con, $date);
        if ((int) $id_period == 0) {
            $id_period = getLatePayPeriod($con, $date);
        }

        $Qry->fields = "'" . $param['accountid'] . "', 
                        '" . $docnumber . "',
                         '" . $idacct . "',
                        '" . $idshift . "', 
                        '" . $idtimeleavetype . "', 
                        '" . $date . "', 
                        '" . $stime . "', 
                        '" . $ftime . "', 
                        '" . $hrs . "', 
                        '" . $date_create . "',
                        '" . $id_period['id'] . "'";
        if (!empty($remarks)) {
            $Qry->selected = $Qry->selected . ", remarks";
            $Qry->fields   = $Qry->fields . ", '" . $remarks . "'";
        }
        $checke = $Qry->exe_INSERT($con);
        if ($checke) {
            $lastID = getLastID($con, $docnumber);
            if (array_key_exists('file', $_FILES)) {
                if ($fileuploads != 0) {
                    $folder = $_SESSION['foldername'];
                    updateData($con, $folder, $lastID);
                } else {
                    $folder_path = $param['targetPath'] . 'req-' . $lastID;
                    $folder_name = 'req-' . $lastID;
                    if (!is_dir($folder_path)) {
                        mkdir($folder_path);
                    }
                    $_SESSION['foldername'] = $folder_name;
                    $check                  = updateData($con, $folder_name, $lastID);
                    if ($check) {
                        $fileCtr = 1;
                        foreach ($_FILES['file']['name'] as $f => $name) {
                            $t         = strtotime($date_create) . time();
                            $extMove   = pathinfo($name, PATHINFO_EXTENSION);
                            $save_name = $fileCtr . '-' . $t . '.' . $extMove;
                            move_uploaded_file($_FILES["file"]["tmp_name"][$f], $folder_path . '/' . $save_name);
                            $fileCtr++;
                        }
                    }
                }
            }
        }
    }
    $fileuploads++;
}


// AUTO EMAIL ??

$return = json_encode(array(
    'status' => 'success',
    'reject' => $reject
));

function hasTimelogs($con, $date, $idacct)
{
    $Qry           = new Query();
    $Qry->table    = "vw_data_timesheet AS a";
    $Qry->selected = "id";
    $Qry->fields   = " a.idacct='" . $idacct . "' AND a.work_date = '" . $date . "' AND ( a.in IS NOT NULL AND a.out IS NOT NULL AND a.in<>'' AND a.out<>'' )";
    $rs            = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1) {
        return true;
    }
    return false;
}

function adjustmentExists($con, $date, $idacct, $idtimeleavetype)
{
    $Qry           = new Query();
    $Qry->table    = "tbltimeadjustment";
    $Qry->selected = "*";
    $Qry->fields   = " idacct='" . $idacct . "' AND date='" . $date . "'  AND stat in ('1','3') AND cancelby IS null";
    $rs            = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1) {
        while ($row = mysqli_fetch_array($rs)) {
            if ($row['idtimetype'] == $idtimeleavetype) {
                return true;
            } elseif ((int) $row['idtimetype'] == 1 && ((int) $idtimeleavetype == 2 || (int) $idtimeleavetype == 3)) {
                return true;
            } elseif ((int) $idtimeleavetype == 1 && ((int) $row['idtimetype'] == 2 || (int) $row['idtimetype'] == 3)) {
                return true;
            }
        }
    }
    return false;
}

function hasLeave($con, $date, $idacct, $idtimeleavetype)
{
    $Qry           = new Query();
    $Qry->table    = "tbltimeleaves";
    $Qry->selected = "*";
    $Qry->fields   = " idacct='" . $idacct . "' AND date='" . $date . "' AND stat in ('1','3') ";
    $rs            = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1) {
        while ($row = mysqli_fetch_array($rs)) {
            if ((int) $row['idtimeleavetype'] == 1) {
                return true;
            } elseif ((int) $row['idtimeleavetype'] <= $idtimeleavetype) {
                return true;
            } elseif ((int) $row['idtimeleavetype'] == 3 && ((int) $idtimeleavetype == 1 || (int) $idtimeleavetype == 3)) {
                return true;
            }
        }
    }
    return false;
}

function updateData($con, $folder_name, $lastID)
{
    $Qry           = new Query();
    $Qry->table    = "tbltimeadjustment";
    $Qry->selected = "file='" . $folder_name . "'";
    $Qry->fields   = "id='" . $lastID . "'";
    return $Qry->exe_UPDATE($con);
}

function getLastID($con, $ticket)
{
    $return        = '';
    $Qry           = new Query();
    $Qry->table    = "tbltimeadjustment";
    $Qry->selected = "id";
    $Qry->fields   = "docnumber='" . $ticket . "' ORDER BY id DESC LIMIT 1";
    $rs            = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1) {
        if ($row = mysqli_fetch_array($rs)) {
            $return = $row['id'];
        }
    } else {
        $return = '';
    }
    return $return;
}

function getTimesheetPayPeriods( $con, $date ){
    $data = array();	
    $Qry = new Query();	
    $Qry->table     = "tbltimesheet";
    $Qry->selected  = "id_payperiod";
    $Qry->fields    = "date='".$date."' ORDER BY id ASC limit 1";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
           
            $data = array( 
                "id"        => $row['id_payperiod']
            );

            return $data;
        }
    }
    return 0;
}