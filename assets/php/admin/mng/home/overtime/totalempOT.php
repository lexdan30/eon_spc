<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$str =  $param->accountid;
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

$dept = getIdUnit($con,$param->accountid);
$ids=0;
//Get Managers Under person
if( !empty( $dept ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$dept);
    if( !empty( $arr["nodechild"] ) ){     
        $ids = join(',', flatten($arr['nodechild']));
    } else {
        $ids = '0';
    }
}

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "GROUP_CONCAT(id) as idacct";
$Qry->fields    = "(idunit IN (".$ids.") OR idsuperior='".$param->accountid."') AND id != '".$param->accountid."'";
$rs 			= $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    $str = mysqli_fetch_assoc($rs)['idacct'];
}

$Qry = new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "id, empname, idunit";
$Qry->fields    = "id IN (".$str.")".$search;
$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        
            $tot_jan_nov =getTotOTHrs($con, $row['id'], $ids, $year."-".str_pad(1,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(12,2,"0",STR_PAD_LEFT)."-01");
            $tot_dec  = getTotOTHrs($con, $row['id'], $ids, $year."-12-01",((int)$year+1)."-01-01");
            $total_units = $tot_jan_nov + $tot_dec;

            $total_amounts =  getTotAmt($con,$row['id'], $ids);

        $data[] = array( 
            "idacct"                => $row['id'],
            "idunit"                => $row['idunit'],
            "empname"               => $row['empname'],
            "total_amount"          => $total_amounts,
            // "total_OTHrs"           => $total_units,
            
            "total_WHrs"            => getScheWH($con, $row['id'],$year),
            "total_OTHrs"           => getCountsOTbyYear($con,  $row['id'], $year),
            "getEmpCostcnter"       => getEmpCostcnter($con, $str),
            "getEmpjobLoc"          => getEmpjobLoc($con, $str)
        );
    }
    $return = json_encode($data);
}else{
    $return = json_encode(array('q'=>$Qry->fields));
}
print $return;
mysqli_close($con);

function getIdUnit($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getIdUnit');
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['idunit'];
    }
    return null;
}

function getTotAmt($con,$idacct,$ids){
    $year = date("Y");
    $data=array();
    $Qry=new Query();
    $Qry->table="tblpayrolltotal";
    $Qry->selected="ytd";
    $Qry->fields="id_acct = '".$idacct."' AND dept_id IN (".$ids.") AND ytd IS NOT NULL AND ytd <> ' ' AND pay_yr ='".$year."' AND id_class=18";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTotAmt');
    if( mysqli_num_rows($rs) >= 1 ){
		while($row=mysqli_fetch_assoc($rs)){
            $data[]=array(
                "amount"    => $row['ytd'],
            );
		}
	}
    return  $data;
}


function getTotOTHrs($con,$idacct, $ids, $dfrom, $dto){
    $Qry=new Query();
    $Qry->table="tblpayroll";
    $Qry->selected="SUM(units) AS ctr";
    $Qry->fields="id_acct = '".$idacct."' AND dept_id IN (".$ids.") AND units IS NOT NULL AND units <> ' ' AND ( pay_date >= '".$dfrom."' AND pay_date < '".$dto."' ) AND class_id=18";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTotOTHrs');
    if( mysqli_num_rows($rs) >= 1 ){
        return mysqli_fetch_assoc($rs)['ctr'];
	}
    return  0;
}

function getCountsOTbyYear($con, $idacct, $year){ 
	$Qry 			= new Query();	
	$Qry->table     = "vw_overtime_approval";
	$Qry->selected  = "SUM(IF(approve_hr = 'PLANNED HOUR',planned_hrs,act_hrs)) AS tot_units, YEAR(`date`) AS yr,idacct,COUNT(idacct) AS ctr";
    $Qry->fields    = "YEAR(`date`)='".$year."' AND stat=1 AND idacct IN (".$idacct.")";
    //$Qry->fields    = "YEAR(`date`)='2021' AND stat=1 AND idacct IN (".$idacct.")";
	$rs 			= $Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getCountsOTbyYear');
	if( mysqli_num_rows($rs) >= 1 ){
        return mysqli_fetch_assoc($rs)['tot_units'];
	}
	return 0;
}
function getScheWH($con, $idacct,$year){
    $Qry=new Query();
    $Qry->table="vw_mng_timesheetfinal";
    $Qry->selected="SUM(shifthrs) AS shifthrs";
    $Qry->fields    = "YEAR(`work_date`)='".$year."' AND shifthrs IS NOT NULL and shifthrs <> ' ' and shifthrs NOT LIKE '0%' AND tid IN (".$idacct.")";
    //$Qry->fields    = "YEAR(`work_date`)='2021' AND shifthrs IS NOT NULL and shifthrs <> ' ' and shifthrs NOT LIKE '0%' AND tid IN (".$idacct.")";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getScheWH');
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['shifthrs'];
    }
    return 0;
}

function getEmpCostcnter($con, $ids){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="DISTINCT(costcenter)";
    $Qry->fields="id IN (".$ids.")";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getEmpCostcnter');
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
			$data[] = array(
				"costcenter" => $row['costcenter'],

			);

        }
    }
    return $data;
}

function getEmpjobLoc($con, $ids){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="DISTINCT(job_loc)";
    $Qry->fields="id IN (".$ids.")";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getEmpjobLoc');
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
			$data[] = array(
				"job_loc" => $row['job_loc'],

			);

        }
    }
    return $data;
}


?>