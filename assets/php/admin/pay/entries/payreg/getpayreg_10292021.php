<?php
error_reporting(0);
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 
session_start();
$_SESSION['transactions'] = array();
$_SESSION['transactionsdeductions'] = array();
$_SESSION['overall'] = [];
$_SESSION['overalldeductions'] = [];
$search='';
$_SESSION['search'] ='';
$ids = '';
$param = json_decode(file_get_contents('php://input'));

if( !empty( $param->filter->site ) ){
    $id_array = getLocationsbunits($con,$param->filter->site);

    $ids = implode(",",$id_array);
   
    //print_r($ids);
}


$countdept =  $param->id_paydate;
$deptcount = getTotal($con,$countdept);

if( !empty( $param->filter->empname ) ){ $search=" AND pr.idacct =   '". $param->filter->empname ."' "; }
if( !empty( $param->filter->bn ) ){ $search = $search . " AND aj.batchnum =   '". $param->filter->bn ."' "; }
if( !empty( $param->filter->site ) ){ $search = $search . " AND pr.idbunit IN   (". $ids .") "; 
                                      $param->pagination->pageSize = $deptcount; }

$id_paydate = $param->id_paydate;
$_SESSION['id_paydate'] = $id_paydate;

$_SESSION['search'] = $search;

$Qry = new Query();	
$Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblaccountjob as aj ON aj.idacct = a.id ";
$Qry->selected  = "pr.*,CONCAT(`a`.`lname`,IFNULL(CONCAT(' ',`a`.`suffix`),''),', ',`a`.`fname`,' ',SUBSTR(`a`.`mname`,1,1),'. ') AS `empname`, a.empid as empid, 
                    (pr.`abs_amount` + pr.late_amount + pr.ut_amount) AS absentlateunderdeduct, (pr.`whrs_amount` - (pr.`abs_amount` + pr.late_amount + pr.ut_amount) ) AS totalnet,
                    (ot_rd + ot_reg + tc_rd + npot_rd + npot_npot) AS ot_rst_hrs,
                    (ot_rd_amount + ot_reg_amount + tc_rd_amount+npot_rd_amount + npot_npot_amount) AS ot_rst_amount,
                    (ot_lh + ot_lhrd + ot_lsh + ot_lshrd + ot_sh + ot_shrd + npot_sh + npot_shrd + npot_lh + npot_lhrd + npot_lsh + npot_lshrd) AS oth_hol_hrs,
                    (ot_lh_amount + ot_lhrd_amount + ot_lsh_amount + ot_lshrd_amount + ot_sh_amount + ot_shrd_amount + npot_sh_amount + npot_shrd_amount + npot_lh_amount + npot_lhrd_amount + npot_lsh_amount + npot_lshrd_amount) AS oth_hol_amount,
                    (hallowance + mallowance + shallowance + swallowance + wallowance + otallowance) AS othertaxincome_hrs,
                    (hallowance_amount + mallowance_amount + shallowance_amount + swallowance_amount + wallowance_amount + otallowance_amount) AS othertaxincome_amount,
                    (adj_mc + adj_el + adj_cl + adj_ml + adj_pl + adj_spl + adj_vl + adj_bl + tc_vl + tc_spl + tc_sl + tc_sil + tc_ml + tc_lwop + tc_el + tc_cl + tc_bl) AS otherNTincome_hrs,
                    (adj_mc_amount + adj_el_amount + adj_cl_amount + adj_ml_amount + adj_pl_amount + adj_spl_amount + adj_vl_amount + adj_bl_amount + tc_vl_amount + tc_spl_amount + tc_sl_amount + tc_sil_amount + tc_ml_amount + tc_lwop_amount + tc_el_amount + tc_cl_amount + tc_bl_amount) AS otherNTincome_amount";
$Qry->fields    = "pr.idpayperiod = '" . $id_paydate  . "'" .$search . "  ORDER BY empname LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize ."";


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        $data = array( 
            "paygroup"              => getPaygroup($con,$row['idpaygrp']),
            "empid"        	        => $row['empid'],
            "empname"        	    => $row['empname'],
            "idunit"        	    => $row['idbunit'],
            "department"        	=> getDepartments($con,$row['idbunit']),
            "section"        	    => getSection($con,$row['idbunit'])
        );

        $notincluded = array("id", "idpayperiod", "idpaygrp" , "idacct", "idbunit", "rhrs");
    
        foreach ($row as $key => $value) {
            if (!in_array($key, $notincluded)) {
              $data[$key] = $value;
            }
        }

        $datas['payreg'][] = $data;


        $datas['additional'][] = getAdditionalearnings($con, $row, 'additional');
        

        $datas['additionaldeductions'][] = getAdditionaldeductions($con, $row, 'additional');

    }

    $myData = array('status' => 'success', 
                    'result' => $datas, 
                    'transactions' => $_SESSION['overall'],
                    'transactionsdeductions' => $_SESSION['overalldeductions'],
                    'totalItems' => getTotal($con , $id_paydate),
                    'grandtotal' => getGrandtotal($con ,$id_paydate),
                    'uniqueDepartment' => getMainDepartment($con)

    );
    $return = json_encode($myData);
}else{
	$return = json_encode(array('status' => 'error'));
	
}
print $return;
mysqli_close($con);


function getTotal($con,$id_paydate){
    $Qry = new Query();	
    $Qry->table         = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblaccountjob as aj ON aj.idacct = a.id ";
    $Qry->selected      = "*";
    $Qry->fields        = "idpayperiod = '" . $id_paydate . "'" . $_SESSION['search'];
    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
                $rowcount=mysqli_num_rows($rs);
				return $rowcount;
			}
		}
		return 0;
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

function getMainDepartment($con){
    $data = array();	
    $Qry = new Query();	
    $Qry->table     = "tblsites";
    $Qry->selected  = "*";
    $Qry->fields    = "id>0 order by sites";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $data[] = array( 
                "id"        => $row['id'],
                "location" 		=> $row['sites']

            );
        }
    }
    return $data;
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
                return  getDepartments($con, $row['idunder']);
            }else if($row['stype'] == 'Department'){
                return '';
            }
        }
    }
 
}


function getGrandtotal($con,$id_paydate){
    $Qry = new Query();	
    $Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblaccountjob as aj ON aj.idacct = a.id ";
    $Qry->selected  = "SUM(pr.salary / 2) as salary, 
                        SUM(late) as late,
                            SUM(pr.`abs_amount` + pr.late_amount + pr.ut_amount) as absentlateunderdeduct, 
                            SUM(pr.`whrs_amount` - (pr.`abs_amount` + pr.late_amount + pr.ut_amount) ) as totalnet,
                            SUM(ot_rd + ot_reg + tc_rd + npot_rd + npot_npot) AS ot_rst_hrs,
                            SUM(ot_rd_amount + ot_reg_amount + tc_rd_amount + npot_rd_amount + + npot_npot_amount) as ot_rst_amount,
                            SUM(ot_lh + ot_lhrd + ot_lsh + ot_lshrd + ot_sh + ot_shrd + npot_sh + npot_shrd + npot_lh + npot_lhrd + npot_lsh + npot_lshrd) AS oth_hol_hrs,
                            SUM(ot_lh_amount + ot_lhrd_amount + ot_lsh_amount + ot_lshrd_amount + ot_sh_amount + ot_shrd_amount + npot_sh_amount + npot_shrd_amount + npot_lh_amount + npot_lhrd_amount + npot_lsh_amount + npot_lshrd_amount) AS oth_hol_amount,
                            SUM(hallowance + mallowance + shallowance + swallowance + wallowance + otallowance) AS othertaxincome_hrs,
                            SUM(hallowance_amount + mallowance_amount + shallowance_amount + swallowance_amount + wallowance_amount + otallowance_amount) AS othertaxincome_amount,
                            SUM(adj_mc + adj_el + adj_cl + adj_ml + adj_pl + adj_spl + adj_vl + adj_bl + tc_vl + tc_spl + tc_sl + tc_sil + tc_ml + tc_lwop + tc_el + tc_cl + tc_bl) AS otherNTincome_hrs,
                            SUM(adj_mc_amount + adj_el_amount + adj_cl_amount + adj_ml_amount + adj_pl_amount + adj_spl_amount + adj_vl_amount + adj_bl_amount + tc_vl_amount + tc_spl_amount + tc_sl_amount + tc_sil_amount + tc_ml_amount + tc_lwop_amount + tc_el_amount + tc_cl_amount + tc_bl_amount) AS otherNTincome_amount,
                            SUM(ut) as ut,
                            SUM(abs) as abs,
                            SUM(whrs) as whrs,
                            SUM(tc_vl) as tc_vl,
                            SUM(tc_sl) as tc_sl,
                            SUM(tc_sil) as tc_sil,
                            SUM(tc_cl) as tc_cl,
                            SUM(tc_ml) as tc_ml,
                            SUM(tc_mc) as tc_mc,
                            SUM(tc_sp) as tc_sp,
                            SUM(tc_pl) as tc_pl,
                            SUM(tc_el) as tc_el,
                            SUM(tc_bl) as tc_bl,
                            SUM(tc_obt) as tc_obt,
                            SUM(tc_bdl) as tc_bdl,
                            SUM(tc_spl) as tc_spl,
                            SUM(tc_lwop) as tc_lwop,
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
                            SUM(adj_sil) as adj_sil,
                            SUM(adj_cl) as adj_cl,
                            SUM(adj_ml) as adj_ml,
                            SUM(adj_mc) as adj_mc,
                            SUM(adj_sp) as adj_sp,
                            SUM(adj_pl) as adj_pl,
                            SUM(adj_el) as adj_el,
                            SUM(adj_bl) as adj_bl,
                            SUM(adj_obt) as adj_obt,
                            SUM(adj_bdl) as adj_bdl,
                            SUM(adj_spl) as adj_spl,
                            SUM(adj_lwop) as adj_lwop,
                            SUM(late_amount) as late_amount,
                            SUM(ut_amount) as ut_amount,
                            SUM(abs_amount) as abs_amount,
                            SUM(whrs_amount) as whrs_amount,
                            SUM(tc_vl_amount) as tc_vl_amount,
                            SUM(tc_sl_amount) as tc_sl_amount,
                            SUM(tc_sil_amount) as tc_sil_amount,
                            SUM(tc_cl_amount) as tc_cl_amount,
                            SUM(tc_ml_amount) as tc_ml_amount,
                            SUM(tc_mc_amount) as tc_mc_amount,
                            SUM(tc_sp_amount) as tc_sp_amount,
                            SUM(tc_pl_amount) as tc_pl_amount,
                            SUM(tc_el_amount) as tc_el_amount,
                            SUM(tc_bl_amount) as tc_bl_amount,
                            SUM(tc_obt_amount) as tc_obt_amount,
                            SUM(tc_bdl_amount) as tc_bdl_amount,
                            SUM(tc_spl_amount) as tc_spl_amount,
                            SUM(tc_lwop_amount) as tc_lwop_amount,
                            SUM(tc_rd_amount) as tc_rd_amount,
                            SUM(h_sh_amount) as h_sh_amount,
                            SUM(h_shrd_amount) as h_shrd_amount,
                            SUM(h_lh_amount) as h_lh_amount,
                            SUM(h_lhrd_amount) as h_lhrd_amount,
                            SUM(h_lsh_amount) as h_lsh_amount,
                            SUM(h_lshrd_amount) as h_lshrd_amount,
                            SUM(ot_reg_amount) as ot_reg_amount,
                            SUM(ot_rd_amount) as ot_rd_amount,
                            SUM(ot_sh_amount) as ot_sh_amount,
                            SUM(ot_shrd_amount) as ot_shrd_amount,
                            SUM(ot_lh_amount) as ot_lh_amount,
                            SUM(ot_lhrd_amount) as ot_lhrd_amount,
                            SUM(ot_lsh_amount) as ot_lsh_amount,
                            SUM(ot_lshrd_amount) as ot_lshrd_amount,
                            SUM(np_npreg_amount) as np_npreg_amount,
                            SUM(np_rd_amount) as np_rd_amount,
                            SUM(np_sh_amount) as np_sh_amount,
                            SUM(np_shrd_amount) as np_shrd_amount,
                            SUM(np_lh_amount) as np_lh_amount,
                            SUM(np_lhrd_amount) as np_lhrd_amount,
                            SUM(np_lsh_amount) as np_lsh_amount,
                            SUM(np_lshrd_amount) as np_lshrd_amount,
                            SUM(npot_npot_amount) as npot_npot_amount,
                            SUM(npot_rd_amount) as npot_rd_amount,
                            SUM(npot_sh_amount) as npot_sh_amount,
                            SUM(npot_shrd_amount) as npot_shrd_amount,
                            SUM(npot_lh_amount) as npot_lh_amount,
                            SUM(npot_lhrd_amount) as npot_lhrd_amount,
                            SUM(npot_lsh_amount) as npot_lsh_amount,
                            SUM(npot_lshrd_amount) as npot_lshrd_amount,
                            SUM(adj_late_amount) as adj_late_amount,
                            SUM(adj_late_rd_amount) as adj_late_rd_amount,
                            SUM(adj_late_sh_amount) as adj_late_sh_amount,
                            SUM(adj_late_shrd_amount) as adj_late_shrd_amount,
                            SUM(adj_late_lh_amount) as adj_late_lh_amount,
                            SUM(adj_late_lhrd_amount) as adj_late_lhrd_amount,
                            SUM(adj_late_lsh_amount) as adj_late_lsh_amount,
                            SUM(adj_late_lshrd_amount) as adj_late_lshrd_amount,
                            SUM(adj_ut_amount) as adj_ut_amount,
                            SUM(adj_ut_rd_amount) as adj_ut_rd_amount,
                            SUM(adj_ut_sh_amount) as adj_ut_sh_amount,
                            SUM(adj_ut_shrd_amount) as adj_ut_shrd_amount,
                            SUM(adj_ut_lh_amount) as adj_ut_lh_amount,
                            SUM(adj_ut_lhrd_amount) as adj_ut_lhrd_amount,
                            SUM(adj_ut_lsh_amount) as adj_ut_lsh_amount,
                            SUM(adj_ut_lshrd_amount) as adj_ut_lshrd_amount,
                            SUM(adj_absent_amount) as adj_absent_amount,
                            SUM(adj_absent_rd_amount) as adj_absent_rd_amount,
                            SUM(adj_absent_sh_amount) as adj_absent_sh_amount,
                            SUM(adj_absent_shrd_amount) as adj_absent_shrd_amount,
                            SUM(adj_absent_lh_amount) as adj_absent_lh_amount,
                            SUM(adj_absent_lhrd_amount) as adj_absent_lhrd_amount,
                            SUM(adj_absent_lsh_amount) as adj_absent_lsh_amount,
                            SUM(adj_ot_amount) as adj_ot_amount,
                            SUM(adj_ot_rd_amount) as adj_ot_rd_amount,
                            SUM(adj_ot_sh_amount) as adj_ot_sh_amount,
                            SUM(adj_ot_shrd_amount) as adj_ot_shrd_amount,
                            SUM(adj_ot_lh_amount) as adj_ot_lh_amount,
                            SUM(adj_ot_lhrd_amount) as adj_ot_lhrd_amount,
                            SUM(adj_ot_lsh_amount) as adj_ot_lsh_amount,
                            SUM(adj_ot_lshrd_amount) as adj_ot_lshrd_amount,
                            SUM(adj_np_amount) as adj_np_amount,
                            SUM(adj_np_rd_amount) as adj_np_rd_amount,
                            SUM(adj_np_sh_amount) as adj_np_sh_amount,
                            SUM(adj_np_shrd_amount) as adj_np_shrd_amount,
                            SUM(adj_np_lh_amount) as adj_np_lh_amount,
                            SUM(adj_np_lhrd_amount) as adj_np_lhrd_amount,
                            SUM(adj_np_lsh_amount) as adj_np_lsh_amount,
                            SUM(adj_np_lshrd_amount) as adj_np_lshrd_amount,
                            SUM(adj_npot_amount) as adj_npot_amount,
                            SUM(adj_npot_rd_amount) as adj_npot_rd_amount,
                            SUM(adj_npot_sh_amount) as adj_npot_sh_amount,
                            SUM(adj_npot_shrd_amount) as adj_npot_shrd_amount,
                            SUM(adj_npot_lh_amount) as adj_npot_lh_amount,
                            SUM(adj_npot_lhrd_amount) as adj_npot_lhrd_amount,
                            SUM(adj_npot_lsh_amount) as adj_npot_lsh_amount,
                            SUM(adj_vl_amount) as adj_vl_amount,
                            SUM(adj_sl_amount) as adj_sl_amount,
                            SUM(adj_sil_amount) as adj_sil_amount,
                            SUM(adj_cl_amount) as adj_cl_amount,
                            SUM(adj_ml_amount) as adj_ml_amount,
                            SUM(adj_mc_amount) as adj_mc_amount,
                            SUM(adj_sp_amount) as adj_sp_amount,
                            SUM(adj_pl_amount) as adj_pl_amount,
                            SUM(adj_el_amount) as adj_el_amount,
                            SUM(adj_bl_amount) as adj_bl_amount,
                            SUM(adj_obt_amount) as adj_obt_amount,
                            SUM(adj_bdl_amount) as adj_bdl_amount,
                            SUM(adj_spl_amount) as adj_spl_amount,
                            SUM(adj_lwop_amount) as adj_lwop_amount,
                            SUM(adj_npot_lshrd_amount) as adj_npot_lshrd_amount,
                            SUM(adj_absent_lshrd_amount) as adj_absent_lshrd_amount,
                            SUM(gross_amount) as gross_amount,
                            SUM(ee) as ee,
                            SUM(m_ee) as m_ee,
                            SUM(p_ee) as p_ee,
                            SUM(ph_ee) as ph_ee,
                            SUM(w_tax) as w_tax,
                            SUM(total_ded) as total_ded,
                            SUM(net_amount) as net_amount,
                            SUM(otallowance) as otallowance,
                            SUM(otallowance_amount) as otallowance_amount,
                            SUM(mallowance) as mallowance,
                            SUM(mallowance_amount) as mallowance_amount,
                            SUM(hallowance) as hallowance,
                            SUM(hallowance_amount) as hallowance_amount,
                            SUM(wallowance) as wallowance,
                            SUM(wallowance_amount) as wallowance_amount,
                            SUM(shallowance) as shallowance,
                            SUM(shallowance_amount) as shallowance_amount,
                            SUM(swallowance) as swallowance,
                            SUM(swallowance_amount) as swallowance_amount
                            ";
    $Qry->fields    = "idpayperiod = '" . $id_paydate . "'"  . $_SESSION['search'];
    $rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            $leaves = array("tc_vl" ,"tc_sl" ,"tc_sil" ,"tc_cl" ,"tc_ml" ,"tc_mc" ,"tc_sp" ,"tc_pl" ,"tc_el" ,"tc_bl" ,"tc_obt" ,"tc_bdl" ,"tc_spl" ,"tc_lwop" );
            $overtime = array("ot_reg" ,"ot_rd" ,"ot_sh" ,"ot_shrd" ,"ot_lh" ,"ot_lhrd" ,"ot_lsh", "ot_lshrd","tc_rd");
            $np = array("np_npreg" ,"np_rd" ,"np_sh" ,"np_shrd" ,"np_lh" ,"np_lhrd" ,"np_lsh" ,"np_lshrd");
            $npot = array("npot_npot" ,"npot_rd" ,"npot_sh" ,"npot_shrd" ,"npot_lh" ,"npot_lhrd" ,"npot_lsh" ,"npot_lshrd");
            $holidays = array("h_sh" ,"h_shrd" ,"h_lh" ,"h_lhrd" ,"h_lsh" ,"h_lshrd");
            $adj = array("adj_late" ,"adj_late_rd" ,"adj_late_sh" ,"adj_late_shrd" ,"adj_late_lh" ,"adj_late_lhrd" ,"adj_late_lsh" ,"adj_late_lshrd" ,"adj_ut" ,"adj_ut_rd" ,"adj_ut_sh" ,"adj_ut_shrd" ,"adj_ut_lh" ,"adj_ut_lhrd" ,"adj_ut_lsh" ,"adj_ut_lshrd" ,"adj_absent" ,"adj_absent_rd" ,"adj_absent_sh" ,"adj_absent_shrd" ,"adj_absent_lh" ,"adj_absent_lhrd" ,"adj_absent_lsh" ,"adj_absent_lshrd" ,"adj_ot" ,"adj_ot_rd" ,"adj_ot_sh" ,"adj_ot_shrd" ,"adj_ot_lh" ,"adj_ot_lhrd" ,"adj_ot_lsh" ,"adj_ot_lshrd" ,"adj_np" ,"adj_np_rd" ,"adj_np_sh" ,"adj_np_shrd" ,"adj_np_lh" ,"adj_np_lhrd" ,"adj_np_lsh" ,"adj_np_lshrd" ,"adj_npot" ,"adj_npot_rd" ,"adj_npot_sh" ,"adj_npot_shrd" ,"adj_npot_lh" ,"adj_npot_lhrd" ,"adj_npot_lsh" ,"adj_npot_lshrd" ,"adj_vl" ,"adj_sl" ,"adj_sil" ,"adj_cl" ,"adj_ml" ,"adj_mc" ,"adj_sp" ,"adj_pl" ,"adj_el" ,"adj_bl" ,"adj_obt" ,"adj_bdl" ,"adj_spl" ,"adj_lwop");

            $leavescounts = 0;
            $overtimecounts = 0;
            $npcounts = 0;
            $npotcounts = 0;
            $holidayscounts = 0;
            $adjcounts = 0;

            foreach ($row as $key => $value) {
               $data[$key] = $value;
               if (in_array($key, $leaves)) {
                    if($value != '0.00'){
                        $leavescounts++;
                    }
                }
                if (in_array($key, $overtime)) {
                    if($value != '0.00'){
                        $overtimecounts++;
                    }
                }
                if (in_array($key, $np)) {
                    if($value != '0.00'){
                        $npcounts++;
                    }
                }
                if (in_array($key, $npot)) {
                    if($value != '0.00'){
                        $npotcounts++;
                    }
                }
                
                if (in_array($key, $holidays)) {
                    if($value != '0.00'){
                        $holidayscounts++;
                    }
                }
                if (in_array($key, $adj)) {
                    if($value != '0.00'){
                        $adjcounts++;
                    }
                }
            }

            $datatransactionsdeductions = [];
            $datatransactions = [];

            foreach ($_SESSION['transactions'] as $key => $value) {
                $datatransactions[] = array( 
                    "transactionid" 	=> $value,
                    "total"             => getGrandtotaladditionalearnings($con, $value)
                );
             }

             
            foreach ($_SESSION['transactionsdeductions'] as $key => $value) {
                $datatransactionsdeductions[] = array( 
                    "transactionid" 	=> $value,
                    "total"             => getGrandtotaladditionaldeductions($con, $value)
                );
             }


            $datas['transactions'] = $datatransactions;
            $datas['transactionsdeductions'] = $datatransactionsdeductions;

            $datas['leavescounts'] = $leavescounts * 2;
            $datas['overtimecounts'] = $overtimecounts * 2;
            $datas['npcounts'] = $npcounts * 2;
            $datas['npotcounts'] = $npotcounts * 2;
            $datas['holidayscounts'] = $holidayscounts * 2;
            $datas['adjcounts'] = $adjcounts * 2;


            $datas['result'] = $data;
        }
    }
 return $datas;
}

function getGrandtotaladditionalearnings($con,$transactionid){
    $Qry = new Query();	
    $Qry->table         = "tblpayregadditional";
    $Qry->selected      = "SUM(amount) as total,type";
    $Qry->fields        = "transactionid = '" . $transactionid . "' AND payregid IN ( SELECT pr.id FROM tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblaccountjob as aj ON aj.idacct = a.id WHERE `idpayperiod` =  '" .$_SESSION['id_paydate']."' " . $_SESSION['search'] . ") AND type = 1";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['total'];
        }
    }
}

function getGrandtotaladditionaldeductions($con,$transactionid){

    $Qry = new Query();	
    $Qry->table         = "tblpayregadditional";
    $Qry->selected      = "SUM(amount) as total,type";
    $Qry->fields        = "transactionid = '" . $transactionid . "' AND payregid IN (SELECT pr.id FROM tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblaccountjob as aj ON aj.idacct = a.id WHERE `idpayperiod` =  '" .$_SESSION['id_paydate'] ."' " . $_SESSION['search'] . ") AND type = 0";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['total'];
        }
    }
}



function getAdditionalearnings($con, $row, $type){
    $data = [];
   
    $Qry = new Query();	
    $Qry->table         = "tblpayregadditional";
    $Qry->selected      = "*";
    $Qry->fields        = "payregid = '" .$row['id']. "' AND type = 1";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $data[] = array(
                "transaction" => $row['transactionid'],
                "amount" => $row['amount'],
                "type" => $row['type']
            );

            if (!in_array($row['transactionid'], $_SESSION['transactions'])) {
                array_push($_SESSION['transactions'],$row['transactionid']);

                $data1 = array('id' =>  $row['transactionid'],
                            'name' => getTransactionName($con,$row['transactionid'])
                        );

                $_SESSION['overall'][] = $data1;
            }
        }
    }
    return $data;
}

function getAdditionaldeductions($con, $row, $type){
    $data = [];
   
    $Qry = new Query();	
    $Qry->table         = "tblpayregadditional";
    $Qry->selected      = "*";
    $Qry->fields        = "payregid = '" .$row['id']. "' AND type = 0";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $data[] = array(
                "transaction" => $row['transactionid'],
                "amount" => $row['amount'],
                "type" => $row['type']
            );

            if (!in_array($row['transactionid'], $_SESSION['transactionsdeductions'])) {
                array_push($_SESSION['transactionsdeductions'],$row['transactionid']);

                $data1 = array('id' =>  $row['transactionid'],
                            'name' => getTransactionName($con,$row['transactionid'])
                        );

                $_SESSION['overalldeductions'][] = $data1;
            }
        }
    }
    return $data;
}



function getTransactionName($con,$id){
    $Qry = new Query();	
    $Qry->table         = "tblclasstrans";
    $Qry->selected      = "name";
    $Qry->fields        = "id = '" . $id . "'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['name'];
        }
    }

}



session_destroy();
?>