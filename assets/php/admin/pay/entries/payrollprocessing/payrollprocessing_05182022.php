<?php
require_once ('../../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once ('../../../../classPhp.php');
session_start();

$param = json_decode(file_get_contents('php://input'));
$date = SysDate();
$time = SysTime();
$return = null;

$_SESSION['additionalcont'] = 0;
$_SESSION['contribution'] = 0;

$idpayperiod = array(  
    "period"		=> getFPPeriod($con, $param),
);


$id_paydate = $idpayperiod['period']['id'];

$_SESSION['startdate'] = $idpayperiod['period']['pay_start'];
$_SESSION['enddate'] = $idpayperiod['period']['pay_end'];
$_SESSION['pay_date'] = $idpayperiod['period']['pay_date'];
$_SESSION['period_type'] = $idpayperiod['period']['period_type'];
$_SESSION['hascontri'] = $idpayperiod['period']['hascontri'];



$_SESSION['agencies'] = getAgencies($con);

if( !empty($param->accountid) ){
	$Qry 			= new Query();	
	$Qry->table     = "tbltimesheetsummary";
    $Qry->selected  = "*";
    $Qry->fields = "idpayperiod ='".$id_paydate."' AND type ='".$idpayperiod['period']['type']."'";
 
    $rs	= $Qry->exe_SELECT($con);

	if(mysqli_num_rows($rs)>=1){
		while($row=mysqli_fetch_assoc($rs)){

            if (!in_array($row['idacct'], $_SESSION['agencies'])) {
                $_SESSION['updatedgross'] = 0;
                $_SESSION['idpaygrp'] = $row['idpaygrp'];

                if($row['idpaygrp'] == 7){
                    processMonthlies($con,$row,$id_paydate);
                }
                if($row['idpaygrp'] == 8){
                    processMonthlies($con,$row,$id_paydate);
                }
                if($row['idpaygrp'] == 9){

                }
                if($row['idpaygrp'] == 11){
                    processDalies($con,$row,$id_paydate);
                }
            }else{
                deletepayreg($con,$row);
            }
        }
        updatepayprocess($con, $param);
        $return = json_encode(array('status'=>'success', 'transactions' =>getreturn($con,$id_paydate)));
    }else{
        $return = json_encode(array('status'=>'error', 'error' => mysqli_error($con)));
    }
}

print $return;
mysqli_close($con);

function processDalies($con,$row,$id_paydate){
    $jto = 0;
    deletepayreg($con,$row);

    $notincluded = array("id", "idpayperiod","type", "idpaygrp", "idacct", "idbunit", "rhrs", "late", "ut", "abs");
    
    $lastid = '';
    $salary = getsalary($con,$row);
    $semisalary = getmsalary($con,$row);
    $_SESSION['salary'] = getmsalary($con,$row);

    foreach ($row as $key => $value) {
        if($value != '0.00' OR $key == 'whrs'){
            if (!in_array($key, $notincluded)) {
                if( $lastid == ''){
                    $lastid =  insertpayreg($con ,$key ,$value ,$row, $salary, $semisalary,'d');
                }else{
                    updatepayreg($con ,$key ,$value ,$row ,$lastid, $salary,'d');
                }
            }
        }
    }

    $peraccount = checkContributionperaccount($con,$row);
    $_SESSION['contribution'] = grossContribution($con,$lastid);

    $batchnum = getBatchnum($con, $row);

    if($row['type'] != 'Japanese' || $row['type'] != 'Japanese Conversion'){
        if($batchnum['batchnum'] != 5){
      
            if($peraccount['sss_deduct1'] == 1 OR $peraccount['sss_deduct2'] == 1 ) {
                if($_SESSION['period_type'] == '1H' AND $peraccount['sss_deduct1'] == 1){
                    sssContribution($con ,$lastid ,grossContributionsss($con,$lastid),$peraccount['sss_amt']);
                }
                if($_SESSION['period_type'] == '2H' AND $peraccount['sss_deduct2'] == 1){
                    sssContribution($con ,$lastid ,grossContributionsss($con,$lastid),$peraccount['sss_amt']);
                }
            }else{
                if($_SESSION['hascontri'] == 2){
                    sssContribution($con ,$lastid ,grossContributionsss($con,$lastid),0);
                }
            }

            if($peraccount['ibig_deduct1'] == 1 OR $peraccount['ibig_deduct2'] == 1 ) {
                if($_SESSION['period_type'] == '1H' AND $peraccount['ibig_deduct1'] == 1){
                    pagibigContribution($con ,$lastid ,grossContributionpi($con,$lastid),$peraccount['ibig_amt']);
                }
                if($_SESSION['period_type'] == '2H' AND $peraccount['ibig_deduct2'] == 1){
                    pagibigContribution($con ,$lastid ,grossContributionpi($con,$lastid),$peraccount['ibig_amt']);
                }
            }else{
                if($_SESSION['hascontri'] == 2){
                    pagibigContribution($con ,$lastid ,grossContributionpi($con,$lastid),0);
                }
            }

            if($peraccount['health_deduct1'] == 1 OR $peraccount['health_deduct2'] == 1 ) {
                if($_SESSION['period_type'] == '1H' AND $peraccount['health_deduct1'] == 1){
                    philhealthContribution($con ,$lastid ,grossContributionphil($con,$lastid),$peraccount['health_amt']);
                }
                if($_SESSION['period_type'] == '2H' AND $peraccount['health_deduct2'] == 1){
                    philhealthContribution($con ,$lastid ,grossContributionphil($con,$lastid),$peraccount['health_amt']);
                }
            }else{
                if($_SESSION['hascontri'] == 2){
                    philhealthContribution($con ,$lastid ,grossContributionphil($con,$lastid),0);
                }
            }
        }
    }
    updateGross($con,$lastid,grossIncome($con, $row,$lastid));
    $_SESSION['updatedgross'] = getGross($con,$lastid);
    employeeLoans($con, $row, $lastid);

    if($row['type'] != 'Japanese' || $row['type'] != 'Japanese Conversion'){
        if($batchnum['batchnum'] != 6){
            if($_SESSION['hascontri'] == 2){
                birContribution($con ,$lastid ,grossContributiontaxable($con,$lastid),$row);
            }
        }
    }
    if($row['type'] == 'Japanese' || $row['type'] == 'Japanese Conversion'){
        if(date('d', strtotime($_SESSION['pay_date'])) != 25){
            $jto = birContribution($con ,$lastid ,grossContributiontaxable($con,$lastid),$row);
        }
    }
    
   
    updatetotalDeductions($con,$lastid,totalDeductions($con, $row,$lastid));
    updateNetpay($con,$lastid, $jto);

    return;
}

function processMonthlies($con,$row,$id_paydate){
    $jto = 0;
    deletepayreg($con,$row);

    $notincluded = array("id", "idpayperiod","type", "idpaygrp", "idacct", "idbunit", "rhrs");
    
    $lastid = '';
    $salary = getsalary($con,$row);
    $semisalary = getmsalary($con,$row);
    $_SESSION['salary'] = getmsalary($con,$row);

    foreach ($row as $key => $value) {
        if($value != '0.00' OR $key == 'whrs'){
            if (!in_array($key, $notincluded)) {
                if( $lastid == ''){
                    $lastid =  insertpayreg($con ,$key ,$value ,$row, $salary, $semisalary,'m');
                }else{
                    updatepayreg($con ,$key ,$value ,$row ,$lastid, $salary,'m');
                }
            }
        }
    }
    $peraccount = checkContributionperaccount($con,$row);
    $_SESSION['contribution'] = grossContribution($con,$lastid);

    $batchnum = getBatchnum($con, $row);

    if($row['type'] != 'Japanese' || $row['type'] != 'Japanese Conversion'){
        if($batchnum['batchnum'] != 5){
      
            if($peraccount['sss_deduct1'] == 1 OR $peraccount['sss_deduct2'] == 1 ) {
                if($_SESSION['period_type'] == '1H' AND $peraccount['sss_deduct1'] == 1){
                    sssContribution($con ,$lastid ,grossContributionsss($con,$lastid),$peraccount['sss_amt']);
                }
                if($_SESSION['period_type'] == '2H' AND $peraccount['sss_deduct2'] == 1){
                    sssContribution($con ,$lastid ,grossContributionsss($con,$lastid),$peraccount['sss_amt']);
                }
            }else{
                if($_SESSION['hascontri'] == 2){
                    sssContribution($con ,$lastid ,grossContributionsss($con,$lastid),0);
                }
            }

            if($peraccount['ibig_deduct1'] == 1 OR $peraccount['ibig_deduct2'] == 1 ) {
                if($_SESSION['period_type'] == '1H' AND $peraccount['ibig_deduct1'] == 1){
                    pagibigContribution($con ,$lastid ,grossContributionpi($con,$lastid),$peraccount['ibig_amt']);
                }
                if($_SESSION['period_type'] == '2H' AND $peraccount['ibig_deduct2'] == 1){
                    pagibigContribution($con ,$lastid ,grossContributionpi($con,$lastid),$peraccount['ibig_amt']);
                }
            }else{
                if($_SESSION['hascontri'] == 2){
                    pagibigContribution($con ,$lastid ,grossContributionpi($con,$lastid),0);
                }
            }

            if($peraccount['health_deduct1'] == 1 OR $peraccount['health_deduct2'] == 1 ) {
                if($_SESSION['period_type'] == '1H' AND $peraccount['health_deduct1'] == 1){
                    philhealthContribution($con ,$lastid ,grossContributionphil($con,$lastid),$peraccount['health_amt']);
                }
                if($_SESSION['period_type'] == '2H' AND $peraccount['health_deduct2'] == 1){
                    philhealthContribution($con ,$lastid ,grossContributionphil($con,$lastid),$peraccount['health_amt']);
                }
            }else{
                if($_SESSION['hascontri'] == 2){
                    philhealthContribution($con ,$lastid ,grossContributionphil($con,$lastid),0);
                }
            }
        }
    }

    updateGross($con,$lastid,grossIncome($con, $row,$lastid));
    $_SESSION['updatedgross'] = getGross($con,$lastid);
    employeeLoans($con, $row, $lastid);

    
    if($row['type'] != 'Japanese' || $row['type'] != 'Japanese Conversion'){
        if($batchnum['batchnum'] != 6){
            if($_SESSION['hascontri'] == 2){
                birContribution($con ,$lastid ,grossContributiontaxable($con,$lastid),$row);
            }
        }
    }
    if($row['type'] == 'Japanese' || $row['type'] == 'Japanese Conversion'){
        if(date('d', strtotime($_SESSION['pay_date'])) != 25){
            $jto = birContribution($con ,$lastid ,grossContributiontaxable($con,$lastid),$row);
        }
      
    }
    

    updatetotalDeductions($con,$lastid,totalDeductions($con, $row,$lastid));
    updateNetpay($con,$lastid, $jto);

    return;
}

function getsalary($con,$row){
    $Qry = new Query();	
    $Qry->table         = "tblaccountjob";
    $Qry->selected      = "salary,daysmonth";
    $Qry->fields        = "idacct = '".$row['idacct']."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return ( ($row['salary'] / $row['daysmonth'] ) / 8);
        }
    }
}

function getmsalary($con,$row){
    $Qry = new Query();	
    $Qry->table         = "tblaccountjob";
    $Qry->selected      = "salary";
    $Qry->fields        = "idacct = '".$row['idacct']."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row['salary'] ;
        }
    }
}

function deletepayreg($con,$row){
    $Qry1 = new Query();	
    $Qry1->table     = "tblpayregadditional";
    $Qry1->fields    = "payregid = (SELECT id FROM `tblpayreg` WHERE idpayperiod='".$row['idpayperiod']."' AND idacct='".$row['idacct']."')";
    $Qry1->exe_DELETE($con);

    $Qry2 = new Query();	
    $Qry2->table     = "tblloanpayment";
    $Qry2->fields    = "payregid = (SELECT id FROM `tblpayreg` WHERE idpayperiod='".$row['idpayperiod']."' AND idacct='".$row['idacct']."')";
    $Qry2->exe_DELETE($con);

    $Qry = new Query();	
    $Qry->table     = "tblpayreg";
    $Qry->fields    = "idpayperiod='".$row['idpayperiod']."' AND idacct='".$row['idacct']."'";
    $Qry->exe_DELETE($con);
    return;
}

function insertpayreg($con ,$cols ,$val, $row, $salary, $semisalary,$paytype){
    $amount = round($salary * $val,2);
    if($cols == 'whrs' && $paytype == 'm'){
        if($row['type'] == 'Japanese' || $row['type'] == 'Japanese Conversion'){
            $amount = 0;
        }else{
            $amount = getmsalary($con,$row) / 2;
        }
        
    }

    $additionals = array("tc_rd","h_sh","h_shrd","h_lh","h_lhrd","h_lsh","h_lshrd","ot_reg","ot_rd","ot_sh","ot_shrd","ot_lh","ot_lhrd","ot_lsh","ot_lshrd","np_npreg","np_rd","np_sh","np_shrd","np_lh","np_lhrd","np_lsh","np_lshrd","npot_npot","npot_rd","npot_sh","npot_shrd","npot_lh","npot_lhrd","npot_lsh","npot_lshrd","adj_late_rd","adj_late_sh","adj_late_shrd","adj_late_lh","adj_late_lhrd","adj_late_lsh","adj_late_lshrd","adj_ut_rd","adj_ut_sh","adj_ut_shrd","adj_ut_lh","adj_ut_lhrd","adj_ut_lsh","adj_ut_lshrd","adj_absent_rd","adj_absent_sh","adj_absent_shrd","adj_absent_lh","adj_absent_lhrd","adj_absent_lsh","adj_absent_lshrd","adj_ot","adj_ot_rd","adj_ot_sh","adj_ot_shrd","adj_ot_lh","adj_ot_lhrd","adj_ot_lsh","adj_ot_lshrd","adj_np","adj_np_rd","adj_np_sh","adj_np_shrd","adj_np_lh","adj_np_lhrd","adj_np_lsh","adj_np_lshrd","adj_npot","adj_npot_rd","adj_npot_sh","adj_npot_shrd","adj_npot_lh","adj_npot_lhrd","adj_npot_lsh","adj_npot_lshrd");

    if (in_array($cols, $additionals)) {
        $amount = round(($salary * additional($con,$cols)) * $val,2);
    }

    if($cols == 'abs' && $row['whrs'] == '0.00'){
        if($row['type'] == 'Japanese' || $row['type'] == 'Japanese Conversion'){
            $amount = getmsalary($con,$row);
        }else{
             $amount = getmsalary($con,$row) / 2; 
        }
      
    }


    $Qry = new Query();	
    $Qry->table         = "tblpayreg";
    $Qry->selected      = "`idpayperiod`,`type`,`idpaygrp`, `salary`,`idacct`,`idbunit`,`rhrs`,". $cols .',' . $cols.'_amount';
    $Qry->fields        = "'".$row['idpayperiod']."','".$row['type']."','".$row['idpaygrp']."','".$semisalary."','".$row['idacct']."','".$row['idbunit']."','".$row['rhrs']."','".$val."','".$amount."'";      
    $Qry->exe_INSERT($con);

    $lastid = mysqli_insert_id($con);

    return $lastid;
}

function updatepayreg($con ,$cols ,$val ,$row ,$lastid, $salary,$paytype){
    $kajimaallowance = array("otallowance", "mallowance", "hallowance", "wallowance", "shallowance", "swallowance");

    $amount = round($salary * $val,2);
    if($cols == 'whrs' && $paytype == 'm'){
        if($row['type'] == 'Japanese' || $row['type'] == 'Japanese Conversion'){
            $amount = 0;
        }else{
            $amount = getmsalary($con,$row) / 2;
        }
    }


    $additionals = array("tc_rd","h_sh","h_shrd","h_lh","h_lhrd","h_lsh","h_lshrd","ot_reg","ot_rd","ot_sh","ot_shrd","ot_lh","ot_lhrd","ot_lsh","ot_lshrd","np_npreg","np_rd","np_sh","np_shrd","np_lh","np_lhrd","np_lsh","np_lshrd","npot_npot","npot_rd","npot_sh","npot_shrd","npot_lh","npot_lhrd","npot_lsh","npot_lshrd","adj_late_rd","adj_late_sh","adj_late_shrd","adj_late_lh","adj_late_lhrd","adj_late_lsh","adj_late_lshrd","adj_ut_rd","adj_ut_sh","adj_ut_shrd","adj_ut_lh","adj_ut_lhrd","adj_ut_lsh","adj_ut_lshrd","adj_absent_rd","adj_absent_sh","adj_absent_shrd","adj_absent_lh","adj_absent_lhrd","adj_absent_lsh","adj_absent_lshrd","adj_ot","adj_ot_rd","adj_ot_sh","adj_ot_shrd","adj_ot_lh","adj_ot_lhrd","adj_ot_lsh","adj_ot_lshrd","adj_np","adj_np_rd","adj_np_sh","adj_np_shrd","adj_np_lh","adj_np_lhrd","adj_np_lsh","adj_np_lshrd","adj_npot","adj_npot_rd","adj_npot_sh","adj_npot_shrd","adj_npot_lh","adj_npot_lhrd","adj_npot_lsh","adj_npot_lshrd");

    if (in_array($cols, $additionals)) {
        $amount = round(($salary * additional($con,$cols)) * $val,2);
    }

    $semisalary = getmsalary($con,$row);

    if (in_array($cols, $kajimaallowance)) {
        $amount = processallowancekajima($con ,$cols ,$val ,$semisalary, $lastid, $row);
    }

    
    if($cols == 'abs' && $row['whrs'] == '0.00'){
        if($row['type'] == 'Japanese' || $row['type'] == 'Japanese Conversion'){
            $amount = getmsalary($con,$row);
        }else{
             $amount = getmsalary($con,$row) / 2;
        }
    }

    if($cols == 'tc_obt'){
        $amount = 0;
    }

    $Qry           = new Query();
    $Qry->table    = "tblpayreg";
    $Qry->selected = $cols."=".$cols." + ".$val .", " . $cols."_amount =".$cols."_amount + ".$amount;

    $Qry->fields   = "idacct='".$row['idacct']."' AND id='".$lastid."'";                        
    $Qry->exe_UPDATE($con);
    return;
}

function checkContributionperaccount($con,$row){
    $Qry = new Query();	
    $Qry->table         = "tblaccountjob";
    $Qry->selected      = "sss_deduct1,sss_deduct2,ibig_deduct1,ibig_deduct2,health_deduct1,health_deduct2,sss_amt,ibig_amt,health_amt";
    $Qry->fields        = "idacct = '".$row['idacct']."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row1=mysqli_fetch_assoc($rs)){
            return $row1;
        }
    }
}

function additional($con,$cols){
    $Qry = new Query();	
    $Qry->table         = "tblclasstrans";
    $Qry->selected      = "rate";
    $Qry->fields        = "FIND_IN_SET('".$cols."',additional)";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row['rate'] ;
        }
    }
}

function grossIncome($con, $row,$lastid){
    batchEntries($con, $row, $lastid);
    recurringTransactions($con, $row, $lastid);
    serviceCharge($con, $row, $lastid);
    p13th($con, $row, $lastid);
    allowance($con, $row, $lastid);

    $Qry = new Query();	
    $Qry->table         = "tblpayreg";
    $Qry->selected      = "(SUM(whrs_amount 
                            + tc_vl_amount
                            + tc_sl_amount
                        
                            + tc_cl_amount
                            + tc_ml_amount
                            + tc_mc_amount

                            + tc_pl_amount
                            + tc_el_amount
                            + tc_bl_amount
                            + tc_obt_amount
                            + tc_bdl_amount
                            + tc_spl_amount

                            + tc_cvl_amount
                            + tc_menstl_amount
                            + tc_wl_amount

                            + tc_rd_amount
                            + h_sh_amount
                            + h_shrd_amount
                            + h_lh_amount
                            + h_lhrd_amount
                            + h_lsh_amount
                            + h_lshrd_amount
                            + ot_reg_amount
                            + ot_rd_amount
                            + ot_sh_amount
                            + ot_shrd_amount
                            + ot_lh_amount
                            + ot_lhrd_amount
                            + ot_lsh_amount
                            + ot_lshrd_amount
                            + np_npreg_amount
                            + np_rd_amount
                            + np_sh_amount
                            + np_shrd_amount
                            + np_lh_amount
                            + np_lhrd_amount
                            + np_lsh_amount
                            + np_lshrd_amount
                            + npot_npot_amount
                            + npot_rd_amount
                            + npot_sh_amount
                            + npot_shrd_amount
                            + npot_lh_amount
                            + npot_lhrd_amount
                            + npot_lsh_amount
                            + npot_lshrd_amount
                            + adj_absent_amount
                            + adj_absent_rd_amount
                            + adj_absent_sh_amount
                            + adj_absent_shrd_amount
                            + adj_absent_lh_amount
                            + adj_absent_lhrd_amount
                            + adj_absent_lsh_amount
                            + adj_absent_lshrd_amount
                            + adj_ot_amount
                            + adj_ot_rd_amount
                            + adj_ot_sh_amount
                            + adj_ot_shrd_amount
                            + adj_ot_lh_amount
                            + adj_ot_lhrd_amount
                            + adj_ot_lsh_amount
                            + adj_ot_lshrd_amount
                            + adj_np_amount
                            + adj_np_rd_amount
                            + adj_np_sh_amount
                            + adj_np_shrd_amount
                            + adj_np_lh_amount
                            + adj_np_lhrd_amount
                            + adj_np_lsh_amount
                            + adj_np_lshrd_amount
                            + adj_npot_amount
                            + adj_npot_rd_amount
                            + adj_npot_sh_amount
                            + adj_npot_shrd_amount
                            + adj_npot_lh_amount
                            + adj_npot_lhrd_amount
                            + adj_npot_lsh_amount
                            + adj_npot_lshrd_amount
                            + adj_vl_amount
                            + adj_sl_amount
                          
                            + adj_cl_amount
                            + adj_ml_amount
                            + adj_mc_amount
                         
                            + adj_pl_amount
                            + adj_el_amount
                            + adj_bl_amount
                            + adj_obt_amount
                            + adj_bdl_amount
                            + adj_spl_amount

                            + adj_cvl_amount
                            + adj_menstl_amount
                            + tc_wl_amount

                            + otallowance_amount
                            + mallowance_amount
                            + hallowance_amount
                            + wallowance_amount
                            + shallowance_amount
                            + swallowance_amount) - SUM(late_amount
                                                + tc_lwop_amount
                                                + ut_amount
                                                + abs_amount
                                                + adj_late_amount
                                                + adj_late_rd_amount
                                                + adj_late_sh_amount
                                                + adj_late_shrd_amount
                                                + adj_late_lh_amount
                                                + adj_late_lhrd_amount
                                                + adj_late_lsh_amount
                                                + adj_late_lshrd_amount
                                                + adj_ut_amount
                                                + adj_ut_rd_amount
                                                + adj_ut_sh_amount
                                                + adj_ut_shrd_amount
                                                + adj_ut_lh_amount
                                                + adj_ut_lhrd_amount
                                                + adj_ut_lsh_amount
                                                + adj_ut_lshrd_amount
                                                + adj_lwop_amount)
                                                )
                                                 AS gross";
    $Qry->fields        = "id = '".$lastid."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row['gross'] + getAdditional($con, $lastid);
        }
    }
}

function grossContribution($con,$lastid){
    $Qry = new Query();	
    $Qry->table         = "tblpayreg";
    $Qry->selected      = "(SUM(whrs_amount 
                            + tc_vl_amount
                            + tc_sl_amount
                          
                            + tc_cl_amount
                            + tc_ml_amount
                            + tc_mc_amount
                           
                            + tc_pl_amount
                            + tc_el_amount
                            + tc_bl_amount
                            + tc_obt_amount
                            + tc_bdl_amount
                            + tc_spl_amount

                            + tc_cvl_amount
                            + tc_menstl_amount
                            + tc_wl_amount

                            + tc_rd_amount
                            + h_sh_amount
                            + h_shrd_amount
                            + h_lh_amount
                            + h_lhrd_amount
                            + h_lsh_amount
                            + h_lshrd_amount
                            + ot_reg_amount
                            + ot_rd_amount
                            + ot_sh_amount
                            + ot_shrd_amount
                            + ot_lh_amount
                            + ot_lhrd_amount
                            + ot_lsh_amount
                            + ot_lshrd_amount
                            + np_npreg_amount
                            + np_rd_amount
                            + np_sh_amount
                            + np_shrd_amount
                            + np_lh_amount
                            + np_lhrd_amount
                            + np_lsh_amount
                            + np_lshrd_amount
                            + npot_npot_amount
                            + npot_rd_amount
                            + npot_sh_amount
                            + npot_shrd_amount
                            + npot_lh_amount
                            + npot_lhrd_amount
                            + npot_lsh_amount
                            + npot_lshrd_amount
                            + adj_absent_amount
                            + adj_absent_rd_amount
                            + adj_absent_sh_amount
                            + adj_absent_shrd_amount
                            + adj_absent_lh_amount
                            + adj_absent_lhrd_amount
                            + adj_absent_lsh_amount
                            + adj_absent_lshrd_amount
                            + adj_ot_amount
                            + adj_ot_rd_amount
                            + adj_ot_sh_amount
                            + adj_ot_shrd_amount
                            + adj_ot_lh_amount
                            + adj_ot_lhrd_amount
                            + adj_ot_lsh_amount
                            + adj_ot_lshrd_amount
                            + adj_np_amount
                            + adj_np_rd_amount
                            + adj_np_sh_amount
                            + adj_np_shrd_amount
                            + adj_np_lh_amount
                            + adj_np_lhrd_amount
                            + adj_np_lsh_amount
                            + adj_np_lshrd_amount
                            + adj_npot_amount
                            + adj_npot_rd_amount
                            + adj_npot_sh_amount
                            + adj_npot_shrd_amount
                            + adj_npot_lh_amount
                            + adj_npot_lhrd_amount
                            + adj_npot_lsh_amount
                            + adj_npot_lshrd_amount
                            + adj_vl_amount
                            + adj_sl_amount
                          
                            + adj_cl_amount
                            + adj_ml_amount
                            + adj_mc_amount
                       
                            + adj_cvl_amount
                            + adj_menstl_amount
                            + adj_wl_amount
                       

                            + adj_pl_amount
                            + adj_el_amount
                            + otallowance_amount
                            + mallowance_amount
                            + hallowance_amount
                            + wallowance_amount
                            + shallowance_amount
                            + swallowance_amount) - SUM(late_amount
                                                + ut_amount
                                                + tc_lwop_amount
                                                + abs_amount
                                                + adj_late_amount
                                                + adj_late_rd_amount
                                                + adj_late_sh_amount
                                                + adj_late_shrd_amount
                                                + adj_late_lh_amount
                                                + adj_late_lhrd_amount
                                                + adj_late_lsh_amount
                                                + adj_late_lshrd_amount
                                                + adj_ut_amount
                                                + adj_ut_rd_amount
                                                + adj_ut_sh_amount
                                                + adj_ut_shrd_amount
                                                + adj_ut_lh_amount
                                                + adj_ut_lhrd_amount
                                                + adj_ut_lsh_amount
                                                + adj_ut_lshrd_amount)
                                                )
                                                 AS gross";
    $Qry->fields        = "id = '".$lastid."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row['gross'];
        }
    }
}

function grossContributionsss($con,$lastid){
    $Qry = new Query();	
    $Qry->table         = "tblpayreg";
    $Qry->selected      = "(SUM(whrs_amount
                            + ot_reg_amount
                            + ot_rd_amount
                            + ot_sh_amount
                            + ot_shrd_amount
                            + ot_lh_amount
                            + ot_lhrd_amount
                            + ot_lsh_amount
                            + ot_lshrd_amount
                            + npot_npot_amount
                            + npot_rd_amount
                            + npot_sh_amount
                            + npot_shrd_amount
                            + npot_lh_amount
                            + npot_lhrd_amount
                            + npot_lsh_amount
                            + npot_lshrd_amount
                            + adj_absent_amount
                            + adj_absent_rd_amount
                            + adj_absent_sh_amount
                            + adj_absent_shrd_amount
                            + adj_absent_lh_amount
                            + adj_absent_lhrd_amount
                            + adj_absent_lsh_amount
                            + adj_absent_lshrd_amount
                            + adj_ot_amount
                            + adj_ot_rd_amount
                            + adj_ot_sh_amount
                            + adj_ot_shrd_amount
                            + adj_ot_lh_amount
                            + adj_ot_lhrd_amount
                            + adj_ot_lsh_amount
                            + adj_ot_lshrd_amount
                            + adj_npot_amount
                            + adj_npot_rd_amount
                            + adj_npot_sh_amount
                            + adj_npot_shrd_amount
                            + adj_npot_lh_amount
                            + adj_npot_lhrd_amount
                            + adj_npot_lsh_amount
                            + adj_npot_lshrd_amount) - SUM(late_amount
                                                + ut_amount
                                                + abs_amount
                                                + adj_late_amount
                                                + adj_late_rd_amount
                                                + adj_late_sh_amount
                                                + adj_late_shrd_amount
                                                + adj_late_lh_amount
                                                + adj_late_lhrd_amount
                                                + adj_late_lsh_amount
                                                + adj_late_lshrd_amount
                                                + adj_ut_amount
                                                + adj_ut_rd_amount
                                                + adj_ut_sh_amount
                                                + adj_ut_shrd_amount
                                                + adj_ut_lh_amount
                                                + adj_ut_lhrd_amount
                                                + adj_ut_lsh_amount
                                                + adj_ut_lshrd_amount)
                                                )
                                                 AS gross";
    $Qry->fields        = "id = '".$lastid."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row['gross'];
        }
    }
}
function grossContributionpi($con,$lastid){
    $Qry = new Query();	
    $Qry->table         = "tblpayreg";
    $Qry->selected      = "(SUM(whrs_amount))
                            AS gross";
    $Qry->fields        = "id = '".$lastid."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row['gross'];
        }
    }
}
function grossContributionphil($con,$lastid){
    $Qry = new Query();	
    $Qry->table         = "tblpayreg";
    $Qry->selected      = "(SUM(salary))
                            AS gross";
    $Qry->fields        = "id = '".$lastid."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row['gross'];
        }
    }
}

function grossContributiontaxable($con,$lastid){
    $Qry = new Query();	
    $Qry->table         = "tblpayreg";
    $Qry->selected      = "(SUM(whrs_amount
                            + ot_reg_amount
                            + ot_rd_amount
                            + ot_sh_amount
                            + ot_shrd_amount
                            + ot_lh_amount
                            + ot_lhrd_amount
                            + ot_lsh_amount
                            + ot_lshrd_amount
                            + npot_npot_amount
                            + npot_rd_amount
                            + npot_sh_amount
                            + npot_shrd_amount
                            + npot_lh_amount
                            + npot_lhrd_amount
                            + npot_lsh_amount
                            + npot_lshrd_amount
                            + adj_absent_amount
                            + adj_absent_rd_amount
                            + adj_absent_sh_amount
                            + adj_absent_shrd_amount
                            + adj_absent_lh_amount
                            + adj_absent_lhrd_amount
                            + adj_absent_lsh_amount
                            + adj_absent_lshrd_amount
                            + adj_ot_amount
                            + adj_ot_rd_amount
                            + adj_ot_sh_amount
                            + adj_ot_shrd_amount
                            + adj_ot_lh_amount
                            + adj_ot_lhrd_amount
                            + adj_ot_lsh_amount
                            + adj_ot_lshrd_amount
                            + adj_npot_amount
                            + adj_npot_rd_amount
                            + adj_npot_sh_amount
                            + adj_npot_shrd_amount
                            + adj_npot_lh_amount
                            + adj_npot_lhrd_amount
                            + adj_npot_lsh_amount
                            + adj_npot_lshrd_amount) - SUM(late_amount
                                                + ut_amount
                                                + abs_amount
                                                + adj_late_amount
                                                + adj_late_rd_amount
                                                + adj_late_sh_amount
                                                + adj_late_shrd_amount
                                                + adj_late_lh_amount
                                                + adj_late_lhrd_amount
                                                + adj_late_lsh_amount
                                                + adj_late_lshrd_amount
                                                + adj_ut_amount
                                                + adj_ut_rd_amount
                                                + adj_ut_sh_amount
                                                + adj_ut_shrd_amount
                                                + adj_ut_lh_amount
                                                + adj_ut_lhrd_amount
                                                + adj_ut_lsh_amount
                                                + adj_ut_lshrd_amount
                                                + ee
                                                + m_ee
                                                + p_ee
                                                + ph_ee)
                                                )
                                                AS gross";
    $Qry->fields        = "id = '".$lastid."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return ($row['gross'] + getAdditionaltaxable($con, $lastid)) + $_SESSION['additionalcont'];
        }
    }
}

function updateGross($con,$lastid,$amount){
    $Qry           = new Query();
    $Qry->table    = "tblpayreg";
    $Qry->selected = "gross_amount = '".$amount."'";
    $Qry->fields   = "id='".$lastid."'";                        
    $Qry->exe_UPDATE($con);

    return;
}

function sssContribution($con,$lastid,$amount,$additionalamount){
   
    $Qry = new Query();	
    $Qry->table         = "tblcont_sss";
    $Qry->selected      = "*";

    if($amount > 24750.00){
        $Qry->fields        = "id = 64";
    }else{
        $Qry->fields        = "'".$amount."' BETWEEN sal_creditfrom AND sal_creditto";
    }

    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){

            $row['empcont']  = $row['empcont'] + $additionalamount;

            $data = array('ee' => $row['empcont'],
                            'er' => $row['emprcont'],
                            'ec' => $row['ecc_er'],
                            'm_ee' => $row['mandatory_ee'],
                            'm_er' => $row['mandatory_er']);

            $deduction = ($row['empcont']  +  $row['mandatory_ee']);
        }
    }

    if( $_SESSION['contribution'] > $deduction AND ($_SESSION['contribution'] - $deduction) > 500){
        $_SESSION['contribution'] = $_SESSION['contribution'] - $deduction;
        $_SESSION['additionalcont'] = $_SESSION['additionalcont'] + $additionalamount;
        updateContribution($con,$lastid,$data);
    }
   
        
}
function pagibigContribution($con,$lastid,$amount,$additionalamount){
 
    if($amount < 1500){
        $ee = $amount * .01;
        $er = $amount * .02;
    }else{
        if( $amount > 5000){
            $ee = (5000 * .02) / 2;
            $er = (5000 * .02) / 2;
        }else{
            $ee = ($amount * .02) / 2;
            $er = ($amount * .02) / 2;
        }
    }
    

    $ee =  $ee + $additionalamount;
    $data = array('p_ee' => $ee,'p_er' => $er);
    $deduction = $ee;

    if( $_SESSION['contribution']> $deduction AND ($_SESSION['contribution'] - $deduction) > 500){
        $_SESSION['contribution'] = $_SESSION['contribution'] - $deduction;
        $_SESSION['additionalcont'] = $_SESSION['additionalcont'] + $additionalamount;
        updateContribution($con,$lastid,$data);
    }  
    
}
function philhealthContribution($con,$lastid,$amount,$additionalamount){       

    $Qry = new Query();	
    $Qry->table         = "tblcont_health";
    $Qry->selected      = "*";

    if(($amount) > 60000){
        $Qry->fields        = "id = 4";
    }else{
        $Qry->fields        = "".$amount." BETWEEN salfrom AND salto";
    }

    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            if(($amount) <= 10000){
                $ee = (10000 * $row['ee']) / 2;
                $er = (10000 * $row['er']) / 2;
            }else{
                $ee = ($amount * $row['ee']) / 2 + $row['fix_amt'];
                $er = ($amount * $row['er']) / 2 + $row['fix_amt'];
            }

            $ee = $ee + $additionalamount;

            $data = array('ph_ee' => $ee,
                            'ph_er' => $er);

            $deduction =  $ee;


            if( $_SESSION['contribution'] > $deduction AND ($_SESSION['contribution'] - $deduction) > 500) {
                $_SESSION['contribution'] = $_SESSION['contribution'] - $deduction;
                $_SESSION['additionalcont'] = $_SESSION['additionalcont'] + $additionalamount;
                updateContribution($con,$lastid,$data);
            }
        }
    }  
}
function birContribution($con,$lastid,$amount,$row){

    $Qry = new Query();	
    $Qry->table         = "tblcont_bir";
    $Qry->selected      = "*";

    $batchnum = getBatchnum($con, $row);
    if($row['type'] == 'Japanese Conversion'){
        $amount = $amount + jGross25($con,$row);
    }

    if($row['type'] == 'Japanese' || $row['type'] == 'Japanese Conversion'){
        if($amount > 333333.00){
            $Qry->fields        = "id = 8 AND mode = 'M'";
        }else{
            $Qry->fields        = "'".$amount."' BETWEEN `mini` AND `max`  AND mode = 'M'";
        }
    }else if($batchnum['batchnum'] == '5'){
        $Qry->fields        = "mode = 'C'";
    }else{
        if($amount > 333333.00){
            $Qry->fields        = "id = 6 AND mode = 'S'";
        }else{
            $Qry->fields        = "'".$amount."' BETWEEN `mini` AND `max`  AND mode = 'S'";
        }
    }

    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row1=mysqli_fetch_assoc($rs)){
            $w_tax = ( ($amount - $row1['mini']) * $row1['multi']) + $row1['fix_amt'];

            if($batchnum['batchnum'] == '5'){
                $data = array('ew_tax' => $w_tax);
            }else{
                $data = array('w_tax' => $w_tax);
            }
        }
    }else{
        $w_tax = 0;
        $data = array('w_tax' => 0);
    }

    updateContribution($con,$lastid,$data);

    if($row['type'] == 'Japanese' || $row['type'] == 'Japanese Conversion'){
        return $w_tax;
    }else{
        return 0;
    }

}
function allowance($con, $row, $lastid){
    $period_type = $_SESSION['period_type'];

    $Qry = new Query();	
    $Qry->table         = "tblacctallowance";
    $Qry->selected      = "id ,amt as amount,idallowance as transactionid";
    $Qry->fields        = "idacct = '".$row['idacct']."'";

    if($period_type == 1){
        $Qry->fields    = "idacct = '".$row['idacct']."' AND firsthalf = 1";
    }
    
    if($period_type == 2){
        $Qry->fields    = "idacct = '".$row['idacct']."' AND secondhalf = 1";
    }
    
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row1=mysqli_fetch_assoc($rs)){
            $data = array('transactiontype' => 'A',
                            'transactionid' => $row1['transactionid'],
                            'amount' => $row1['amount'],
                            'type' => transType($con,$row1['transactionid'])
                        );

           if($data['type'] == 0){
                if( getGross($con,$lastid) > 500){
                    additionalContribution($con,$lastid,$data);
                }
           }else{
                additionalContribution($con,$lastid,$data);
           }
            
        }
    }
}
function batchEntries($con, $row, $lastid){
    $start = $_SESSION['startdate'];
    $end = $_SESSION['enddate'];

    $Qry = new Query();	
    $Qry->table         = "tblbatchentries AS a LEFT JOIN tblbatchentriesdetails AS b ON a.id = b.batchentriesid";
    $Qry->selected      = "a.id,b.amount,a.transactionid";
    $Qry->fields        = "a.paydate = '".$_SESSION['pay_date']."' AND b.empid = '".$row['idacct']."' ORDER BY a.transactionid";
    
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row1=mysqli_fetch_assoc($rs)){
            
            $data = array('transactiontype' => 'B',
                            'transactionid' => $row1['transactionid'],
                            'amount' => $row1['amount'],
                            'type' => transType($con,$row1['transactionid'])
                        );
                        
            if($data['type'] == 0){
                if( ptgrossIncome($con,$lastid) > 500){
                    additionalContribution($con,$lastid,$data);
                }
            }else{
                additionalContribution($con,$lastid,$data);
            }
            
        }
    }

}
function recurringTransactions($con, $row, $lastid){
    $pay_date =  $_SESSION['pay_date'];
    $period_type = $_SESSION['period_type'];
  
    $start = $_SESSION['startdate'];
    $end = $_SESSION['enddate'];

    $date1=date_create($start);
    $date2=date_create($end);
    $diff=date_diff($date1,$date2);
    $adddate = $diff->format("%a");

    $Qry = new Query();	
    $Qry->table         = "tblrecurring as a left JOIN tblrecurringdetails as b oN a.id = b.recurringid";
    $Qry->selected      = "a.id,b.amount,a.payitemid";

    if($period_type == 1){
        $Qry->fields    = "'".$pay_date."' BETWEEN sdate AND edate AND b.empid = '".$row['idacct']."' AND fh = 1 ORDER BY a.payitemid";
    }
    
    if($period_type == 2){
        $Qry->fields    = "'".$pay_date."' BETWEEN sdate AND edate AND b.empid = '".$row['idacct']."' AND sh = 1 ORDER BY a.payitemid";
    }
    
    $rs = $Qry->exe_SELECT($con);

    if(mysqli_num_rows($rs)>= 1){
        while($row1=mysqli_fetch_assoc($rs)){
            $data = array('transactiontype' => 'R',
                            'transactionid' => $row1['payitemid'],
                            'amount' => $row1['amount'],
                            'type' => transType($con,$row1['payitemid']) 
                        );
            if($data['type'] == 0){
                if( ptgrossIncome($con,$lastid) > 500){
                    additionalContribution($con,$lastid,$data);
                }else{

                }
            }else{
                additionalContribution($con,$lastid,$data);
            }

        }
    }

    
}
function serviceCharge($con, $row, $lastid){
    $pay_date =  $_SESSION['pay_date'];
    $pay_end  =  $_SESSION['enddate'];

    $pr = date('Y-m-d', strtotime($pay_end . ' +1 day'));


    $Qry = new Query();	
    $Qry->table         = "tblservicecharge AS a LEFT JOIN tblservicechargedetails AS b ON a.id = b.servicechargeid";
    $Qry->selected      = "a.id,b.amount,a.payitemid";
    $Qry->fields    = "releasedate BETWEEN '".$pr."' AND  '".$pay_date."' AND b.empid = '".$row['idacct']."'";
    
    
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row1=mysqli_fetch_assoc($rs)){
            $data = array('transactiontype' => 'S',
                            'transactionid' => $row1['payitemid'],
                            'amount' => $row1['amount'],
                            'type' => transType($con,$row1['payitemid'])
                        );
                        
            if($data['type'] == 0){
                if( ptgrossIncome($con,$lastid) > 500){
                    additionalContribution($con,$lastid,$data);
                }
            }else{
                additionalContribution($con,$lastid,$data);
            }
            
        }
    }


}
function p13th($con, $row, $lastid){
    $Qry = new Query();	
    $Qry->table     = "tbl13th";
    $Qry->selected  = "*";
    $Qry->fields    = "paydate = '". $_SESSION['pay_date'] ."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row1=mysqli_fetch_assoc($rs)){
           if($row1['idpaygroup'] == 'all'){
            
                $Qry1 = new Query();	
                $Qry1->table     = "tblpayreg";
                $Qry1->selected  = "(SUM(salary) / 12) * (COUNT(idpayperiod)/2)
                                    - IFNULL((SELECT 
                                        (CASE
                                            WHEN DATE_FORMAT(work_date,'%d') BETWEEN DATE_FORMAT('2021-01-01','%d') AND DATE_FORMAT('2021-01-15','%d') THEN ((DATE_FORMAT(work_date,'%d') - DATE_FORMAT('2021-01-01','%d')) * (salary/daysmonth) /12)
                                            ELSE ((DATE_FORMAT(work_date,'%d') - DATE_FORMAT('2021-01-15','%d')) * (salary/daysmonth) /12)
                                        END) AS days
                                        FROM ( SELECT MIN(work_date) AS 'work_date',salary,daysmonth FROM vw_timesheetfinal WHERE tid = '".$row['idacct']."') AS tmp
                                    WHERE work_date BETWEEN '".$row1['start']."' AND '".$row1['end']."' ),0)
                                     AS amount";


                if($row['type'] == 'Local Employee'){
                    $tblpayperiod = 'tblpayperiod';
                }
                if($row['type'] == 'Helper'){
                    $tblpayperiod = 'tblpayperiod_helper';
                }
                if($row['type'] == 'Japanese'){
                    $tblpayperiod= 'tblpayperiod_japanese';
                }
                if($row['type'] == 'Japanese Conversion'){
                    $tblpayperiod = 'tblpayperiod_japaneseconversion';
                }
                                
                $Qry1->fields    = "idacct = '".$row['idacct']."' AND idpayperiod 
                                    IN (SELECT id
                                        FROM `".$tblpayperiod."`
                                        WHERE period_start BETWEEN '".$row1['start']."' AND '".$row1['end']."' 
                                        AND period_end BETWEEN '".$row1['start']."' AND '".$row1['end']."'
                                    )";
                $rs1             = $Qry1->exe_SELECT($con);
                if(mysqli_num_rows($rs1)>= 1){
                    if($row2=mysqli_fetch_assoc($rs1)){
                        $data = array('transactiontype' => 'p13th',
                                    'transactionid' => '158',
                                    'amount' => $row2['amount'],
                                    'type' => transType($con,'158')
                                );
                        if($data['type'] == 0){
                            if( ptgrossIncome($con,$lastid) > 500){
                                additionalContribution($con,$lastid,$data);
                            }
                        }else{
                            additionalContribution($con,$lastid,$data);
                        }

                       
                    }
                }
                return true;
           }else{
                if($row['idpaygrp'] == $row1['idpaygroup']){
                    return true;
                }
           }
        }
    }
}
function ptgrossIncome($con,$lastid){
    $Qry = new Query();	
    $Qry->table         = "tblpayreg";
    $Qry->selected      = "(SUM(whrs_amount 
                            + tc_vl_amount
                            + tc_sl_amount
                         
                            + tc_cl_amount
                            + tc_ml_amount
                            + tc_mc_amount
                       
                            + tc_pl_amount
                            + tc_el_amount
                            + tc_bl_amount
                            + tc_obt_amount
                            + tc_bdl_amount
                            + tc_spl_amount

                            + tc_cvl_amount
                            + tc_menstl_amount
                            + tc_wl_amount

                            + tc_rd_amount
                            + h_sh_amount
                            + h_shrd_amount
                            + h_lh_amount
                            + h_lhrd_amount
                            + h_lsh_amount
                            + h_lshrd_amount
                            + ot_reg_amount
                            + ot_rd_amount
                            + ot_sh_amount
                            + ot_shrd_amount
                            + ot_lh_amount
                            + ot_lhrd_amount
                            + ot_lsh_amount
                            + ot_lshrd_amount
                            + np_npreg_amount
                            + np_rd_amount
                            + np_sh_amount
                            + np_shrd_amount
                            + np_lh_amount
                            + np_lhrd_amount
                            + np_lsh_amount
                            + np_lshrd_amount
                            + npot_npot_amount
                            + npot_rd_amount
                            + npot_sh_amount
                            + npot_shrd_amount
                            + npot_lh_amount
                            + npot_lhrd_amount
                            + npot_lsh_amount
                            + npot_lshrd_amount
                            + adj_absent_amount
                            + adj_absent_rd_amount
                            + adj_absent_sh_amount
                            + adj_absent_shrd_amount
                            + adj_absent_lh_amount
                            + adj_absent_lhrd_amount
                            + adj_absent_lsh_amount
                            + adj_absent_lshrd_amount
                            + adj_ot_amount
                            + adj_ot_rd_amount
                            + adj_ot_sh_amount
                            + adj_ot_shrd_amount
                            + adj_ot_lh_amount
                            + adj_ot_lhrd_amount
                            + adj_ot_lsh_amount
                            + adj_ot_lshrd_amount
                            + adj_np_amount
                            + adj_np_rd_amount
                            + adj_np_sh_amount
                            + adj_np_shrd_amount
                            + adj_np_lh_amount
                            + adj_np_lhrd_amount
                            + adj_np_lsh_amount
                            + adj_np_lshrd_amount
                            + adj_npot_amount
                            + adj_npot_rd_amount
                            + adj_npot_sh_amount
                            + adj_npot_shrd_amount
                            + adj_npot_lh_amount
                            + adj_npot_lhrd_amount
                            + adj_npot_lsh_amount
                            + adj_npot_lshrd_amount
                            + adj_vl_amount
                            + adj_sl_amount
                           
                            + adj_cl_amount
                            + adj_ml_amount
                            + adj_mc_amount
                          
                            + adj_cvl_amount
                            + adj_menstl_amount
                            + adj_wl_amount

                            + adj_pl_amount
                            + adj_el_amount
                            + otallowance_amount
                            + mallowance_amount
                            + hallowance_amount
                            + wallowance_amount
                            + shallowance_amount
                            + swallowance_amount) - SUM(late_amount
                                                + tc_lwop_amount
                                                + ut_amount
                                                + abs_amount
                                                + adj_late_amount
                                                + adj_late_rd_amount
                                                + adj_late_sh_amount
                                                + adj_late_shrd_amount
                                                + adj_late_lh_amount
                                                + adj_late_lhrd_amount
                                                + adj_late_lsh_amount
                                                + adj_late_lshrd_amount
                                                + adj_ut_amount
                                                + adj_ut_rd_amount
                                                + adj_ut_sh_amount
                                                + adj_ut_shrd_amount
                                                + adj_ut_lh_amount
                                                + adj_ut_lhrd_amount
                                                + adj_ut_lsh_amount
                                                + adj_ut_lshrd_amount
                                                + adj_lwop_amount)
                                                )
                                                 AS gross";
    $Qry->fields        = "id = '".$lastid."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row['gross'] + getAdditional($con, $lastid);
        }
    }
}
function employeeLoans($con, $row, $lastid){
    $pay_date =  $_SESSION['pay_date'];
    $period_type = $_SESSION['period_type'];

    $Qry = new Query();	
    $Qry->table         = "tblloans as a LEFT JOIN tblloantype as b on a.loanid = b.id";
    $Qry->selected      = "a.*, b.transid,(a.begginingbalance - IFNULL( (SELECT SUM(amount) as total FROM `tblloanpayment` WHERE loanid = a.id),0) ) as balance,
                            (a.systemamortization + a.useramortization) AS amount";

    if($period_type == 1){
        $Qry->fields    = "a.empid = '".$row['idacct']."' AND a.firstpaydate <= '".$pay_date."' AND b.app_first = 1";
    }
    
    if($period_type == 2){
        $Qry->fields    = "a.empid = '".$row['idacct']."' AND  a.firstpaydate <= '".$pay_date."' AND b.app_second = 1";
    }
    
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row1=mysqli_fetch_assoc($rs)){
            if($row1['balance'] != 0){
                if($row1['balance']  > $row1['amount'] ){
                    $amount = $row1['amount'];
                }else{
                    $amount = $row1['balance'];
                }
                
                $data = array('transactiontype' => 'L',
                        'transactionid' => $row1['transid'],
                        'amount' => $amount,
                        'type' => transType($con,$row1['transid'])
                    );
           
                if($data['type'] == 0){
                    if(  $_SESSION['updatedgross'] > 500 AND $_SESSION['updatedgross'] >  $data['amount']){
                        $_SESSION['updatedgross'] =  $_SESSION['updatedgross'] - $data['amount'];
                        additionalContribution($con,$lastid,$data);
                        loanpayments($con, $row1['id'], $amount,$lastid);
                    }
                }else{
                    additionalContribution($con,$lastid,$data);
                    loanpayments($con, $row1['id'], $amount,$lastid);
                }

        
                
            }
        }
    }

}
function loanpayments($con, $id, $amount,$lastid){
    $Qry = new Query();	
    $Qry->table         = "tblloanpayment";
    $Qry->selected      = "`loanid`,`amount`, `payment_date`,payregid";
    $Qry->fields        = "'".$id."','".$amount."',NOW(),'".$lastid."'";      
    $Qry->exe_INSERT($con);
    echo mysqli_error($con);
}
function updateContribution($con,$lastid,$data){
    foreach ($data as $key => $value) {
        $Qry           = new Query();
        $Qry->table    = "tblpayreg";
        $Qry->selected =  $key."=".$value ."";
        $Qry->fields   = "id='".$lastid."'";                        
        $Qry->exe_UPDATE($con);
    }
    return;
}
function additionalContribution($con,$lastid,$data){
   
    $Qry           = new Query();
    $Qry->table         = "tblpayregadditional";
    $Qry->selected      = "payregid";
    $Qry->fields        = "'".$lastid."'";      
    $Qry->exe_INSERT($con);
    echo mysqli_error($con);
   
    $payregid = mysqli_insert_id($con);

    foreach ($data as $key => $value) {
        $Qry                = new Query();
        $Qry->table         = "tblpayregadditional";
        $Qry->selected      =  $key."='".$value ."'";
        $Qry->fields        = "id='".$payregid."'";      
        $Qry->exe_UPDATE($con);                   
    }
    return;
}
function transType($con,$id){
    $Qry = new Query();	
    $Qry->table         = "`tblclasstrans` as a LEFT JOIN tblclass as b ON a.idclass = b.id";
    $Qry->selected      = "b.transactiontype";
    $Qry->fields        = "a.id = '".$id."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row['transactiontype'];
        }
    }
}

function getAdditional($con, $lastid){
    $amount = 0;
    $Qry = new Query();	
    $Qry->table         = "tblpayregadditional";
    $Qry->selected      = "*";
    $Qry->fields        = "payregid = '" . $lastid . "' AND type = 1";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
         
            $amount =  $amount + $row['amount'];
        }
    }
    return $amount;
}

function getAdditionaltaxable($con, $lastid){
    $amount = 0;
    $Qry = new Query();	
    $Qry->table         = "tblpayregadditional AS pa LEFT JOIN tblclasstrans AS ct ON pa.transactionid = ct.id LEFT JOIN tblclass AS c ON ct.idclass = c.id";
    $Qry->selected      = "pa.*";
    $Qry->fields        = "pa.payregid = '" . $lastid . "' AND pa.type = 1 AND c.istax = 1";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            $amount =  $amount + $row['amount'];

            $benefits = $amount - 90000;
            if($benefits > 0){
                updatebenefits90k($con,$lastid, 90000);
                $deminimis = $benefits - 70000;
                if($deminimis > 0){
                    updatedeminimis70k($con,$lastid, 70000);
                    return $deminimis;
                }else{
                    updatedeminimis70k($con,$lastid,$benefits);
                    return 0;
                }
            }else{
                updatebenefits90k($con,$lastid,$amount);
                return 0;
            }
        }
    }
    return $amount;
}

function getAdditionaldeductions($con, $lastid){
    $amount = 0;
    $Qry = new Query();	
    $Qry->table         = "tblpayregadditional";
    $Qry->selected      = "*";
    $Qry->fields        = "payregid = '" . $lastid . "' AND type = 0";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            $amount =  $amount + ($row['amount'] * -1);
        }
    }
    return $amount;
}
function updatetotalDeductions($con,$lastid,$amount){
    $Qry           = new Query();
    $Qry->table    = "tblpayreg";
    $Qry->selected = "total_ded = '".$amount."'";
    $Qry->fields   = "id='".$lastid."'";                        
    $Qry->exe_UPDATE($con);

    return;
}
function totalDeductions($con, $row,$lastid){
    $Qry = new Query();	
    $Qry->table         = "tblpayreg";
    $Qry->selected      = "(ee + m_ee + p_ee + ph_ee + w_tax + ew_tax) AS deductions ";
    $Qry->fields        = "id = '".$lastid."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row['deductions'] + (getAdditionaldeductions($con, $lastid) * - 1);
        }
    }
}
function updateNetpay($con,$lastid, $jto){
    $Qry           = new Query();
    $Qry->table    = "tblpayreg";
    $Qry->selected = "net_amount = gross_amount - total_ded + ".$jto."";
    $Qry->fields   = "id='".$lastid."'";                        
    $Qry->exe_UPDATE($con);

    echo mysqli_error($con);
    return;
}
function processallowancekajima($con ,$cols ,$value ,$semisalary, $lastid, $row){
    if($cols == 'otallowance'){
        $Qry = new Query();	
        $Qry->table         = "tblotallowancebracket";
        $Qry->selected      = "*";
        $Qry->fields        = "'".$semisalary."' BETWEEN `min` AND `max`";
    
        $rs = $Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>= 1){
            if($row1=mysqli_fetch_assoc($rs)){
                $amount = $value * $row1['amount'];
            }
        }else{
            $amount = 0;
        }

       return $amount;
    }else{
        if($cols == 'mallowance'){
            $Qry = new Query();
            $Qry->table         = "tblpreference";
            $Qry->selected      = "*";
            $Qry->fields        = "alias = 'MIN MEAL_ALLOW'";

            $rs = $Qry->exe_SELECT($con);
            if(mysqli_num_rows($rs)>= 1){
                if($row1=mysqli_fetch_assoc($rs)){
                    $amount = $value * $row1['value'];
                }
            }else{
                $amount = 0;
            }
            
            return $amount;
        }
        if($cols == 'hallowance'){
            $Qry = new Query();
            $Qry->table         = "tblpreference";
            $Qry->selected      = "*";
            $Qry->fields        = "alias = 'HD MEAL_ALLOW'";

            $rs = $Qry->exe_SELECT($con);
            if(mysqli_num_rows($rs)>= 1){
                if($row1=mysqli_fetch_assoc($rs)){
                    $amount = $value * $row1['value'];
                }
            }else{
                $amount = 0;
            }
            
            return $amount;
        }
        if($cols == 'shallowance'){
            $Qry = new Query();
            $Qry->table         = "tblpreference";
            $Qry->selected      = "*";
            $Qry->fields        = "alias = 'SHD MEAL_ALLOW'";

            $rs = $Qry->exe_SELECT($con);
            if(mysqli_num_rows($rs)>= 1){
                if($row1=mysqli_fetch_assoc($rs)){
                    $amount = $value * $row1['value'];
                }
            }else{
                $amount = 0;
            }
            
            return $amount;
        }
        if($cols == 'wallowance'){

            $Qry = new Query();
            $Qry->table         = "tblpreference";
            $Qry->selected      = "*";
            $Qry->fields        = "alias = 'WD MEAL_ALLOW'";

            $rs = $Qry->exe_SELECT($con);
            if(mysqli_num_rows($rs)>= 1){
                if($row1=mysqli_fetch_assoc($rs)){
                    $amount = $value * $row1['value'];
                }
            }else{
                $amount = 0;
            }
            
            return $amount;
        }

        if($cols == 'swallowance'){

            $Qry = new Query();
            $Qry->table         = "tblpreference";
            $Qry->selected      = "*";
            $Qry->fields        = "alias = 'SWD MEAL_ALLOW'";

            $rs = $Qry->exe_SELECT($con);
            if(mysqli_num_rows($rs)>= 1){
                if($row1=mysqli_fetch_assoc($rs)){
                    $amount = $value * $row1['value'];
                }
            }else{
                $amount = 0;
            }
            
            return $amount;
        }
    }
}
function getGross($con,$lastid){
    $Qry           = new Query();
    $Qry->table    = "tblpayreg";
    $Qry->selected = "gross_amount";
    $Qry->fields   = "id='".$lastid."'";                        
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row['gross_amount'];
        }
    }
    
}
function getreturn($con,$id_paydate){

    $Qry           = new Query();
    $Qry->table    = "tblpayreg as pr 
                        LEFT JOIN tblaccountjob as aj ON pr.idacct = aj.idacct 
                        LEFT JOIN tblbunits as d ON d.id = aj.idunit";
    $Qry->selected = "count(*) as empcount,d.name,SUM(net_amount) totalamount";
    $Qry->fields   = "idpayperiod='".$id_paydate."' GROUP BY aj.idunit";                        
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            $return[] = $row;
        }
    }else{
        $return = ''; 
    }

    return $return;
    
}
function getFPPeriod($con, $param){
    if($param->data->paytype == 'Local Employee'){
        $type = 'ho';
    }
    if($param->data->paytype == 'Helper'){
        $type = 'helper';
    }
    if($param->data->paytype== 'Japanese'){
        $type= 'hajap';
    }
    if($param->data->paytype == 'Japanese Conversion'){
        $type = 'hajapc';
    }


    $data = array();	
    $Qry = new Query();	
    $Qry->table     = "vw_payperiod_all";
    $Qry->selected  = "*";
    $Qry->fields   = "pay_date='".$param->data->paydate."' AND type='".$type."'";      
    
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            if($row['type'] == 'ho'){
                $row['type'] = 'Local Employee';
            }
            if($row['type'] == 'helper'){
                $row['type'] = 'Helper';
            }
            if($row['type'] == 'hajap'){
                $row['type'] = 'Japanese';
            }
            if($row['type'] == 'hajapc'){
                $row['type'] = 'Japanese Conversion';
            }

            $data = array( 
                "id"        	=> $row['id'],
                "pay_start"		=> $row['period_start'],
                "pay_end"		=> $row['period_end'],
                "pay_date"		=> $row['pay_date'],
                "hascontri" 	=> $row['hascontri'],
                "pay_stat"		=> $row['stat'],
                "tkstatus"		=> $row['tkstatus'],
                "period_type" 	=> $row['pay_period'],
                "type" 			=> $row['type'],
                "tkprocess" 	=> $row['tkprocess'],
                "payprocess" 	=> $row['payprocess'],
            );
        }
    }
    return $data;
}
function updatepayprocess($con, $param){
    $idpayperiod = array(  
        "period"		=> getFPPeriod($con, $param),
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

    $Qry->selected = "payprocess = 1";
    $Qry->fields   = "id='".$idpayperiod['period']['id']."'";                        
    $Qry->exe_UPDATE($con);
    
    echo mysqli_error($con);
}
function getBatchnum($con, $row){
    $data = '';
    $Qry = new Query();	
    $Qry->table     = "tblaccountjob";
    $Qry->selected  = "batchnum";
    $Qry->fields   = "idacct='".$row['idacct']."'";   
    
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            $data = $row;
        }
    }
    return $data;
}
function getAgencies($con){
    $data=array();
    $Qry = new Query();	
    $Qry->table     = "tblaccount";
    $Qry->selected  = "id";
    $Qry->fields   = " empid LIKE 'KPIA%'";   
    
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            array_push($data,$row['id']);
        }
    }else{
        $data =array();
    }
    return $data;
}
function jGross25($con, $row){
    $g25 = 0;
    $Qry = new Query();	
    $Qry->table     = "tblpayperiod_japanese";
    $Qry->selected  = "id";
    $Qry->fields   = "period_start='".$_SESSION['startdate']."' AND period_end ='".$_SESSION['enddate']."'";   
    
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row1=mysqli_fetch_assoc($rs)){
            $idpayperiod = $row1['id'];
        }
    }
    

    $Qry = new Query();	
    $Qry->table     = "tblpayreg";
    $Qry->selected  = "gross_amount";
    $Qry->fields   = "idpayperiod ='".$idpayperiod."' AND type = 'Japanese'";   
    
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row2=mysqli_fetch_assoc($rs)){
            $g25 = $row2['gross_amount'];
        }
    }
    

    return $g25;
}
function updatebenefits90k($con,$lastid, $val){
    $Qry           = new Query();
    $Qry->table    = "tblpayreg";
    $Qry->selected = "benefits = '".$val."'";
    $Qry->fields   = "id='".$lastid."'";                        
    $Qry->exe_UPDATE($con);

    echo mysqli_error($con);
    return;
}
function updatedeminimis70k($con,$lastid, $val){
    $Qry           = new Query();
    $Qry->table    = "tblpayreg";
    $Qry->selected = "deminimis = '".$val."'";
    $Qry->fields   = "id='".$lastid."'";                        
    $Qry->exe_UPDATE($con);

    echo mysqli_error($con);
    return;
}
session_destroy();
?>