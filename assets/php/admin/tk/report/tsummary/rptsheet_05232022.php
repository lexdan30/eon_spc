<?php
require_once ('../../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once ('../../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));
$data = array();
$search = '';
$where = $search;

$param->id = getPayid($con, $param);


if( !empty( $param->filter->search_acct ) ){ $search=" AND tid =   '". $param->filter->search_acct ."' "; }
if( !empty( $param->filter->department ) ){ $search=" AND idunit =   '". $param->filter->department ."' "; }

$Qry = new Query();
if ($param->type == 'Helper'){
    $Qry->table = "vw_timesheetfinal_helper";
}else if ($param->type == 'Japanese'){
    $Qry->table = "vw_timesheetfinal_japanese";
}else if ($param->type == 'Japanese Conversion'){
    $Qry->table = "vw_timesheetfinal_japanesec";
}else{
    $Qry->table = "vw_timesheetfinal_ho";
}

$Qry->selected = "tid,
                    empid,
                    idpayperiod,
                    idpaygrp,
                    idunit,
                    empname,
                    SUM(shifthrs) AS Rhrs,
                    SUM(late) + IF(SUM(latecount) >= 3 , SUM(lateref),0) as Late,
                    SUM(ut) AS Undertime,
                    SUM(absent) AS `Abs`,
                    SUM( (acthrs - excess)  ) + IF(SUM(latecount) >= 3 , SUM(lateref),0) AS WHrs,
                    SUM(IF(leaveidtype = 2 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_VL,
                    SUM(IF(leaveidtype = 1 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_SL,
                    SUM(IF(leaveidtype = 6 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_CL,
                    SUM(IF(leaveidtype = 12 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_ML,
                    SUM(IF(leaveidtype = 10 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_MC,
                    SUM(IF(leaveidtype = 5 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_PL,
                    SUM(IF(leaveidtype = 9 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_EL,
                    SUM(IF(leaveidtype = 11 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_BL,
                    SUM((IF(obtripstatus = 1
                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(obstart AS TIME), CAST(obend AS TIME) ) / 3600,2) 
                            - (CASE 
                                    WHEN (breakinref BETWEEN obstart AND obend) AND (breakoutref BETWEEN obstart AND obend) 
                                    THEN ROUND(TIMESTAMPDIFF( SECOND,CAST(breakinref AS TIME), CAST(breakoutref AS TIME) ) / 3600,2)
                                    ELSE 0
                                END)
                            ,0
                        ))) AS TC_OBT,
                    SUM(IF(leaveidtype = 8 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_BDL,
                    SUM(IF(leaveidtype = 4 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_SPL,
                    SUM(IF(leaveidtype = 3 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_LWOP,
                    SUM(IF(leaveidtype = 21 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_WL,
                    SUM(IF(leaveidtype = 22 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_MENSTL,
                    SUM(IF(leaveidtype = 23 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_CVL,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL AND idlvl NOT IN(1,2,3,4,5) THEN   
                            IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                                ,IF(othrsp > 8,8,othrsp)
                                ,othrsp
                            )
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL AND idlvl NOT IN(1,2,3,4,5) THEN (acthrs - excess) 
                        ELSE 0
                        END)) 
                    AS TC_RD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'SPECIAL' AND idlvl NOT IN(1,2,3,4,5) THEN (acthrs - excess) + (IF(leaveidtype != 3 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0))
                        ELSE 0
                        END))
                    AS H_SH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'SPECIAL' AND idlvl NOT IN(1,2,3,4,5) THEN (acthrs - excess)
                        ELSE 0
                        END)) 
                    AS H_SHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL' AND idlvl NOT IN(1,2,3,4,5) THEN (acthrs - excess)
                        ELSE 0
                        END))
                    AS H_LH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL' AND idlvl NOT IN(1,2,3,4,5) THEN (acthrs - excess)
                        
                        ELSE 0
                        END))
                    AS H_LHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL SPECIAL' AND idlvl NOT IN(1,2,3,4,5) THEN (acthrs - excess)
                        
                        ELSE 0
                        END))
                    AS H_LSH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL SPECIAL' AND idlvl NOT IN(1,2,3,4,5) THEN (acthrs - excess)
                        
                        ELSE 0
                        END))
                    AS H_LSHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype IS NULL AND otstatus = 1 AND idlvl NOT IN(1,2,3,4,5)  THEN 
                        IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                            ,IF(othrsp > 8,othrsp - 8,othrsp)
                            ,othrsp
                        )
                        ELSE 0
                        END)) 
                    AS OT_OTReg,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL AND otstatus = 1 AND idlvl NOT IN(1,2,3,4,5)  THEN 
                        IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                            ,IF(othrsp > 8,othrsp - 8,0)
                            ,othrsp
                        )
                        ELSE 0
                        END)) 
                    AS OT_RD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'SPECIAL' AND otstatus = 1 AND idlvl NOT IN(1,2,3,4,5)  THEN 
                    IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                            ,IF(othrsp > 8,othrsp - 8,0)
                            ,othrsp
                        )
                        ELSE 0
                        END)) 
                    AS OT_SH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'SPECIAL' AND otstatus = 1 AND idlvl NOT IN(1,2,3,4,5)  THEN 
                        IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                            ,IF(othrsp > 8,othrsp - 8,0)
                            ,othrsp
                        )
                        
                        ELSE 0
                        END)) 
                    AS OT_SHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL' AND otstatus = 1 AND idlvl NOT IN(1,2,3,4,5)  THEN 
                        IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                            ,IF(othrsp > 8,othrsp - 8,0)
                            ,othrsp
                        )
                        
                        ELSE 0
                        END)) 
                    AS OT_LH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL' AND otstatus = 1 AND idlvl NOT IN(1,2,3,4,5)  THEN 
                        IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                            ,IF(othrsp > 8,othrsp - 8,0)
                            ,othrsp
                        )
                        
                        ELSE 0
                        END)) 
                    AS OT_LHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 AND idlvl NOT IN(1,2,3,4,5)  THEN 
                        IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                            ,IF(othrsp > 8,othrsp - 8,0)
                            ,othrsp
                        )
                        ELSE 0
                        END)) 
                    AS OT_LSH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 AND idlvl NOT IN(1,2,3,4,5)  THEN 
                        IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                            ,IF(othrsp > 8,othrsp - 8,0)
                            ,othrsp
                        )
                        ELSE 0
                        END)) 
                    AS OT_LSHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype IS NULL THEN np
                        ELSE 0
                        END)) 
                    AS NP_NPReg,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL THEN np
                        ELSE 0
                        END)) 
                    AS NP_RD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'SPECIAL' THEN np
                        ELSE 0
                        END)) 
                    AS NP_SH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'SPECIAL' THEN np
                        ELSE 0
                        END)) 
                    AS NP_SHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL' THEN np
                        ELSE 0
                        END)) 
                    AS NP_LH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL' THEN np
                        ELSE 0
                        END)) 
                    AS NP_LHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL SPECIAL' THEN np
                        ELSE 0
                        END)) 
                    AS NP_LSH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL SPECIAL' THEN np
                        ELSE 0
                        END)) 
                    AS NP_LSHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype IS NULL AND otstatus = 1 THEN npot
                        ELSE 0
                        END)) 
                    AS NPOT_NPOT,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL AND otstatus = 1 THEN npot
                        ELSE 0
                        END)) 
                    AS NPOT_RD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'SPECIAL' AND otstatus = 1 THEN npot
                        ELSE 0
                        END)) 
                    AS NPOT_SH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'SPECIAL' AND otstatus = 1 THEN npot
                        ELSE 0
                        END)) 
                    AS NPOT_SHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL' AND otstatus = 1 THEN npot
                        ELSE 0
                        END)) 
                    AS NPOT_LH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL' AND otstatus = 1 THEN npot
                        ELSE 0
                        END)) 
                    AS NPOT_LHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 THEN npot
                        ELSE 0
                        END)) 
                    AS NPOT_LSH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 THEN npot
                        ELSE 0
                        END)) 
                    AS NPOT_LSHRD,
                    SUM( IF( idlvl IN (2,3)
                                ,othrsp
                                ,0
                            ) 
                        ) 
                    AS otallowance,
                    SUM( 
                        IF( (acthrs - excess  < 4 AND  acthrs - excess > 2  AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW'))) AND NOT FIND_IN_SET(3, batchnum))
                            OR ((timein IS NOT NULL OR timeout IS NOT NULL OR timein != '' OR timeout != '') 
                            AND (holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') 
                            AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW'))) AND NOT FIND_IN_SET(3, batchnum)
                            AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(date_in, ' ',  IF( CAST(timein AS TIME) < CAST(loginschedref AS TIME) ,loginschedref ,timein)) AS DATETIME), CAST(CONCAT(date_out, ' ', IF( CAST(timeout AS TIME) < CAST(logoutschedref AS TIME) ,timeout ,logoutschedref)) AS DATETIME)) / 60) / 15)) * 15) < 240)
                            OR ((holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') AND othrsp < 4 AND othrsp > 2)
                            OR (location = 2  AND aaapprove IS NOT NULL AND aaapprove BETWEEN period_start AND period_endref  AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(work_date, ' ',  aastime) AS DATETIME), CAST(CONCAT(work_date, ' ', aaftime) AS DATETIME)) / 60) / 15)) * 15) < 240)
                            ,1
                            ,0)
                        )
                        AS mallowance,
                        SUM(
                            IF( ((acthrs - excess  >= 4 AND  (acthrs - excess  < 8))  AND ( location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW')) ) AND NOT FIND_IN_SET(3, batchnum))
                            OR ((timein IS NOT NULL OR timeout IS NOT NULL OR timein != '' OR timeout != '')
                            AND (holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') 
                            AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW'))) AND NOT FIND_IN_SET(3, batchnum)
                            AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(date_in, ' ',  IF( CAST(timein AS TIME) < CAST(loginschedref AS TIME) ,loginschedref ,timein)) AS DATETIME), CAST(CONCAT(date_out, ' ', IF( CAST(timeout AS TIME) < CAST(logoutschedref AS TIME) ,timeout ,logoutschedref)) AS DATETIME)) / 60) / 15)) * 15) >= 240 AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(date_in, ' ',  IF( CAST(timein AS TIME) < CAST(loginschedref AS TIME) ,loginschedref ,timein)) AS DATETIME), CAST(CONCAT(date_out, ' ', IF( CAST(timeout AS TIME) < CAST(logoutschedref AS TIME) ,timeout ,logoutschedref)) AS DATETIME)) / 60) / 15)) * 15) < 480)
                            OR ((holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') AND othrsp >= 4 AND othrsp < 8)
                            OR (location = 2  AND aaapprove IS NOT NULL AND aaapprove BETWEEN period_start AND period_endref  AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(work_date, ' ',  IF( CAST(aastime AS TIME) < CAST(loginschedref AS TIME) ,loginschedref ,aastime)) AS DATETIME), CAST(CONCAT(work_date, ' ', IF( CAST(aaftime AS TIME) < CAST(logoutschedref AS TIME) ,aaftime ,logoutschedref)) AS DATETIME)) / 60) / 15)) * 15) < 480)
                                    ,1
                                ,0
                            )  
                        )AS hallowance,
                        SUM(
                            IF( (acthrs - excess >= 8 AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW')) ) AND NOT FIND_IN_SET(3, batchnum))
                            OR ((timein IS NOT NULL OR timeout IS NOT NULL OR timein != '' OR timeout != '')
                            AND (holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') 
                            AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW'))) AND NOT FIND_IN_SET(3, batchnum)
                            AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(date_in, ' ',  IF( CAST(timein AS TIME) < CAST(loginschedref AS TIME) ,loginschedref ,timein)) AS DATETIME), CAST(CONCAT(date_out, ' ', IF( CAST(timeout AS TIME) < CAST(logoutschedref AS TIME) ,timeout ,logoutschedref)) AS DATETIME)) / 60) / 15)) * 15) >= 480)
                            OR ((holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') AND othrsp >= 8)
                            OR (location = 2  AND aaapprove IS NOT NULL AND aaapprove BETWEEN period_start AND period_endref  AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(work_date, ' ',  IF( CAST(aastime AS TIME) < CAST(loginschedref AS TIME) ,loginschedref ,aastime)) AS DATETIME), CAST(CONCAT(work_date, ' ', IF( CAST(aaftime AS TIME) < CAST(logoutschedref AS TIME) ,timeout ,logoutschedref)) AS DATETIME)) / 60) / 15)) * 15) >= 480)
                                ,1
                                ,0
                            )
                        )AS wallowance,
                        SUM(
                        IF ( (holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') 
                        AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW')) )
                            AND NOT FIND_IN_SET(3, batchnum)
                            ,IF( (allowref != 0  AND (rdacthrs >= 4 AND rdacthrs < 8 )) AND (timein IS NOT NULL OR timeout IS NOT NULL OR timein != '' OR timeout != '' OR aaapprove IS NOT NULL)  ,1,0)
                            ,0
                        )
                        )AS shallowance,
                        SUM(
                        IF ((holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') 
                        AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW')) )
                            AND NOT FIND_IN_SET(3, batchnum)
                            ,IF( (allowref != 0 AND (rdacthrs >= 8)) AND (timein IS NOT NULL OR timeout IS NOT NULL OR timein != '' OR timeout != '' OR aaapprove IS NOT NULL)  ,1,0)
                            ,0
                        )
                        )AS swallowance
                    ";
$Qry->fields = "idpayperiod = '" . $param->id . "' " .$search . "
                    GROUP BY tid ORDER BY  empname LIMIT " . $param->pagination->pageSize . " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize . "";
$rs = $Qry->exe_SELECT($con);

if (mysqli_num_rows($rs) >= 1)
{
    while ($row = mysqli_fetch_array($rs))
    {
        $adj_late = '';
        $adj_late_rd = '';
        $adj_late_sh = '';
        $adj_late_shrd = '';
        $adj_late_lh = '';
        $adj_late_lhrd = '';
        $adj_late_lsh = '';
        $adj_late_lshrd = '';
        $adj_ut = '';
        $adj_ut_rd = '';
        $adj_ut_sh = '';
        $adj_ut_shrd = '';
        $adj_ut_lh = '';
        $adj_ut_lhrd = '';
        $adj_ut_lsh = '';
        $adj_ut_lshrd = '';
        $adj_absent = '';
        $adj_absent_rd = '';
        $adj_absent_sh = '';
        $adj_absent_shrd = '';
        $adj_absent_lh = '';
        $adj_absent_lhrd = '';
        $adj_absent_lsh = '';
        $adj_absent_lshrd = '';
        $adj_ot = '';
        $adj_ot_rd = '';
        $adj_ot_sh = '';
        $adj_ot_shrd = '';
        $adj_ot_lh = '';
        $adj_ot_lhrd = '';
        $adj_ot_lsh = '';
        $adj_ot_lshrd = '';
        $adj_np = '';
        $adj_np_rd = '';
        $adj_np_sh = '';
        $adj_np_shrd = '';
        $adj_np_lh = '';
        $adj_np_lhrd = '';
        $adj_np_lsh = '';
        $adj_np_lshrd = '';
        $adj_npot = '';
        $adj_npot_rd = '';
        $adj_npot_sh = '';
        $adj_npot_shrd = '';
        $adj_npot_lh = '';
        $adj_npot_lhrd = '';
        $adj_npot_lsh = '';
        $adj_npot_lshrd = '';
        $adj_vl = '';
        $adj_sl = '';

        $adj_cl = '';
        $adj_ml = '';
        $adj_mc = '';

        $adj_pl = '';
        $adj_el = '';
        $adj_bl = '';
        $adj_obt = '';
        $adj_bdl = '';
        $adj_spl = '';
        $adj_lwop = '';

        $adj_cvl = '';
        $adj_menstl = '';
        $adj_wl = '';

        $otallowance = '';
        $mallowance = '';
        $hallowance = '';
        $wallowance = '';
        $shallowance = '';
        $swallowance = '';

        $Qryts = new Query();
        $Qryts->table = "tbltimesheetsummary";
        $Qryts->selected = "*";
        $Qryts->fields = "idpayperiod = '" . $param->id . "' AND idacct =  '" . $row['tid'] . "' and type = '" . $param->type . "'";
        $rsts = $Qryts->exe_SELECT($con);
        if (mysqli_num_rows($rsts) >= 1)
        {
            if ($row1 = mysqli_fetch_array($rsts))
            {
                if ($param->type == 'Japanese' || $param->type == 'Japanese Conversion')
                {
                    $row['Rhrs'] = 0;
                }
                else
                {
                    $row['Rhrs'] = $row1['rhrs'];
                }

                $row['Late'] = $row1['late'];
                $row['Undertime'] = $row1['ut'];
                $row['Abs'] = $row1['abs'];
                $row['WHrs'] = $row1['whrs'];
                $row['TC_VL'] = $row1['tc_vl'];
                $row['TC_SL'] = $row1['tc_sl'];
                $row['TC_CL'] = $row1['tc_cl'];
                $row['TC_ML'] = $row1['tc_ml'];
                $row['TC_MC'] = $row1['tc_mc'];
                $row['TC_PL'] = $row1['tc_pl'];
                $row['TC_EL'] = $row1['tc_el'];
                $row['TC_BL'] = $row1['tc_bl'];
                $row['TC_OBT'] = $row1['tc_obt'];
                $row['TC_BDL'] = $row1['tc_bdl'];
                $row['TC_SPL'] = $row1['tc_spl'];
                $row['TC_LWOP'] = $row1['tc_lwop'];

                $row['TC_CVL'] = $row1['tc_cvl'];
                $row['TC_MENSTL'] = $row1['tc_menstl'];
                $row['TC_WL'] = $row1['tc_wl'];

                $row['TC_RD'] = $row1['tc_rd'];
                $row['H_SH'] = $row1['h_sh'];
                $row['H_SHRD'] = $row1['h_shrd'];
                $row['H_LH'] = $row1['h_lh'];
                $row['H_LHRD'] = $row1['h_lhrd'];
                $row['H_LSH'] = $row1['h_lsh'];
                $row['H_LSHRD'] = $row1['h_lshrd'];
                $row['OT_OTReg'] = $row1['ot_reg'];
                $row['OT_RD'] = $row1['ot_rd'];
                $row['OT_SH'] = $row1['ot_sh'];
                $row['OT_SHRD'] = $row1['ot_shrd'];
                $row['OT_LH'] = $row1['ot_lh'];
                $row['OT_LHRD'] = $row1['ot_lhrd'];
                $row['OT_LSH'] = $row1['ot_lsh'];
                $row['OT_LSHRD'] = $row1['ot_lshrd'];
                $row['NP_NPReg'] = $row1['np_npreg'];
                $row['NP_RD'] = $row1['np_rd'];
                $row['NP_SH'] = $row1['np_sh'];
                $row['NP_SHRD'] = $row1['np_shrd'];
                $row['NP_LH'] = $row1['np_lh'];
                $row['NP_LHRD'] = $row1['np_lhrd'];
                $row['NP_LSH'] = $row1['np_lsh'];
                $row['NP_LSHRD'] = $row1['np_lshrd'];
                $row['NPOT_NPOT'] = $row1['npot_npot'];
                $row['NPOT_RD'] = $row1['npot_rd'];
                $row['NPOT_SH'] = $row1['npot_sh'];
                $row['NPOT_SHRD'] = $row1['npot_shrd'];
                $row['NPOT_LH'] = $row1['npot_lh'];
                $row['NPOT_LHRD'] = $row1['npot_lhrd'];
                $row['NPOT_LSH'] = $row1['npot_lsh'];
                $row['NPOT_LSHRD'] = $row1['npot_lshrd'];
                $adj_late = $row1['adj_late'];
                $adj_late_rd = $row1['adj_late_rd'];
                $adj_late_sh = $row1['adj_late_sh'];
                $adj_late_shrd = $row1['adj_late_shrd'];
                $adj_late_lh = $row1['adj_late_lh'];
                $adj_late_lhrd = $row1['adj_late_lhrd'];
                $adj_late_lsh = $row1['adj_late_lsh'];
                $adj_late_lshrd = $row1['adj_late_lshrd'];
                $adj_ut = $row1['adj_ut'];
                $adj_ut_rd = $row1['adj_ut_rd'];
                $adj_ut_sh = $row1['adj_ut_sh'];
                $adj_ut_shrd = $row1['adj_ut_shrd'];
                $adj_ut_lh = $row1['adj_ut_lh'];
                $adj_ut_lhrd = $row1['adj_ut_lhrd'];
                $adj_ut_lsh = $row1['adj_ut_lsh'];
                $adj_ut_lshrd = $row1['adj_ut_lshrd'];
                $adj_absent = $row1['adj_absent'];
                $adj_absent_rd = $row1['adj_absent_rd'];
                $adj_absent_sh = $row1['adj_absent_sh'];
                $adj_absent_shrd = $row1['adj_absent_shrd'];
                $adj_absent_lh = $row1['adj_absent_lh'];
                $adj_absent_lhrd = $row1['adj_absent_lhrd'];
                $adj_absent_lsh = $row1['adj_absent_lsh'];
                $adj_absent_lshrd = $row1['adj_absent_lshrd'];
                $adj_ot = $row1['adj_ot'];
                $adj_ot_rd = $row1['adj_ot_rd'];
                $adj_ot_sh = $row1['adj_ot_sh'];
                $adj_ot_shrd = $row1['adj_ot_shrd'];
                $adj_ot_lh = $row1['adj_ot_lh'];
                $adj_ot_lhrd = $row1['adj_ot_lhrd'];
                $adj_ot_lsh = $row1['adj_ot_lsh'];
                $adj_ot_lshrd = $row1['adj_ot_lshrd'];
                $adj_np = $row1['adj_np'];
                $adj_np_rd = $row1['adj_np_rd'];
                $adj_np_sh = $row1['adj_np_sh'];
                $adj_np_shrd = $row1['adj_np_shrd'];
                $adj_np_lh = $row1['adj_np_lh'];
                $adj_np_lhrd = $row1['adj_np_lhrd'];
                $adj_np_lsh = $row1['adj_np_lsh'];
                $adj_np_lshrd = $row1['adj_np_lshrd'];
                $adj_npot = $row1['adj_npot'];
                $adj_npot_rd = $row1['adj_npot_rd'];
                $adj_npot_sh = $row1['adj_npot_sh'];
                $adj_npot_shrd = $row1['adj_npot_shrd'];
                $adj_npot_lh = $row1['adj_npot_lh'];
                $adj_npot_lhrd = $row1['adj_npot_lhrd'];
                $adj_npot_lsh = $row1['adj_npot_lsh'];
                $adj_npot_lshrd = $row1['adj_npot_lshrd'];
                $adj_vl = $row1['adj_vl'];
                $adj_sl = $row1['adj_sl'];
                $adj_cl = $row1['adj_cl'];
                $adj_ml = $row1['adj_ml'];
                $adj_mc = $row1['adj_mc'];
                $adj_pl = $row1['adj_pl'];
                $adj_el = $row1['adj_el'];
                $adj_bl = $row1['adj_bl'];
                $adj_obt = $row1['adj_obt'];
                $adj_bdl = $row1['adj_bdl'];
                $adj_spl = $row1['adj_spl'];
                $adj_lwop = $row1['adj_lwop'];

                $adj_cvl = $row1['adj_cvl'];
                $adj_menstl = $row1['adj_menstl'];
                $adj_wl = $row1['adj_wl'];

                $otallowance = $row1['otallowance'];
                $mallowance = $row1['mallowance'];
                $hallowance = $row1['hallowance'];
                $wallowance = $row1['wallowance'];
                $shallowance = $row1['shallowance'];
                $swallowance = $row1['swallowance'];
            }
        }

        $data[] = array(
            "empid" => $row['empid'],
            "empname" => $row['empname'],
            "idunit" => $row['idunit'],
            "department" => getDepartments($con, $row['idunit']) ,
            "section" => getSection($con, $row['idunit']) ,
            "paygroup" => getPaygroup($con, $row['idpaygrp']) ,
            "Rhrs" => round($row['Rhrs'], 2) ,
            "Late" => round($row['Late'], 2) ,
            "Undertime" => round($row['Undertime'], 2) ,
            "Abs" => round($row['Abs'], 2) ,
            "WHrs" => round($row['WHrs'], 2) ,
            "TC_VL" => round($row['TC_VL'], 2) ,
            "TC_SL" => round($row['TC_SL'], 2) ,
            "TC_CL" => round($row['TC_CL'], 2) ,
            "TC_ML" => round($row['TC_ML'], 2) ,
            "TC_MC" => round($row['TC_MC'], 2) ,
            "TC_PL" => round($row['TC_PL'], 2) ,
            "TC_EL" => round($row['TC_EL'], 2) ,
            "TC_BL" => round($row['TC_BL'], 2) ,
            "TC_OBT" => round($row['TC_OBT'], 2) ,
            "TC_BDL" => round($row['TC_BDL'], 2) ,
            "TC_SPL" => round($row['TC_SPL'], 2) ,
            "TC_LWOP" => round($row['TC_LWOP'], 2) ,

            "TC_CVL" => round($row['TC_CVL'], 2) ,
            "TC_MENSTL" => round($row['TC_MENSTL'], 2) ,
            "TC_WL" => round($row['TC_WL'], 2) ,

            "TC_RD" => round($row['TC_RD'], 2) ,
            "H_SH" => round($row['H_SH'], 2) ,
            "H_SHRD" => round($row['H_SHRD'], 2) ,
            "H_LH" => round($row['H_LH'], 2) ,
            "H_LHRD" => round($row['H_LHRD'], 2) ,
            "H_LSH" => round($row['H_LSH'], 2) ,
            "H_LSHRD" => round($row['H_LSHRD'], 2) ,
            "OT_OTReg" => round($row['OT_OTReg'], 2) ,
            "OT_RD" => round($row['OT_RD'], 2) ,
            "OT_SH" => round($row['OT_SH'], 2) ,
            "OT_SHRD" => round($row['OT_SHRD'], 2) ,
            "OT_LH" => round($row['OT_LH'], 2) ,
            "OT_LHRD" => round($row['OT_LHRD'], 2) ,
            "OT_LSH" => round($row['OT_LSH'], 2) ,
            "OT_LSHRD" => round($row['OT_LSHRD'], 2) ,
            "NP_NPReg" => round($row['NP_NPReg'], 2) ,
            "NP_RD" => round($row['NP_RD'], 2) ,
            "NP_SH" => round($row['NP_SH'], 2) ,
            "NP_SHRD" => round($row['NP_SHRD'], 2) ,
            "NP_LH" => round($row['NP_LH'], 2) ,
            "NP_LHRD" => round($row['NP_LHRD'], 2) ,
            "NP_LSH" => round($row['NP_LSH'], 2) ,
            "NP_LSHRD" => round($row['NP_LSHRD'], 2) ,
            "NPOT_NPOT" => round($row['NPOT_NPOT'], 2) ,
            "NPOT_RD" => round($row['NPOT_RD'], 2) ,
            "NPOT_SH" => round($row['NPOT_SH'], 2) ,
            "NPOT_SHRD" => round($row['NPOT_SHRD'], 2) ,
            "NPOT_LH" => round($row['NPOT_LH'], 2) ,
            "NPOT_LHRD" => round($row['NPOT_LHRD'], 2) ,
            "NPOT_LSH" => round($row['NPOT_LSH'], 2) ,
            "NPOT_LSHRD" => round($row['NPOT_LSHRD'], 2) ,
            "adj_late" => round($adj_late, 2) ,
            "adj_late_rd" => round($adj_late_rd, 2) ,
            "adj_late_sh" => round($adj_late_sh, 2) ,
            "adj_late_shrd" => round($adj_late_shrd, 2) ,
            "adj_late_lh" => round($adj_late_lh, 2) ,
            "adj_late_lhrd" => round($adj_late_lhrd, 2) ,
            "adj_late_lsh" => round($adj_late_lsh, 2) ,
            "adj_late_lshrd" => round($adj_late_lshrd, 2) ,
            "adj_ut" => round($adj_ut, 2) ,
            "adj_ut_rd" => round($adj_ut_rd, 2) ,
            "adj_ut_sh" => round($adj_ut_sh, 2) ,
            "adj_ut_shrd" => round($adj_ut_shrd, 2) ,
            "adj_ut_lh" => round($adj_ut_lh, 2) ,
            "adj_ut_lhrd" => round($adj_ut_lhrd, 2) ,
            "adj_ut_lsh" => round($adj_ut_lsh, 2) ,
            "adj_ut_lshrd" => round($adj_ut_lshrd, 2) ,
            "adj_absent" => round($adj_absent, 2) ,
            "adj_absent_rd" => round($adj_absent_rd, 2) ,
            "adj_absent_sh" => round($adj_absent_sh, 2) ,
            "adj_absent_shrd" => round($adj_absent_shrd, 2) ,
            "adj_absent_lh" => round($adj_absent_lh, 2) ,
            "adj_absent_lhrd" => round($adj_absent_lhrd, 2) ,
            "adj_absent_lsh" => round($adj_absent_lsh, 2) ,
            "adj_absent_lshrd" => round($adj_absent_lshrd, 2) ,
            "adj_ot" => round($adj_ot, 2) ,
            "adj_ot_rd" => round($adj_ot_rd, 2) ,
            "adj_ot_sh" => round($adj_ot_sh, 2) ,
            "adj_ot_shrd" => round($adj_ot_shrd, 2) ,
            "adj_ot_lh" => round($adj_ot_lh, 2) ,
            "adj_ot_lhrd" => round($adj_ot_lhrd, 2) ,
            "adj_ot_lsh" => round($adj_ot_lsh, 2) ,
            "adj_ot_lshrd" => round($adj_ot_lshrd, 2) ,
            "adj_np" => round($adj_np, 2) ,
            "adj_np_rd" => round($adj_np_rd, 2) ,
            "adj_np_sh" => round($adj_np_sh, 2) ,
            "adj_np_shrd" => round($adj_np_shrd, 2) ,
            "adj_np_lh" => round($adj_np_lh, 2) ,
            "adj_np_lhrd" => round($adj_np_lhrd, 2) ,
            "adj_np_lsh" => round($adj_np_lsh, 2) ,
            "adj_np_lshrd" => round($adj_np_lshrd, 2) ,
            "adj_npot" => round($adj_npot, 2) ,
            "adj_npot_rd" => round($adj_npot_rd, 2) ,
            "adj_npot_sh" => round($adj_npot_sh, 2) ,
            "adj_npot_shrd" => round($adj_npot_shrd, 2) ,
            "adj_npot_lh" => round($adj_npot_lh, 2) ,
            "adj_npot_lhrd" => round($adj_npot_lhrd, 2) ,
            "adj_npot_lsh" => round($adj_npot_lsh, 2) ,
            "adj_npot_lshrd" => round($adj_npot_lshrd, 2) ,
            "adj_vl" => round($adj_vl, 2) ,
            "adj_sl" => round($adj_sl, 2) ,
            "adj_cl" => round($adj_cl, 2) ,
            "adj_ml" => round($adj_ml, 2) ,
            "adj_mc" => round($adj_mc, 2) ,
            "adj_pl" => round($adj_pl, 2) ,
            "adj_el" => round($adj_el, 2) ,
            "adj_bl" => round($adj_bl, 2) ,
            "adj_obt" => round($adj_obt, 2) ,
            "adj_bdl" => round($adj_bdl, 2) ,
            "adj_spl" => round($adj_spl, 2) ,
            "adj_lwop" => round($adj_lwop, 2) ,

            "adj_cvl" => round($adj_cvl, 2) ,
            "adj_menstl" => round($adj_menstl, 2) ,
            "adj_wl" => round($adj_wl, 2) ,

            "otallowance" => round($otallowance, 2) ,
            "mallowance" => round($mallowance, 2) ,
            "hallowance" => round($hallowance, 2) ,
            "wallowance" => round($wallowance, 2) ,
            "shallowance" => round($shallowance, 2) ,
            "swallowance" => round($swallowance, 2)

        );
    }

    $myData = array(
        'status' => 'success',
        'result' => $data,
        'totalItems' => getTotal($con, $param) ,
        'grandtotal' => getGrandtotal($con, $param) ,

    );
    $return = json_encode($myData);
}
else
{
    $return = json_encode(array(
        'error' => mysqli_error($con)
    ));
}

print $return;
mysqli_close($con);

function getTotal($con, $param)
{
    $Qry = new Query();
    if ($param->type == 'Helper')
    {
        $Qry->table = "vw_timesheetfinal_helper";
    }
    else if ($param->type == 'Japanese')
    {
        $Qry->table = "vw_timesheetfinal_japanese";
    }
    else if ($param->type == 'Japanese Conversion')
    {
        $Qry->table = "vw_timesheetfinal_japanesec";
    }
    else
    {
        $Qry->table = "vw_timesheetfinal_ho";
    }

    $Qry->selected = "*";
    $Qry->fields = "idpayperiod = '" . $param->id . "'
                            GROUP BY tid";
    $rs = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1)
    {
        if ($row = mysqli_fetch_array($rs))
        {
            $rowcount = mysqli_num_rows($rs);
            return $rowcount;
        }
    }
    return 0;
}

function getDepartments($con, $idunit)
{
    $Qry = new Query();
    $Qry->table = "vw_databusinessunits";
    $Qry->selected = "idunder,name,stype";
    $Qry->fields = "id='" . $idunit . "'";
    $rs = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1)
    {
        while ($row = mysqli_fetch_array($rs))
        {
            if ($row['stype'] == 'Department')
            {
                return $row['name'];
            }
            else if ($row['stype'] != 'Division')
            {
                return getDepartments($con, $row['idunder']);
            }
            else if ($row['stype'] == 'Division')
            {
                return '';
            }
        }
    }
}

function getSection($con, $idunit)
{
    $Qry = new Query();
    $Qry->table = "vw_databusinessunits";
    $Qry->selected = "idunder,name,stype";
    $Qry->fields = "id='" . $idunit . "'";
    $rs = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1)
    {
        while ($row = mysqli_fetch_array($rs))
        {
            if ($row['stype'] == 'Section')
            {
                return $row['name'];
            }
            else if ($row['stype'] != 'Department')
            {
                return getDepartments($con, $row['idunder']);
            }
            else if ($row['stype'] == 'Department')
            {
                return '';
            }
        }
    }

}

function getGrandtotal($con, $param)
{

    $Qry = new Query();
    if ($param->type == 'Local Employee')
    {
        $Qry->table = "vw_timesheetfinal";
    }
    if ($param->type == 'Helper')
    {
        $Qry->table = "vw_timesheetfinal_helper";
    }
    if ($param->type == 'Japanese')
    {
        $Qry->table = "vw_timesheetfinal_japanese";
    }
    if ($param->type == 'Japanese Conversion')
    {
        $Qry->table = "vw_timesheetfinal_japanesec";
    }

    $Qry->selected = "tid,
                        empid,
                        idpayperiod,
                        idpaygrp,
                        idunit,
                        empname,
                        SUM(shifthrs) AS Rhrs,
                        SUM(late) + IF(SUM(latecount) >= 3 , SUM(lateref),0) as Late,
                        SUM(ut) AS Undertime,
                        SUM(absent) AS `Abs`,
                        SUM( (acthrs - excess)  ) + IF(SUM(latecount) >= 3 , SUM(lateref),0) AS WHrs,
                        SUM(IF(leaveidtype = 2 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_VL,
                        SUM(IF(leaveidtype = 1 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_SL,
                        SUM(IF(leaveidtype = 6 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_CL,
                        SUM(IF(leaveidtype = 12 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_ML,
                        SUM(IF(leaveidtype = 10 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_MC,
                        SUM(IF(leaveidtype = 5 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_PL,
                        SUM(IF(leaveidtype = 9 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_EL,
                        SUM(IF(leaveidtype = 11 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_BL,
                        SUM((IF(obtripstatus = 1
                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(obstart AS TIME), CAST(obend AS TIME) ) / 3600,2) 
                                - (CASE 
                                        WHEN (breakinref BETWEEN obstart AND obend) AND (breakoutref BETWEEN obstart AND obend) 
                                        THEN ROUND(TIMESTAMPDIFF( SECOND,CAST(breakinref AS TIME), CAST(breakoutref AS TIME) ) / 3600,2)
                                        ELSE 0
                                    END)
                                ,0
                            ))) AS TC_OBT,
                        SUM(IF(leaveidtype = 8 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_BDL,
                        SUM(IF(leaveidtype = 4 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_SPL,
                        SUM(IF(leaveidtype = 3 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_LWOP,
                        SUM(IF(leaveidtype = 21 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_WL,
                        SUM(IF(leaveidtype = 22 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_MENSTL,
                        SUM(IF(leaveidtype = 23 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0)) AS TC_CVL,
                        SUM((CASE 
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL AND idlvl NOT IN(1,2,3,4,5) THEN   
                                IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                                    ,IF(othrsp > 8,8,othrsp)
                                    ,othrsp
                                )
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL AND idlvl NOT IN(1,2,3,4,5) THEN (acthrs - excess) 
                            ELSE 0
                            END)) 
                        AS TC_RD,
                        SUM((CASE 
                            WHEN defaultschedid != 4 AND holidaytype = 'SPECIAL' AND idlvl NOT IN(1,2,3,4,5) THEN (acthrs - excess) + (IF(leaveidtype != 3 AND leaveappstatus = 1 AND lvapprove BETWEEN period_start AND period_endref , `leave`, 0))
                            ELSE 0
                            END))
                        AS H_SH,
                        SUM((CASE 
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'SPECIAL' AND idlvl NOT IN(1,2,3,4,5) THEN (acthrs - excess)
                            ELSE 0
                            END)) 
                        AS H_SHRD,
                        SUM((CASE 
                            WHEN defaultschedid != 4 AND holidaytype = 'LEGAL' AND idlvl NOT IN(1,2,3,4,5) THEN (acthrs - excess)
                            ELSE 0
                            END))
                        AS H_LH,
                        SUM((CASE 
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL' AND idlvl NOT IN(1,2,3,4,5) THEN (acthrs - excess)
                            
                            ELSE 0
                            END))
                        AS H_LHRD,
                        SUM((CASE 
                            WHEN defaultschedid != 4 AND holidaytype = 'LEGAL SPECIAL' AND idlvl NOT IN(1,2,3,4,5) THEN (acthrs - excess)
                            
                            ELSE 0
                            END))
                        AS H_LSH,
                        SUM((CASE 
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL SPECIAL' AND idlvl NOT IN(1,2,3,4,5) THEN (acthrs - excess)
                            
                            ELSE 0
                            END))
                        AS H_LSHRD,
                        SUM((CASE 
                            WHEN defaultschedid != 4 AND holidaytype IS NULL AND otstatus = 1 AND idlvl NOT IN(1,2,3,4,5)  THEN 
                            IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                                ,IF(othrsp > 8,othrsp - 8,othrsp)
                                ,othrsp
                            )
                            ELSE 0
                            END)) 
                        AS OT_OTReg,
                        SUM((CASE 
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL AND otstatus = 1 AND idlvl NOT IN(1,2,3,4,5)  THEN 
                            IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                                ,IF(othrsp > 8,othrsp - 8,0)
                                ,othrsp
                            )
                            ELSE 0
                            END)) 
                        AS OT_RD,
                        SUM((CASE 
                            WHEN defaultschedid != 4 AND holidaytype = 'SPECIAL' AND otstatus = 1 AND idlvl NOT IN(1,2,3,4,5)  THEN 
                        IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                                ,IF(othrsp > 8,othrsp - 8,0)
                                ,othrsp
                            )
                            ELSE 0
                            END)) 
                        AS OT_SH,
                        SUM((CASE 
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'SPECIAL' AND otstatus = 1 AND idlvl NOT IN(1,2,3,4,5)  THEN 
                            IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                                ,IF(othrsp > 8,othrsp - 8,0)
                                ,othrsp
                            )
                            
                            ELSE 0
                            END)) 
                        AS OT_SHRD,
                        SUM((CASE 
                            WHEN defaultschedid != 4 AND holidaytype = 'LEGAL' AND otstatus = 1 AND idlvl NOT IN(1,2,3,4,5)  THEN 
                            IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                                ,IF(othrsp > 8,othrsp - 8,0)
                                ,othrsp
                            )
                            
                            ELSE 0
                            END)) 
                        AS OT_LH,
                        SUM((CASE 
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL' AND otstatus = 1 AND idlvl NOT IN(1,2,3,4,5)  THEN 
                            IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                                ,IF(othrsp > 8,othrsp - 8,0)
                                ,othrsp
                            )
                            
                            ELSE 0
                            END)) 
                        AS OT_LHRD,
                        SUM((CASE 
                            WHEN defaultschedid != 4 AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 AND idlvl NOT IN(1,2,3,4,5)  THEN 
                            IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                                ,IF(othrsp > 8,othrsp - 8,0)
                                ,othrsp
                            )
                            ELSE 0
                            END)) 
                        AS OT_LSH,
                        SUM((CASE 
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 AND idlvl NOT IN(1,2,3,4,5)  THEN 
                            IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                                ,IF(othrsp > 8,othrsp - 8,0)
                                ,othrsp
                            )
                            ELSE 0
                            END)) 
                        AS OT_LSHRD,
                        SUM((CASE 
                            WHEN defaultschedid != 4 AND holidaytype IS NULL THEN np
                            ELSE 0
                            END)) 
                        AS NP_NPReg,
                        SUM((CASE 
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL THEN np
                            ELSE 0
                            END)) 
                        AS NP_RD,
                        SUM((CASE 
                            WHEN defaultschedid != 4 AND holidaytype = 'SPECIAL' THEN np
                            ELSE 0
                            END)) 
                        AS NP_SH,
                        SUM((CASE 
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'SPECIAL' THEN np
                            ELSE 0
                            END)) 
                        AS NP_SHRD,
                        SUM((CASE 
                            WHEN defaultschedid != 4 AND holidaytype = 'LEGAL' THEN np
                            ELSE 0
                            END)) 
                        AS NP_LH,
                        SUM((CASE 
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL' THEN np
                            ELSE 0
                            END)) 
                        AS NP_LHRD,
                        SUM((CASE 
                            WHEN defaultschedid != 4 AND holidaytype = 'LEGAL SPECIAL' THEN np
                            ELSE 0
                            END)) 
                        AS NP_LSH,
                        SUM((CASE 
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL SPECIAL' THEN np
                            ELSE 0
                            END)) 
                        AS NP_LSHRD,
                        SUM((CASE 
                            WHEN defaultschedid != 4 AND holidaytype IS NULL AND otstatus = 1 THEN npot
                            ELSE 0
                            END)) 
                        AS NPOT_NPOT,
                        SUM((CASE 
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL AND otstatus = 1 THEN npot
                            ELSE 0
                            END)) 
                        AS NPOT_RD,
                        SUM((CASE 
                            WHEN defaultschedid != 4 AND holidaytype = 'SPECIAL' AND otstatus = 1 THEN npot
                            ELSE 0
                            END)) 
                        AS NPOT_SH,
                        SUM((CASE 
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'SPECIAL' AND otstatus = 1 THEN npot
                            ELSE 0
                            END)) 
                        AS NPOT_SHRD,
                        SUM((CASE 
                            WHEN defaultschedid != 4 AND holidaytype = 'LEGAL' AND otstatus = 1 THEN npot
                            ELSE 0
                            END)) 
                        AS NPOT_LH,
                        SUM((CASE 
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL' AND otstatus = 1 THEN npot
                            ELSE 0
                            END)) 
                        AS NPOT_LHRD,
                        SUM((CASE 
                            WHEN defaultschedid != 4 AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 THEN npot
                            ELSE 0
                            END)) 
                        AS NPOT_LSH,
                        SUM((CASE 
                            WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 THEN npot
                            ELSE 0
                            END)) 
                        AS NPOT_LSHRD,
                        SUM( IF( idlvl IN (2,3)
                                    ,othrsp
                                    ,0
                                ) 
                            ) 
                        AS otallowance,
                        SUM( 
                            IF( (acthrs - excess  < 4 AND  acthrs - excess > 2  AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW'))) AND NOT FIND_IN_SET(3, batchnum))
                                OR ((timein IS NOT NULL OR timeout IS NOT NULL OR timein != '' OR timeout != '') 
                                AND (holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') 
                                AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW'))) AND NOT FIND_IN_SET(3, batchnum)
                                AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(date_in, ' ',  IF( CAST(timein AS TIME) < CAST(loginschedref AS TIME) ,loginschedref ,timein)) AS DATETIME), CAST(CONCAT(date_out, ' ', IF( CAST(timeout AS TIME) < CAST(logoutschedref AS TIME) ,timeout ,logoutschedref)) AS DATETIME)) / 60) / 15)) * 15) < 240)
                                OR ((holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') AND othrsp < 4 AND othrsp > 2)
                                OR (location = 2  AND aaapprove IS NOT NULL AND aaapprove BETWEEN period_start AND period_endref  AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(work_date, ' ',  aastime) AS DATETIME), CAST(CONCAT(work_date, ' ', aaftime) AS DATETIME)) / 60) / 15)) * 15) < 240)
                                ,1
                                ,0)
                            )
                            AS mallowance,
                            SUM(
                                IF( ((acthrs - excess  >= 4 AND  (acthrs - excess  < 8))  AND ( location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW')) ) AND NOT FIND_IN_SET(3, batchnum))
                                OR ((timein IS NOT NULL OR timeout IS NOT NULL OR timein != '' OR timeout != '')
                                AND (holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') 
                                AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW'))) AND NOT FIND_IN_SET(3, batchnum)
                                AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(date_in, ' ',  IF( CAST(timein AS TIME) < CAST(loginschedref AS TIME) ,loginschedref ,timein)) AS DATETIME), CAST(CONCAT(date_out, ' ', IF( CAST(timeout AS TIME) < CAST(logoutschedref AS TIME) ,timeout ,logoutschedref)) AS DATETIME)) / 60) / 15)) * 15) >= 240 AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(date_in, ' ',  IF( CAST(timein AS TIME) < CAST(loginschedref AS TIME) ,loginschedref ,timein)) AS DATETIME), CAST(CONCAT(date_out, ' ', IF( CAST(timeout AS TIME) < CAST(logoutschedref AS TIME) ,timeout ,logoutschedref)) AS DATETIME)) / 60) / 15)) * 15) < 480)
                                OR ((holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') AND othrsp >= 4 AND othrsp < 8)
                                OR (location = 2  AND aaapprove IS NOT NULL AND aaapprove BETWEEN period_start AND period_endref  AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(work_date, ' ',  IF( CAST(aastime AS TIME) < CAST(loginschedref AS TIME) ,loginschedref ,aastime)) AS DATETIME), CAST(CONCAT(work_date, ' ', IF( CAST(aaftime AS TIME) < CAST(logoutschedref AS TIME) ,aaftime ,logoutschedref)) AS DATETIME)) / 60) / 15)) * 15) < 480)
                                        ,1
                                    ,0
                                )  
                            )AS hallowance,
                            SUM(
                                IF( (acthrs - excess >= 8 AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW')) ) AND NOT FIND_IN_SET(3, batchnum))
                                OR ((timein IS NOT NULL OR timeout IS NOT NULL OR timein != '' OR timeout != '')
                                AND (holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') 
                                AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW'))) AND NOT FIND_IN_SET(3, batchnum)
                                AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(date_in, ' ',  IF( CAST(timein AS TIME) < CAST(loginschedref AS TIME) ,loginschedref ,timein)) AS DATETIME), CAST(CONCAT(date_out, ' ', IF( CAST(timeout AS TIME) < CAST(logoutschedref AS TIME) ,timeout ,logoutschedref)) AS DATETIME)) / 60) / 15)) * 15) >= 480)
                                OR ((holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') AND othrsp >= 8)
                                OR (location = 2  AND aaapprove IS NOT NULL AND aaapprove BETWEEN period_start AND period_endref  AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(work_date, ' ',  IF( CAST(aastime AS TIME) < CAST(loginschedref AS TIME) ,loginschedref ,aastime)) AS DATETIME), CAST(CONCAT(work_date, ' ', IF( CAST(aaftime AS TIME) < CAST(logoutschedref AS TIME) ,timeout ,logoutschedref)) AS DATETIME)) / 60) / 15)) * 15) >= 480)
                                    ,1
                                    ,0
                                )
                            )AS wallowance,
                            SUM(
                            IF ( (holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') 
                            AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW')) )
                                AND NOT FIND_IN_SET(3, batchnum)
                                ,IF( (allowref != 0  AND (rdacthrs >= 4 AND rdacthrs < 8 )) AND (timein IS NOT NULL OR timeout IS NOT NULL OR timein != '' OR timeout != '' OR aaapprove IS NOT NULL)  ,1,0)
                                ,0
                            )
                            )AS shallowance,
                            SUM(
                            IF ((holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') 
                            AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW')) )
                                AND NOT FIND_IN_SET(3, batchnum)
                                ,IF( (allowref != 0 AND (rdacthrs >= 8)) AND (timein IS NOT NULL OR timeout IS NOT NULL OR timein != '' OR timeout != '' OR aaapprove IS NOT NULL)  ,1,0)
                                ,0
                            )
                            )AS swallowance
                    ";

    $Qry->fields = "idpayperiod = '" . $param->id . "'";
    $rs = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1)
    {
        while ($row = mysqli_fetch_array($rs))
        {
            $adj_late = '';
            $adj_late_rd = '';
            $adj_late_sh = '';
            $adj_late_shrd = '';
            $adj_late_lh = '';
            $adj_late_lhrd = '';
            $adj_late_lsh = '';
            $adj_late_lshrd = '';
            $adj_ut = '';
            $adj_ut_rd = '';
            $adj_ut_sh = '';
            $adj_ut_shrd = '';
            $adj_ut_lh = '';
            $adj_ut_lhrd = '';
            $adj_ut_lsh = '';
            $adj_ut_lshrd = '';
            $adj_absent = '';
            $adj_absent_rd = '';
            $adj_absent_sh = '';
            $adj_absent_shrd = '';
            $adj_absent_lh = '';
            $adj_absent_lhrd = '';
            $adj_absent_lsh = '';
            $adj_absent_lshrd = '';
            $adj_ot = '';
            $adj_ot_rd = '';
            $adj_ot_sh = '';
            $adj_ot_shrd = '';
            $adj_ot_lh = '';
            $adj_ot_lhrd = '';
            $adj_ot_lsh = '';
            $adj_ot_lshrd = '';
            $adj_np = '';
            $adj_np_rd = '';
            $adj_np_sh = '';
            $adj_np_shrd = '';
            $adj_np_lh = '';
            $adj_np_lhrd = '';
            $adj_np_lsh = '';
            $adj_np_lshrd = '';
            $adj_npot = '';
            $adj_npot_rd = '';
            $adj_npot_sh = '';
            $adj_npot_shrd = '';
            $adj_npot_lh = '';
            $adj_npot_lhrd = '';
            $adj_npot_lsh = '';
            $adj_npot_lshrd = '';
            $adj_vl = '';
            $adj_sl = '';
            $adj_sil = '';
            $adj_cl = '';
            $adj_ml = '';
            $adj_mc = '';
            $adj_sp = '';
            $adj_pl = '';
            $adj_el = '';
            $adj_bl = '';
            $adj_obt = '';
            $adj_bdl = '';
            $adj_spl = '';
            $adj_lwop = '';

            $adj_cvl = '';
            $adj_menstl = '';
            $adj_wl = '';

            $otallowance = '';
            $mallowance = '';
            $hallowance = '';
            $wallowance = '';
            $shallowance = '';
            $swallowance = '';

            $Qryts = new Query();
            $Qryts->table = "tbltimesheetsummary";
            $Qryts->selected = "SUM(rhrs) as rhrs,
                                SUM(late) as late,
                                SUM(ut) as ut,
                                SUM(abs) as abs,
                                SUM(whrs) as whrs,
                                SUM(tc_vl) as tc_vl,
                                SUM(tc_sl) as tc_sl,
                                SUM(tc_cl) as tc_cl,
                                SUM(tc_ml) as tc_ml,
                                SUM(tc_mc) as tc_mc,
                                SUM(tc_pl) as tc_pl,
                                SUM(tc_el) as tc_el,
                                SUM(tc_bl) as tc_bl,
                                SUM(tc_obt) as tc_obt,
                                SUM(tc_bdl) as tc_bdl,
                                SUM(tc_spl) as tc_spl,
                                SUM(tc_lwop) as tc_lwop,

                                SUM(tc_cvl) as tc_cvl,
                                SUM(tc_menstl) as tc_menstl,
                                SUM(tc_wl) as tc_wl,

                                SUM(tc_rd) as tc_rd,
                                SUM(h_sh) as h_sh,
                                SUM(h_shrd) as h_shrd,
                                SUM(h_lh) as h_lh,
                                SUM(h_lhrd) as h_lhrd,
                                SUM(h_lsh) as h_lsh,
                                SUM(h_lshrd) as h_lshrd,
                                SUM(ot_reg) as ot_reg,
                                SUM(ot_rd) as ot_rd,
                                SUM(ot_sh) as ot_sh,
                                SUM(ot_shrd) as ot_shrd,
                                SUM(ot_lh) as ot_lh,
                                SUM(ot_lhrd) as ot_lhrd,
                                SUM(ot_lsh) as ot_lsh,
                                SUM(ot_lshrd) as ot_lshrd,
                                SUM(np_npreg) as np_npreg,
                                SUM(np_rd) as np_rd,
                                SUM(np_sh) as np_sh,
                                SUM(np_shrd) as np_shrd,
                                SUM(np_lh) as np_lh,
                                SUM(np_lhrd) as np_lhrd,
                                SUM(np_lsh) as np_lsh,
                                SUM(np_lshrd) as np_lshrd,
                                SUM(npot_npot) as npot_npot,
                                SUM(npot_rd) as npot_rd,
                                SUM(npot_sh) as npot_sh,
                                SUM(npot_shrd) as npot_shrd,
                                SUM(npot_lh) as npot_lh,
                                SUM(npot_lhrd) as npot_lhrd,
                                SUM(npot_lsh) as npot_lsh,
                                SUM(npot_lshrd) as npot_lshrd,
                                SUM(adj_late) as adj_late,
                                SUM(adj_late_rd) as adj_late_rd,
                                SUM(adj_late_sh) as adj_late_sh,
                                SUM(adj_late_shrd) as adj_late_shrd,
                                SUM(adj_late_lh) as adj_late_lh,
                                SUM(adj_late_lhrd) as adj_late_lhrd,
                                SUM(adj_late_lsh) as adj_late_lsh,
                                SUM(adj_late_lshrd) as adj_late_lshrd,
                                SUM(adj_ut) as adj_ut,
                                SUM(adj_ut_rd) as adj_ut_rd,
                                SUM(adj_ut_sh) as adj_ut_sh,
                                SUM(adj_ut_shrd) as adj_ut_shrd,
                                SUM(adj_ut_lh) as adj_ut_lh,
                                SUM(adj_ut_lhrd) as adj_ut_lhrd,
                                SUM(adj_ut_lsh) as adj_ut_lsh,
                                SUM(adj_ut_lshrd) as adj_ut_lshrd,
                                SUM(adj_absent) as adj_absent,
                                SUM(adj_absent_rd) as adj_absent_rd,
                                SUM(adj_absent_sh) as adj_absent_sh,
                                SUM(adj_absent_shrd) as adj_absent_shrd,
                                SUM(adj_absent_lh) as adj_absent_lh,
                                SUM(adj_absent_lhrd) as adj_absent_lhrd,
                                SUM(adj_absent_lsh) as adj_absent_lsh,
                                SUM(adj_absent_lshrd) as adj_absent_lshrd,
                                SUM(adj_ot) as adj_ot,
                                SUM(adj_ot_rd) as adj_ot_rd,
                                SUM(adj_ot_sh) as adj_ot_sh,
                                SUM(adj_ot_shrd) as adj_ot_shrd,
                                SUM(adj_ot_lh) as adj_ot_lh,
                                SUM(adj_ot_lhrd) as adj_ot_lhrd,
                                SUM(adj_ot_lsh) as adj_ot_lsh,
                                SUM(adj_ot_lshrd) as adj_ot_lshrd,
                                SUM(adj_np) as adj_np,
                                SUM(adj_np_rd) as adj_np_rd,
                                SUM(adj_np_sh) as adj_np_sh,
                                SUM(adj_np_shrd) as adj_np_shrd,
                                SUM(adj_np_lh) as adj_np_lh,
                                SUM(adj_np_lhrd) as adj_np_lhrd,
                                SUM(adj_np_lsh) as adj_np_lsh,
                                SUM(adj_np_lshrd) as adj_np_lshrd,
                                SUM(adj_npot) as adj_npot,
                                SUM(adj_npot_rd) as adj_npot_rd,
                                SUM(adj_npot_sh) as adj_npot_sh,
                                SUM(adj_npot_shrd) as adj_npot_shrd,
                                SUM(adj_npot_lh) as adj_npot_lh,
                                SUM(adj_npot_lhrd) as adj_npot_lhrd,
                                SUM(adj_npot_lsh) as adj_npot_lsh,
                                SUM(adj_npot_lshrd) as adj_npot_lshrd,
                                SUM(adj_vl) as adj_vl,
                                SUM(adj_sl) as adj_sl,
                                SUM(adj_cl) as adj_cl,
                                SUM(adj_ml) as adj_ml,
                                SUM(adj_mc) as adj_mc,
                                SUM(adj_pl) as adj_pl,
                                SUM(adj_el) as adj_el,
                                SUM(adj_bl) as adj_bl,
                                SUM(adj_obt) as adj_obt,
                                SUM(adj_bdl) as adj_bdl,
                                SUM(adj_spl) as adj_spl,
                                SUM(adj_lwop) as adj_lwop,

                                SUM(adj_cvl) as adj_cvl,
                                SUM(adj_menstl) as adj_menstl,
                                SUM(adj_wl) as adj_wl,


                                SUM(otallowance) as otallowance,
                                SUM(mallowance) as mallowance,
                                SUM(hallowance) as hallowance,
                                SUM(wallowance) as wallowance,
                                SUM(shallowance) as shallowance,
                                SUM(swallowance) as swallowance
                                ";
            $Qryts->fields = "idpayperiod = '" . $param->id . "' and type  = '" . $param->type . "'";
            $rsts = $Qryts->exe_SELECT($con);
            if (mysqli_num_rows($rsts) >= 1)
            {
                if ($row1 = mysqli_fetch_array($rsts))
                {
                    if ($row1['rhrs'])
                    {
                        if ($param->type == 'Japanese' || $param->type == 'Japanese Conversion')
                        {
                            $row['Rhrs'] = 0;
                        }
                        else
                        {
                            $row['Rhrs'] = $row1['rhrs'];
                        }

                        $row['Late'] = $row1['late'];
                        $row['Undertime'] = $row1['ut'];
                        $row['Abs'] = $row1['abs'];
                        $row['WHrs'] = $row1['whrs'];
                        $row['TC_VL'] = $row1['tc_vl'];
                        $row['TC_SL'] = $row1['tc_sl'];
                        $row['TC_CL'] = $row1['tc_cl'];
                        $row['TC_ML'] = $row1['tc_ml'];
                        $row['TC_MC'] = $row1['tc_mc'];
                        $row['TC_PL'] = $row1['tc_pl'];
                        $row['TC_EL'] = $row1['tc_el'];
                        $row['TC_BL'] = $row1['tc_bl'];
                        $row['TC_OBT'] = $row1['tc_obt'];
                        $row['TC_BDL'] = $row1['tc_bdl'];
                        $row['TC_SPL'] = $row1['tc_spl'];
                        $row['TC_LWOP'] = $row1['tc_lwop'];

                        $row['TC_CVL'] = $row1['tc_cvl'];
                        $row['TC_MENSTL'] = $row1['tc_menstl'];
                        $row['TC_WL'] = $row1['tc_wl'];

                        $row['TC_RD'] = $row1['tc_rd'];
                        $row['H_SH'] = $row1['h_sh'];
                        $row['H_SHRD'] = $row1['h_shrd'];
                        $row['H_LH'] = $row1['h_lh'];
                        $row['H_LHRD'] = $row1['h_lhrd'];
                        $row['H_LSH'] = $row1['h_lsh'];
                        $row['H_LSHRD'] = $row1['h_lshrd'];
                        $row['OT_OTReg'] = $row1['ot_reg'];
                        $row['OT_RD'] = $row1['ot_rd'];
                        $row['OT_SH'] = $row1['ot_sh'];
                        $row['OT_SHRD'] = $row1['ot_shrd'];
                        $row['OT_LH'] = $row1['ot_lh'];
                        $row['OT_LHRD'] = $row1['ot_lhrd'];
                        $row['OT_LSH'] = $row1['ot_lsh'];
                        $row['OT_LSHRD'] = $row1['ot_lshrd'];
                        $row['NP_NPReg'] = $row1['np_npreg'];
                        $row['NP_RD'] = $row1['np_rd'];
                        $row['NP_SH'] = $row1['np_sh'];
                        $row['NP_SHRD'] = $row1['np_shrd'];
                        $row['NP_LH'] = $row1['np_lh'];
                        $row['NP_LHRD'] = $row1['np_lhrd'];
                        $row['NP_LSH'] = $row1['np_lsh'];
                        $row['NP_LSHRD'] = $row1['np_lshrd'];
                        $row['NPOT_NPOT'] = $row1['npot_npot'];
                        $row['NPOT_RD'] = $row1['npot_rd'];
                        $row['NPOT_SH'] = $row1['npot_sh'];
                        $row['NPOT_SHRD'] = $row1['npot_shrd'];
                        $row['NPOT_LH'] = $row1['npot_lh'];
                        $row['NPOT_LHRD'] = $row1['npot_lhrd'];
                        $row['NPOT_LSH'] = $row1['npot_lsh'];
                        $row['NPOT_LSHRD'] = $row1['npot_lshrd'];
                        $adj_late = $row1['adj_late'];
                        $adj_late_rd = $row1['adj_late_rd'];
                        $adj_late_sh = $row1['adj_late_sh'];
                        $adj_late_shrd = $row1['adj_late_shrd'];
                        $adj_late_lh = $row1['adj_late_lh'];
                        $adj_late_lhrd = $row1['adj_late_lhrd'];
                        $adj_late_lsh = $row1['adj_late_lsh'];
                        $adj_late_lshrd = $row1['adj_late_lshrd'];
                        $adj_ut = $row1['adj_ut'];
                        $adj_ut_rd = $row1['adj_ut_rd'];
                        $adj_ut_sh = $row1['adj_ut_sh'];
                        $adj_ut_shrd = $row1['adj_ut_shrd'];
                        $adj_ut_lh = $row1['adj_ut_lh'];
                        $adj_ut_lhrd = $row1['adj_ut_lhrd'];
                        $adj_ut_lsh = $row1['adj_ut_lsh'];
                        $adj_ut_lshrd = $row1['adj_ut_lshrd'];
                        $adj_absent = $row1['adj_absent'];
                        $adj_absent_rd = $row1['adj_absent_rd'];
                        $adj_absent_sh = $row1['adj_absent_sh'];
                        $adj_absent_shrd = $row1['adj_absent_shrd'];
                        $adj_absent_lh = $row1['adj_absent_lh'];
                        $adj_absent_lhrd = $row1['adj_absent_lhrd'];
                        $adj_absent_lsh = $row1['adj_absent_lsh'];
                        $adj_absent_lshrd = $row1['adj_absent_lshrd'];
                        $adj_ot = $row1['adj_ot'];
                        $adj_ot_rd = $row1['adj_ot_rd'];
                        $adj_ot_sh = $row1['adj_ot_sh'];
                        $adj_ot_shrd = $row1['adj_ot_shrd'];
                        $adj_ot_lh = $row1['adj_ot_lh'];
                        $adj_ot_lhrd = $row1['adj_ot_lhrd'];
                        $adj_ot_lsh = $row1['adj_ot_lsh'];
                        $adj_ot_lshrd = $row1['adj_ot_lshrd'];
                        $adj_np = $row1['adj_np'];
                        $adj_np_rd = $row1['adj_np_rd'];
                        $adj_np_sh = $row1['adj_np_sh'];
                        $adj_np_shrd = $row1['adj_np_shrd'];
                        $adj_np_lh = $row1['adj_np_lh'];
                        $adj_np_lhrd = $row1['adj_np_lhrd'];
                        $adj_np_lsh = $row1['adj_np_lsh'];
                        $adj_np_lshrd = $row1['adj_np_lshrd'];
                        $adj_npot = $row1['adj_npot'];
                        $adj_npot_rd = $row1['adj_npot_rd'];
                        $adj_npot_sh = $row1['adj_npot_sh'];
                        $adj_npot_shrd = $row1['adj_npot_shrd'];
                        $adj_npot_lh = $row1['adj_npot_lh'];
                        $adj_npot_lhrd = $row1['adj_npot_lhrd'];
                        $adj_npot_lsh = $row1['adj_npot_lsh'];
                        $adj_npot_lshrd = $row1['adj_npot_lshrd'];
                        $adj_vl = $row1['adj_vl'];
                        $adj_sl = $row1['adj_sl'];
                        $adj_cl = $row1['adj_cl'];
                        $adj_ml = $row1['adj_ml'];
                        $adj_mc = $row1['adj_mc'];
                        $adj_pl = $row1['adj_pl'];
                        $adj_el = $row1['adj_el'];
                        $adj_bl = $row1['adj_bl'];
                        $adj_obt = $row1['adj_obt'];
                        $adj_bdl = $row1['adj_bdl'];
                        $adj_spl = $row1['adj_spl'];
                        $adj_lwop = $row1['adj_lwop'];

                        $adj_cvl = $row1['adj_cvl'];
                        $adj_menstl = $row1['adj_menstl'];
                        $adj_wl = $row1['adj_wl'];

                        $otallowance = $row1['otallowance'];
                        $mallowance = $row1['mallowance'];
                        $hallowance = $row1['hallowance'];
                        $wallowance = $row1['wallowance'];
                        $shallowance = $row1['shallowance'];
                        $swallowance = $row1['swallowance'];
                    }
                }
            }
            $data[] = array(
                "Rhrs" => round($row['Rhrs'], 2) ,
                "Late" => round($row['Late'], 2) ,
                "Undertime" => round($row['Undertime'], 2) ,
                "Abs" => round($row['Abs'], 2) ,
                "WHrs" => round($row['WHrs'], 2) ,
                "TC_VL" => round($row['TC_VL'], 2) ,
                "TC_SL" => round($row['TC_SL'], 2) ,
                "TC_CL" => round($row['TC_CL'], 2) ,
                "TC_ML" => round($row['TC_ML'], 2) ,
                "TC_MC" => round($row['TC_MC'], 2) ,
                "TC_PL" => round($row['TC_PL'], 2) ,
                "TC_EL" => round($row['TC_EL'], 2) ,
                "TC_BL" => round($row['TC_BL'], 2) ,
                "TC_OBT" => round($row['TC_OBT'], 2) ,
                "TC_BDL" => round($row['TC_BDL'], 2) ,
                "TC_SPL" => round($row['TC_SPL'], 2) ,
                "TC_LWOP" => round($row['TC_LWOP'], 2) ,

                "TC_CVL" => round($row['TC_CVL'], 2) ,
                "TC_MENSTL" => round($row['TC_MENSTL'], 2) ,
                "TC_WL" => round($row['TC_WL'], 2) ,

                "TC_RD" => round($row['TC_RD'], 2) ,
                "H_SH" => round($row['H_SH'], 2) ,
                "H_SHRD" => round($row['H_SHRD'], 2) ,
                "H_LH" => round($row['H_LH'], 2) ,
                "H_LHRD" => round($row['H_LHRD'], 2) ,
                "H_LSH" => round($row['H_LSH'], 2) ,
                "H_LSHRD" => round($row['H_LSHRD'], 2) ,
                "OT_OTReg" => round($row['OT_OTReg'], 2) ,
                "OT_RD" => round($row['OT_RD'], 2) ,
                "OT_SH" => round($row['OT_SH'], 2) ,
                "OT_SHRD" => round($row['OT_SHRD'], 2) ,
                "OT_LH" => round($row['OT_LH'], 2) ,
                "OT_LHRD" => round($row['OT_LHRD'], 2) ,
                "OT_LSH" => round($row['OT_LSH'], 2) ,
                "OT_LSHRD" => round($row['OT_LSHRD'], 2) ,
                "NP_NPReg" => round($row['NP_NPReg'], 2) ,
                "NP_RD" => round($row['NP_RD'], 2) ,
                "NP_SH" => round($row['NP_SH'], 2) ,
                "NP_SHRD" => round($row['NP_SHRD'], 2) ,
                "NP_LH" => round($row['NP_LH'], 2) ,
                "NP_LHRD" => round($row['NP_LHRD'], 2) ,
                "NP_LSH" => round($row['NP_LSH'], 2) ,
                "NP_LSHRD" => round($row['NP_LSHRD'], 2) ,
                "NPOT_NPOT" => round($row['NPOT_NPOT'], 2) ,
                "NPOT_RD" => round($row['NPOT_RD'], 2) ,
                "NPOT_SH" => round($row['NPOT_SH'], 2) ,
                "NPOT_SHRD" => round($row['NPOT_SHRD'], 2) ,
                "NPOT_LH" => round($row['NPOT_LH'], 2) ,
                "NPOT_LHRD" => round($row['NPOT_LHRD'], 2) ,
                "NPOT_LSH" => round($row['NPOT_LSH'], 2) ,
                "NPOT_LSHRD" => round($row['NPOT_LSHRD'], 2) ,
                "adj_late" => round($adj_late, 2) ,
                "adj_late_rd" => round($adj_late_rd, 2) ,
                "adj_late_sh" => round($adj_late_sh, 2) ,
                "adj_late_shrd" => round($adj_late_shrd, 2) ,
                "adj_late_lh" => round($adj_late_lh, 2) ,
                "adj_late_lhrd" => round($adj_late_lhrd, 2) ,
                "adj_late_lsh" => round($adj_late_lsh, 2) ,
                "adj_late_lshrd" => round($adj_late_lshrd, 2) ,
                "adj_ut" => round($adj_ut, 2) ,
                "adj_ut_rd" => round($adj_ut_rd, 2) ,
                "adj_ut_sh" => round($adj_ut_sh, 2) ,
                "adj_ut_shrd" => round($adj_ut_shrd, 2) ,
                "adj_ut_lh" => round($adj_ut_lh, 2) ,
                "adj_ut_lhrd" => round($adj_ut_lhrd, 2) ,
                "adj_ut_lsh" => round($adj_ut_lsh, 2) ,
                "adj_ut_lshrd" => round($adj_ut_lshrd, 2) ,
                "adj_absent" => round($adj_absent, 2) ,
                "adj_absent_rd" => round($adj_absent_rd, 2) ,
                "adj_absent_sh" => round($adj_absent_sh, 2) ,
                "adj_absent_shrd" => round($adj_absent_shrd, 2) ,
                "adj_absent_lh" => round($adj_absent_lh, 2) ,
                "adj_absent_lhrd" => round($adj_absent_lhrd, 2) ,
                "adj_absent_lsh" => round($adj_absent_lsh, 2) ,
                "adj_absent_lshrd" => round($adj_absent_lshrd, 2) ,
                "adj_ot" => round($adj_ot, 2) ,
                "adj_ot_rd" => round($adj_ot_rd, 2) ,
                "adj_ot_sh" => round($adj_ot_sh, 2) ,
                "adj_ot_shrd" => round($adj_ot_shrd, 2) ,
                "adj_ot_lh" => round($adj_ot_lh, 2) ,
                "adj_ot_lhrd" => round($adj_ot_lhrd, 2) ,
                "adj_ot_lsh" => round($adj_ot_lsh, 2) ,
                "adj_ot_lshrd" => round($adj_ot_lshrd, 2) ,
                "adj_np" => round($adj_np, 2) ,
                "adj_np_rd" => round($adj_np_rd, 2) ,
                "adj_np_sh" => round($adj_np_sh, 2) ,
                "adj_np_shrd" => round($adj_np_shrd, 2) ,
                "adj_np_lh" => round($adj_np_lh, 2) ,
                "adj_np_lhrd" => round($adj_np_lhrd, 2) ,
                "adj_np_lsh" => round($adj_np_lsh, 2) ,
                "adj_np_lshrd" => round($adj_np_lshrd, 2) ,
                "adj_npot" => round($adj_npot, 2) ,
                "adj_npot_rd" => round($adj_npot_rd, 2) ,
                "adj_npot_sh" => round($adj_npot_sh, 2) ,
                "adj_npot_shrd" => round($adj_npot_shrd, 2) ,
                "adj_npot_lh" => round($adj_npot_lh, 2) ,
                "adj_npot_lhrd" => round($adj_npot_lhrd, 2) ,
                "adj_npot_lsh" => round($adj_npot_lsh, 2) ,
                "adj_npot_lshrd" => round($adj_npot_lshrd, 2) ,
                "adj_vl" => round($adj_vl, 2) ,
                "adj_sl" => round($adj_sl, 2) ,
                "adj_cl" => round($adj_cl, 2) ,
                "adj_ml" => round($adj_ml, 2) ,
                "adj_mc" => round($adj_mc, 2) ,
                "adj_pl" => round($adj_pl, 2) ,
                "adj_el" => round($adj_el, 2) ,
                "adj_bl" => round($adj_bl, 2) ,
                "adj_obt" => round($adj_obt, 2) ,
                "adj_bdl" => round($adj_bdl, 2) ,
                "adj_spl" => round($adj_spl, 2) ,
                "adj_lwop" => round($adj_lwop, 2) ,

                "adj_cvl" => round($adj_cvl, 2) ,
                "adj_menstl" => round($adj_menstl, 2) ,
                "adj_wl" => round($adj_wl, 2) ,

                "otallowance" => round($otallowance, 2) ,
                "mallowance" => round($mallowance, 2) ,
                "hallowance" => round($hallowance, 2) ,
                "wallowance" => round($wallowance, 2) ,
                "shallowance" => round($shallowance, 2) ,
                "swallowance" => round($swallowance, 2)

            );
        }
    }
    return $data;
}

function getPaygroup($con, $id)
{
    $Qry = new Query();
    $Qry->table = "tblpaygrp";
    $Qry->selected = "`group`";
    $Qry->fields = "id='" . $id . "'";
    $rs = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1)
    {
        if ($row = mysqli_fetch_array($rs))
        {
            return $row['group'];
        }
    }
}

function getPayid($con, $param)
{
    if ($param->type == 'Local Employee')
    {
        $type = 'ho';
    }
    if ($param->type == 'Helper')
    {
        $type = 'helper';
    }
    if ($param->type == 'Japanese')
    {
        $type = 'hajap';
    }
    if ($param->type == 'Japanese Conversion')
    {
        $type = 'hajapc';
    }

    $Qry = new Query();
    $Qry->table = "vw_payperiod_all";
    $Qry->selected = "id";
    $Qry->fields = "pay_date='" . $param->paydate . "' AND type ='" . $type . "' ";
    $rs = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1)
    {
        if ($row = mysqli_fetch_assoc($rs))
        {
            return $row['id'];
        }
    }
}

?>
