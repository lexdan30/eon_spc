<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param  = json_decode(file_get_contents('php://input'));

$Qry=new Query();
$Qry->table="tbltimesheetsummary AS ts
                LEFT JOIN tblaccount AS a ON a.id = ts.idacct
                LEFT JOIN tblaccountjob AS aj ON aj.idacct = ts.idacct
                LEFT JOIN tblfinalpay AS fp ON fp.idacct = ts.idacct";
$Qry->selected="ts.idacct,
                fp.start,
                fp.end,
                fp.tkstatus,
                a.empid,
                CONCAT(`a`.`lname`,IFNULL(CONCAT(' ',`a`.`suffix`),''),', ',`a`.`fname`,' ',SUBSTR(`a`.`mname`,1,1),'. ') AS `empname`,
                aj.idunit,
                ts.idpaygrp,
                SUM(rhrs) as rhrs,
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

                SUM(adj_otallowance) as adj_otallowance,

                SUM(otallowance) as otallowance,
                SUM(mallowance) as mallowance,
                SUM(hallowance) as hallowance,
                SUM(wallowance) as wallowance,
                SUM(shallowance) as shallowance,
                SUM(swallowance) as swallowance,
                SUM(lc_sl) as lc_sl,
                SUM(lc_vl) as lc_vl";
$Qry->fields="type = 'Final Pay' AND fp.prstatus = 0 GROUP BY ts.idacct ORDER BY ts.id DESC";
$rs=$Qry->exe_SELECT($con);
if (mysqli_num_rows($rs) >= 1){
    while ($row = mysqli_fetch_array($rs)){
        $row['Rhrs'] = $row['rhrs'];
        $row['Late'] = $row['late'];
        $row['Undertime'] = $row['ut'];
        $row['Abs'] = $row['abs'];
        $row['WHrs'] = $row['whrs'];
        $row['TC_VL'] = $row['tc_vl'];
        $row['TC_SL'] = $row['tc_sl'];
        $row['TC_CL'] = $row['tc_cl'];
        $row['TC_ML'] = $row['tc_ml'];
        $row['TC_MC'] = $row['tc_mc'];
        $row['TC_PL'] = $row['tc_pl'];
        $row['TC_EL'] = $row['tc_el'];
        $row['TC_BL'] = $row['tc_bl'];
        $row['TC_OBT'] = $row['tc_obt'];
        $row['TC_BDL'] = $row['tc_bdl'];
        $row['TC_SPL'] = $row['tc_spl'];
        $row['TC_LWOP'] = $row['tc_lwop'];

        $row['TC_CVL'] = $row['tc_cvl'];
        $row['TC_MENSTL'] = $row['tc_menstl'];
        $row['TC_WL'] = $row['tc_wl'];

        $row['TC_RD'] = $row['tc_rd'];
        $row['H_SH'] = $row['h_sh'];
        $row['H_SHRD'] = $row['h_shrd'];
        $row['H_LH'] = $row['h_lh'];
        $row['H_LHRD'] = $row['h_lhrd'];
        $row['H_LSH'] = $row['h_lsh'];
        $row['H_LSHRD'] = $row['h_lshrd'];
        $row['OT_OTReg'] = $row['ot_reg'];
        $row['OT_RD'] = $row['ot_rd'];
        $row['OT_SH'] = $row['ot_sh'];
        $row['OT_SHRD'] = $row['ot_shrd'];
        $row['OT_LH'] = $row['ot_lh'];
        $row['OT_LHRD'] = $row['ot_lhrd'];
        $row['OT_LSH'] = $row['ot_lsh'];
        $row['OT_LSHRD'] = $row['ot_lshrd'];
        $row['NP_NPReg'] = $row['np_npreg'];
        $row['NP_RD'] = $row['np_rd'];
        $row['NP_SH'] = $row['np_sh'];
        $row['NP_SHRD'] = $row['np_shrd'];
        $row['NP_LH'] = $row['np_lh'];
        $row['NP_LHRD'] = $row['np_lhrd'];
        $row['NP_LSH'] = $row['np_lsh'];
        $row['NP_LSHRD'] = $row['np_lshrd'];
        $row['NPOT_NPOT'] = $row['npot_npot'];
        $row['NPOT_RD'] = $row['npot_rd'];
        $row['NPOT_SH'] = $row['npot_sh'];
        $row['NPOT_SHRD'] = $row['npot_shrd'];
        $row['NPOT_LH'] = $row['npot_lh'];
        $row['NPOT_LHRD'] = $row['npot_lhrd'];
        $row['NPOT_LSH'] = $row['npot_lsh'];
        $row['NPOT_LSHRD'] = $row['npot_lshrd'];
        $adj_late = $row['adj_late'];
        $adj_late_rd = $row['adj_late_rd'];
        $adj_late_sh = $row['adj_late_sh'];
        $adj_late_shrd = $row['adj_late_shrd'];
        $adj_late_lh = $row['adj_late_lh'];
        $adj_late_lhrd = $row['adj_late_lhrd'];
        $adj_late_lsh = $row['adj_late_lsh'];
        $adj_late_lshrd = $row['adj_late_lshrd'];
        $adj_ut = $row['adj_ut'];
        $adj_ut_rd = $row['adj_ut_rd'];
        $adj_ut_sh = $row['adj_ut_sh'];
        $adj_ut_shrd = $row['adj_ut_shrd'];
        $adj_ut_lh = $row['adj_ut_lh'];
        $adj_ut_lhrd = $row['adj_ut_lhrd'];
        $adj_ut_lsh = $row['adj_ut_lsh'];
        $adj_ut_lshrd = $row['adj_ut_lshrd'];
        $adj_absent = $row['adj_absent'];
        $adj_absent_rd = $row['adj_absent_rd'];
        $adj_absent_sh = $row['adj_absent_sh'];
        $adj_absent_shrd = $row['adj_absent_shrd'];
        $adj_absent_lh = $row['adj_absent_lh'];
        $adj_absent_lhrd = $row['adj_absent_lhrd'];
        $adj_absent_lsh = $row['adj_absent_lsh'];
        $adj_absent_lshrd = $row['adj_absent_lshrd'];
        $adj_ot = $row['adj_ot'];
        $adj_ot_rd = $row['adj_ot_rd'];
        $adj_ot_sh = $row['adj_ot_sh'];
        $adj_ot_shrd = $row['adj_ot_shrd'];
        $adj_ot_lh = $row['adj_ot_lh'];
        $adj_ot_lhrd = $row['adj_ot_lhrd'];
        $adj_ot_lsh = $row['adj_ot_lsh'];
        $adj_ot_lshrd = $row['adj_ot_lshrd'];
        $adj_np = $row['adj_np'];
        $adj_np_rd = $row['adj_np_rd'];
        $adj_np_sh = $row['adj_np_sh'];
        $adj_np_shrd = $row['adj_np_shrd'];
        $adj_np_lh = $row['adj_np_lh'];
        $adj_np_lhrd = $row['adj_np_lhrd'];
        $adj_np_lsh = $row['adj_np_lsh'];
        $adj_np_lshrd = $row['adj_np_lshrd'];
        $adj_npot = $row['adj_npot'];
        $adj_npot_rd = $row['adj_npot_rd'];
        $adj_npot_sh = $row['adj_npot_sh'];
        $adj_npot_shrd = $row['adj_npot_shrd'];
        $adj_npot_lh = $row['adj_npot_lh'];
        $adj_npot_lhrd = $row['adj_npot_lhrd'];
        $adj_npot_lsh = $row['adj_npot_lsh'];
        $adj_npot_lshrd = $row['adj_npot_lshrd'];
        $adj_vl = $row['adj_vl'];
        $adj_sl = $row['adj_sl'];
        $adj_cl = $row['adj_cl'];
        $adj_ml = $row['adj_ml'];
        $adj_mc = $row['adj_mc'];
        $adj_pl = $row['adj_pl'];
        $adj_el = $row['adj_el'];
        $adj_bl = $row['adj_bl'];
        $adj_obt = $row['adj_obt'];
        $adj_bdl = $row['adj_bdl'];
        $adj_spl = $row['adj_spl'];
        $adj_lwop = $row['adj_lwop'];

        $adj_cvl = $row['adj_cvl'];
        $adj_menstl = $row['adj_menstl'];
        $adj_wl = $row['adj_wl'];

        $adj_otallowance = $row['adj_otallowance'];

        $otallowance = $row['otallowance'];
        $mallowance = $row['mallowance'];
        $hallowance = $row['hallowance'];
        $wallowance = $row['wallowance'];
        $shallowance = $row['shallowance'];
        $swallowance = $row['swallowance'];

        $data[] = array(
            "idacct" => $row['idacct'],
            "empid" => $row['empid'],
            "empname" => $row['empname'],
            "start" => $row['start'],
            "end" => $row['end'],
            "tkstatus" => $row['tkstatus'],
            "idunit" => $row['idunit'],
            "department" => getDepartments($con, $row['idunit']),
            "section" => getSection($con, $row['idunit']),
            "paygroup" => getPaygroup($con, $row['idpaygrp']),

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
            "adj_otallowance" => round($adj_otallowance, 2) ,
            "otallowance" => round($otallowance, 2) ,
            "mallowance" => round($mallowance, 2) ,
            "hallowance" => round($hallowance, 2) ,
            "wallowance" => round($wallowance, 2) ,
            "shallowance" => round($shallowance, 2) ,
            "swallowance" => round($swallowance, 2),
            "lc_sl" => round($row['lc_sl'], 2) ,
            "lc_vl" => round($row['lc_vl'], 2)
        );
    }

    $myData = array(
        'status'     => 'success',
        'result'     => $data,
        'totalItems' => getTotal($con, $param) ,
        'grandtotal' => getGrandtotal($con, $param)
    );
    $return = json_encode($myData);
}else{
    $return = json_encode(array(
        'error' => mysqli_error($con)
    ));
}


print $return;
mysqli_close($con);

function getDepartments($con, $idunit){
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
function getSection($con, $idunit){
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
function getPaygroup($con, $id){
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
function getTotal($con, $param){
    $Qry = new Query();
    $Qry->table = "tbltimesheetsummary";
    $Qry->selected = "*";
    $Qry->fields = "type = 'Final Pay'";
    $rs = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1){
        if ($row = mysqli_fetch_array($rs)){
            $rowcount = mysqli_num_rows($rs);
            return $rowcount;
        }
    }
    return 0;
}
function getGrandtotal($con, $param){
    $Qryts = new Query();
    $Qryts->table = "tbltimesheetsummary AS a LEFT JOIN tblaccountjob as aj ON aj.idacct = a.idacct";
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

                        SUM(adj_otallowance) as adj_otallowance,

                        SUM(otallowance) as otallowance,
                        SUM(mallowance) as mallowance,
                        SUM(hallowance) as hallowance,
                        SUM(wallowance) as wallowance,
                        SUM(shallowance) as shallowance,
                        SUM(swallowance) as swallowance,
                        SUM(lc_sl) as lc_sl,
                        SUM(lc_vl) as lc_vl
                        ";
    $Qryts->fields = "type  = 'Final Pay' ";
    $rsts = $Qryts->exe_SELECT($con);
    if (mysqli_num_rows($rsts) >= 1){
        if ($row = mysqli_fetch_array($rsts)){
                $row['Rhrs'] = $row['rhrs'];
                $row['Late'] = $row['late'];
                $row['Undertime'] = $row['ut'];
                $row['Abs'] = $row['abs'];
                $row['WHrs'] = $row['whrs'];
                $row['TC_VL'] = $row['tc_vl'];
                $row['TC_SL'] = $row['tc_sl'];
                $row['TC_CL'] = $row['tc_cl'];
                $row['TC_ML'] = $row['tc_ml'];
                $row['TC_MC'] = $row['tc_mc'];
                $row['TC_PL'] = $row['tc_pl'];
                $row['TC_EL'] = $row['tc_el'];
                $row['TC_BL'] = $row['tc_bl'];
                $row['TC_OBT'] = $row['tc_obt'];
                $row['TC_BDL'] = $row['tc_bdl'];
                $row['TC_SPL'] = $row['tc_spl'];
                $row['TC_LWOP'] = $row['tc_lwop'];

                $row['TC_CVL'] = $row['tc_cvl'];
                $row['TC_MENSTL'] = $row['tc_menstl'];
                $row['TC_WL'] = $row['tc_wl'];

                $row['TC_RD'] = $row['tc_rd'];
                $row['H_SH'] = $row['h_sh'];
                $row['H_SHRD'] = $row['h_shrd'];
                $row['H_LH'] = $row['h_lh'];
                $row['H_LHRD'] = $row['h_lhrd'];
                $row['H_LSH'] = $row['h_lsh'];
                $row['H_LSHRD'] = $row['h_lshrd'];
                $row['OT_OTReg'] = $row['ot_reg'];
                $row['OT_RD'] = $row['ot_rd'];
                $row['OT_SH'] = $row['ot_sh'];
                $row['OT_SHRD'] = $row['ot_shrd'];
                $row['OT_LH'] = $row['ot_lh'];
                $row['OT_LHRD'] = $row['ot_lhrd'];
                $row['OT_LSH'] = $row['ot_lsh'];
                $row['OT_LSHRD'] = $row['ot_lshrd'];
                $row['NP_NPReg'] = $row['np_npreg'];
                $row['NP_RD'] = $row['np_rd'];
                $row['NP_SH'] = $row['np_sh'];
                $row['NP_SHRD'] = $row['np_shrd'];
                $row['NP_LH'] = $row['np_lh'];
                $row['NP_LHRD'] = $row['np_lhrd'];
                $row['NP_LSH'] = $row['np_lsh'];
                $row['NP_LSHRD'] = $row['np_lshrd'];
                $row['NPOT_NPOT'] = $row['npot_npot'];
                $row['NPOT_RD'] = $row['npot_rd'];
                $row['NPOT_SH'] = $row['npot_sh'];
                $row['NPOT_SHRD'] = $row['npot_shrd'];
                $row['NPOT_LH'] = $row['npot_lh'];
                $row['NPOT_LHRD'] = $row['npot_lhrd'];
                $row['NPOT_LSH'] = $row['npot_lsh'];
                $row['NPOT_LSHRD'] = $row['npot_lshrd'];
                $adj_late = $row['adj_late'];
                $adj_late_rd = $row['adj_late_rd'];
                $adj_late_sh = $row['adj_late_sh'];
                $adj_late_shrd = $row['adj_late_shrd'];
                $adj_late_lh = $row['adj_late_lh'];
                $adj_late_lhrd = $row['adj_late_lhrd'];
                $adj_late_lsh = $row['adj_late_lsh'];
                $adj_late_lshrd = $row['adj_late_lshrd'];
                $adj_ut = $row['adj_ut'];
                $adj_ut_rd = $row['adj_ut_rd'];
                $adj_ut_sh = $row['adj_ut_sh'];
                $adj_ut_shrd = $row['adj_ut_shrd'];
                $adj_ut_lh = $row['adj_ut_lh'];
                $adj_ut_lhrd = $row['adj_ut_lhrd'];
                $adj_ut_lsh = $row['adj_ut_lsh'];
                $adj_ut_lshrd = $row['adj_ut_lshrd'];
                $adj_absent = $row['adj_absent'];
                $adj_absent_rd = $row['adj_absent_rd'];
                $adj_absent_sh = $row['adj_absent_sh'];
                $adj_absent_shrd = $row['adj_absent_shrd'];
                $adj_absent_lh = $row['adj_absent_lh'];
                $adj_absent_lhrd = $row['adj_absent_lhrd'];
                $adj_absent_lsh = $row['adj_absent_lsh'];
                $adj_absent_lshrd = $row['adj_absent_lshrd'];
                $adj_ot = $row['adj_ot'];
                $adj_ot_rd = $row['adj_ot_rd'];
                $adj_ot_sh = $row['adj_ot_sh'];
                $adj_ot_shrd = $row['adj_ot_shrd'];
                $adj_ot_lh = $row['adj_ot_lh'];
                $adj_ot_lhrd = $row['adj_ot_lhrd'];
                $adj_ot_lsh = $row['adj_ot_lsh'];
                $adj_ot_lshrd = $row['adj_ot_lshrd'];
                $adj_np = $row['adj_np'];
                $adj_np_rd = $row['adj_np_rd'];
                $adj_np_sh = $row['adj_np_sh'];
                $adj_np_shrd = $row['adj_np_shrd'];
                $adj_np_lh = $row['adj_np_lh'];
                $adj_np_lhrd = $row['adj_np_lhrd'];
                $adj_np_lsh = $row['adj_np_lsh'];
                $adj_np_lshrd = $row['adj_np_lshrd'];
                $adj_npot = $row['adj_npot'];
                $adj_npot_rd = $row['adj_npot_rd'];
                $adj_npot_sh = $row['adj_npot_sh'];
                $adj_npot_shrd = $row['adj_npot_shrd'];
                $adj_npot_lh = $row['adj_npot_lh'];
                $adj_npot_lhrd = $row['adj_npot_lhrd'];
                $adj_npot_lsh = $row['adj_npot_lsh'];
                $adj_npot_lshrd = $row['adj_npot_lshrd'];
                $adj_vl = $row['adj_vl'];
                $adj_sl = $row['adj_sl'];
                $adj_cl = $row['adj_cl'];
                $adj_ml = $row['adj_ml'];
                $adj_mc = $row['adj_mc'];
                $adj_pl = $row['adj_pl'];
                $adj_el = $row['adj_el'];
                $adj_bl = $row['adj_bl'];
                $adj_obt = $row['adj_obt'];
                $adj_bdl = $row['adj_bdl'];
                $adj_spl = $row['adj_spl'];
                $adj_lwop = $row['adj_lwop'];

                $adj_cvl = $row['adj_cvl'];
                $adj_menstl = $row['adj_menstl'];
                $adj_wl = $row['adj_wl'];

                $adj_otallowance = $row['adj_otallowance'];

                $otallowance = $row['otallowance'];
                $mallowance = $row['mallowance'];
                $hallowance = $row['hallowance'];
                $wallowance = $row['wallowance'];
                $shallowance = $row['shallowance'];
                $swallowance = $row['swallowance'];
            
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

        "adj_otallowance" => round($adj_otallowance, 2) ,

        "otallowance" => round($otallowance, 2) ,
        "mallowance" => round($mallowance, 2) ,
        "hallowance" => round($hallowance, 2) ,
        "wallowance" => round($wallowance, 2) ,
        "shallowance" => round($shallowance, 2),
        "swallowance" => round($swallowance, 2),
        "lc_sl" => round($row['lc_sl'], 2),
        "lc_vl" => round($row['lc_vl'], 2)
    );
 
    return $data;
}
?>
