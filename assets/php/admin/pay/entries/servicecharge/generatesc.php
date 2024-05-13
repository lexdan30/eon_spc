<?php 
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
if(!empty($param->accountid)){
$paydates = getallPayperiod($con,$param);

$payperiodid = array(  
    "period"		=> getPayPeriod($con),
);


$date =  $payperiodid['period']['pay_start'];
$date1 =  $payperiodid['period']['pay_end'];


$data  = array();
if(empty($param->info->amountmonth)){
    $return = json_encode(array('status'=>'cleared'));
    print $return; 
    mysqli_close($con);
    return;
}
$datesys  = SysDate();
$timesys  = SysTime();
$arr_id = array();
$counter = 0;
$totbpays = 0;
$leaves = "tc_sl + tc_vl + tc_spl + tc_pl + tc_slh + tc_ul + tc_il + tc_el + tc_mc + tc_bl + tc_ml + tc_s + tc_psl + tc_vvawc + tc_bdl + tc_cl";
$adj_leaves = "adj_sl + adj_vl + adj_spl+ adj_pl + adj_slh + adj_ul + adj_il + adj_el + adj_mc + adj_bl + adj_ml + adj_s + adj_psl + adj_vvawc + adj_bdl + adj_cl"; 

$peushare = getUnionPref($con,$unionpref = 'PEU');
$mpscshare = getUnionPref($con,$mpscpref = 'MPSC');
$peushare_hrs = getUnionHrsPref($con,$unionpref_hrs = 'PEU_HRS');
$mpscshare_hrs = getUnionHrsPref($con,$mpscpref_hrs = 'MPSC_HRS');

foreach($paydates as $key=>$value){
    array_push( $arr_id, $value['id']);
}
	
$ids = implode(",",$arr_id); 

$prev_scrate = getPrevSCRate($con,$ids);

$where = " AND (p.idpayperiod IN (".$ids."))  GROUP by a.id ORDER BY a.empname";
$searchall = " AND (p.idpayperiod IN (".$ids."))"; 

$totwrhs = gettotalwhrs($con,$searchall,$date,$date1);
$totwrhs_adj = gettotalwhrs_adj($con,$searchall);

$totushare = 0;
$peu_whr = $peushare_hrs * $peushare;
$mpsc_whr = $mpscshare_hrs * $mpscshare;
$totalunion_shares = $peu_whr + $mpsc_whr;

$totbp_amount = $totwrhs_adj * $prev_scrate;
$netsc = $param->info->amountmonth - $totbp_amount;

$total = $totwrhs + $totalunion_shares;
$scrate = $netsc / $total;

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees AS a LEFT JOIN tbltimesheetsummary AS p ON a.id = p.idacct"; 
$Qry->selected  = "a.id,a.empid,a.empname,a.lname,a.fname,a.mname,(CASE
WHEN a.hdate BETWEEN '".$date."' AND '".$date1."' THEN IF(whrs>0,whrs,0) 
ELSE IF(whrs>0,IF((104 - SUM(late+ut+`abs`))<0,0,(104 - SUM(late+ut+`abs`))),0) 
END)  AS totwhrs,(adj_absent - SUM(adj_late + adj_ut)) + (".$adj_leaves.") AS adj_totwhrs"; //IF(pa.transactiontype = 'UD',pa.amount,0.00) AS uniondues //+ (".$leaves.")
$Qry->fields    = "(p.idpayperiod IN (".$ids.")) AND a.sc_stat='1' AND a.id NOT IN(83,105,915) GROUP by a.id ORDER BY a.lname LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize; //"; //a.sc_stat='1' AND 
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($param->pagination->currentPage>1){
        $counter = ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
    }
	while($row=mysqli_fetch_array($rs)){
        $counter++;
        $empbpays = '';
        $data[] = array( 
            'counter'       => $counter,
            "id" 		    => (($row['id'])),
            "empid" 		=> (($row['empid'])),
            "name" 		    => (($row['empname'])),
            "lname" 		=> (($row['lname'])),
            "fname" 		=> (($row['fname'])),
            "mname" 		=> (($row['mname'])),
            "totwhrs" 		=> (($row['totwhrs'])),
            "adj_totwhrs" 	=> (($row['totwhrs'])),
            "empbpays" 	    => $row['adj_totwhrs'], 
            "prevsc_rate" 	=> $prev_scrate,
            "total_empbpay" => $row['adj_totwhrs'] * $prev_scrate,
            "scrate" 	    => $scrate,
            "empsc" 	    => $scrate * $row['totwhrs'],
            "totempsc" 	    => ($row['adj_totwhrs'] * $prev_scrate) + ($scrate * $row['totwhrs'])
        );		
	}
}

$myData = array('status' => 'success', 'result' => $data,'query' =>  $Qry->fields,
 'totalItems' => getTotal($con , $where, $leaves, $date,$date1 ),'totalwhrs' => $totwrhs,'totbpays' => $totwrhs_adj, 'prev_scrate' => $prev_scrate,
 'totbp_amount' => $totbp_amount, 'basedsc' => $param->info->amountmonth,'netsc' => $netsc, 'totushare' => $totushare,'total' => $total,'scrate' => $scrate,
 'docnum' => 'SC'. strtotime($datesys .' '. $timesys ),
 'peu_whr' => $peu_whr,
 'peu_share' => $peushare,
 'peu_sc' => round($peu_whr * $scrate,2),
 'mpsc_whr' => $mpsc_whr,
 'mpsc_share' => $mpscshare,
 'mpsc_sc' => round($mpsc_whr * $scrate,2),
 'totalunion_shares' => $totalunion_shares,
 'scrate' => $scrate,
 'gettotalemp_sc' => getTotalEmpDetails($con,$searchall,$scrate,$leaves,$date,$date1),
 'overallsc' => $param->info->amountmonth);

$return = json_encode($myData);
print $return;
 mysqli_close($con);
}else{
	mysqli_close($con);
	header("Location: https://peninsula.mydhris.com/mph/");  // unAuth back to login page
}
function cutNum($num, $precision = 2) {
    return floor($num) . substr(str_replace(floor($num), '', $num), 0, $precision + 1);
}

function getallPayperiod($con,$param){
    $data = array();
    $time = strtotime($param->info->month);
    $date = date('Y-m-d',$time);
    
    $Qry = new Query();	
    $Qry->table     = "vw_payperiod_all";
    $Qry->selected  = "id,`type`";
    $Qry->fields = "tkprocess = 1 AND (pay_date) = '" . $date . "'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $data[] = array(
                "id" 	            => $row['id'],
                "type" 	            => $row['type']
            );
        }
    }

    return $data;
}

function getTotal($con,$search,$leaves,$date,$date1){
	$Qry = new Query();	
	$Qry->table ="vw_dataemployees AS a LEFT JOIN tbltimesheetsummary AS p ON a.id = p.idacct";
	$Qry->selected ="a.empname,(CASE
                        WHEN a.hdate BETWEEN '".$date."' AND '".$date1."' THEN IF(whrs>0,whrs,0) 
                        ELSE IF(whrs>0,IF((104 - SUM(late+ut+`abs`))<0,0,(104 - SUM(late+ut+`abs`))),0) 
                    END) AS totwhrs"; // + (".$leaves.")
	$Qry->fields = "a.sc_stat='1'  AND a.id NOT IN(83,105,915) AND (104 - (late+ut+`abs`)) > 0 ". $search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

function getTotalEmpDetails($con,$search,$scrate,$leaves,$date,$date1){
    $gettotalemp_sc = 0;
	$Qry = new Query();	
	$Qry->table ="vw_dataemployees AS a LEFT JOIN tbltimesheetsummary AS p ON a.id = p.idacct";
	$Qry->selected ="a.empname,(CASE
            WHEN a.hdate BETWEEN '".$date."' AND '".$date1."' THEN IF(whrs>0,whrs,0) 
            ELSE IF(whrs>0,IF((104 - SUM(late+ut+`abs`))<0,0,(104 - SUM(late+ut+`abs`))),0) 
        END) AS totwhrs"; //+ (".$leaves.")
	$Qry->fields =  "a.sc_stat='1'  AND a.id NOT IN(83,105,915) ". $search;
	$rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $gettotalemp_sc = $gettotalemp_sc + ($scrate * $row['totwhrs']);
        }
    }
    return $gettotalemp_sc;
}

function gettotalwhrs($con,$search,$date,$date1){
    $leaves = "SUM(tc_sl) + SUM(tc_vl) + SUM(tc_spl) + SUM(tc_pl) + SUM(tc_slh) + SUM(tc_ul) + SUM(tc_il) + SUM(tc_el) + SUM(tc_mc) + SUM(tc_bl) + SUM(tc_ml) + SUM(tc_s) + SUM(tc_psl) + SUM(tc_vvawc) + SUM(tc_bdl) + SUM(tc_cl)";
    $overallworkhrs = 0;
	$Qry = new Query();	
	$Qry->table ="vw_dataemployees AS a LEFT JOIN tbltimesheetsummary AS p ON a.id = p.idacct";
	// $Qry->selected ="(SUM((CASE
	// WHEN a.hdate BETWEEN '".$date."' AND '".$date1."' THEN whrs
    //     ELSE 104
    // END)) - (SUM(late)+SUM(ut)+SUM(`abs`))) AS totwhrs"; // + (".$leaves.")
    $Qry->selected ="SUM((CASE
    WHEN a.hdate BETWEEN '".$date."' AND '".$date1."' THEN IF(p.whrs>0,p.whrs,0) 
    ELSE IF(p.whrs>0,IF((104 - (p.late+p.ut+p.`abs`))<0,0,(104 - (p.late+p.ut+p.`abs`))),0) 
    END)) AS totwhrs"; // + (".$leaves.")
	$Qry->fields =  "a.sc_stat='1'  AND a.id NOT IN(83,105,915) AND whrs > 0 ". $search;
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $overallworkhrs = $row['totwhrs'];	
        }
    }
    return $overallworkhrs;
}

function gettotalwhrs_adj($con,$search){
    $adj_leaves = "SUM(adj_sl) + SUM(adj_vl) + SUM(adj_spl) + SUM(adj_pl) + SUM(adj_slh) + SUM(adj_ul) + SUM(adj_il) + SUM(adj_el) + SUM(adj_mc) + SUM(adj_bl) + SUM(adj_ml) + SUM(adj_s) + SUM(adj_psl) + SUM(adj_vvawc) + SUM(adj_bdl) + SUM(adj_cl)"; 
    $overallworkhrs_adj = 0;
	$Qry = new Query();	
	$Qry->table ="vw_dataemployees AS a LEFT JOIN tbltimesheetsummary AS p ON a.id = p.idacct";
	$Qry->selected ="(SUM(adj_absent) - (SUM(adj_late) + SUM(adj_ut))) + (".$adj_leaves.") AS adj_totwhrs";
	$Qry->fields =  "a.sc_stat='1'  AND a.id NOT IN(83,105,915) ". $search;
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $overallworkhrs_adj = $row['adj_totwhrs'];	
        }
    }
    return $overallworkhrs_adj;
}

function getUnionPref($con,$alias){
    $share = 0;
	$Qry = new Query();	
	$Qry->table ="tblpreference";
	$Qry->selected ="`value`";
	$Qry->fields = "alias = '".$alias."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $share = $row['value'];	
        }
    }
    return $share;
}
function getUnionHrsPref($con,$alias){
    $hrs = 0;
	$Qry = new Query();	
	$Qry->table ="tblpreference";
	$Qry->selected ="`value`";
	$Qry->fields = "alias = '".$alias."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $hrs = $row['value'];	
        }
    }
    return $hrs;
}

function getPrevSCRate($con,$id){
    $id = (int)$id - 1;
    $paydate = '';
    
    $Qry = new Query();	
    $Qry->table     = "vw_payperiod_all";
    $Qry->selected  = "`pay_date`";
    $Qry->fields = "id = '".$id."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $paydate = $row['pay_date'];
        }
    }else{
        if($id > 0){
            getPrevSCRate($con,$id);
        }
    }


    if(!empty($paydate)){
        $Qry2 = new Query();	
        $Qry2->table     = "tblservicecharge";
        $Qry2->selected  = "hourlyrate";
        $Qry2->fields = "releasedate = '".$paydate."'";
        $rs2 = $Qry2->exe_SELECT($con);
        if(mysqli_num_rows($rs2)>= 1){
            if($row2=mysqli_fetch_array($rs2)){
                return $row2['hourlyrate'];
            }
        }else{
            return 0.00;
        }
    }else{
        return 0.00;
    }
}
?>