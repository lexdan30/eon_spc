<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$paydates = getallPayperiod($con,$param);

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

$peushare = getUnionPref($con,$unionpref = 'PEU');
$mpscshare = getUnionPref($con,$mpscpref = 'MPSC');
$peushare_hrs = getUnionHrsPref($con,$unionpref_hrs = 'PEU_HRS');
$mpscshare_hrs = getUnionHrsPref($con,$mpscpref_hrs = 'MPSC_HRS');

// whrs_amount - (late_amount + ut_amount + abs_amount) getting hours
// BASIC PAY - (LATE + UNDERTIME + ABSENCES)
// how to get backpay?

foreach($paydates as $key=>$value){
    array_push( $arr_id, $value['id']);
}

if(!empty($param->info->objPayBack)){
    //print_r($param->info->objPayBack);
    foreach($param->info->objPayBack as $key=>$value){
        $totbpays = $totbpays + $value->amount;
    } 
}
	
$ids = implode(",",$arr_id); 

$where = "(p.idpayperiod IN (".$ids."))  GROUP by a.id ORDER BY a.empname";
$searchall = "(p.idpayperiod IN (".$ids."))"; 

$totwrhs = gettotalwhrs($con,$searchall);

$totushare = 0;
$peu_whr = $peushare_hrs * $peushare;
$mpsc_whr = $mpscshare_hrs * $mpscshare;
$totalunion_shares = $peu_whr + $mpsc_whr;
$total = $totwrhs  + $totbpays + $totalunion_shares;
$scrate = $param->info->amountmonth / $total;
// $scrate = sprintf('%0.2f', floatval($sc));
// $scrate = cutNum($sc, 2);

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees AS a LEFT JOIN tblpayreg AS p ON a.id = p.idacct";
$Qry->selected  = "a.id,a.empname,(whrs - SUM(late+ut+`abs`)) AS totwhrs"; //IF(pa.transactiontype = 'UD',pa.amount,0.00) AS uniondues
$Qry->fields    = "(p.idpayperiod IN (".$ids."))  GROUP by a.id ORDER BY a.empname LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize; //"; //a.sc_stat='1' AND 
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($param->pagination->currentPage>1){
        $counter = ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
    }
	while($row=mysqli_fetch_array($rs)){
        $counter++;
        $empbpays = '';
        if(!empty($param->info->objPayBack)){
            foreach($param->info->objPayBack as $key=>$value){
                if($value->id == $row['id']){
                    $empbpays = $value->amount;
                }
            }
        }

        //$overallworkhrs = $overallworkhrs + $row['totwhrs'];
        $data[] = array( 
            'counter'       => $counter,
            "id" 		    => (($row['id'])),
            "name" 		    => (($row['empname'])),
            "totwhrs" 		=> (($row['totwhrs'])),
            "empbpays" 	    => $empbpays,//(($row['uniondues'])),
            "scrate" 	    => $scrate,
            "empsc" 	    => round($scrate * $row['totwhrs'],2)
        );		
	}
}

$myData = array('status' => 'success', 'result' => $data,'query' =>  $Qry->fields,
 'totalItems' => getTotal($con , $where),'totalwhrs' => $totwrhs,'totbpays' => $totbpays,
 'totushare' => $totushare,'total' => $total,'scrate' => $scrate,
 'docnum' => 'SC'. strtotime($datesys .' '. $timesys ),
 'peu_whr' => $peu_whr,
 'peu_share' => $peushare,
 'peu_sc' => round($peu_whr * $scrate,2),
 'mpsc_whr' => $mpsc_whr,
 'mpsc_share' => $mpscshare,
 'mpsc_sc' => round($mpsc_whr * $scrate,2),
 'totalunion_shares' => $totalunion_shares,
 'scrate' => $scrate,
 'gettotalemp_sc' => getTotalEmpDetails($con,$searchall,$scrate),
 'overallsc' => $param->info->amountmonth);

$return = json_encode($myData);
print $return;
mysqli_close($con);

function cutNum($num, $precision = 2) {
    return floor($num) . substr(str_replace(floor($num), '', $num), 0, $precision + 1);
}

// function getallPayperiod($con,$param){
//     $data = array();
//     $time = strtotime($param->info->month);
//     $month = date('Y-m-d',$time);
    
//     $Qry = new Query();	
//     $Qry->table     = "vw_payperiod_all";
//     $Qry->selected  = "id,`type`";
//     $Qry->fields = "payprocess = 1 AND YEAR(pay_date) = YEAR('" . $month . "') AND MONTH(pay_date) between MONTH('" . $month . "') AND MONTH('" . $month . "')";
//     $rs = $Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>= 1){
//         while($row=mysqli_fetch_array($rs)){
//             $data[] = array(
//                 "id" 	            => $row['id'],
//                 "type" 	            => $row['type']
//             );
//         }
//     }

//     return $data;
// }

function getallPayperiod($con,$param){
    $data = array();
    $time = strtotime($param->info->month);
    $date = date('Y-m-d',$time);
    
    $Qry = new Query();	
    $Qry->table     = "vw_payperiod_all";
    $Qry->selected  = "id,`type`";
    $Qry->fields = "payprocess = 1 AND (pay_date) = '" . $date . "'";
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

function getTotal($con,$search){
	$Qry = new Query();	
	$Qry->table ="vw_dataemployees AS a LEFT JOIN tblpayreg AS p ON a.id = p.idacct LEFT JOIN tblpayregadditional AS pa ON p.id = pa.payregid";
	$Qry->selected ="a.empname,SUM(whrs) AS totwhrs,IF(pa.transactiontype = 'UD',pa.amount,0.00) AS uniondues";
	$Qry->fields = $search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

function getTotalEmpDetails($con,$search,$scrate){
    $gettotalemp_sc = 0;
	$Qry = new Query();	
	$Qry->table ="vw_dataemployees AS a LEFT JOIN tblpayreg AS p ON a.id = p.idacct LEFT JOIN tblpayregadditional AS pa ON p.id = pa.payregid";
	$Qry->selected ="a.empname,SUM(whrs) AS totwhrs,IF(pa.transactiontype = 'UD',pa.amount,0.00) AS uniondues";
	$Qry->fields = $search;
	$rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $gettotalemp_sc = $gettotalemp_sc + ($scrate * $row['totwhrs']);
        }
    }
    return $gettotalemp_sc;
}

function gettotalwhrs($con,$search){
    $overallworkhrs = 0;
	$Qry = new Query();	
	$Qry->table ="vw_dataemployees AS a LEFT JOIN tblpayreg AS p ON a.id = p.idacct LEFT JOIN tblpayregadditional AS pa ON p.id = pa.payregid";
	$Qry->selected ="SUM(whrs) AS totwhrs";
	$Qry->fields = $search;
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $overallworkhrs = $row['totwhrs'];	
        }
    }
    return $overallworkhrs;
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
?>