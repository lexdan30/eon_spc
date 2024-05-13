<?php

require_once ('../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once ('../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));
$date_create = SysDate();
$time_create = SysTime();
$return = null;

if ($param->button == 'save')
{
    foreach ($param->employee as $key => $value)
    {
        $ctr = 1;
        foreach ($value->sched as $key2 => $value2)
        {
            $newsched = $value2->drpndng;
            if ($newsched){
                if (!checkIfHasPending($con, $value->id, $value2->work_date)){
                    $remarks = 'New schedule from ' . $value2->shift_status . ' to ' . getIdShift($con, $value2->drpndng);

                    $time = time();
                    $docnumber = "SS" . $value->id . strtotime($date_create . $time) . $time . $ctr;
                    $ctr++;

                    $id_payperiod = getIdPayPeriod($con, $value2->work_date);

                    $Qry = new Query();
                    $Qry->table = "tbldutyroster";
                    $Qry->selected = "docnumber, 
                                            unit, 
                                            creator, 
                                            idacct, 
                                            idshift, 
                                            date, 
                                            remarks, 
                                            id_payperiod, 
                                            date_create, 
                                            time_create, 
                                            type_creator, 
                                            secretary";
                    $Qry->fields = " '" . $docnumber . "',
                                            '" . $param->businessunit . "',
                                            '" . $param->accountid . "',
                                            '" . $value->id . "',
                                            '" . $value2->drpndng . "',
                                            '" . $value2->work_date . "',
                                            '" . $remarks . "',
                                            '" . $id_payperiod . "',
                                            '" . $date_create . "',
                                            '" . $time_create . "',
                                            '" . $param->typec . "',
                                            '1'";
                    $checkentry = $Qry->exe_INSERT($con);
                }else{
                    $Qrycheck = new Query();
                    $Qrycheck->table = "tbldutyroster";
                    $Qrycheck->selected = "*";
                    $Qrycheck->fields = "idacct='" . $value->id . "' AND date='" . $value2->work_date . "' ORDER BY id DESC LIMIT 1";
                    $rscheck = $Qrycheck->exe_SELECT($con);
                    if (mysqli_num_rows($rscheck) >= 1)
                    {
                        while ($rowcheck = mysqli_fetch_array($rscheck))
                        {
                            if ($rowcheck['manager'] == 1)
                            {
                                $remarks = 'New schedule from ' . $value2->shift_status . ' to ' . getIdShift($con, $value2->drpndng);

                                $time = time();
                                $docnumber = "SS" . $value->id . strtotime($date_create . $time) . $time . $ctr;
                                $ctr++;

                                $id_payperiod = getIdPayPeriod($con, $value2->work_date);

                               
                                $Qry = new Query();
                                $Qry->table = "tbldutyroster";
                                $Qry->selected = "docnumber, 
                                                        unit, 
                                                        creator, 
                                                        idacct, 
                                                        idshift, 
                                                        date, 
                                                        remarks, 
                                                        id_payperiod, 
                                                        date_create, 
                                                        time_create, 
                                                        type_creator, 
                                                        secretary";
                                $Qry->fields = " '" . $docnumber . "',
                                                        '" . $param->businessunit . "',
                                                        '" . $param->accountid . "',
                                                        '" . $value->id . "',
                                                        '" . $value2->drpndng . "',
                                                        '" . $value2->work_date . "',
                                                        '" . $remarks . "',
                                                        '" . $id_payperiod . "',
                                                        '" . $date_create . "',
                                                        '" . $time_create . "',
                                                        '" . $param->typec . "',
                                                        '1'";
                                $checkentry = $Qry->exe_INSERT($con);

                            }
                            else
                            {
                                $remarks = 'New schedule from ' . $value2->shift_status . ' to ' . getIdShift($con, $value2->drpndng);

                                $Qryb = new Query();
                                $Qryb->table = "tbldutyroster";
                                $Qryb->selected = "idshift='" . $value2->drpndng . "', remarks='" . $remarks . "'";
                                $Qryb->fields = "idacct='" . $value->id . "' AND date='" . $value2->work_date . "' ORDER BY id DESC LIMIT 1";
                                $checkentryb = $Qryb->exe_UPDATE($con);
                            }
                        }
                    }

                }
            }else{
                $Qry = new Query();
                $Qry->table = "tbldutyroster";
                $Qry->fields = "idacct='" . $value->id . "' AND date='" . $value2->work_date . "' AND secretary='1' AND type_creator='1'";
                $Qry->exe_DELETE($con);
            }
        }
    }

    $return = json_encode(array(
        'savestatus' => 'success'
    ));
    print $return;
    mysqli_close($con);
}

if ($param->button == 'approve')
{
    $count = 0;
    
    foreach ($param->employee as $key => $value)
    {
        foreach ($value->sched as $key2 => $value2)
        {
            if ($value2->drpndng)
            {
                $count++;
            }
        }
    }

    if ($count != 0)
    {
        foreach ($param->employee as $key => $value)
        {
            foreach ($value->sched as $key2 => $value2)
            {
                if($value2->drpndng){
                    $Qryb = new Query();
                    $Qryb->table = "tbldutyroster";
                    $Qryb->selected ="idstat=1, 
                                        manager='1', 
                                        date_approve ='" . SysDate() . "', 
                                        time_approve='" . SysTime() . "'";
                    $Qryb->fields = "idacct='" . $value->id . "' AND date='" . $value2->work_date . "' AND (secretary=1 OR secretary is null) AND (manager=0 OR manager IS NULL)";
                    $checkentryb = $Qryb->exe_UPDATE($con);

                    $timesheet_data = getTimeSheetData($con, $value->id, $value2->work_date);
                    // if (!empty($timesheet_data)){
                    //     if (addToBackuptimesheet($con, $timesheet_data))
                    //     {
                    //         error_reporting(0);
                    //         $param->info->date = $value2->work_date;
                    //         $param->info->idacct = $value->id;
                    //         $return = updateTimesheetLate($con, $param, $value2->drpndng);
                    //     }
                    // }

                    $id_payperiod = getIdPayPeriod($con, $value2->work_date);

                    // $Qry = new Query();
                    // $Qry->table = "tbltimesheet";
                    // $Qry->selected = "*";
                    // $Qry->fields = "idacct='" . $value->id . "' AND date='" . $value2->work_date . "'";
                    // $rs = $Qry->exe_SELECT($con);
                    // if (mysqli_num_rows($rs) >= 1){
                    //     while ($row = mysqli_fetch_array($rs)){
                    //         $Qry2 = new Query();
                    //         $Qry2->table        = "tbltimesheet";
                    //         $Qry2->selected     = "idshift='" . $value2->drpndng . "'";
                    //         $Qry2->fields       = "id='" . $row['id'] . "'";
                    //         $Qry2->exe_UPDATE($con);
                    //     }
                    // }else{
                    //     $Qry2 			    = new Query();
                    //     $Qry2->table 	    = "tbltimesheet";
                    //     $Qry2->selected 	= "idacct,
                    //                             day,
                    //                             date,
                    //                             idshift,
                    //                             id_payperiod";
                    //     $Qry2->fields 	    = "'".$value->id."',
                    //                             DAYNAME('" . $value2->work_date . "'),
                    //                             '" . $value2->work_date . "',
                    //                             '" . $value2->drpndng . "',
                    //                             '" . $id_payperiod . "'";
                    //     $Qry2->exe_INSERT($con); 
                    // }
                }

            }
        }

        $return = json_encode(array(
            'savestatus' => 'success'
        ));
        print $return;
        mysqli_close($con);
    }
    else
    {
        $return = json_encode(array(
            'savestatus' => 'oops'
        ));
        print $return;
        mysqli_close($con);
    }
}

if ($param->button == 'recall')
{
    $count = 0;
    foreach ($param->employee as $key => $value)
    {
        foreach ($value->sched as $key2 => $value2)
        {
            if ($value2->drpndng)
            {
                $count++;
            }
        }
    }

    if ($count != 0)
    {
        foreach ($param->employee as $key => $value)
        {
            foreach ($value->sched as $key2 => $value2)
            {
                $Qryb = new Query();
                $Qryb->table = "tbldutyroster";
                $Qryb->selected = "secretary='0'";
                $Qryb->fields = "idacct='" . $value2->id . "' AND date='" . $value2->work_date . "' AND secretary='1' AND type_creator='2'";
                $checkentryb = $Qryb->exe_UPDATE($con);
            }
        }
        $return = json_encode(array(
            'savestatus' => 'success'
        ));
        print $return;
        mysqli_close($con);
    }
    else
    {
        $return = json_encode(array(
            'savestatus' => 'nosched'
        ));
        print $return;
        mysqli_close($con);
    }

}

function getIdPayPeriod($con, $date)
{
    $Qry = new Query();
    $Qry->table = "tblpayperiod";
    $Qry->selected = "*";
    $Qry->fields = "period_start<='" . $date . "' AND period_end>='" . $date . "'";
    $rs = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1)
    {
        while ($row = mysqli_fetch_array($rs))
        {
            return $row['id'];
        }
    }
    return '';
}

function getIdShift($con, $name)
{
    $Qry = new Query();
    $Qry->table = "tblshift";
    $Qry->selected = "name";
    $Qry->fields = "id='" . $name . "'";
    $rs = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1)
    {
        while ($row = mysqli_fetch_array($rs))
        {
            return $row['name'];
        }
    }
    return '';
}

function checkIfHasPending($con, $idacct, $date)
{
    $Qry = new Query();
    $Qry->table = "tbldutyroster";
    $Qry->selected = "*";
    $Qry->fields = "idacct='" . $idacct . "' AND date='" . $date . "'";
    $rs = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1)
    {
        while ($row = mysqli_fetch_array($rs))
        {
            return true;
        }
    }
    return false;
}

function checkIfSaved($con, $idacct, $date)
{
    $Qry = new Query();
    $Qry->table = "tbldutyroster";
    $Qry->selected = "*";
    $Qry->fields = "idacct='" . $idacct . "' AND date='" . $date . "' AND (secretary is null OR secretary=1) AND (manager is null OR manager=0)";
    $rs = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1)
    {
        while ($row = mysqli_fetch_array($rs))
        {
            return true;
        }
    }
    return false;
}

function checkIfHasRecall($con, $idacct, $date)
{
    $Qry = new Query();
    $Qry->table = "tbldutyroster";
    $Qry->selected = "*";
    $Qry->fields = "idacct='" . $idacct . "' AND date='" . $date . "' AND type_creator=2";
    $rs = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1)
    {
        while ($row = mysqli_fetch_array($rs))
        {
            return true;
        }
    }
    return false;
}

?>
