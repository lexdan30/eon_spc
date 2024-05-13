<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param  = json_decode(file_get_contents('php://input'));
$ids='';

$idpayperiod = array(  
    "period"		=> getPayPeriod($con),
);

$date =  $idpayperiod['period']['pay_start'];
$date1 =  $idpayperiod['period']['pay_end'];

if( !empty( $param->info->classi ) ){
    $dept = $param->info->classi;
    if (!empty($dept)) {
        $arr_id = array();
        $arr    = getHierarchy($con, $dept);
        array_push($arr_id, $dept);
        if (!empty($arr["nodechild"])) {
            $a = getChildNode($arr_id, $arr["nodechild"]);
            if (!empty($a)) {
                foreach ($a as $v) {
                    array_push($arr_id, $v);
                }
            }
        }
        if (count($arr_id) == 1) {
            $ids = $arr_id[0];
        } else {
            $ids = implode(",", $arr_id);
        }
    }
}

$Qry 			= new Query();



if($idpayperiod['period']['type'] == 'Helper'){
    $Qry->table     = "vw_timesheetfinal_helper";
}else if($idpayperiod['period']['type'] == 'Japanese'){
    $Qry->table     = "vw_timesheetfinal_japanese";
}else if($idpayperiod['period']['type'] == 'Japanese Conversion'){
    $Qry->table     = "vw_timesheetfinal_japanesec";
}else{
    $Qry->table     = "vw_timesheetfinal_ho";
}

$Qry->selected  = "tid,
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
                        WHEN defaultschedid != 4 AND holidaytype IS NULL AND otstatus = 1  AND otapprove BETWEEN period_start AND period_endref AND idlvl NOT IN(1,2,3,4,5)  THEN 
                        IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                            ,IF(othrsp > 8,othrsp - 8,othrsp)
                            ,othrsp
                        )
                        ELSE 0
                        END)) 
                    AS OT_OTReg,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref AND idlvl NOT IN(1,2,3,4,5)  THEN 
                        IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                            ,IF(othrsp > 8,othrsp - 8,0)
                            ,othrsp
                        )
                        ELSE 0
                        END)) 
                    AS OT_RD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref AND idlvl NOT IN(1,2,3,4,5)  THEN 
                        IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                                ,IF(othrsp > 8,othrsp - 8,0)
                                ,othrsp
                            )
                        ELSE 0
                        END)) 
                    AS OT_SH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref AND idlvl NOT IN(1,2,3,4,5)  THEN 
                        IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                            ,IF(othrsp > 8,othrsp - 8,0)
                            ,othrsp
                        )
                        ELSE 0
                        END)) 
                    AS OT_SHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref AND idlvl NOT IN(1,2,3,4,5)  THEN 
                        IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                            ,IF(othrsp > 8,othrsp - 8,0)
                            ,othrsp
                        )
                        ELSE 0
                        END)) 
                    AS OT_LH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref AND idlvl NOT IN(1,2,3,4,5)  THEN 
                        IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                            ,IF(othrsp > 8,othrsp - 8,0)
                            ,othrsp
                        )
                        ELSE 0
                        END)) 
                    AS OT_LHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref AND idlvl NOT IN(1,2,3,4,5)  THEN 
                        IF(DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday'
                            ,IF(othrsp > 8,othrsp - 8,0)
                            ,othrsp
                        )
                        ELSE 0
                        END)) 
                    AS OT_LSH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref AND idlvl NOT IN(1,2,3,4,5)  THEN 
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
                        WHEN defaultschedid != 4 AND holidaytype IS NULL AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN npot
                        ELSE 0
                        END)) 
                    AS NPOT_NPOT,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN npot
                        ELSE 0
                        END)) 
                    AS NPOT_RD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN npot
                        ELSE 0
                        END)) 
                    AS NPOT_SH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN npot
                        ELSE 0
                        END)) 
                    AS NPOT_SHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN npot
                        ELSE 0
                        END)) 
                    AS NPOT_LH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN npot
                        ELSE 0
                        END)) 
                    AS NPOT_LHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN npot
                        ELSE 0
                        END)) 
                    AS NPOT_LSH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN npot
                        ELSE 0
                        END)) 
                    AS NPOT_LSHRD,
                    SUM(
                        (CASE 
                            WHEN FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'AUTOOT') ) THEN othrsp
                            WHEN idlvl IN (2,3) AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN othrsp
                            ELSE 0
                        END)
                        ) 
                        AS otallowance,
                        SUM( 
                            IF( (acthrs - excess  < 4 AND  acthrs - excess > 2  AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW'))) AND NOT FIND_IN_SET(3, batchnum))
                                OR ((timein IS NOT NULL OR timeout IS NOT NULL OR timein != '' OR timeout != '') 
                                AND (holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') 
                                AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW'))) AND NOT FIND_IN_SET(3, batchnum)
                                AND  FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(date_in, ' ',  IF( CAST(timein AS TIME) < CAST(loginschedref AS TIME) ,loginschedref ,timein)) AS DATETIME), CAST(CONCAT(date_out, ' ',timeout) AS DATETIME)) / 60) / 15)) * 15) < 240)
                                OR (location = 2  AND (holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref AND othrsp < 4 AND othrsp > 2)
                                OR (location = 2  AND location = 2  AND aaapprove IS NOT NULL AND aaapprove BETWEEN period_start AND period_endref  AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(work_date, ' ',  aastime) AS DATETIME), CAST(CONCAT(work_date, ' ', aaftime) AS DATETIME)) / 60) / 15)) * 15) < 240)
                            ,1
                            ,0)
                        )
                        AS mallowance,
                        SUM(
                            IF( ((acthrs - excess  >= 4 AND  (acthrs - excess  < 8))  AND ( location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW')) ) AND NOT FIND_IN_SET(3, batchnum))
                                    OR ((timein IS NOT NULL OR timeout IS NOT NULL OR timein != '' OR timeout != '')
                                    AND (holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') 
                                    AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW'))) AND NOT FIND_IN_SET(3, batchnum)
                                    AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(date_in, ' ', timein) AS DATETIME), CAST(CONCAT(date_out, ' ', timeout) AS DATETIME)) / 60) / 15)) * 15) >= 240 AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(date_in, ' ',  timein) AS DATETIME), CAST(CONCAT(date_out, ' ', timeout) AS DATETIME)) / 60) / 15)) * 15) < 480)
                                    OR (location = 2  AND (holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref AND othrsp >= 4 AND othrsp < 8)
                                    OR (location = 2  AND aaapprove IS NOT NULL AND aaapprove BETWEEN period_start AND period_endref  AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(work_date, ' ',  IF( CAST(aastime AS TIME) < CAST(loginschedref AS TIME) ,loginschedref ,aastime)) AS DATETIME), CAST(CONCAT(work_date, ' ', IF( CAST(aaftime AS TIME) < CAST(logoutschedref AS TIME) ,aaftime ,logoutschedref)) AS DATETIME)) / 60) / 15)) * 15) < 480)
                                ,1
                                ,0
                            )  
                        )
                        AS hallowance,
                        SUM(
                            IF( (acthrs - excess >= 8 AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW')) ) AND NOT FIND_IN_SET(3, batchnum))
                                    OR ((timein IS NOT NULL OR timeout IS NOT NULL OR timein != '' OR timeout != '')
                                    AND (holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') 
                                    AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW'))) AND NOT FIND_IN_SET(3, batchnum)
                                    AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(date_in, ' ',  timein) AS DATETIME), CAST(CONCAT(date_out, ' ', timeout) AS DATETIME)) / 60) / 15)) * 15) >= 480)
                                    OR (location = 2  AND (holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref AND othrsp >= 8)
                                    OR (location = 2  AND aaapprove IS NOT NULL AND aaapprove BETWEEN period_start AND period_endref  AND FLOOR((((TIMESTAMPDIFF( SECOND,CAST(CONCAT(work_date, ' ',  IF( CAST(aastime AS TIME) < CAST(loginschedref AS TIME) ,loginschedref ,aastime)) AS DATETIME), CAST(CONCAT(work_date, ' ', IF( CAST(aaftime AS TIME) < CAST(logoutschedref AS TIME) ,timeout ,logoutschedref)) AS DATETIME)) / 60) / 15)) * 15) >= 480)
                                ,1
                                ,0
                            )
                        )
                        AS wallowance,
                        SUM(
                            IF ( (holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') 
                            AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW')) )
                                AND NOT FIND_IN_SET(3, batchnum)
                                ,IF( (allowref != 0  AND (rdacthrs >= 4 AND rdacthrs < 8 )) AND (timein IS NOT NULL OR timeout IS NOT NULL OR timein != '' OR timeout != '' OR aaapprove IS NOT NULL)  ,1,0)
                                ,0
                            )
                        )
                        AS shallowance,
                        SUM(
                            IF ((holiday IS NOT NULL OR DAYNAME(work_date) = 'Sunday'  OR DAYNAME(work_date) = 'Saturday') 
                            AND (location = 2 OR FIND_IN_SET(tid, (SELECT `value` FROM tblpreference WHERE alias = 'MSALLOW')) )
                                AND NOT FIND_IN_SET(3, batchnum)
                                ,IF( (allowref != 0 AND (rdacthrs >= 8)) AND (timein IS NOT NULL OR timeout IS NOT NULL OR timein != '' OR timeout != '' OR aaapprove IS NOT NULL)  ,1,0)
                                ,0
                            )
                        )
                        AS swallowance
                    ";

if(!empty( $param->info->employee)){
    $Qry->fields    = "tid = '".$param->info->employee."' AND work_date BETWEEN '".$date."' AND '".$date1."' GROUP BY tid  ORDER BY CONCAT(tid,work_date) ASC";
}else if($ids == ''){
    $Qry->fields    = "work_date BETWEEN '".$date."' AND '".$date1."' GROUP BY tid  ORDER BY CONCAT(tid,work_date) ASC";
}else{
    $Qry->fields    = "idunit in (".$ids.") AND  work_date BETWEEN '".$date."' AND '".$date1."' GROUP BY tid  ORDER BY CONCAT(tid,work_date) ASC";
}

$rs = $Qry->exe_SELECT($con);

if(mysqli_num_rows($rs)>= 1){
    
    while($row=mysqli_fetch_assoc($rs)){ 
        deletets($con,$row['tid'], $idpayperiod);
        
        $Qry           = new Query();
        $Qry->table    = "tbltimesheetsummary";
        $Qry->selected = "type,
                            `idpayperiod`, 
                            idpaygrp,
                            `idacct`, 
                            `idbunit`, 
                            `rhrs`, 
                            `late`, 
                            `ut`, 
                            `abs`, 
                            `whrs`, 
                            `tc_vl`, 
                            `tc_sl`, 
                            `tc_cl`, 
                            `tc_ml`, 
                            `tc_mc`, 
                            `tc_pl`, 
                            `tc_el`,
                            `tc_bl`, 
                            `tc_obt`,
                            `tc_bdl`,
                            `tc_spl`,
                            `tc_lwop`,
                            `tc_cvl`, 
                            `tc_menstl`, 
                            `tc_wl`,
                            `tc_rd`,
                            `h_sh`,
                            `h_shrd`,
                            `h_lh`, 
                            `h_lhrd`, 
                            `h_lsh`, 
                            `h_lshrd`, 
                            `ot_reg`, 
                            `ot_rd`, 
                            `ot_sh`, 
                            `ot_shrd`, 
                            `ot_lh`, 
                            `ot_lhrd`, 
                            `ot_lsh`, 
                            `ot_lshrd`, 
                            `np_npreg`, 
                            `np_rd`, 
                            `np_sh`, 
                            `np_shrd`, 
                            `np_lh`, 
                            `np_lhrd`, 
                            `np_lsh`, 
                            `np_lshrd`, 
                            `npot_npot`, 
                            `npot_rd`, 
                            `npot_sh`, 
                            `npot_shrd`, 
                            `npot_lh`, 
                            `npot_lhrd`,
                            `npot_lsh`,
                            `npot_lshrd`,
                            `otallowance`,
                            `mallowance`,
                            `hallowance`,
                            `wallowance`,
                            `shallowance`,
                            `swallowance`
                            ";
        $Qry->fields   = "'".$idpayperiod['period']['type']."',
                            '".$row['idpayperiod']."',
                            '".$row['idpaygrp']."',
                            '".$row['tid']."',
                            '".$row['idunit']."',
                            '".round($row['Rhrs'],2)."',
                            '".round($row['Late'],2)."',
                            '".round($row['Undertime'],2)."',
                            '".round($row['Abs'],2)."',
                            '".round($row['WHrs'],2)."',
                            '".round($row['TC_VL'],2)."',
                            '".round($row['TC_SL'],2)."',
                            '".round($row['TC_CL'],2)."',
                            '".round($row['TC_ML'],2)."',
                            '".round($row['TC_MC'],2)."',
                            '".round($row['TC_PL'],2)."',
                            '".round($row['TC_EL'],2)."',
                            '".round($row['TC_BL'],2)."',
                            '".round($row['TC_OBT'],2)."',
                            '".round($row['TC_BDL'],2)."',
                            '".round($row['TC_SPL'],2)."',
                            '".round($row['TC_LWOP'],2)."',
                              
                            '".round($row['TC_CVL'],2)."',
                            '".round($row['TC_MENSTL'],2)."',
                            '".round($row['TC_WL'],2)."',


                            '".round($row['TC_RD'],2)."',
                            '".round($row['H_SH'],2)."',
                            '".round($row['H_SHRD'],2)."',
                            '".round($row['H_LH'],2)."',
                            '".round($row['H_LHRD'],2)."',
                            '".round($row['H_LSH'],2)."',
                            '".round($row['H_LSHRD'],2)."',
                            '".round($row['OT_OTReg'],2)."',
                            '".round($row['OT_RD'],2)."',
                            '".round($row['OT_SH'],2)."',
                            '".round($row['OT_SHRD'],2)."',
                            '".round($row['OT_LH'],2)."',
                            '".round($row['OT_LHRD'],2)."',
                            '".round($row['OT_LSH'],2)."',
                            '".round($row['OT_LSHRD'],2)."',
                            '".round($row['NP_NPReg'],2)."',
                            '".round($row['NP_RD'],2)."',
                            '".round($row['NP_SH'],2)."',
                            '".round($row['NP_SHRD'],2)."',
                            '".round($row['NP_LH'],2)."',
                            '".round($row['NP_LHRD'],2)."',
                            '".round($row['NP_LSH'],2)."',
                            '".round($row['NP_LSHRD'],2)."',
                            '".round($row['NPOT_NPOT'],2)."',
                            '".round($row['NPOT_RD'],2)."',
                            '".round($row['NPOT_SH'],2)."',
                            '".round($row['NPOT_SHRD'],2)."',
                            '".round($row['NPOT_LH'],2)."',
                            '".round($row['NPOT_LHRD'],2)."',
                            '".round($row['NPOT_LSH'],2)."',
                            '".round($row['NPOT_LSHRD'],2)."',
                            '".round($row['otallowance'],2)."',
                            '".round($row['mallowance'],2)."',
                            '".round($row['hallowance'],2)."',
                            '".round($row['wallowance'],2)."',
                            '".round($row['shallowance'],2)."',
                            '".round($row['swallowance'],2)."'
                            ";                      
        $Qry->exe_INSERT($con);

        echo mysqli_error($con);

        $lastid = mysqli_insert_id($con);
        checkadjustments($con,$date,$date1,$row,$lastid);

        $data[] = array( 
            "idpayperiod"        	=> $row['idpayperiod'],
            "empid"        	        => $row['tid'],
            "empname"        	    => $row['empname'],
            "idunit"        	    => $row['idunit'],
            "Rhrs"        	        => round($row['Rhrs'],2),
            "Late"        	        => round($row['Late'],2),
            "Undertime"        	    => round($row['Undertime'],2),
            "Abs"        	        => round($row['Abs'],2),
            "WHrs"        	        => round($row['WHrs'],2),
            "TC_VL"        	        => round($row['TC_VL'],2),
            "TC_SL"        	        => round($row['TC_SL'],2),
            "TC_CL"        	        => round($row['TC_CL'],2),
            "TC_ML"        	        => round($row['TC_ML'],2),
            "TC_MC"        	        => round($row['TC_MC'],2),
            "TC_PL"        	        => round($row['TC_PL'],2),
            "TC_EL"        	        => round($row['TC_EL'],2),
            "TC_BL"        	        => round($row['TC_BL'],2),
            "TC_OBT"        	    => round($row['TC_OBT'],2),
            "TC_BDL"        	    => round($row['TC_BDL'],2),
            "TC_SPL"        	    => round($row['TC_SPL'],2),
            "TC_LWOP"        	    => round($row['TC_LWOP'],2),

            "TC_CVL"        	    => round($row['TC_CVL'],2),
            "TC_MENSTL"        	    => round($row['TC_MENSTL'],2),
            "TC_WL"        	    => round($row['TC_WL'],2),

            "TC_RD"        	        => round($row['TC_RD'],2),
            "H_SH"        	        => round($row['H_SH'],2),
            "H_SHRD"        	    => round($row['H_SHRD'],2),
            "H_LH"        	        => round($row['H_LH'],2),
            "H_LHRD"        	    => round($row['H_LHRD'],2),
            "H_LSH"        	        => round($row['H_LSH'],2),
            "H_LSHRD"        	    => round($row['H_LSHRD'],2),
            "OT_OTReg"        	    => round($row['OT_OTReg'],2),
            "OT_RD"        	        => round($row['OT_RD'],2),
            "OT_SH"        	        => round($row['OT_SH'],2),
            "OT_SHRD"        	    => round($row['OT_SHRD'],2),
            "OT_LH"        	        => round($row['OT_LH'],2),
            "OT_LHRD"        	    => round($row['OT_LHRD'],2),
            "OT_LSH"        	    => round($row['OT_LSH'],2),
            "OT_LSHRD"        	    => round($row['OT_LSHRD'],2),
            "NP_NPReg"        	    => round($row['NP_NPReg'],2),
            "NP_RD"        	        => round($row['NP_RD'],2),
            "NP_SH"        	        => round($row['NP_SH'],2),
            "NP_SHRD"        	    => round($row['NP_SHRD'],2),
            "NP_LH"        	        => round($row['NP_LH'],2),
            "NP_LHRD"        	    => round($row['NP_LHRD'],2),
            "NP_LSH"        	    => round($row['NP_LSH'],2),
            "NP_LSHRD"        	    => round($row['NP_LSHRD'],2),
            "NPOT_NPOT"        	    => round($row['NPOT_NPOT'],2),
            "NPOT_RD"        	    => round($row['NPOT_RD'],2),
            "NPOT_SH"        	    => round($row['NPOT_SH'],2),
            "NPOT_SHRD"        	    => round($row['NPOT_SHRD'],2),
            "NPOT_LH"        	    => round($row['NPOT_LH'],2),
            "NPOT_LHRD"        	    => round($row['NPOT_LHRD'],2),
            "NPOT_LSH"        	    => round($row['NPOT_LSH'],2),
            "NPOT_LSHRD"        	=> round($row['NPOT_LSHRD'],2),
            "otallowance"           => round($row['otallowance'],2),
            "mallowance"            => round($row['mallowance'],2),
            "hallowance"            => round($row['hallowance'],2),
            "wallowance"            => round($row['wallowance'],2),
            "shallowance"            => round($row['shallowance'],2),
            "swallowance"            => round($row['swallowance'],2)
        );

    }

    updatetkprocess($con);

    $myData = array('status' => 'success', 
                    'result' => $data
                );
    $return = json_encode($myData);
}else{
    $return = json_encode(array('error' => mysqli_error($con), 'query' => $Qry));
}


print $return;
mysqli_close($con);


function deletets($con,$empid, $idpayperiod){
    $Qry = new Query();	
    $Qry->table     = "tbltimesheetsummary";
    $Qry->fields    = "idpayperiod='".$idpayperiod['period']['id']."' AND type ='".$idpayperiod['period']['type']."'  AND idacct='".$empid."'";
    $rs = $Qry->exe_DELETE($con);
}

function checkadjustments($con,$date,$date1,$prow,$lastid){
    $empid = $prow['tid'];
    $idpayperiod = $prow['idpayperiod'] - 1;

    error_reporting(0);
    $dates  =  '\'\'';
    $Qry = new Query();	
    $Qry->table         = "tbltimeovertime as app LEFT JOIN tblaccountjob as aj ON app.idacct = aj.idacct";
    $Qry->selected      = "app.`date`";
    $Qry->fields        = "app.idacct = '".$empid."' AND 
                            app.stat = 1 
                            AND app.approver1_date between (CASE 
                                                                WHEN aj.idlvl IN (2) THEN DATE_ADD('".$date."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                                                ELSE DATE_ADD('".$date."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                                                            END) AND  (CASE 
                                                                    WHEN aj.idlvl IN (2) THEN DATE_ADD('".$date1."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                                                    ELSE DATE_ADD('".$date1."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                                                                END)
                            AND app.`date` < '".$date."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            // $count =  $count + $row['total'];
            updateadjustments($con,$date,$date1,$empid,$lastid,$row);
            if($dates == '\'\''){
                $dates = "'" . $row['date'] . "'";
            }else{
                $dates =  $dates . ',' .  "'" . $row['date'] . "'";
            }
           
        }
    }

    $Qry2 = new Query();	
    $Qry2->table         = "tbltimeobtrip as app LEFT JOIN tblaccountjob as aj ON app.idacct = aj.idacct";
    $Qry2->selected      = "app.`date`";
    $Qry2->fields        = "app.idacct = '".$empid."' AND 
                            app.stat = 1 
                            AND app.date_approve between (CASE 
                                                                    WHEN aj.idlvl IN (2) THEN DATE_ADD('".$date."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                                                    ELSE DATE_ADD('".$date."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                                                                END) AND  (CASE 
                                                                                WHEN aj.idlvl IN (2) THEN DATE_ADD('".$date1."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                                                                ELSE DATE_ADD('".$date1."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                                                                            END)
                            AND app.`date` < '".$date."' AND app.`date` NOT IN (".$dates.")";
    $rs2 = $Qry2->exe_SELECT($con);
    if(mysqli_num_rows($rs2)>= 1){
        while($row=mysqli_fetch_assoc($rs2)){
            updateadjustments($con,$date,$date1,$empid,$lastid,$row);
            if($dates == '\'\''){
                $dates =  "'" . $row['date'] . "'";
            }else{
                $dates =  $dates . ',' . "'" . $row['date'] . "'";
            }
        }
    }

    $Qry3 = new Query();	
    $Qry3->table         = "tbltimeshift  as app LEFT JOIN tblaccountjob as aj ON app.idacct = aj.idacct";
    $Qry3->selected      = "app.`date`";
    $Qry3->fields        = "app.idacct = '".$empid."' AND 
                            app.stat = 1 
                            AND app.approver1_date between (CASE 
                                                                WHEN aj.idlvl IN (2) THEN DATE_ADD('".$date."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                                                ELSE DATE_ADD('".$date."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                                                            END) AND  (CASE 
                                                                        WHEN aj.idlvl IN (2) THEN DATE_ADD('".$date1."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                                                        ELSE DATE_ADD('".$date1."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                                                                    END)
                            AND app.`date` < '".$date."' AND app.`date` NOT IN (".$dates.")";
    $rs3 = $Qry3->exe_SELECT($con);
    if(mysqli_num_rows($rs3)>= 1){
        while($row=mysqli_fetch_assoc($rs3)){
            updateadjustments($con,$date,$date1,$empid,$lastid,$row);
            if($dates == '\'\''){
                $dates =  "'" . $row['date'] . "'";
            }else{
                $dates =  $dates . ',' .  "'" . $row['date'] . "'";
            }
        }
    }

    $Qry4 = new Query();	
    $Qry4->table         = "tbltimeleaves as app LEFT JOIN tblaccountjob as aj ON app.idacct = aj.idacct";
    $Qry4->selected      = "app.`date`";
    $Qry4->fields        = "app.idacct = '".$empid."' AND 
                            app.stat = 1 
                            AND app.date_approve between (CASE 
                                                                WHEN aj.idlvl IN (2) THEN DATE_ADD('".$date."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                                                ELSE DATE_ADD('".$date."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                                                            END) AND  (CASE 
                                                                            WHEN aj.idlvl IN (2) THEN DATE_ADD('".$date1."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                                                            ELSE DATE_ADD('".$date1."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                                                                        END)
                            AND app.`date` < '".$date."' AND app.`date` NOT IN (".$dates.")";
    $rs4 = $Qry4->exe_SELECT($con);
    if(mysqli_num_rows($rs4)>= 1){
        while($row=mysqli_fetch_assoc($rs4)){
            updateadjustments($con,$date,$date1,$empid,$lastid,$row);
            if($dates == '\'\''){
                $dates =  "'" . $row['date'] . "'";
            }else{
                $dates =  $dates . ',' .  "'" . $row['date'] . "'";
            }
        }
    }

    $Qry5 = new Query();	
    $Qry5->table         = "tbltimeadjustment as app LEFT JOIN tblaccountjob as aj ON app.idacct = aj.idacct";
    $Qry5->selected      = "app.`date`";
    $Qry5->fields        = "app.idacct = '".$empid."' AND 
                            app.stat = 1 
                            AND app.approver1_date between (CASE 
                                                                WHEN aj.idlvl IN (2) THEN DATE_ADD('".$date."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                                                ELSE DATE_ADD('".$date."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                                                            END) AND  (CASE 
                                                                    WHEN aj.idlvl IN (2) THEN DATE_ADD('".$date1."' , INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'MAGP'),0) DAY)
                                                                    ELSE DATE_ADD('".$date1."', INTERVAL IFNULL((SELECT `value` FROM `tblpreference` WHERE alias = 'NMAGP'),0) DAY)
                                                                END)
                            AND app.`date` < '".$date."' AND app.`date` NOT IN (".$dates.")";
    $rs5 = $Qry5->exe_SELECT($con);

    if(mysqli_num_rows($rs5)>= 1){
        while($row=mysqli_fetch_assoc($rs5)){
           
            updateadjustments($con,$date,$date1,$empid,$lastid,$row);
            if($dates == '\'\''){
                $dates =  "'" . $row['date'] . "'";
            }else{
                $dates =  $dates . ',' .  "'" . $row['date'] . "'";
            }
        }
       
    }
}

function updateadjustments($con,$date,$date1,$empid,$lastid,$prow){
    $appdate = $prow['date'];

    $Qry = new Query();	
    $Qry->table         ="vw_timesheetfinal";
    $Qry->selected      ="*";
    $Qry->fields        = "work_date = '".$appdate."' AND tid = '".$empid."'";                
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            $Qry1 = new Query();	
            $Qry1->table     = "vw_timesheetaadjustmentfinal";
            $Qry1->selected  = "*";
            $Qry1->fields    = "work_date = '".$appdate."' AND tid = '".$empid."'";
            $rs1 = $Qry1->exe_SELECT($con);
            if(mysqli_num_rows($rs1)>= 1){
                if($row1=mysqli_fetch_assoc($rs1)){
                  
                    if($row['absent'] != 0){
                        
                        if($row1['obtriptype'] == 1){
                            $val = $row1['shifthrs'];
                            updateadjustmentscol($con,'adj_obt',$val,$empid,$lastid);
                            return;
                        }else if($row1['leave']){
                            if($row1['leavename'] == 'Sick Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_sl',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Vacation Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_vl',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Leave Without Pay'){
                                $val  = $row1['leave'];
                                updateadjustmentscol($con,'adj_lwop',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Solo Parent Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_spl',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Compensatory Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_cl',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Paternity Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_pl',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Birthday Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_bdl',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Emergency Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_el',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Magna Carta Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_mc',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Bereavement Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_bl',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Maternity Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_ml',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Service Incentive Leave'){
                                $val  = $row1['leave'];
                               updateadjustmentscol($con,'adj_sil',$val,$empid,$lastid);
                            }


                            if($row1['leavename'] == 'Covid Leave'){
                                $val  = $row1['leave'];
                               updateadjustmentscol($con,'adj_cvl',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Menstrual Leave'){
                                $val  = $row1['leave'];
                               updateadjustmentscol($con,'adj_menstl',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Wedding Leave'){
                                $val  = $row1['leave'];
                               updateadjustmentscol($con,'adj_wl',$val,$empid,$lastid);
                            }
                            return;
                        }else{
                           $late = $row['late'] - $row1['late'];
                           $ut = $row['ut'] - $row1['ut'];
                           $absent = $row['absent'] - $row1['absent'];

                            $ot = $row1['othrsp'] - $row['othrsp'];
                            $np = $row1['np'] - $row['np'];

                            $npot = $row1['npot'] - $row['npot'];

                           if($row['defaultschedid'] == 4 || $row['defaultsched'] == 'Rest Day'){
                               if($row['holiday']){
                                    if($row['holidaytype'] == 'Legal'){
                                        updateadjustmentscol($con,'adj_late_lhrd',$late,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_ut_lhrd',$ut,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_absent_lhrd',$absent,$empid,$lastid);

                                        if($row['idlvl'] == '2' || $row['idlvl'] == '3' ){
                                            updateadjustmentscol($con,'otallowance',$ot,$empid,$lastid);
                                        }else{
                                            updateadjustmentscol($con,'adj_ot_lhrd',$ot,$empid,$lastid);
                                            updateadjustmentscol($con,'adj_np_lhrd',$np,$empid,$lastid);
                                            updateadjustmentscol($con,'adj_npot_lhrd',$npot,$empid,$lastid);
                                        }
                                    }
                                    if($row['holidaytype'] == 'Special'){
                                        updateadjustmentscol($con,'adj_late_shrd',$late,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_ut_shrd',$ut,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_absent_shrd',$absent,$empid,$lastid);

                                        if($row['idlvl'] == '2' || $row['idlvl'] == '3' ){
                                            updateadjustmentscol($con,'otallowance',$ot,$empid,$lastid);
                                        }else{
                                            updateadjustmentscol($con,'adj_ot_shrd',$ot,$empid,$lastid);
                                            updateadjustmentscol($con,'adj_np_shrd',$np,$empid,$lastid);
                                            updateadjustmentscol($con,'adj_npot_shrd',$npot,$empid,$lastid);
                                        }
                                    }
                                    if($row['holidaytype'] == 'Legal Special'){
                                        updateadjustmentscol($con,'adj_late_lshrd',$late,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_ut_lshrd',$ut,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_absent_lshrd',$absent,$empid,$lastid);

                                        if($row['idlvl'] == '2' || $row['idlvl'] == '3' ){
                                            updateadjustmentscol($con,'otallowance',$ot,$empid,$lastid);
                                        }else{
                                            updateadjustmentscol($con,'adj_ot_lshrd',$ot,$empid,$lastid);
                                            updateadjustmentscol($con,'adj_np_lshrd',$np,$empid,$lastid);
                                            updateadjustmentscol($con,'adj_npot_lshrd',$npot,$empid,$lastid);
                                        }
                                    }
                               }else{
                                    updateadjustmentscol($con,'adj_late_rd',$late,$empid,$lastid);
                                    updateadjustmentscol($con,'adj_ut_rd',$ut,$empid,$lastid);
                                    updateadjustmentscol($con,'adj_absent_rd',$absent,$empid,$lastid);

                                    if($row['idlvl'] == '2' || $row['idlvl'] == '3' ){
                                        updateadjustmentscol($con,'otallowance',$ot,$empid,$lastid);
                                    }else{
                                        updateadjustmentscol($con,'adj_ot_rd',$ot,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_np_rd',$np,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_npot_rd',$npot,$empid,$lastid);
                                    }
                               }
                           }else{
                                if($row['holiday']){
                                    if($row['holidaytype'] == 'Legal'){
                                        updateadjustmentscol($con,'adj_late_lh',$late,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_ut_lh',$ut,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_absent_lh',$absent,$empid,$lastid);

                                        if($row['idlvl'] == '2' || $row['idlvl'] == '3' ){
                                            updateadjustmentscol($con,'otallowance',$ot,$empid,$lastid);
                                        }else{
                                            updateadjustmentscol($con,'adj_ot_lh',$ot,$empid,$lastid);
                                            updateadjustmentscol($con,'adj_np_lh',$np,$empid,$lastid);
                                            updateadjustmentscol($con,'adj_npot_lh',$npot,$empid,$lastid);
                                        }
                                    }
                                    if($row['holidaytype'] == 'Special'){
                                        updateadjustmentscol($con,'adj_late_sh',$late,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_ut_sh',$ut,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_absent_sh',$absent,$empid,$lastid);

                                        if($row['idlvl'] == '2' || $row['idlvl'] == '3' ){
                                            updateadjustmentscol($con,'otallowance',$ot,$empid,$lastid);
                                        }else{
                                            updateadjustmentscol($con,'adj_ot_sh',$ot,$empid,$lastid);
                                            updateadjustmentscol($con,'adj_np_sh',$np,$empid,$lastid);
                                            updateadjustmentscol($con,'adj_npot_sh',$npot,$empid,$lastid);
                                        }
                                    }
                                    if($row['holidaytype'] == 'Legal Special'){
                                        updateadjustmentscol($con,'adj_late_lsh',$late,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_ut_lsh',$ut,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_absent_lsh',$absent,$empid,$lastid);

                                        if($row['idlvl'] == '2' || $row['idlvl'] == '3' ){
                                            updateadjustmentscol($con,'otallowance',$ot,$empid,$lastid);
                                        }else{
                                            updateadjustmentscol($con,'adj_ot_lsh',$ot,$empid,$lastid);
                                            updateadjustmentscol($con,'adj_np_lsh',$np,$empid,$lastid);
                                            updateadjustmentscol($con,'adj_npot_lsh',$npot,$empid,$lastid);
                                        }
                                    }
                                }else{
                                        updateadjustmentscol($con,'adj_late',$late,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_ut',$ut,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_absent',$absent,$empid,$lastid);

                                        if($row['idlvl'] == '2' || $row['idlvl'] == '3' ){
                                            updateadjustmentscol($con,'otallowance',$ot,$empid,$lastid);
                                        }else{
                                            updateadjustmentscol($con,'adj_ot',$ot,$empid,$lastid);
                                            updateadjustmentscol($con,'adj_np',$np,$empid,$lastid);
                                            updateadjustmentscol($con,'adj_npot',$npot,$empid,$lastid);
                                        }
                                }
                           }

                        }
                    }else{
                    
                        $late = $row['late'] - $row1['late'];
                        $ut = $row['ut'] - $row1['ut'];

                        $ot = $row1['othrsp'] - $row['othrsp'];
                        $np = $row1['np'] - $row['np'];
                        $npot = $row1['npot'] - $row['npot'];

                        if($row['defaultschedid'] == 4 || $row['defaultsched'] == 'Rest Day'){
                            $whrs  = $row['acthrs'] - $row['excess'];
                            $whrs1  = $row1['acthrs'] - $row1['excess'];
                            $absent =  $whrs1 - $whrs;


                            if($row['holiday']){
                                 if($row['holidaytype'] == 'Legal'){
                                     updateadjustmentscol($con,'adj_late_lhrd',$late,$empid,$lastid);
                                     updateadjustmentscol($con,'adj_ut_lhrd',$ut,$empid,$lastid);
                                     updateadjustmentscol($con,'adj_absent_lhrd',$absent,$empid,$lastid);

                                     if($row['idlvl'] == '2' || $row['idlvl'] == '3' ){
                                        updateadjustmentscol($con,'otallowance',$ot,$empid,$lastid);
                                    }else{
                                        updateadjustmentscol($con,'adj_ot_lhrd',$ot,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_np_lhrd',$np,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_npot_lhrd',$npot,$empid,$lastid);
                                    }
                                 }
                                 if($row['holidaytype'] == 'Special'){
                                     updateadjustmentscol($con,'adj_late_shrd',$late,$empid,$lastid);
                                     updateadjustmentscol($con,'adj_ut_shrd',$ut,$empid,$lastid);
                                     updateadjustmentscol($con,'adj_absent_shrd',$absent,$empid,$lastid);

                                     if($row['idlvl'] == '2' || $row['idlvl'] == '3' ){
                                        updateadjustmentscol($con,'otallowance',$ot,$empid,$lastid);
                                    }else{
                                        updateadjustmentscol($con,'adj_ot_shrd',$ot,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_np_shrd',$np,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_npot_shrd',$npot,$empid,$lastid);
                                    }
                                 }
                                 if($row['holidaytype'] == 'Legal Special'){
                                     updateadjustmentscol($con,'adj_late_lshrd',$late,$empid,$lastid);
                                     updateadjustmentscol($con,'adj_ut_lshrd',$ut,$empid,$lastid);
                                     updateadjustmentscol($con,'adj_absent_lshrd',$absent,$empid,$lastid);

                                     if($row['idlvl'] == '2' || $row['idlvl'] == '3' ){
                                        updateadjustmentscol($con,'otallowance',$ot,$empid,$lastid);
                                    }else{
                                        updateadjustmentscol($con,'adj_ot_lshrd',$ot,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_np_lshrd',$np,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_npot_lshrd',$npot,$empid,$lastid);
                                    }
                                 }
                            }else{
                                 updateadjustmentscol($con,'adj_late_rd',$late,$empid,$lastid);
                                 updateadjustmentscol($con,'adj_ut_rd',$ut,$empid,$lastid);
                                 updateadjustmentscol($con,'adj_absent_rd',$absent,$empid,$lastid);

                                 if($row['idlvl'] == '2' || $row['idlvl'] == '3' ){
                                    updateadjustmentscol($con,'otallowance',$ot,$empid,$lastid);
                                }else{
                                    updateadjustmentscol($con,'adj_ot_rd',$ot,$empid,$lastid);
                                    updateadjustmentscol($con,'adj_np_rd',$np,$empid,$lastid);
                                    updateadjustmentscol($con,'adj_npot_rd',$npot,$empid,$lastid);
                                }
                            }
                        }else{
                             if($row['holiday']){
                                 if($row['holidaytype'] == 'Legal'){
                                     updateadjustmentscol($con,'adj_late_lh',$late,$empid,$lastid);
                                     updateadjustmentscol($con,'adj_ut_lh',$ut,$empid,$lastid);

                                     if($row['idlvl'] == '2' || $row['idlvl'] == '3' ){
                                        updateadjustmentscol($con,'otallowance',$ot,$empid,$lastid);
                                    }else{
                                        updateadjustmentscol($con,'adj_ot_lh',$ot,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_np_lh',$np,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_npot_lh',$npot,$empid,$lastid);
                                    }
                                 }
                                 if($row['holidaytype'] == 'Special'){
                                     updateadjustmentscol($con,'adj_late_sh',$late,$empid,$lastid);
                                     updateadjustmentscol($con,'adj_ut_sh',$ut,$empid,$lastid);

                                     if($row['idlvl'] == '2' || $row['idlvl'] == '3' ){
                                        updateadjustmentscol($con,'otallowance',$ot,$empid,$lastid);
                                    }else{
                                        updateadjustmentscol($con,'adj_ot_sh',$ot,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_np_sh',$np,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_npot_sh',$npot,$empid,$lastid);
                                    }
                                 }
                                 if($row['holidaytype'] == 'Legal Special'){
                                     updateadjustmentscol($con,'adj_late_lsh',$late,$empid,$lastid);
                                     updateadjustmentscol($con,'adj_ut_lsh',$ut,$empid,$lastid);

                                     if($row['idlvl'] == '2' || $row['idlvl'] == '3' ){
                                        updateadjustmentscol($con,'otallowance',$ot,$empid,$lastid);
                                    }else{
                                        updateadjustmentscol($con,'adj_ot_lsh',$ot,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_np_lsh',$np,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_npot_lsh',$npot,$empid,$lastid);
                                    }
                                 }
                             }else{
                                     updateadjustmentscol($con,'adj_late',$late,$empid,$lastid);
                                     updateadjustmentscol($con,'adj_ut',$ut,$empid,$lastid);

                                     if($row['idlvl'] == '2' || $row['idlvl'] == '3' ){
                                        updateadjustmentscol($con,'otallowance',$ot,$empid,$lastid);
                                    }else{
                                        updateadjustmentscol($con,'adj_ot',$ot,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_np',$np,$empid,$lastid);
                                        updateadjustmentscol($con,'adj_npot',$npot,$empid,$lastid);
                                    }
                             }
                        }
                    }
                   
                }  
            }
        }
        
    }
}

function updateadjustmentscol($con ,$cols ,$val ,$empid ,$lastid){
    $Qryleave           = new Query();
    $Qryleave->table    = "tbltimesheetsummary";
    $Qryleave->selected = $cols."=".$cols." + ".$val."";

    $Qryleave->fields   = "idacct='".$empid."' AND id='".$lastid."'";                        
    $Qryleave->exe_UPDATE($con);
}

function updatetkprocess($con){
    $idpayperiod = array(  
        "period"		=> getPayPeriod($con),
    );

    $Qry = new Query();	

    if($idpayperiod['period']['type'] == 'Helper'){
        $Qry->table     = "	tblpayperiod_helper";
    }else if($idpayperiod['period']['type'] == 'Japanese'){
        $Qry->table     = "tblpayperiod_japanese";
    }else if($idpayperiod['period']['type'] == 'Japanese Conversion'){
        $Qry->table     = "tblpayperiod_japaneseconversion";
    }else{
        $Qry->table     = "tblpayperiod";
    }

    $Qry->selected = "tkprocess = 1";
    $Qry->fields   = "id='".$idpayperiod['period']['id']."'";                        
    $Qry->exe_UPDATE($con);
    
    echo mysqli_error($con);
}


function getGracePeriod($con){
    $Qry=new Query();
    $Qry->table="tblpreference";
    $Qry->selected="value";
    $Qry->fields="idacct='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        if($row=mysqli_fetch_assoc($rs)){
            $data = $row;          
        }
    }

    return $data;
}

?>