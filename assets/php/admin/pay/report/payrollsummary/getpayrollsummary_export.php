<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 
session_start();

$date=SysDate();
$param = $_GET;
$search='';

$idpayperiod = array(  
    "period"		=> getFPPeriod($con, $param),
);

$id_array = getJoblevels($con,$param['jobleveltype']);
$ids = implode(",",$id_array);


$id_paydate = $idpayperiod['period']['id'];
$type = $idpayperiod['period']['type'];



if( !empty( $param['empname'] ) ){ $search=" AND pr.idacct ='". $param['empname'] ."' "; }
if( !empty( $param['bn']  ) ){ $search = $search . " AND aj.batchnum =   '". $param['bn']  ."' "; }

if( !empty( $param['site'] ) ){ $search = $search . " AND pr.idbunit IN  (".  $param['site']  .") "; }
if( !empty( $param['jobleveltype'] ) ){ $search=" AND aj.idlvl IN  (". $ids .") "; }


$Qry = new Query();	
$Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblaccountjob as aj ON aj.idacct = a.id ";
$Qry->selected  = "pr.id,
                    pr.idpaygrp,
                    pr.idbunit,
                    CONCAT(`a`.`lname`,IFNULL(CONCAT(' ',`a`.`suffix`),''),', ',`a`.`fname`,' ',SUBSTR(`a`.`mname`,1,1),'. ') AS `empname`,
                    a.empid AS empid,
                    gross_amount,
                    SUM(whrs_amount
                    - ut_amount
                    - late_amount
                    - abs_amount
                    + tc_vl_amount
                   
                    + tc_cl_amount
                    + tc_mc_amount
               
                    + tc_pl_amount
                    + tc_el_amount
                    + tc_bl_amount
                    + tc_obt_amount
                    + tc_bdl_amount
                    + tc_spl_amount
                    - tc_lwop_amount ) AS netbasic,
                    ee,
                    m_ee,
                    p_ee,
                    ph_ee,
                    w_tax,
                    IFNULL((SELECT SUM(amount) AS total FROM `tblpayregadditional` WHERE TYPE = 0 AND payregid = pr.id AND transactionid NOT IN(SELECT transid FROM `tblloantype`)),0) AS othrdeduction,
                    IFNULL((SELECT SUM(amount) AS total FROM `tblpayregadditional` WHERE payregid = pr.id AND transactionid IN(SELECT transid FROM `tblloantype`)),0) AS loans,
                    total_ded,
                    net_amount";
$Qry->fields = "pr.idpaygrp = '".$param['paygrp']."' AND pr.idpayperiod = '" . $id_paydate . "'  AND pr.type = '" . $type  . "' " .$search . "GROUP BY pr.id ORDER BY empname ";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        $data[] = array( 
            "paygroup"              => getPaygroup($con,$row['idpaygrp']),
            "empid"        	        => $row['empid'],
            "empname"        	    => $row['empname'],
            "department"        	=> getDepartments($con,$row['idbunit']),
            "section"        	    => getSection($con,$row['idbunit']),
            "ti"                    => $row['gross_amount'],
            "nti"                   => '',
            "gross"                 => $row['gross_amount'],
            "sss"                   => $row['ee'],
            "wisp"                  => $row['m_ee'],
            "hdmf"                  => $row['p_ee'],
            "phic"                  => $row['ph_ee'],
            "wtax"                  => $row['w_tax'],
            "loans"                 => $row['loans'],
            "othrded"               => $row['othrdeduction'],
            "td"                    => $row['total_ded'],
            "netpay"                => $row['net_amount']
        );

    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=PayrollSummary'.$date.'.csv');
$output = fopen('php://output', 'w');

// fputcsv($output, array($param['company']));
fputcsv($output, array('New World Makati Hotel'));
fputcsv($output, array("Payroll Summary Report"));
fputcsv($output, array("Pay Period: ".$param['period']));
fputcsv($output, array("Pay Type: ".$param['paytype']));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Payroll Group',
						'Employee No.',
                        'Employee Name',
                        'Department',
                        'Section',
                        'Taxable Income',
                        'Non Taxable',
                        'Gross Income',
                        'SSS',
                        'WISP',
                        'HDMF',
                        'PHIC',
                        'Withholding Tax',
                        'Loans',
                        'Other Deductions',
                        'Total Deduction',
                       'Net Pay',
                         )); 

if (isset($data)) {
    foreach ($data as $row22) {
            fputcsv($output, $row22);
    }
}

$grandtotal = grandTotal($con,$idpayperiod,$param);

array_unshift($grandtotal,'');
array_unshift($grandtotal,'');
array_unshift($grandtotal,'');
array_unshift($grandtotal,'');
array_unshift($grandtotal,'Grand Total');

fputcsv($output, $grandtotal);

fputcsv($output, array('Total Headcount:',
                        getTotal($con,$idpayperiod,$param),
                        '',
                        //grandTotal($con,$idpayperiod,$param),                  
                        '',
                         )); 


mysqli_close($con);

function grandTotal($con,$idpayperiod,$param){
    $search='';

    $id_array = getJoblevels($con,$param['jobleveltype']);
    $ids = implode(",",$id_array);

    if( !empty( $param['empname'] ) ){ $search=" AND pr.idacct ='". $param['empname'] ."' "; }
    if( !empty( $param['bn']  ) ){ $search = $search . " AND aj.batchnum =   '". $param['bn']  ."' "; }

    
    if( !empty( $param['site'] ) ){ $search = $search . " AND pr.idbunit IN  (".  $param['site']  .") "; }
    if( !empty( $param['jobleveltype'] ) ){ $search=" AND aj.idlvl IN  (". $ids .") "; }

    $id_paydate = $idpayperiod['period']['id'];
    $type = $idpayperiod['period']['type'];
    // SUM(whrs_amount
    //                     - ut_amount
    //                     - late_amount
    //                     - abs_amount
    //                     + tc_vl_amount
                     
    //                     + tc_cl_amount
    //                     + tc_mc_amount
                   
    //                     + tc_pl_amount
    //                     + tc_el_amount
    //                     + tc_bl_amount
    //                     + tc_obt_amount
    //                     + tc_bdl_amount
    //                     + tc_spl_amount
    //                     - tc_lwop_amount ) 
    //                     as netbasictotal

    $Qry = new Query();	
    $Qry->table         = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblaccountjob as aj ON aj.idacct = a.id ";
    $Qry->selected  = "SUM(gross_amount) as grosstotal,
                      (gross_amount-gross_amount) as nti,
                      SUM(gross_amount) as netbasictotal,
                        SUM(ee) as eetotal,
                        SUM(m_ee) as m_eetotal,
                        SUM(p_ee) as p_eetotal,
                        SUM(ph_ee) as ph_eetotal,
                        SUM(w_tax) as w_taxtotal,
                        IFNULL((SELECT SUM(amount) as total FROM `tblpayregadditional` WHERE `type` = 0 and payregid = pr.id AND transactionid NOT IN (SELECT transid FROM `tblloantype`)),0) as othrdeduction,
                        IFNULL((SELECT SUM(amount) as total FROM `tblpayregadditional` WHERE payregid = pr.id AND transactionid IN (SELECT transid FROM `tblloantype`)),0) as loans,
                        SUM(total_ded) as totalded,
                        SUM(net_amount) as totalnet";
    $Qry->fields        = "pr.idpaygrp = '".$param['paygrp']."' AND pr.idpayperiod = '" . $id_paydate . "'  AND pr.type = '" . $type  . "'" .$search . "";
    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_assoc($rs)){
            
				return $row;
			}
		}
		return 0;
}

function getTotal($con,$idpayperiod,$param){
    $search='';
    $id_array = getJoblevels($con,$param['jobleveltype']);
    $ids = implode(",",$id_array);


    if( !empty( $param['empname'] ) ){ $search=" AND pr.idacct ='". $param['empname'] ."' "; }
    if( !empty( $param['bn']  ) ){ $search = $search . " AND aj.batchnum =   '". $param['bn']  ."' "; }

    if( !empty( $param['site'] ) ){ $search = $search . " AND pr.idbunit IN  (".  $param['site']  .") "; }
    if( !empty( $param['jobleveltype'] ) ){ $search=" AND aj.idlvl IN  (". $ids .") "; }

    $id_paydate = $idpayperiod['period']['id'];;
    $type = $idpayperiod['period']['type'];;

    $Qry = new Query();	
    $Qry->table         = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblaccountjob as aj ON aj.idacct = a.id ";
    $Qry->selected  = "pr.id,
                        pr.idpaygrp,
                        pr.idbunit,
                        CONCAT(`a`.`lname`,IFNULL(CONCAT(' ',`a`.`suffix`),''),', ',`a`.`fname`,' ',SUBSTR(`a`.`mname`,1,1),'. ') AS `empname`,
                        a.empid AS empid,
                        gross_amount,
                        SUM(whrs_amount
                        - ut_amount
                        - late_amount
                        - abs_amount
                        + tc_vl_amount
                     
                        + tc_cl_amount
                        + tc_mc_amount
                      
                        + tc_pl_amount
                        + tc_el_amount
                        + tc_bl_amount
                        + tc_obt_amount
                        + tc_bdl_amount
                        + tc_spl_amount
                        - tc_lwop_amount ) AS netbasic,
                        ee,
                        m_ee,
                        p_ee,
                        ph_ee,
                        w_tax,
                        IFNULL((SELECT SUM(amount) AS total FROM `tblpayregadditional` WHERE TYPE = 0 AND payregid = pr.id AND transactionid NOT IN(SELECT transid FROM `tblloantype`)),0) AS othrdeduction,
                        IFNULL((SELECT SUM(amount) AS total FROM `tblpayregadditional` WHERE payregid = pr.id AND transactionid IN(SELECT transid FROM `tblloantype`)),0) AS loans,
                        total_ded,
                        net_amount";
    $Qry->fields     = "pr.idpaygrp = '".$param['paygrp']."' AND pr.idpayperiod = '" . $id_paydate . "' " .$search . "GROUP BY pr.id ";
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


function getFPPeriod($con, $param){
    if($param['paytype']== 'Local Employee'){
        $type = 'ho';
    }
    if($param['paytype'] == 'Helper'){
        $type = 'helper';
    }
    if($param['paytype']== 'Japanese'){
        $type= 'hajap';
    }
    if($param['paytype'] == 'Japanese Conversion'){
        $type = 'hajapc';
    }


    $data = array();	
    $Qry = new Query();	
    $Qry->table     = "vw_payperiod_all";
    $Qry->selected  = "*";
    $Qry->fields   = "pay_date='".$param['period']."' AND type='".$type."'";      
    
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

function getJoblevels($con, $param){
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


session_destroy();
?>