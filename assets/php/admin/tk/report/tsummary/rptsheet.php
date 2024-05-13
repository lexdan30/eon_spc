<?php
error_reporting(0);
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();
$search='';
$tssearch='';

$id_array = getJoblevels($con,$param->filter->jobleveltype);
$ids = implode(",",$id_array);

$idpayperiod = array(  
    "period"		=> getPayPeriod($con),
);

$date =  $idpayperiod['period']['pay_start'];
$date1 =  $idpayperiod['period']['pay_end'];

if( !empty( $param->filter->empid ) ){ 
    $search=" AND tid =  '".$param->filter->empid."' "; 
    $tssearch=" AND ts.idacct =  '".$param->filter->empid."' "; 
}
if( !empty( $param->department ) ){ 
    $search=" AND idunit =  '".$param->department."' "; 
    $tssearch=" AND idbunit =  '".$param->department."' "; 
}
if( !empty( $param->filter->jobleveltype ) ){ 
    $search=" AND idlvl IN  (". $ids .") ";
    $tssearch=" AND aj.idlvl IN  (". $ids .") ";
 }

$param->id = getPayid($con, $param);

$Qry 			= new Query();	
$Qry->table     = "vw_timesheetfinal";

$Qry->selected  = "tid,
                    empid,
                    idpayperiod,
                    idpaygrp,
                    idunit,
                    empname,
                    SUM(shifthrs) AS Rhrs,
                    SUM((CASE 
                            WHEN lateref + utref > 1 THEN late
                            WHEN lateref + utref != 0 THEN 0
                            ELSE late
                        END)) AS Late,
                    SUM((CASE 
                            WHEN lateref + utref > 1 THEN ut
                            WHEN lateref + utref != 0 THEN 0
                            ELSE ut
                        END)) AS Undertime,
                    SUM(absent) AS `Abs`,
                    SUM((CASE 
                            WHEN lateref + utref > 1 THEN  (acthrs - excess)
                            WHEN lateref + utref < 1  THEN if(lateref + utref <= 1,if(lateref + utref != 0,acthrs+lateref + utref,acthrs),acthrs) - (excess +lateref + utref)
                            ELSE (acthrs - excess)
                        END)) AS WHrs,
                    SUM(IF( FIND_IN_SET(idunit,(SELECT `value` FROM tblpreference WHERE alias = 'BTA')) AND shifttype = 'Broken Schedule' AND acthrs != 0,1,0)) AS btallowance,
                    SUM(IF(obtripstatus = 1 AND obapprove BETWEEN period_start AND period_endref AND oballowance = 1 ,1,0)) AS mallowance,

                    SUM(IF(leaveidtype = 1 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_SL,
                    SUM(IF(leaveidtype = 2 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_VL,
                    SUM(IF(leaveidtype = 33 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_AL,
                    SUM(IF(leaveidtype = 35 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_SVL,
                    SUM(IF(leaveidtype = 3 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_LWOP,
                    SUM(IF(leaveidtype = 34 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_MLWOP,
                    SUM(IF(leaveidtype = 4 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_SPL,
                    SUM(IF(leaveidtype = 5 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_PL,
                    SUM(IF(leaveidtype = 6 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_SLH,
                    SUM(IF(leaveidtype = 7 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_UL,
                    SUM(IF(leaveidtype = 8 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_IL,
                    SUM(IF(leaveidtype = 9 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_EL,
                    SUM(IF(leaveidtype = 10 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_MC,
                    SUM(IF(leaveidtype = 11 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_BL,
                    SUM(IF(leaveidtype = 12 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_ML,
                    SUM(IF(leaveidtype = 13 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_S,
                    SUM(IF(leaveidtype = 14 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_PSL,
                    SUM(IF(leaveidtype = 15 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_VVAWC,
                    SUM(IF(leaveidtype = 16 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_BDL,
                    SUM(IF(leaveidtype = 17 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_CL,
                    SUM(IF(obtripstatus = 1 AND obapprove BETWEEN period_start AND period_endref, obhrs,0)) AS TC_OBT,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL 
                        THEN IF(othrsp > 8
                                ,IF(othrsp - 8 > 0,8,othrsp)
                                ,IF(othrsp != 0,othrsp,0)
                            )
                        ELSE 0
                        END)) 
                    AS TC_RD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'SPECIAL' AND absent = 0  
                        THEN (CASE 
                                    WHEN lateref + utref > 1 THEN  (acthrs - excess)
                                    WHEN lateref + utref < 1  THEN if(lateref + utref <= 1,if(lateref + utref != 0,acthrs+lateref + utref,acthrs),acthrs) - (excess +lateref + utref)
                                    ELSE (acthrs - excess)
                                END)
                        ELSE 0
                        END))
                    AS H_SH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'SPECIAL' AND absent = 0 
                        THEN (CASE 
                                    WHEN lateref + utref > 1 THEN  (acthrs - excess)
                                    WHEN lateref + utref < 1  THEN if(lateref + utref <= 1,if(lateref + utref != 0,acthrs+lateref + utref,acthrs),acthrs) - (excess +lateref + utref)
                                    ELSE (acthrs - excess)
                                END)
                        ELSE 0
                        END)) 
                    AS H_SHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL' AND absent = 0 
                        THEN (CASE 
                                    WHEN lateref + utref > 1 THEN  (acthrs - excess)
                                    WHEN lateref + utref < 1  THEN if(lateref + utref <= 1,if(lateref + utref != 0,acthrs+lateref + utref,acthrs),acthrs) - (excess +lateref + utref)
                                    ELSE (acthrs - excess)
                                END)
                        ELSE 0
                        END))
                    AS H_LH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL' AND absent = 0 
                        THEN (CASE 
                                    WHEN lateref + utref > 1 THEN  (acthrs - excess)
                                    WHEN lateref + utref < 1  THEN if(lateref + utref <= 1,if(lateref + utref != 0,acthrs+lateref + utref,acthrs),acthrs) - (excess +lateref + utref)
                                    ELSE (acthrs - excess)
                                END)
                        ELSE 0
                        END))
                    AS H_LHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL SPECIAL' AND absent = 0 
                        THEN (CASE 
                                    WHEN lateref + utref > 1 THEN  (acthrs - excess)
                                    WHEN lateref + utref < 1  THEN if(lateref + utref <= 1,if(lateref + utref != 0,acthrs+lateref + utref,acthrs),acthrs) - (excess +lateref + utref)
                                    ELSE (acthrs - excess)
                                END)
                        ELSE 0
                        END))
                    AS H_LSH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL SPECIAL' AND absent = 0 
                        THEN (CASE 
                                    WHEN lateref + utref > 1 THEN  (acthrs - excess)
                                    WHEN lateref + utref < 1  THEN if(lateref + utref <= 1,if(lateref + utref != 0,acthrs+lateref + utref,acthrs),acthrs) - (excess +lateref + utref)
                                    ELSE (acthrs - excess)
                                END)
                        ELSE 0
                        END))
                    AS H_LSHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype IS NULL AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref  THEN 
                        othrsp
                        ELSE 0
                        END)) 
                    AS OT_OTReg,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                            IF(othrsp - 8 > 0,othrsp - 8,0)
                        ELSE 0
                        END)) 
                    AS OT_RD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                        othrsp
                        ELSE 0
                        END)) 
                    AS OT_SH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                        othrsp
                        ELSE 0
                        END)) 
                    AS OT_SHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                        othrsp
                        ELSE 0
                        END)) 
                    AS OT_LH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                        othrsp
                        ELSE 0
                        END)) 
                    AS OT_LHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                        othrsp
                        ELSE 0
                        END)) 
                    AS OT_LSH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                        othrsp
                        ELSE 0
                        END)) 
                    AS OT_LSHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype IS NULL AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                        ELSE 0
                        END)) 
                    AS NP_NPReg,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                        ELSE 0
                        END)) 
                    AS NP_RD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'SPECIAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                        ELSE 0
                        END)) 
                    AS NP_SH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'SPECIAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                        ELSE 0
                        END)) 
                    AS NP_SHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                        ELSE 0
                        END)) 
                    AS NP_LH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                        ELSE 0
                        END)) 
                    AS NP_LHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype = 'LEGAL SPECIAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                        ELSE 0
                        END)) 
                    AS NP_LSH,
                    SUM((CASE 
                        WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL SPECIAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                        ELSE 0
                        END)) 
                    AS NP_LSHRD,
                    SUM((CASE 
                        WHEN defaultschedid != 4 AND holidaytype IS NULL AND otstatus = 1  AND otapprove BETWEEN period_start AND period_endref THEN npot
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
                    AS NPOT_LSHRD";
$Qry->fields    = "(separationdate > '".$date1."' OR separationdate IS NULL) AND idemptype = 1 AND idpayperiod = '".$param->id."'
                    " . $search .  " GROUP BY tid ORDER BY  empname LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize ."";
$rs 			= $Qry->exe_SELECT($con);


if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $adj_late = '';
        $adj_late_rd  = '';
        $adj_late_sh  = '';
        $adj_late_shrd  = '';
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
        $adj_obt = '';

        $adj_sl = '';
        $adj_vl = '';
        $adj_al = '';
        $adj_lwop = '';
        $adj_spl = '';
        $adj_pl = '';
        $adj_slh = '';
        $adj_ul = '';
        $adj_il = '';
        $adj_el = '';
        $adj_mc = '';
        $adj_bl = '';
        $adj_ml = '';
        $adj_s = '';
        $adj_psl = '';
        $adj_vvawc = '';
        $adj_bdl = '';
        $adj_cl = '';


        $Qryts = new Query();	
        $Qryts->table         = "tbltimesheetsummary";
        $Qryts->selected      = "*";
        $Qryts->fields        = "idpayperiod = '".$param->id."' AND idacct =  '".$row['tid']."' and type = '". $param->type."'";
        $rsts = $Qryts->exe_SELECT($con);
        if(mysqli_num_rows($rsts)>= 1){
            if($row1=mysqli_fetch_array($rsts)){
                $row['Rhrs']           = $row1['rhrs'];
                
                $row['Late']            = $row1['late'];
                $row['Undertime']       = $row1['ut'];
                $row['Abs']             = $row1['abs'];
                $row['WHrs']            = $row1['whrs'];
                $row['btallowance']     = $row1['btallowance'];
                $row['mallowance']     = $row1['mallowance'];

                $row['TC_SL']           = $row1['tc_sl'];
                $row['TC_VL']           = $row1['tc_vl']; 
                $row['TC_AL']           = $row1['tc_al']; 
                $row['TC_SVL']           = $row1['tc_svl']; 
                $row['TC_LWOP']            = $row1['tc_lwop'];
                $row['TC_MLWOP']            = $row1['tc_mlwop'];
                $row['TC_SPL']            = $row1['tc_spl'];
                $row['TC_PL']            = $row1['tc_pl'];
                $row['TC_SLH']            = $row1['tc_slh'];
                $row['TC_UL']            = $row1['tc_ul'];
                $row['TC_IL']            = $row1['tc_il'];
                $row['TC_EL']            = $row1['tc_el'];
                $row['TC_MC']            = $row1['tc_mc'];
                $row['TC_BL']            = $row1['tc_bl'];
                $row['TC_ML']            = $row1['tc_ml'];
                $row['TC_S']            = $row1['tc_s'];
                $row['TC_PSL']            = $row1['tc_psl'];
                $row['TC_VVAWC']            = $row1['tc_vvawc'];
                $row['TC_BDL']            = $row1['tc_bdl'];
                $row['TC_CL']           = $row1['tc_cl'];


                $row['TC_OBT']            = $row1['tc_obt'];
                $row['TC_RD']            = $row1['tc_rd'];

                $row['H_SH']            = $row1['h_sh'];
                $row['H_SHRD']            = $row1['h_shrd'];
                $row['H_LH']            = $row1['h_lh'];
                $row['H_LHRD']            = $row1['h_lhrd'];
                $row['H_LSH']            = $row1['h_lsh'];
                $row['H_LSHRD']            = $row1['h_lshrd'];
                $row['OT_OTReg']           = $row1['ot_reg']; 
                $row['OT_RD']            = $row1['ot_rd'];
                $row['OT_SH']            = $row1['ot_sh'];
                $row['OT_SHRD']            = $row1['ot_shrd'];
                $row['OT_LH']            = $row1['ot_lh'];
                $row['OT_LHRD']            = $row1['ot_lhrd'];
                $row['OT_LSH']            = $row1['ot_lsh'];
                $row['OT_LSHRD']            = $row1['ot_lshrd'];
                $row['NP_NPReg']            = $row1['np_npreg'];
                $row['NP_RD']            = $row1['np_rd'];
                $row['NP_SH']            = $row1['np_sh'];
                $row['NP_SHRD']            = $row1['np_shrd'];
                $row['NP_LH']            = $row1['np_lh'];
                $row['NP_LHRD']            = $row1['np_lhrd'];
                $row['NP_LSH']            = $row1['np_lsh'];
                $row['NP_LSHRD']            = $row1['np_lshrd'];
                $row['NPOT_NPOT']            = $row1['npot_npot'];
                $row['NPOT_RD']            = $row1['npot_rd'];
                $row['NPOT_SH']            = $row1['npot_sh'];
                $row['NPOT_SHRD']            = $row1['npot_shrd'];
                $row['NPOT_LH']            = $row1['npot_lh'];
                $row['NPOT_LHRD']            = $row1['npot_lhrd'];
                $row['NPOT_LSH']            = $row1['npot_lsh'];
                $row['NPOT_LSHRD']            = $row1['npot_lshrd'];
                $adj_late                       = $row1['adj_late'];
                $adj_late_rd                       = $row1['adj_late_rd'];
                $adj_late_sh                       = $row1['adj_late_sh'];
                $adj_late_shrd                       = $row1['adj_late_shrd'];
                $adj_late_lh                      = $row1['adj_late_lh'];
                $adj_late_lhrd                      = $row1['adj_late_lhrd'];
                $adj_late_lsh                      = $row1['adj_late_lsh'];
                $adj_late_lshrd                      = $row1['adj_late_lshrd'];
                $adj_ut                      = $row1['adj_ut'];
                $adj_ut_rd                      = $row1['adj_ut_rd'];
                $adj_ut_sh                      = $row1['adj_ut_sh'];
                $adj_ut_shrd                      = $row1['adj_ut_shrd'];
                $adj_ut_lh                      = $row1['adj_ut_lh'];
                $adj_ut_lhrd                      = $row1['adj_ut_lhrd'];
                $adj_ut_lsh                      = $row1['adj_ut_lsh'];
                $adj_ut_lshrd                      = $row1['adj_ut_lshrd'];
                $adj_absent                      = $row1['adj_absent'];
                $adj_absent_rd                      = $row1['adj_absent_rd'];
                $adj_absent_sh                      = $row1['adj_absent_sh'];
                $adj_absent_shrd                      = $row1['adj_absent_shrd'];
                $adj_absent_lh                      = $row1['adj_absent_lh'];
                $adj_absent_lhrd                      = $row1['adj_absent_lhrd'];
                $adj_absent_lsh                      = $row1['adj_absent_lsh'];
                $adj_absent_lshrd                      = $row1['adj_absent_lshrd'];
                $adj_ot                      = $row1['adj_ot'];
                $adj_ot_rd                      = $row1['adj_ot_rd'];
                $adj_ot_sh                      = $row1['adj_ot_sh'];
                $adj_ot_shrd                      = $row1['adj_ot_shrd'];
                $adj_ot_lh                      = $row1['adj_ot_lh'];
                $adj_ot_lhrd                      = $row1['adj_ot_lhrd'];
                $adj_ot_lsh                      = $row1['adj_ot_lsh'];
                $adj_ot_lshrd                      = $row1['adj_ot_lshrd'];
                $adj_np                      = $row1['adj_np'];
                $adj_np_rd                      = $row1['adj_np_rd'];
                $adj_np_sh                      = $row1['adj_np_sh'];
                $adj_np_shrd                      = $row1['adj_np_shrd'];
                $adj_np_lh                      = $row1['adj_np_lh'];
                $adj_np_lhrd                      = $row1['adj_np_lhrd'];
                $adj_np_lsh                      = $row1['adj_np_lsh'];
                $adj_np_lshrd                      = $row1['adj_np_lshrd'];
                $adj_npot                      = $row1['adj_npot'];
                $adj_npot_rd                      = $row1['adj_npot_rd'];
                $adj_npot_sh                      = $row1['adj_npot_sh'];
                $adj_npot_shrd                      = $row1['adj_npot_shrd'];
                $adj_npot_lh                      = $row1['adj_npot_lh'];
                $adj_npot_lhrd                      = $row1['adj_npot_lhrd'];
                $adj_npot_lsh                      = $row1['adj_npot_lsh'];
                $adj_npot_lshrd                      = $row1['adj_npot_lshrd'];
               
                $adj_obt                      = $row1['adj_obt'];

                $adj_sl                       = $row1['adj_sl'];
                $adj_vl                        = $row1['adj_vl'];
                $adj_al                        = $row1['adj_al'];
                $adj_lwop                        = $row1['adj_lwop'];
                $adj_spl                        = $row1['adj_spl'];
                $adj_pl                        = $row1['adj_pl'];
                $adj_slh                        = $row1['adj_slh'];
                $adj_ul                        = $row1['adj_ul'];
                $adj_il                        = $row1['adj_il'];
                $adj_el                        = $row1['adj_el'];
                $adj_mc                        = $row1['adj_mc'];
                $adj_bl                        = $row1['adj_bl'];
                $adj_ml                        = $row1['adj_ml'];
                $adj_s                        = $row1['adj_s'];
                $adj_psl                        = $row1['adj_psl'];
                $adj_vvawc                        = $row1['adj_vvawc'];
                $adj_bdl                        = $row1['adj_bdl'];
                $adj_cl                        = $row1['adj_cl'];
            }
        }

        $data[] = array( 
            "empid"        	        => $row['empid'],
            "empname"        	    => $row['empname'],
            "idunit"        	    => $row['idunit'],
            "department"        	=> getDepartments($con,$row['idunit']),
            "section"        	    => getSection($con,$row['idunit']),
            "paygroup"        	    => getPaygroup($con,$row['idpaygrp']),
            "Rhrs"        	        => round($row['Rhrs'],2),
            "Late"        	        => round($row['Late'],2),
            "Undertime"        	    => round($row['Undertime'],2),
            "Abs"        	        => round($row['Abs'],2),
            "WHrs"        	        => round($row['WHrs'],2),
            "btallowance"        	=> round($row['btallowance'],2),

            "TC_OBT"        	    => round($row['TC_OBT'],2),
            "TC_RD"        	        => round($row['TC_RD'],2),
            "TC_SL"         	    => round($row['TC_SL'],2),
            "TC_VL"        	        => round($row['TC_VL'],2),
            "TC_AL"        	        => round($row['TC_AL'],2),
            "TC_SVL"        	    => round($row['TC_SVL'],2),
            "TC_LWOP"        	    => round($row['TC_LWOP'],2),
            "TC_MLWOP"        	    => round($row['TC_MLWOP'],2),
            "TC_SPL"        	    => round($row['TC_SPL'],2),
            "TC_PL"         	    => round($row['TC_PL'],2),
            "TC_SLH"         	    => round($row['TC_SLH'],2),
            "TC_UL"         	    => round($row['TC_UL'],2),
            "TC_IL"         	    => round($row['TC_IL'],2),
            "TC_EL"        	        => round($row['TC_EL'],2),
            "TC_MC"        	        => round($row['TC_MC'],2),
            "TC_BL"         	    => round($row['TC_BL'],2),
            "TC_ML"         	    => round($row['TC_ML'],2),
            "TC_S"         	        => round($row['TC_S'],2),
            "TC_PSL"         	    => round($row['TC_PSL'],2),
            "TC_VVAWC"         	    => round($row['TC_VVAWC'],2),
            "TC_BDL"         	    => round($row['TC_BDL'],2),
            "TC_CL"         	    => round($row['TC_CL'],2),

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
            "adj_late"        	    => round($adj_late,2),
            "adj_late_rd"        	=> round($adj_late_rd ,2),
            "adj_late_sh"        	=> round($adj_late_sh ,2),
            "adj_late_shrd"        	=> round($adj_late_shrd ,2),
            "adj_late_lh"           => round($adj_late_lh,2),
            "adj_late_lhrd"        	=> round($adj_late_lhrd,2),
            "adj_late_lsh"        	=> round($adj_late_lsh,2),
            "adj_late_lshrd"        => round($adj_late_lshrd,2),
            "adj_ut"        	    => round($adj_ut,2),
            "adj_ut_rd"        	    => round($adj_ut_rd,2),
            "adj_ut_sh"        	    => round($adj_ut_sh,2),
            "adj_ut_shrd"        	=> round($adj_ut_shrd,2),
            "adj_ut_lh"        	    => round($adj_ut_lh,2),
            "adj_ut_lhrd"        	=> round($adj_ut_lhrd,2),
            "adj_ut_lsh"        	=> round($adj_ut_lsh,2),
            "adj_ut_lshrd"        	=> round($adj_ut_lshrd,2),
            "adj_absent"        	=> round($adj_absent,2),
            "adj_absent_rd"        	=> round($adj_absent_rd,2),
            "adj_absent_sh"        	=> round($adj_absent_sh,2),
            "adj_absent_shrd"       => round($adj_absent_shrd,2),
            "adj_absent_lh"        	=> round($adj_absent_lh,2),
            "adj_absent_lhrd"       => round($adj_absent_lhrd,2),
            "adj_absent_lsh"        => round($adj_absent_lsh,2),
            "adj_absent_lshrd"      => round($adj_absent_lshrd,2),
            "adj_ot"        	    => round($adj_ot,2),
            "adj_ot_rd"        	    => round($adj_ot_rd,2),
            "adj_ot_sh"        	    => round($adj_ot_sh,2),
            "adj_ot_shrd"        	=> round($adj_ot_shrd,2),
            "adj_ot_lh"        	    => round($adj_ot_lh,2),
            "adj_ot_lhrd"          	=> round($adj_ot_lhrd,2),
            "adj_ot_lsh"        	=> round($adj_ot_lsh,2),
            "adj_ot_lshrd"        	=> round($adj_ot_lshrd,2),
            "adj_np"        	    => round($adj_np,2),
            "adj_np_rd"        	    => round($adj_np_rd,2),
            "adj_np_sh"        	    => round($adj_np_sh,2),
            "adj_np_shrd"        	=> round($adj_np_shrd,2),
            "adj_np_lh"        	    => round($adj_np_lh,2),
            "adj_np_lhrd"        	=> round($adj_np_lhrd,2),
            "adj_np_lsh"        	=> round($adj_np_lsh,2),
            "adj_np_lshrd"        	=> round($adj_np_lshrd,2),
            "adj_npot"        	    => round($adj_npot,2),
            "adj_npot_rd"        	=> round($adj_npot_rd,2),
            "adj_npot_sh"        	=> round($adj_npot_sh,2),
            "adj_npot_shrd"        	=> round($adj_npot_shrd,2),
            "adj_npot_lh"        	=> round($adj_npot_lh,2),
            "adj_npot_lhrd"        	=> round($adj_npot_lhrd,2),
            "adj_npot_lsh"        	=> round($adj_npot_lsh,2),
            "adj_npot_lshrd"        => round($adj_npot_lshrd,2),
           
            "adj_obt"        	    => round($adj_obt,2),

            "adj_sl"                       =>  round($adj_sl,2),
            "adj_vl"                       =>  round($adj_vl,2),
            "adj_al"                       =>  round($adj_al,2),
            "adj_lwop"                     =>  round($adj_lwop,2),
            "adj_spl"                      =>  round($adj_spl,2),
            "adj_pl"                       =>  round($adj_pl,2),
            "adj_slh"                      =>  round($adj_slh,2),
            "adj_ul"                       =>  round($adj_ul,2),
            "adj_il"                       =>  round($adj_il,2),
            "adj_el"                       =>  round($adj_el,2),
            "adj_mc"                       =>  round($adj_mc,2),
            "adj_bl"                       =>  round($adj_bl,2),
            "adj_ml"                       =>  round($adj_ml,2),
            "adj_s"                        =>  round($adj_s,2),
            "adj_psl"                      =>  round($adj_psl,2),
            "adj_vvawc"                    =>  round($adj_vvawc,2),
            "adj_bdl"                      =>  round($adj_bdl,2),
            "adj_cl"                       =>  round($adj_cl,2)
        
        );
    }

    $myData = array('status' => 'success', 
                    'result' => $data, 
                    'totalItems' => getTotal($con , $param, $search, $date1),
                    'grandtotal' => getGrandtotal($con , $param, $search, $date1,$tssearch)

                );
    $return = json_encode($myData);
}else{
    $return = json_encode(array('error' => mysqli_error($con)));
}


print $return;
mysqli_close($con);


function getTotal($con,$param, $search, $date1){
    $Qry = new Query();	
    $Qry->table     = "vw_timesheetfinal";
    $Qry->selected      = "*";
    $Qry->fields        = "(separationdate > '".$date1."' OR separationdate IS NULL) AND idemptype = 1 AND idpayperiod = '".$param->id."'  " . $search .  "
                            GROUP BY tid";
    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
                $rowcount=mysqli_num_rows($rs);
				return $rowcount;
			}
		}
		return 0;
}

function getDepartments($con,$idunit){
    $Qry = new Query();	
    $Qry->table     = "vw_databusinessunits";
    $Qry->selected  = "idunder,name,stype";
    $Qry->fields = "id='".$idunit."'";
    $rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            if($row['stype'] == 'Department'){
                return $row['name'];
            }else if($row['stype'] != 'Division'){
                return getDepartments($con, $row['idunder']);
            }else if($row['stype'] == 'Division'){
                return '';
            }
        }
    }
}

function getSection($con,$idunit){
    $Qry = new Query();	
    $Qry->table     = "vw_databusinessunits";
    $Qry->selected  = "idunder,name,stype";
    $Qry->fields = "id='".$idunit."'";
    $rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            if($row['stype'] == 'Section'){
                return $row['name'];
            }else if($row['stype'] != 'Department'){
                return getDepartments($con, $row['idunder']);
            }else if($row['stype'] == 'Department'){
                return '';
            }
        }
    }
 
}

function getGrandtotal($con,$param, $search, $date1,$tssearch){
    $Qry = new Query();	
    $Qry->table     = "vw_timesheetfinal";
    $Qry->selected  = "tid,
                            empid,
                            idpayperiod,
                            idpaygrp,
                            idunit,
                            empname,
                            SUM(shifthrs) AS Rhrs,
                            SUM((CASE 
                                    WHEN lateref + utref > 1 THEN late
                                    WHEN lateref + utref != 0 THEN 0
                                    ELSE late
                                END)) AS Late,
                            SUM((CASE 
                                    WHEN lateref + utref > 1 THEN ut
                                    WHEN lateref + utref != 0 THEN 0
                                    ELSE ut
                                END)) AS Undertime,
                            SUM(absent) AS `Abs`,
                            SUM((CASE 
                                    WHEN lateref + utref > 1 THEN  (acthrs - excess)
                                    WHEN lateref + utref != 0 THEN (acthrs - excess) + (lateref + utref)
                                    ELSE (acthrs - excess)
                                END)) AS WHrs,
                            SUM(IF( FIND_IN_SET(idunit,(SELECT `value` FROM tblpreference WHERE alias = 'BTA')) AND shifttype = 'Broken Schedule' AND acthrs != 0,1,0)) AS btallowance,
                            SUM(IF(obtripstatus = 1 AND obapprove BETWEEN period_start AND period_endref AND oballowance = 1 ,1,0)) AS mallowance,

                            SUM(IF(leaveidtype = 1 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_SL,
                            SUM(IF(leaveidtype = 2 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_VL,
                            SUM(IF(leaveidtype = 33 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_AL,
                            SUM(IF(leaveidtype = 35 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_SVL,
                            SUM(IF(leaveidtype = 3 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_LWOP,
                            SUM(IF(leaveidtype = 34 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_MLWOP,
                            SUM(IF(leaveidtype = 4 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_SPL,
                            SUM(IF(leaveidtype = 5 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_PL,
                            SUM(IF(leaveidtype = 6 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_SLH,
                            SUM(IF(leaveidtype = 7 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_UL,
                            SUM(IF(leaveidtype = 8 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_IL,
                            SUM(IF(leaveidtype = 9 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_EL,
                            SUM(IF(leaveidtype = 10 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_MC,
                            SUM(IF(leaveidtype = 11 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_BL,
                            SUM(IF(leaveidtype = 12 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_ML,
                            SUM(IF(leaveidtype = 13 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_S,
                            SUM(IF(leaveidtype = 14 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_PSL,
                            SUM(IF(leaveidtype = 15 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_VVAWC,
                            SUM(IF(leaveidtype = 16 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_BDL,
                            SUM(IF(leaveidtype = 17 AND leaveappstatus = 1  AND lvapprove BETWEEN period_start AND period_endref, `leave`, 0)) AS TC_CL,
                            SUM(IF(obtripstatus = 1 AND obapprove BETWEEN period_start AND period_endref, obhrs,0)) AS TC_OBT,
                            SUM((CASE 
                                WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL 
                                THEN IF(othrsp > 8
                                        ,IF(othrsp - 8 > 0,8,othrsp)
                                        ,IF(othrsp != 0,othrsp,0)
                                    )
                                ELSE 0
                                END)) 
                            AS TC_RD,
                            SUM((CASE 
                                WHEN defaultschedid != 4 AND holidaytype = 'SPECIAL' AND absent = 0  
                                THEN (CASE 
                                            WHEN lateref + utref > 1 THEN  (acthrs - excess)
                                            WHEN lateref + utref != 0 THEN (acthrs - excess) + (lateref + utref)
                                            ELSE (acthrs - excess)
                                        END)
                                ELSE 0
                                END))
                            AS H_SH,
                            SUM((CASE 
                                WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'SPECIAL' AND absent = 0 
                                THEN (CASE 
                                            WHEN lateref + utref > 1 THEN  (acthrs - excess)
                                            WHEN lateref + utref != 0 THEN (acthrs - excess) + (lateref + utref)
                                            ELSE (acthrs - excess)
                                        END)
                                ELSE 0
                                END)) 
                            AS H_SHRD,
                            SUM((CASE 
                                WHEN defaultschedid != 4 AND holidaytype = 'LEGAL' AND absent = 0 
                                THEN (CASE 
                                            WHEN lateref + utref > 1 THEN  (acthrs - excess)
                                            WHEN lateref + utref != 0 THEN (acthrs - excess) + (lateref + utref)
                                            ELSE (acthrs - excess)
                                        END)
                                ELSE 0
                                END))
                            AS H_LH,
                            SUM((CASE 
                                WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL' AND absent = 0 
                                THEN (CASE 
                                            WHEN lateref + utref > 1 THEN  (acthrs - excess)
                                            WHEN lateref + utref != 0 THEN (acthrs - excess) + (lateref + utref)
                                            ELSE (acthrs - excess)
                                        END)
                                ELSE 0
                                END))
                            AS H_LHRD,
                            SUM((CASE 
                                WHEN defaultschedid != 4 AND holidaytype = 'LEGAL SPECIAL' AND absent = 0 
                                THEN (CASE 
                                            WHEN lateref + utref > 1 THEN  (acthrs - excess)
                                            WHEN lateref + utref != 0 THEN (acthrs - excess) + (lateref + utref)
                                            ELSE (acthrs - excess)
                                        END)
                                ELSE 0
                                END))
                            AS H_LSH,
                            SUM((CASE 
                                WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL SPECIAL' AND absent = 0 
                                THEN (CASE 
                                            WHEN lateref + utref > 1 THEN  (acthrs - excess)
                                            WHEN lateref + utref != 0 THEN (acthrs - excess) + (lateref + utref)
                                            ELSE (acthrs - excess)
                                        END)
                                ELSE 0
                                END))
                            AS H_LSHRD,
                            SUM((CASE 
                                WHEN defaultschedid != 4 AND holidaytype IS NULL AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref  THEN 
                                othrsp
                                ELSE 0
                                END)) 
                            AS OT_OTReg,
                            SUM((CASE 
                                WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                                    IF(othrsp - 8 > 0,othrsp - 8,0)
                                ELSE 0
                                END)) 
                            AS OT_RD,
                            SUM((CASE 
                                WHEN defaultschedid != 4 AND holidaytype = 'SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                                othrsp
                                ELSE 0
                                END)) 
                            AS OT_SH,
                            SUM((CASE 
                                WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                                othrsp
                                ELSE 0
                                END)) 
                            AS OT_SHRD,
                            SUM((CASE 
                                WHEN defaultschedid != 4 AND holidaytype = 'LEGAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                                othrsp
                                ELSE 0
                                END)) 
                            AS OT_LH,
                            SUM((CASE 
                                WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                                othrsp
                                ELSE 0
                                END)) 
                            AS OT_LHRD,
                            SUM((CASE 
                                WHEN defaultschedid != 4 AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                                othrsp
                                ELSE 0
                                END)) 
                            AS OT_LSH,
                            SUM((CASE 
                                WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                                othrsp
                                ELSE 0
                                END)) 
                            AS OT_LSHRD,
                            SUM((CASE 
                                WHEN defaultschedid != 4 AND holidaytype IS NULL AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                                ELSE 0
                                END)) 
                            AS NP_NPReg,
                            SUM((CASE 
                                WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype IS NULL AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                                ELSE 0
                                END)) 
                            AS NP_RD,
                            SUM((CASE 
                                WHEN defaultschedid != 4 AND holidaytype = 'SPECIAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                                ELSE 0
                                END)) 
                            AS NP_SH,
                            SUM((CASE 
                                WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'SPECIAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                                ELSE 0
                                END)) 
                            AS NP_SHRD,
                            SUM((CASE 
                                WHEN defaultschedid != 4 AND holidaytype = 'LEGAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                                ELSE 0
                                END)) 
                            AS NP_LH,
                            SUM((CASE 
                                WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                                ELSE 0
                                END)) 
                            AS NP_LHRD,
                            SUM((CASE 
                                WHEN defaultschedid != 4 AND holidaytype = 'LEGAL SPECIAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                                ELSE 0
                                END)) 
                            AS NP_LSH,
                            SUM((CASE 
                                WHEN (defaultschedid = 4 OR idshift = 4) AND holidaytype = 'LEGAL SPECIAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                                ELSE 0
                                END)) 
                            AS NP_LSHRD,
                            SUM((CASE 
                                WHEN defaultschedid != 4 AND holidaytype IS NULL AND otstatus = 1  AND otapprove BETWEEN period_start AND period_endref THEN npot
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
                            AS NPOT_LSHRD";
    
    $Qry->fields = "idpayperiod = '".$param->id."'  " . $search .  " ";
    $rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $adj_late = '';
            $adj_late_rd  = '';
            $adj_late_sh  = '';
            $adj_late_shrd  = '';
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
            $adj_obt = '';

            
            $adj_sl = '';
            $adj_vl = '';
            $adj_al = '';
            $adj_lwop = '';
            $adj_spl = '';
            $adj_pl = '';
            $adj_slh = '';
            $adj_ul = '';
            $adj_il = '';
            $adj_el = '';
            $adj_mc = '';
            $adj_bl = '';
            $adj_ml = '';
            $adj_s = '';
            $adj_psl = '';
            $adj_vvawc = '';
            $adj_bdl = '';
            $adj_cl = '';
            
    
           
        $Qryts = new Query();	
        $Qryts->table         = "tbltimesheetsummary AS ts LEFT JOIN tblaccountjob AS aj ON ts.idacct = aj.idacct";
        $Qryts->selected      = "SUM(rhrs) as rhrs,
                                SUM(late) as late,
                                SUM(ut) as ut,
                                SUM(abs) as abs,
                                SUM(whrs) as whrs,
                                SUM(btallowance) as btallowance,
                                SUM(mallowance) as mallowance,
                                SUM(tc_obt) as tc_obt,
                                SUM(tc_rd) as tc_rd,
                                SUM(tc_sl) as tc_sl,
                                SUM(tc_vl) as tc_vl,
                                SUM(tc_al) as tc_al,
                                SUM(tc_svl) as tc_svl,
                                SUM(tc_lwop) as tc_lwop,
                                SUM(tc_mlwop) as tc_mlwop,
                                SUM(tc_spl) as tc_spl,
                                SUM(tc_pl) as tc_pl,
                                SUM(tc_slh) as tc_slh,
                                SUM(tc_ul) as tc_ul,
                                SUM(tc_il) as tc_il,
                                SUM(tc_el) as tc_el,
                                SUM(tc_mc) as tc_mc,
                                SUM(tc_bl) as tc_bl,
                                SUM(tc_ml) as tc_ml,
                                SUM(tc_s) as tc_s,
                                SUM(tc_psl) as tc_psl,
                                SUM(tc_vvawc) as tc_vvawc,
                                SUM(tc_bdl) as tc_bdl,
                                SUM(tc_cl) as tc_cl,
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
                                SUM(adj_obt) as adj_obt,
                                SUM(adj_sl) as adj_sl,
                                SUM(adj_vl) as adj_vl,
                                SUM(adj_al) as adj_al,
                                SUM(adj_lwop) as adj_lwop,
                                SUM(adj_spl) as adj_spl,
                                SUM(adj_pl) as adj_pl,
                                SUM(adj_slh) as adj_slh,
                                SUM(adj_ul) as adj_ul,
                                SUM(adj_il) as adj_il,
                                SUM(adj_el) as adj_el,
                                SUM(adj_mc) as adj_mc,
                                SUM(adj_bl) as adj_bl,
                                SUM(adj_ml) as adj_ml,
                                SUM(adj_s) as adj_s,
                                SUM(adj_psl) as adj_psl,
                                SUM(adj_vvawc) as adj_vvawc
                                ";
        $Qryts->fields        = "idpayperiod = '".$param->id."' and type  = '".$param->type."' " . $tssearch .  " ";
        $rsts = $Qryts->exe_SELECT($con);
        if(mysqli_num_rows($rsts)>= 1){
            if($row1=mysqli_fetch_array($rsts)){
                if($row1['rhrs']){
                   
                    $row['Rhrs']                    = $row1['rhrs'];
                    $row['Late']                    = $row1['late'];
                    $row['Undertime']               = $row1['ut'];
                    $row['Abs']                     = $row1['abs'];
                    $row['WHrs']                    = $row1['whrs'];

                    $row['btallowance']             = $row1['btallowance'];
                    $row['mallowance']              = $row1['mallowance'];

                    $row['TC_SL']         	        = $row1['tc_sl'];
                    $row['TC_VL']         	        = $row1['tc_vl'];
                    $row['TC_AL']         	        = $row1['tc_al'];
                    $row['TC_SVL']         	        = $row1['tc_svl'];
                    $row['TC_LWOP']        	        = $row1['tc_lwop'];
                    $row['TC_MLWOP']        	    = $row1['tc_mlwop'];
                    $row['TC_SPL']        	        = $row1['tc_spl'];
                    $row['TC_PL']         	        = $row1['tc_pl'];
                    $row['TC_SLH']         	        = $row1['tc_slh'];
                    $row['TC_UL']         	        = $row1['tc_ul'];
                    $row['TC_IL']         	        = $row1['tc_il'];
                    $row['TC_EL']        	        = $row1['tc_el'];
                    $row['TC_MC']        	        = $row1['tc_mc'];
                    $row['TC_BL']         	        = $row1['tc_bl'];
                    $row['TC_ML']         	        = $row1['tc_ml'];
                    $row['TC_S']         	        = $row1['tc_s'];
                    $row['TC_PSL']         	        = $row1['tc_psl'];
                    $row['TC_VVAWC']         	    = $row1['tc_vvawc'];
                    $row['TC_BDL']         	        = $row1['tc_bdl'];
                    $row['TC_CL']         	        = $row1['tc_cl'];

                    $row['TC_OBT']                  = $row1['tc_obt'];
                    $row['TC_RD']                   = $row1['tc_rd'];
                    $row['H_SH']                    = $row1['h_sh'];
                    $row['H_SHRD']                  = $row1['h_shrd'];
                    $row['H_LH']                    = $row1['h_lh'];
                    $row['H_LHRD']                  = $row1['h_lhrd'];
                    $row['H_LSH']                   = $row1['h_lsh'];
                    $row['H_LSHRD']                 = $row1['h_lshrd'];
                    $row['OT_OTReg']                = $row1['ot_reg']; 
                    $row['OT_RD']                   = $row1['ot_rd'];
                    $row['OT_SH']                   = $row1['ot_sh'];
                    $row['OT_SHRD']                 = $row1['ot_shrd'];
                    $row['OT_LH']                   = $row1['ot_lh'];
                    $row['OT_LHRD']            = $row1['ot_lhrd'];
                    $row['OT_LSH']            = $row1['ot_lsh'];
                    $row['OT_LSHRD']            = $row1['ot_lshrd'];
                    $row['NP_NPReg']            = $row1['np_npreg'];
                    $row['NP_RD']            = $row1['np_rd'];
                    $row['NP_SH']            = $row1['np_sh'];
                    $row['NP_SHRD']            = $row1['np_shrd'];
                    $row['NP_LH']            = $row1['np_lh'];
                    $row['NP_LHRD']            = $row1['np_lhrd'];
                    $row['NP_LSH']            = $row1['np_lsh'];
                    $row['NP_LSHRD']            = $row1['np_lshrd'];
                    $row['NPOT_NPOT']            = $row1['npot_npot'];
                    $row['NPOT_RD']            = $row1['npot_rd'];
                    $row['NPOT_SH']            = $row1['npot_sh'];
                    $row['NPOT_SHRD']            = $row1['npot_shrd'];
                    $row['NPOT_LH']            = $row1['npot_lh'];
                    $row['NPOT_LHRD']            = $row1['npot_lhrd'];
                    $row['NPOT_LSH']            = $row1['npot_lsh'];
                    $row['NPOT_LSHRD']            = $row1['npot_lshrd'];
                    $adj_late                       = $row1['adj_late'];
                    $adj_late_rd                       = $row1['adj_late_rd'];
                    $adj_late_sh                       = $row1['adj_late_sh'];
                    $adj_late_shrd                       = $row1['adj_late_shrd'];
                    $adj_late_lh                      = $row1['adj_late_lh'];
                    $adj_late_lhrd                      = $row1['adj_late_lhrd'];
                    $adj_late_lsh                      = $row1['adj_late_lsh'];
                    $adj_late_lshrd                      = $row1['adj_late_lshrd'];
                    $adj_ut                      = $row1['adj_ut'];
                    $adj_ut_rd                      = $row1['adj_ut_rd'];
                    $adj_ut_sh                      = $row1['adj_ut_sh'];
                    $adj_ut_shrd                      = $row1['adj_ut_shrd'];
                    $adj_ut_lh                      = $row1['adj_ut_lh'];
                    $adj_ut_lhrd                      = $row1['adj_ut_lhrd'];
                    $adj_ut_lsh                      = $row1['adj_ut_lsh'];
                    $adj_ut_lshrd                      = $row1['adj_ut_lshrd'];
                    $adj_absent                      = $row1['adj_absent'];
                    $adj_absent_rd                      = $row1['adj_absent_rd'];
                    $adj_absent_sh                      = $row1['adj_absent_sh'];
                    $adj_absent_shrd                      = $row1['adj_absent_shrd'];
                    $adj_absent_lh                      = $row1['adj_absent_lh'];
                    $adj_absent_lhrd                      = $row1['adj_absent_lhrd'];
                    $adj_absent_lsh                      = $row1['adj_absent_lsh'];
                    $adj_absent_lshrd                      = $row1['adj_absent_lshrd'];
                    $adj_ot                      = $row1['adj_ot'];
                    $adj_ot_rd                      = $row1['adj_ot_rd'];
                    $adj_ot_sh                      = $row1['adj_ot_sh'];
                    $adj_ot_shrd                      = $row1['adj_ot_shrd'];
                    $adj_ot_lh                      = $row1['adj_ot_lh'];
                    $adj_ot_lhrd                      = $row1['adj_ot_lhrd'];
                    $adj_ot_lsh                      = $row1['adj_ot_lsh'];
                    $adj_ot_lshrd                      = $row1['adj_ot_lshrd'];
                    $adj_np                      = $row1['adj_np'];
                    $adj_np_rd                      = $row1['adj_np_rd'];
                    $adj_np_sh                      = $row1['adj_np_sh'];
                    $adj_np_shrd                      = $row1['adj_np_shrd'];
                    $adj_np_lh                      = $row1['adj_np_lh'];
                    $adj_np_lhrd                      = $row1['adj_np_lhrd'];
                    $adj_np_lsh                      = $row1['adj_np_lsh'];
                    $adj_np_lshrd                      = $row1['adj_np_lshrd'];
                    $adj_npot                      = $row1['adj_npot'];
                    $adj_npot_rd                      = $row1['adj_npot_rd'];
                    $adj_npot_sh                      = $row1['adj_npot_sh'];
                    $adj_npot_shrd                      = $row1['adj_npot_shrd'];
                    $adj_npot_lh                      = $row1['adj_npot_lh'];
                    $adj_npot_lhrd                      = $row1['adj_npot_lhrd'];
                    $adj_npot_lsh                      = $row1['adj_npot_lsh'];
                    $adj_npot_lshrd                      = $row1['adj_npot_lshrd'];
                   
                    $adj_obt                      = $row1['adj_obt'];

                    $adj_sl                        = $row1['$adj_sl'];
                    $adj_vl                        = $row1['$adj_vl'];
                    $adj_al                        = $row1['$adj_al'];
                    $adj_lwop                      = $row1['$adj_lwop'];
                    $adj_spl                       = $row1['$adj_spl'];
                    $adj_pl                        = $row1['$adj_pl'];
                    $adj_slh                       = $row1['$adj_slh'];
                    $adj_ul                        = $row1['$adj_ul'];
                    $adj_il                        = $row1['$adj_il'];
                    $adj_el                        = $row1['$adj_el'];
                    $adj_mc                        = $row1['$adj_mc'];
                    $adj_bl                        = $row1['$adj_bl'];
                    $adj_ml                        = $row1['$adj_ml'];
                    $adj_s                         = $row1['$adj_s'];
                    $adj_psl                       = $row1['$adj_psl'];
                    $adj_vvawc                     = $row1['$adj_vvawc'];
                    $adj_bdl                       = $row1['$adj_bdl'];
                    $adj_cl                        = $row1['$adj_cl'];
                   
                }
            }
        }
            $data[] = array( 
                "Rhrs"        	        => round($row['Rhrs'],2),
                "Late"        	        => round($row['Late'],2),
                "Undertime"        	    => round($row['Undertime'],2),
                "Abs"        	        => round($row['Abs'],2),
                "WHrs"        	        => round($row['WHrs'],2),
                "btallowance"        	=> round($row['btallowance'],2),
                "mallowance"        	=> round($row['mallowance'],2),


                "TC_SL"     	        => round( $row['TC_SL'],2),
                "TC_VL"     	        => round( $row['TC_VL'],2),
                "TC_AL"     	        => round( $row['TC_AL'],2),
                "TC_SVL"     	        => round( $row['TC_SVL'],2),
                "TC_LWOP"    	        => round( $row['TC_LWOP'],2),
                "TC_MLWOP"    	        => round( $row['TC_MLWOP'],2),
                "TC_SPL"    	        => round( $row['TC_SPL'],2),
                "TC_PL"     	        => round( $row['TC_PL'],2),
                "TC_SLH"     	        => round( $row['TC_SLH'],2),
                "TC_UL"     	        => round( $row['TC_UL'],2),
                "TC_IL"     	        => round( $row['TC_IL'],2),
                "TC_EL"    	            => round( $row['TC_EL'],2),
                "TC_MC"    	            => round( $row['TC_MC'],2),
                "TC_BL"     	        => round( $row['TC_BL'],2),
                "TC_ML"     	        => round( $row['TC_ML'],2),
                "TC_S"     	            => round( $row['TC_S'],2),
                "TC_PSL"     	        => round( $row['TC_PSL'],2),
                "TC_VVAWC"     	        => round( $row['TC_VVAWC'],2),
                "TC_BDL"     	        => round( $row['TC_BDL'],2),
                "TC_CL"     	        => round( $row['TC_CL'],2),

                "TC_OBT"        	    => round($row['TC_OBT'],2),
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
                "adj_late"        	    => round($adj_late,2),
                "adj_late_rd"        	=> round($adj_late_rd ,2),
                "adj_late_sh"        	=> round($adj_late_sh ,2),
                "adj_late_shrd"        	=> round($adj_late_shrd ,2),
                "adj_late_lh"           => round($adj_late_lh,2),
                "adj_late_lhrd"        	=> round($adj_late_lhrd,2),
                "adj_late_lsh"        	=> round($adj_late_lsh,2),
                "adj_late_lshrd"        => round($adj_late_lshrd,2),
                "adj_ut"        	    => round($adj_ut,2),
                "adj_ut_rd"        	    => round($adj_ut_rd,2),
                "adj_ut_sh"        	    => round($adj_ut_sh,2),
                "adj_ut_shrd"        	=> round($adj_ut_shrd,2),
                "adj_ut_lh"        	    => round($adj_ut_lh,2),
                "adj_ut_lhrd"        	=> round($adj_ut_lhrd,2),
                "adj_ut_lsh"        	=> round($adj_ut_lsh,2),
                "adj_ut_lshrd"        	=> round($adj_ut_lshrd,2),
                "adj_absent"        	=> round($adj_absent,2),
                "adj_absent_rd"        	=> round($adj_absent_rd,2),
                "adj_absent_sh"        	=> round($adj_absent_sh,2),
                "adj_absent_shrd"       => round($adj_absent_shrd,2),
                "adj_absent_lh"        	=> round($adj_absent_lh,2),
                "adj_absent_lhrd"       => round($adj_absent_lhrd,2),
                "adj_absent_lsh"        => round($adj_absent_lsh,2),
                "adj_absent_lshrd"      => round($adj_absent_lshrd,2),
                "adj_ot"        	    => round($adj_ot,2),
                "adj_ot_rd"        	    => round($adj_ot_rd,2),
                "adj_ot_sh"        	    => round($adj_ot_sh,2),
                "adj_ot_shrd"        	=> round($adj_ot_shrd,2),
                "adj_ot_lh"        	    => round($adj_ot_lh,2),
                "adj_ot_lhrd"          	=> round($adj_ot_lhrd,2),
                "adj_ot_lsh"        	=> round($adj_ot_lsh,2),
                "adj_ot_lshrd"        	=> round($adj_ot_lshrd,2),
                "adj_np"        	    => round($adj_np,2),
                "adj_np_rd"        	    => round($adj_np_rd,2),
                "adj_np_sh"        	    => round($adj_np_sh,2),
                "adj_np_shrd"        	=> round($adj_np_shrd,2),
                "adj_np_lh"        	    => round($adj_np_lh,2),
                "adj_np_lhrd"        	=> round($adj_np_lhrd,2),
                "adj_np_lsh"        	=> round($adj_np_lsh,2),
                "adj_np_lshrd"        	=> round($adj_np_lshrd,2),
                "adj_npot"        	    => round($adj_npot,2),
                "adj_npot_rd"        	=> round($adj_npot_rd,2),
                "adj_npot_sh"        	=> round($adj_npot_sh,2),
                "adj_npot_shrd"        	=> round($adj_npot_shrd,2),
                "adj_npot_lh"        	=> round($adj_npot_lh,2),
                "adj_npot_lhrd"        	=> round($adj_npot_lhrd,2),
                "adj_npot_lsh"        	=> round($adj_npot_lsh,2),
                "adj_npot_lshrd"        => round($adj_npot_lshrd,2),
               
                "adj_obt"        	    => round($adj_obt,2),
                "adj_sl"                       =>  round($adj_sl,2),
                "adj_vl"                       =>  round($adj_vl,2),
                "adj_al"                       =>  round($adj_al,2),
                "adj_lwop"                     =>  round($adj_lwop,2),
                "adj_spl"                      =>  round($adj_spl,2),
                "adj_pl"                       =>  round($adj_pl,2),
                "adj_slh"                      =>  round($adj_slh,2),
                "adj_ul"                       =>  round($adj_ul,2),
                "adj_il"                       =>  round($adj_il,2),
                "adj_el"                       =>  round($adj_el,2),
                "adj_mc"                       =>  round($adj_mc,2),
                "adj_bl"                       =>  round($adj_bl,2),
                "adj_ml"                       =>  round($adj_ml,2),
                "adj_s"                        =>  round($adj_s,2),
                "adj_psl"                      =>  round($adj_psl,2),
                "adj_vvawc"                    =>  round($adj_vvawc,2),
                "adj_bdl"                      =>  round($adj_bdl,2),
                "adj_cl"                       =>  round($adj_cl,2)
                
            );
        }
    }
 return $data;
}

function getPaygroup($con,$id){
    $Qry = new Query();	
    $Qry->table     = "tblpaygrp";
    $Qry->selected  = "`group`";
    $Qry->fields = "id='".$id."'";
    $rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
           return $row['group'];
        }
    }
}

function getPayid($con, $param){
    $type = 'ho';
    $Qry = new Query();	
    $Qry->table     = "vw_payperiod_all";
    $Qry->selected  = "id";
    $Qry->fields = "pay_date='".$param->paydate."' AND type ='". $type."' ";
    $rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
           return $row['id'];
        }
    }
}

function getJoblevels($con, $param){
    error_reporting(0);
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