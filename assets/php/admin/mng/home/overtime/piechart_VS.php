<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$pay_period = getPayPeriod($con);

$search='';
if( !empty($param->dateFrom) && !empty($param->dateTo) ){
    $search=$search." AND pay_date BETWEEN DATE('".$param->dateFrom."') AND DATE('".$param->dateTo."') ";
   
}

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

$str =  $param->accountid;

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "GROUP_CONCAT(id) as idacct";
$Qry->fields    = "(idunit IN (".$ids.") OR idsuperior='".$param->accountid."') AND id != '".$param->accountid."'";
$rs 			= $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    $str = mysqli_fetch_assoc($rs)['idacct'];
}

$pie_data	= array(getScheWH($con, $param->accountid,$param,$ids,$str,$param->dateFrom,$param->dateTo),getActHours($con, $param->accountid,$param,$ids,$str,$param->dateFrom,$param->dateTo));
$pie_colour = array('#b0beec','#ebab76');
$pie_labels	= array('Scheduled Work Hours','Actual Duty Hours');
    
$data = array(
    "lbl" 	=> $pie_labels,
    "ctr"	=> $pie_data,
    "colour"=> $pie_colour,
    "sum"	=> (int)array_sum($pie_data),
    // "href" 	     => $pie_href
);

$return = json_encode($data);

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

// function getHoursOT($con, $idacct,$param,$ids){
//     $Qry=new Query();
//     $Qry->table="tblpayroll";
//     $Qry->selected="SUM(units) AS ctr";
//     $Qry->fields="class_id=18 AND (pay_date >= '".$param->dateFrom."' AND pay_date <= '".$param->dateTo."') AND idstatus=1 AND dept_id IN (".$ids.")";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){

//             return intval($row['ctr']);
//         }
//     }
//     return 0;
// }


function getActHours($con, $idacct,$param,$ids,$str,$dFrom,$dTo){
    $Qry=new Query();
    $Qry->table="vw_mng_timesheetfinal";
    $Qry->selected="SUM(acthrs) AS acthrs";
    $Qry->fields    = "( work_date >= '".$dFrom."' AND work_date < '".$dTo."' ) AND acthrs IS NOT NULL and acthrs <> ' ' and acthrs NOT LIKE '0%' AND tid IN (".$str.")";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getActHours');
    if(mysqli_num_rows($rs)>=1){
        return intval(mysqli_fetch_assoc($rs)['acthrs']);
    }
    return 0;
}


// function getScheWH($con, $idacct,$param,$ids,$str){
//     $Qry=new Query();
//     $Qry->table="vw_data_timesheet as dt left join vw_dataemployees as de on dt.empID = de.id";
//     $Qry->selected="SUM(excess) as ctr";
//     $Qry->fields="(dt.work_date >= '".$param->dateFrom."' AND dt.work_date <= '".$param->dateTo."') AND de.idunit IN (".$ids.")";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
//             return intval($row['ctr']);
//         }
//     }
//     return 0;
// }

function getScheWH($con, $idacct,$param,$ids,$str,$dFrom,$dTo){
    $Qry=new Query();
    $Qry->table="vw_mng_timesheetfinal";
    $Qry->selected="SUM(shifthrs) AS shifthrs";
    $Qry->fields    = "( work_date >= '".$dFrom."' AND work_date < '".$dTo."' ) AND shifthrs IS NOT NULL and shifthrs <> ' ' and shifthrs NOT LIKE '0%' AND tid IN (".$str.")";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getScheWH');
    if(mysqli_num_rows($rs)>=1){
        return intval(mysqli_fetch_assoc($rs)['shifthrs']);
    }
    return 0;
}




?>