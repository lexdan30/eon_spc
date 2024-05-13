<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$year = date("Y");

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

$pie_data	= array(getHoursLeaves($con, $param->accountid,$ids,$str,$year),getScheWH($con,$str,$year));
$pie_colour = array('#b0beec','#ebab76');
$pie_labels	= array('Leave Hours','Scheduled Work Hours');

$data = array(
    "lbl" 	=> $pie_labels,
    "ctr"	=> $pie_data,
    "colour"=> $pie_colour,
    "ids"   => $str,
    "sum"	=> (int)array_sum($pie_data),
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

// function getHoursLeaves($con, $idacct,$ids){
//     $Qry=new Query();
//     $Qry->table="tblpayroll";
//     $Qry->selected="SUM(units) AS ctr";
//     $Qry->fields="class_id=19 AND idstatus=1 AND dept_id IN (".$ids.")";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){

//             return intval($row['ctr']);
//         }
//     }
//     return 0;
// }

function getHoursLeaves($con, $idacct,$ids,$str,$year){
    $Qry=new Query();
    $Qry->table="tbltimeleaves";
    $Qry->selected="SUM(hrs) AS lvhrs";
    $Qry->fields="YEAR(`date`)='".$year."' AND stat=1 AND idacct IN (".$str.")";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getHoursLeaves');
    if(mysqli_num_rows($rs)>=1){
        return intval(mysqli_fetch_assoc($rs)['lvhrs']);
    }
    return 0;
}

// function getScheWH($con, $idacct,$ids,$str,$year){
//     $Qry=new Query();
//     $Qry->table="vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID = de.id";
//     $Qry->selected="SUM(dt.absent) as ctr";
//     $Qry->fields="de.idunit IN (".$ids.")";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
//             return intval($row['ctr']);
//         }
//     }
//     return 0;
// }

function getScheWH($con, $idacct,$year){
    $Qry=new Query();
    $Qry->table="vw_timesheetfinal";
    $Qry->selected="SUM(shifthrs) AS shifthrs";
    $Qry->fields    = "YEAR(`work_date`)='".$year."' AND shifthrs IS NOT NULL and shifthrs <> ' ' and shifthrs NOT LIKE '0%' AND tid IN (".$idacct.")";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getScheWH');
    if(mysqli_num_rows($rs)>=1){
        return intval(mysqli_fetch_assoc($rs)['shifthrs']);
    }
    return 0;
}


?>