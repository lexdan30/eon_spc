<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$search ='';

if( !empty( $param->deppt ) ){ $search=$search." AND idunit = '".$param->deppt."' "; }
if( !empty( $param->costcenter ) ){ $search=$search." AND costcenter = '".$param->costcenter."' "; }
if( !empty( $param->jobloc ) ){ $search=$search." AND idloc = '".$param->jobloc."' "; }


$year = date("Y");
$month = 1;
$months = array(
	'January',
	'February',
	'March',
	'April',
	'May',
	'June',
	'July ', 
	'August',
	'September',
	'October',
	'November',
	'December',
);


$Qry = new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "*";
$Qry->fields    = "id>0".$search;
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        
            $tot_jan_nov =getTotOTHrs($con, $row['id'],$year."-".str_pad(1,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(12,2,"0",STR_PAD_LEFT)."-01");
            $tot_dec  = getTotOTHrs($con, $row['id'],$year."-12-01",((int)$year+1)."-01-01");
            $total_units = $tot_jan_nov + $tot_dec;

            $total_amounts =  getTotAmt($con, $row['id']);

            $othrs = number_format(getCountsOTbyYear($con,  $row['id'], $year), 2, '.', '');

            if( $othrs !=0 &&  $othrs !=null){
                $whrs=getScheWH($con, $row['id'],$year);
            }else{
                $whrs=0;
            }

        $data[] = array( 
            "idacct"                => $row['id'], 
            "idunit"                => $row['idunit'],
            "empname"               => $row['empname'], 
            "total_amount"          => $total_amounts,
            // "total_OTHrs"           => $total_units,
            "total_OTHrs"           => $othrs,
            "total_WHrs"            => $whrs,
            

        );
    }
    $return = json_encode($data);
}else{
    $return = json_encode(array('q'=>$Qry->fields));
}
print $return;
mysqli_close($con);


function getTotAmt($con, $idacct){
    $year = date("Y");
    $data=array();
    $Qry=new Query();
    $Qry->table="tblpayrolltotal";
    $Qry->selected="*";
    $Qry->fields="id_acct IN (".$idacct.") AND ytd IS NOT NULL AND ytd <> ' ' AND pay_yr ='".$year."' AND id_class=18";
    $rs=$Qry->exe_SELECT($con);
    if( mysqli_num_rows($rs) >= 1 ){
		while($row=mysqli_fetch_array($rs)){
            
            $data[]=array(
                "amount"    => $row['ytd'],
            );
		}
	}
    return  $data;
}


function getTotOTHrs($con, $idacct, $dfrom, $dto){
    $Qry=new Query();
    $Qry->table="tblpayroll";
    $Qry->selected="SUM(units) AS ctr";
    $Qry->fields="id_acct IN (".$idacct.") AND units IS NOT NULL AND units <> ' ' AND ( pay_date >= '".$dfrom."' AND pay_date < '".$dto."' ) AND class_id=18";
    $rs=$Qry->exe_SELECT($con);
    if( mysqli_num_rows($rs) >= 1 ){
		while($row=mysqli_fetch_array($rs)){ 
			return $row['ctr'];
		}
	}
    return  0;
}

function getCountsOTbyYear($con, $idacct, $year){
	$Qry 			= new Query();	
	$Qry->table     = "vw_overtime_approval";
	$Qry->selected  = "SUM(IF(approve_hr = 'PLANNED HOUR',planned_hrs,act_hrs)) AS tot_units, YEAR(`date`) AS yr,idacct,COUNT(idacct) AS ctr";
	$Qry->fields    = "YEAR(`date`)='".$year."' AND stat=1 AND idacct IN (".$idacct.")";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['tot_units'];
		}
	}
	return 0;
}
function getScheWH($con, $idacct,$year){
    $Qry=new Query();
    $Qry->table="vw_timesheetfinal";
    $Qry->selected="SUM(shifthrs) AS shifthrs";
    $Qry->fields    = "YEAR(`work_date`)='".$year."' AND shifthrs IS NOT NULL and shifthrs <> ' ' and shifthrs NOT LIKE '0%' AND tid IN (".$idacct.")";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['shifthrs'];
        }
    }
    return 0;
}


?>