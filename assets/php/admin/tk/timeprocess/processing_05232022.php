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

//AS WHrs

// WHEN lateref + utref > 1 THEN  IF( (acthrs - excess) > shifthrs, shifthrs, (acthrs - excess))
//                                 WHEN lateref + utref != 0 THEN IF( (acthrs - excess) + (lateref + utref)  > shifthrs, shifthrs,(acthrs - excess) + (lateref + utref) )

//WHEN holidaytype IS NOT NULL AND othrs IS NULL AND wshifttyperef = 'Admin' AND (joblvl = 'Supervisor' OR joblvl = 'Rank and File' OR joblvl = 'Confidential') THEN 0 Requested by Eunice but disregard

$Qry->table     = "vw_timesheetfinal";
$Qry->selected  = "tid,
                        empid,
                        idpayperiod,
                        idpaygrp,
                        idunit,
                        empname,
                        SUM(shifthrs) AS Rhrs,
                        SUM((CASE 
                                WHEN holidaytype IS NOT NULL THEN 0
                                WHEN lateref + utref > 1 THEN late
                                WHEN lateref + utref != 0 THEN 0
                                ELSE late
                            END)) AS Late,
                        SUM((CASE 
                                WHEN holidaytype IS NOT NULL THEN 0
                                WHEN lateref + utref > 1 THEN ut
                                WHEN lateref + utref != 0 THEN 0
                                ELSE ut
                            END)) AS Undertime,
                        SUM(IF(leaveappstatus = 1 AND leaveidtype NOT IN(3,32) AND `leave` = 4 AND acthrs = 0.000000 AND idshift != 66,absent - `leave`,IF(leaveidtype IN(3,32),`leave`,absent))) AS `Abs`,
                        SUM((CASE 
                                
                                WHEN timein IS NOT NULL AND timeout IS NOT NULL AND leaveappstatus = 1 AND `leave` = 4 AND acthrs > (shifthrs / 2) THEN (shifthrs / 2)
                                WHEN holidaytype IS NOT NULL THEN IF( (acthrs - excess)  > shifthrs, shifthrs,(acthrs - excess) )
                                WHEN lateref + utref > 1 THEN  IF( (acthrs - excess) > shifthrs, shifthrs, (acthrs - excess))
                                WHEN lateref + utref != 0 THEN IF( (acthrs - excess) + (lateref + utref)  > shifthrs, shifthrs,(acthrs - excess) + (lateref + utref) )
                                ELSE  IF( (acthrs - excess) > shifthrs, shifthrs, (acthrs - excess))
                            END)) AS WHrs, 
                        SUM(IF( FIND_IN_SET(idunit,(SELECT `value` FROM tblpreference WHERE alias = 'BTA')) AND shifttype = 'Broken Schedule' AND acthrs != 0 AND lvapprove IS NULL,1,0)) AS btallowance,
                        SUM(IF(obtripstatus = 1 AND obapprove BETWEEN period_start AND period_endref AND oballowance = 1 ,1,0)) AS mallowance,

                        SUM(IF(leaveidtype = 1 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_SL,
                        SUM(IF(leaveidtype = 2 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_VL,
                        SUM(IF(leaveidtype = 33 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_AL,
                        SUM(IF(leaveidtype = 35 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_SVL,
                        SUM(IF(leaveidtype = 3 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_LWOP,
                        SUM(IF(leaveidtype = 34 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_MLWOP,
                        SUM(IF(leaveidtype = 4 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_SPL,
                        SUM(IF(leaveidtype = 5 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_PL,
                        SUM(IF(leaveidtype = 6 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_SLH,
                        SUM(IF(leaveidtype = 7 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_UL,
                        SUM(IF(leaveidtype = 8 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_IL,
                        SUM(IF(leaveidtype = 9 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_EL,
                        SUM(IF(leaveidtype = 10 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_MC,
                        SUM(IF(leaveidtype = 11 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_BL,
                        SUM(IF(leaveidtype = 12 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_ML,
                        SUM(IF(leaveidtype = 13 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_S,
                        SUM(IF(leaveidtype = 14 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_PSL,
                        SUM(IF(leaveidtype = 15 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_VVAWC,
                        SUM(IF(leaveidtype = 16 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_BDL,
                        SUM(IF(leaveidtype = 17 AND leaveappstatus = 1  AND ( (lvapprove BETWEEN period_start AND period_endref) OR lvapprove < period_start ), `leave`, 0)) AS TC_CL,
                        SUM(IF(obtripstatus = 1 AND obapprove BETWEEN period_start AND period_endref, obhrs,0)) AS TC_OBT,
                        SUM((CASE 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype IS NULL 
                            THEN IF(othrsp > 8
                                    ,IF(othrsp - 8 > 0,8,othrsp)
                                    ,IF(othrsp != 0,othrsp,0)
                                )
                            ELSE 0
                            END))  
                        AS TC_RD,
                        SUM((CASE 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'SPECIAL' AND absent = 0 AND wshifttype = 2 AND idlvl IN (SELECT id FROM `tbljoblvl` WHERE TYPE = 'Confidential' OR TYPE = 'Supervisor' OR TYPE='Rank and File')
                            THEN IF(othrsp > 8 ,8 ,othrsp) 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'SPECIAL' AND absent = 0 AND wshifttype != 2 AND idlvl IN (SELECT id FROM `tbljoblvl` WHERE TYPE = 'Confidential' OR TYPE = 'Supervisor' OR TYPE = 'Rank and File')
                            THEN (CASE 
                            WHEN lateref + utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                            WHEN lateref + utref != 0 THEN IF((acthrs - excess) + (lateref + utref) > 8 ,8 ,(acthrs - excess) + (lateref + utref))
                                    WHEN utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    WHEN utref != 0 THEN IF((acthrs - excess) + (utref) > 8 ,8 ,(acthrs - excess) + (utref))
                                    ELSE IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    END)
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'SPECIAL' AND absent = 0 AND idlvl NOT IN (SELECT id FROM `tbljoblvl` where type = 'Managers')
                            THEN (CASE 
                            WHEN lateref + utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                            WHEN lateref + utref != 0 THEN IF((acthrs - excess) + (lateref + utref) > 8 ,8 ,(acthrs - excess) + (lateref + utref))
                                    WHEN utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    WHEN utref != 0 THEN IF((acthrs - excess) + (utref) > 8 ,8 ,(acthrs - excess) + (utref))
                                    ELSE IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    END)
                            ELSE 0
                            END))
                        AS H_SH,
                        SUM((CASE 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'SPECIAL' AND wshifttype = 2 AND idlvl IN (SELECT id FROM `tbljoblvl` WHERE TYPE = 'Confidential' OR TYPE = 'Supervisor' OR TYPE = 'Rank and File')
                            THEN IF(othrsp > 8 ,8 ,othrsp) 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'SPECIAL' AND wshifttype != 2 AND idlvl IN (SELECT id FROM `tbljoblvl` WHERE TYPE = 'Confidential' OR TYPE = 'Supervisor' OR TYPE = 'Rank and File')
                            THEN (CASE 
                            WHEN lateref + utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                            WHEN lateref + utref != 0 THEN IF((acthrs - excess) + (lateref + utref) > 8 ,8 ,(acthrs - excess) + (lateref + utref))
                                    WHEN utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    WHEN utref != 0 THEN IF((acthrs - excess) + (utref) > 8 ,8 ,(acthrs - excess) + (utref))
                                ELSE IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                            END)
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'SPECIAL' AND absent = 0 AND idlvl NOT IN (SELECT id FROM `tbljoblvl` where type = 'Managers')
                            THEN (CASE 
                            WHEN lateref + utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                            WHEN lateref + utref != 0 THEN IF((acthrs - excess) + (lateref + utref) > 8 ,8 ,(acthrs - excess) + (lateref + utref))
                                    WHEN utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    WHEN utref != 0 THEN IF((acthrs - excess) + (utref) > 8 ,8 ,(acthrs - excess) + (utref))
                                ELSE IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                            END)
                            ELSE 0
                            END)) 
                        AS H_SHRD,
                        SUM((CASE 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'LEGAL' AND wshifttype = 2 AND idlvl IN (SELECT id FROM `tbljoblvl` WHERE TYPE = 'Confidential' OR TYPE = 'Supervisor' OR TYPE = 'Rank and File')
                            THEN IF(othrsp > 8 ,8 ,othrsp) 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'LEGAL' AND wshifttype != 2 AND idlvl IN (SELECT id FROM `tbljoblvl` WHERE TYPE = 'Confidential' OR TYPE = 'Supervisor' OR TYPE = 'Rank and File')
                            THEN (CASE 
                            WHEN lateref + utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                            WHEN lateref + utref != 0 THEN IF((acthrs - excess) + (lateref + utref) > 8 ,8 ,(acthrs - excess) + (lateref + utref))
                                    WHEN utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    WHEN utref != 0 THEN IF((acthrs - excess) + (utref) > 8 ,8 ,(acthrs - excess) + (utref))
                                ELSE IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                END)
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'LEGAL' AND wshifttype != 2 AND absent = 0 AND idlvl NOT IN (SELECT id FROM `tbljoblvl` where type = 'Managers')
                                THEN (CASE 
                                WHEN lateref + utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                WHEN lateref + utref != 0 THEN IF((acthrs - excess) + (lateref + utref) > 8 ,8 ,(acthrs - excess) + (lateref + utref))
                                    WHEN utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    WHEN utref != 0 THEN IF((acthrs - excess) + (utref) > 8 ,8 ,(acthrs - excess) + (utref))
                                    ELSE IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    END)
                            ELSE 0 
                            END))
                        AS H_LH,
                        SUM((CASE 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'LEGAL' AND wshifttype = 2 AND idlvl IN (SELECT id FROM `tbljoblvl` WHERE TYPE = 'Confidential' OR TYPE = 'Supervisor' OR TYPE = 'Rank and File')
                            THEN IF(othrsp > 8 ,8 ,othrsp) 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'LEGAL' AND wshifttype != 2 AND idlvl IN (SELECT id FROM `tbljoblvl` WHERE TYPE = 'Confidential' OR TYPE = 'Supervisor' OR TYPE = 'Rank and File')
                            THEN (CASE 
                            WHEN lateref + utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                            WHEN lateref + utref != 0 THEN IF((acthrs - excess) + (lateref + utref) > 8 ,8 ,(acthrs - excess) + (lateref + utref))
                                    WHEN utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    WHEN utref != 0 THEN IF((acthrs - excess) + (utref) > 8 ,8 ,(acthrs - excess) + (utref))
                                ELSE IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                END)
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'LEGAL' AND absent = 0 AND idlvl NOT IN (SELECT id FROM `tbljoblvl` where type = 'Managers')
                                THEN (CASE 
                                WHEN lateref + utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                WHEN lateref + utref != 0 THEN IF((acthrs - excess) + (lateref + utref) > 8 ,8 ,(acthrs - excess) + (lateref + utref))
                                    WHEN utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    WHEN utref != 0 THEN IF((acthrs - excess) + (utref) > 8 ,8 ,(acthrs - excess) + (utref))
                                    ELSE IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    END)
                            ELSE 0
                            END))
                        AS H_LHRD,
                        SUM((CASE 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'LEGAL SPECIAL' AND wshifttype = 2 AND idlvl IN (SELECT id FROM `tbljoblvl` WHERE TYPE = 'Confidential' OR TYPE = 'Supervisor' OR TYPE = 'Rank and File')
                            THEN IF(othrsp > 8 ,8 ,othrsp) 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'LEGAL SPECIAL' AND wshifttype != 2 AND idlvl IN (SELECT id FROM `tbljoblvl` WHERE TYPE = 'Confidential' OR TYPE = 'Supervisor' OR TYPE = 'Rank and File')
                            THEN (CASE 
                            WHEN lateref + utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                            WHEN lateref + utref != 0 THEN IF((acthrs - excess) + (lateref + utref) > 8 ,8 ,(acthrs - excess) + (lateref + utref))
                                    WHEN utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    WHEN utref != 0 THEN IF((acthrs - excess) + (utref) > 8 ,8 ,(acthrs - excess) + (utref))
                                ELSE IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                END)
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'LEGAL SPECIAL' AND absent = 0 AND idlvl NOT IN (SELECT id FROM `tbljoblvl` where type = 'Managers')
                                THEN (CASE 
                                WHEN lateref + utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                WHEN lateref + utref != 0 THEN IF((acthrs - excess) + (lateref + utref) > 8 ,8 ,(acthrs - excess) + (lateref + utref))
                                    WHEN utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    WHEN utref != 0 THEN IF((acthrs - excess) + (utref) > 8 ,8 ,(acthrs - excess) + (utref))
                                    ELSE IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    END)
                            ELSE 0
                            END))
                        AS H_LSH,
                        SUM((CASE 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'LEGAL SPECIAL' AND wshifttype = 2 AND idlvl IN (SELECT id FROM `tbljoblvl` WHERE TYPE = 'Confidential' OR TYPE = 'Supervisor' OR TYPE = 'Rank and File')
                            THEN IF(othrsp > 8 ,8 ,othrsp) 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'LEGAL SPECIAL' AND wshifttype != 2 AND idlvl IN (SELECT id FROM `tbljoblvl` WHERE TYPE = 'Confidential' OR TYPE = 'Supervisor' OR TYPE = 'Rank and File')
                            THEN (CASE 
                            WHEN lateref + utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                            WHEN lateref + utref != 0 THEN IF((acthrs - excess) + (lateref + utref) > 8 ,8 ,(acthrs - excess) + (lateref + utref))
                                    WHEN utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    WHEN utref != 0 THEN IF((acthrs - excess) + (utref) > 8 ,8 ,(acthrs - excess) + (utref))
                                ELSE IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                END)
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'LEGAL SPECIAL' AND absent = 0 AND idlvl NOT IN (SELECT id FROM `tbljoblvl` where type = 'Managers')
                                THEN (CASE 
                                WHEN lateref + utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                WHEN lateref + utref != 0 THEN IF((acthrs - excess) + (lateref + utref) > 8 ,8 ,(acthrs - excess) + (lateref + utref))
                                    WHEN utref > 1 THEN  IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    WHEN utref != 0 THEN IF((acthrs - excess) + (utref) > 8 ,8 ,(acthrs - excess) + (utref))
                                    ELSE IF( (acthrs - excess) > 8 ,8 , (acthrs - excess))
                                    END)
                            ELSE 0
                            END))
                        AS H_LSHRD,
                        SUM((CASE 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype IS NULL AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref  THEN 
                               othrsp
                            ELSE 0
                            END)) 
                        AS OT_OTReg,
                        SUM((CASE 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype IS NULL AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                                IF(othrsp - 8 > 0,othrsp - 8,0)
                            ELSE 0
                            END)) 
                        AS OT_RD,
                        SUM((CASE 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                               othrsp
                            ELSE 0
                            END)) 
                        AS OT_SH,
                        SUM((CASE 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                               othrsp
                            ELSE 0
                            END)) 
                        AS OT_SHRD,
                        SUM((CASE 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'LEGAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                               othrsp
                            ELSE 0
                            END)) 
                        AS OT_LH,
                        SUM((CASE 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'LEGAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                               othrsp
                            ELSE 0
                            END)) 
                        AS OT_LHRD,
                        SUM((CASE 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                               othrsp
                            ELSE 0
                            END)) 
                        AS OT_LSH,
                        SUM((CASE 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN 
                               othrsp
                            ELSE 0
                            END)) 
                        AS OT_LSHRD,
                        SUM((CASE 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype IS NULL AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                            ELSE 0
                            END)) 
                        AS NP_NPReg,
                        SUM((CASE 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype IS NULL AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                            ELSE 0
                            END)) 
                        AS NP_RD,
                        SUM((CASE 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'SPECIAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                            ELSE 0
                            END)) 
                        AS NP_SH,
                        SUM((CASE 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'SPECIAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                            ELSE 0
                            END)) 
                        AS NP_SHRD,
                        SUM((CASE 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'LEGAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                            ELSE 0
                            END)) 
                        AS NP_LH,
                        SUM((CASE 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'LEGAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                            ELSE 0
                            END)) 
                        AS NP_LHRD,
                        SUM((CASE 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'LEGAL SPECIAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                            ELSE 0
                            END)) 
                        AS NP_LSH,
                        SUM((CASE 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'LEGAL SPECIAL' AND idlvl NOT IN(1,2,3,4,9,10,15,27,28) THEN np
                            ELSE 0
                            END)) 
                        AS NP_LSHRD,
                        SUM((CASE 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype IS NULL AND otstatus = 1  AND otapprove BETWEEN period_start AND period_endref THEN npot
                            ELSE 0
                            END)) 
                        AS NPOT_NPOT,
                        SUM((CASE 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype IS NULL AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN npot
                            ELSE 0
                            END)) 
                        AS NPOT_RD,
                        SUM((CASE 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN npot
                            ELSE 0
                            END)) 
                        AS NPOT_SH,
                        SUM((CASE 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN npot
                            ELSE 0
                            END)) 
                        AS NPOT_SHRD,
                        SUM((CASE 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'LEGAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN npot
                            ELSE 0
                            END)) 
                        AS NPOT_LH,
                        SUM((CASE 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'LEGAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN npot
                            ELSE 0
                            END)) 
                        AS NPOT_LHRD,
                        SUM((CASE 
                            WHEN ((idshift != 4) OR ((idshift IS NULL and defaultschedid != 4))) AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN npot
                            ELSE 0
                            END)) 
                        AS NPOT_LSH,
                        SUM((CASE 
                            WHEN ((idshift = 4) OR ((idshift IS NULL and defaultschedid = 4))) AND holidaytype = 'LEGAL SPECIAL' AND otstatus = 1 AND otapprove BETWEEN period_start AND period_endref THEN npot
                            ELSE 0
                            END)) 
                        AS NPOT_LSHRD";

if(!empty( $param->info->employee)){
    $Qry->fields    = "(separationdate > '".$date1."' OR separationdate IS NULL) AND idemptype = 1 AND tid = '".$param->info->employee."' AND work_date BETWEEN '".$date."' AND '".$date1."' GROUP BY tid  ORDER BY CONCAT(tid,work_date) ASC";
}else if($ids == ''){
    $Qry->fields    = "(separationdate > '".$date1."' OR separationdate IS NULL) AND idemptype = 1 AND work_date BETWEEN '".$date."' AND '".$date1."' GROUP BY tid  ORDER BY CONCAT(tid,work_date) ASC";
}else{
    $Qry->fields    = "(separationdate > '".$date1."' OR separationdate IS NULL) AND idemptype = 1 AND idunit in (".$ids.") AND  work_date BETWEEN '".$date."' AND '".$date1."' GROUP BY tid  ORDER BY CONCAT(tid,work_date) ASC";
}

$rs = $Qry->exe_SELECT($con);

if(mysqli_num_rows($rs)>= 1){
    
    while($row=mysqli_fetch_array($rs)){ 
        deletets($con,$row['tid'], $idpayperiod);

        $row['WHrs'] = $row['WHrs'] + $row['TC_SL']  + $row['TC_VL'] + $row['TC_AL'] + $row['TC_SPL'] + $row['TC_PL'] +$row['TC_SLH'] + $row['TC_UL'] + $row['TC_IL'] +  $row['TC_EL'] +  $row['TC_MC'] +  $row['TC_BL'] +  $row['TC_ML'] +  $row['TC_S'] + $row['TC_PSL'] + $row['TC_VVAWC']  + $row['TC_BDL']  + $row['TC_CL'];
        
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
                            `btallowance`,
                            `mallowance`,
                            `tc_sl`, 
                            `tc_vl`, 
                            `tc_al`, 
                            `tc_svl`, 
                            `tc_lwop`,
                            `tc_mlwop`,
                            `tc_spl`,
                            `tc_pl`, 
                            `tc_slh`, 
                            `tc_ul`, 
                            `tc_il`, 
                            `tc_el`,
                            `tc_mc`,
                            `tc_bl`, 
                            `tc_ml`, 
                            `tc_s`, 
                            `tc_psl`, 
                            `tc_vvawc`, 
                            `tc_bdl`, 
                            `tc_cl`,
                            `tc_obt`,
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
                            `npot_lshrd`
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
                            '".round($row['btallowance'],2)."',
                            '".round($row['mallowance'],2)."',

                           '".round( $row['TC_SL'],2)."',
                            '".round( $row['TC_VL'],2)."',
                            '".round( $row['TC_AL'],2)."',
                            '".round( $row['TC_SVL'],2)."',
                            '".round( $row['TC_LWOP'],2)."',
                            '".round( $row['TC_MLWOP'],2)."',
                            '".round( $row['TC_SPL'],2)."',
                            '".round( $row['TC_PL'],2)."',
                            '".round( $row['TC_SLH'],2)."',
                            '".round( $row['TC_UL'],2)."',
                            '".round( $row['TC_IL'],2)."',
                            '".round( $row['TC_EL'],2)."',
                            '".round( $row['TC_MC'],2)."',
                            '".round( $row['TC_BL'],2)."',
                            '".round( $row['TC_ML'],2)."',
                            '".round( $row['TC_S'],2)."',
                            '".round( $row['TC_PSL'],2)."',
                            '".round( $row['TC_VVAWC'],2)."',
                            '".round( $row['TC_BDL'],2)."',
                            '".round( $row['TC_CL'],2)."',

                            '".round($row['TC_OBT'],2)."',
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
                            '".round($row['NPOT_LSHRD'],2)."'
                            ";                      
        $Qry->exe_INSERT($con);

        echo mysqli_error($con) ;

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
            "btallowance"        	=> round($row['btallowance'],2),
            "mallowance"        	=> round($row['mallowance'],2),


            "TC_SL"         	        => round($row['TC_SL'],2),
            "TC_VL"         	        => round($row['TC_VL'],2),
            "TC_AL"         	        => round($row['TC_AL'],2),
            "TC_SVL"         	        => round($row['TC_SVL'],2),
            "TC_LWOP"        	        => round($row['TC_LWOP'],2),
            "TC_MLWOP"        	        => round($row['TC_MLWOP'],2),
            "TC_SPL"        	        => round($row['TC_SPL'],2),
            "TC_PL"         	        => round($row['TC_PL'],2),
            "TC_SLH"         	        => round($row['TC_SLH'],2),
            "TC_UL"         	        => round($row['TC_UL'],2),
            "TC_IL"         	        => round($row['TC_IL'],2),
            "TC_EL"        	            => round($row['TC_EL'],2),
            "TC_MC"        	            => round($row['TC_MC'],2),
            "TC_BL"         	        => round($row['TC_BL'],2),
            "TC_ML"         	        => round($row['TC_ML'],2),
            "TC_S"         	            => round($row['TC_S'],2),
            "TC_PSL"         	        => round($row['TC_PSL'],2),
            "TC_VVAWC"         	        => round($row['TC_VVAWC'],2),
            "TC_BDL"         	        => round($row['TC_BDL'],2),
            "TC_CL"         	        => round($row['TC_CL'],2),

            "TC_OBT"        	        => round($row['TC_OBT'],2),
            "TC_RD"        	            => round($row['TC_RD'],2),
            "H_SH"        	            => round($row['H_SH'],2),
            "H_SHRD"        	        => round($row['H_SHRD'],2),
            "H_LH"        	            => round($row['H_LH'],2),
            "H_LHRD"        	        => round($row['H_LHRD'],2),
            "H_LSH"        	            => round($row['H_LSH'],2),
            "H_LSHRD"        	        => round($row['H_LSHRD'],2),
            "OT_OTReg"          	    => round($row['OT_OTReg'],2),
            "OT_RD"        	            => round($row['OT_RD'],2),
            "OT_SH"        	            => round($row['OT_SH'],2),
            "OT_SHRD"        	        => round($row['OT_SHRD'],2),
            "OT_LH"        	            => round($row['OT_LH'],2),
            "OT_LHRD"        	        => round($row['OT_LHRD'],2),
            "OT_LSH"        	        => round($row['OT_LSH'],2),
            "OT_LSHRD"        	        => round($row['OT_LSHRD'],2),
            "NP_NPReg"          	    => round($row['NP_NPReg'],2),
            "NP_RD"        	            => round($row['NP_RD'],2),
            "NP_SH"        	            => round($row['NP_SH'],2),
            "NP_SHRD"        	        => round($row['NP_SHRD'],2),
            "NP_LH"        	            => round($row['NP_LH'],2),
            "NP_LHRD"        	        => round($row['NP_LHRD'],2),
            "NP_LSH"        	        => round($row['NP_LSH'],2),
            "NP_LSHRD"        	        => round($row['NP_LSHRD'],2),
            "NPOT_NPOT"        	        => round($row['NPOT_NPOT'],2),
            "NPOT_RD"        	        => round($row['NPOT_RD'],2),
            "NPOT_SH"        	        => round($row['NPOT_SH'],2),
            "NPOT_SHRD"        	        => round($row['NPOT_SHRD'],2),
            "NPOT_LH"        	        => round($row['NPOT_LH'],2),
            "NPOT_LHRD"        	        => round($row['NPOT_LHRD'],2),
            "NPOT_LSH"        	        => round($row['NPOT_LSH'],2),
            "NPOT_LSHRD"        	    => round($row['NPOT_LSHRD'],2)
        );

    }

    updatetkprocess($con);

    $myData = array('status' => 'success', 
                    'result' => $data
                );
    $return = json_encode($myData);
}else{
    $return = json_encode(array('error' => mysqli_error($con)));
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
    $Qry->fields        = "work_date IS NOT NULL  AND  work_date = '".$appdate."' AND tid = '".$empid."'";                
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $Qry1 = new Query();	
            $Qry1->table     = "vw_timesheetaadjustmentfinal";
            $Qry1->selected  = "*";
            $Qry1->fields    = "work_date IS NOT NULL  AND  work_date = '".$appdate."' AND tid = '".$empid."'";
            $rs1 = $Qry1->exe_SELECT($con);
            if(mysqli_num_rows($rs1)>= 1){
                if($row1=mysqli_fetch_array($rs1)){
                  
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
                            if($row1['leavename'] == 'Annual Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_al',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Suspension Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_svl',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Leave Without Pay'){
                                $val  = $row1['leave'];
                                updateadjustmentscol($con,'adj_lwop',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Maternity Leave Without Pay'){
                                $val  = $row1['leave'];
                                updateadjustmentscol($con,'adj_mlwop',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Solo Parent Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_spl',$val,$empid,$lastid);
                            }
                           
                            if($row1['leavename'] == 'Paternity Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_pl',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Sick Leave Holiday'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_slh',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Union Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_ul',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Incentive Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_il',$val,$empid,$lastid);
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
                            if($row1['leavename'] == 'Suspension'){
                                $val  = $row1['leave'];
                               updateadjustmentscol($con,'adj_s',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Prolonged Sick leave'){
                                $val  = $row1['leave'];
                               updateadjustmentscol($con,'adj_psl',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Victims of Violence Against Women and their Children'){
                                $val  = $row1['leave'];
                               updateadjustmentscol($con,'adj_vvawc',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Birthday Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_bdl',$val,$empid,$lastid);
                            }
                            if($row1['leavename'] == 'Compensatory Leave'){
                                $val = $row1['leave'];
                                updateadjustmentscol($con,'adj_cl',$val,$empid,$lastid);
                            }
                            return;
                        }else{
                           $late = $row['late'] - $row1['late'];
                           $ut = $row['ut'] - $row1['ut'];
                           $absent = $row['absent'] - $row1['absent'];

                            $ot = $row1['othrsp'] - $row['othrsp'];
                            $np = $row1['np'] - $row['np'];
                            $npot = $row1['npot'] - $row['npot'];

                            $MEX = array(1,2,3,4,9,10,15,27,28);
                            $supNconfi = array(5,6,7,11,13,16,17,18,19,24,29);

                            if (in_array($row['idlvl'], $MEX)) {
                                $ot = 0;
                                $np = 0;
                                $npot = 0;
                            }

                            if (in_array($row['idlvl'], $supNconfi)) {
                                $ot = 0;
                                $npot = 0;
                            }

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
                        
                        $MEX = array(1,2,3,4,9,10,15,27,28);
                        $supNconfi = array(5,6,7,11,13,16,17,18,19,24,29);

                        if (in_array($row['idlvl'], $MEX)) {
                            $ot = 0;
                            $np = 0;
                            $npot = 0;
                        }

                        if (in_array($row['idlvl'], $supNconfi)) {
                            $ot = 0;
                            $npot = 0;
                        }

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