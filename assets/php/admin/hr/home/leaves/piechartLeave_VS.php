<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$pay_period = getPayPeriod($con);
$year = date("Y");

$pie_data	= array(getHoursLeaves($con, $param->accountid,$year),getScheWH($con, $param->accountid,$year));
$pie_colour = array('#b0beec','#ebab76');
$pie_labels	= array('Leave Hours','Scheduled Work Hours');
    


$data = array(
    "lbl" 	=> $pie_labels,
    "ctr"	=> $pie_data,
    "colour"=> $pie_colour,
    "sum"	=> (int)array_sum($pie_data),
);

$return = json_encode($data);


print $return;
mysqli_close($con);



// function getHoursLeaves($con, $idacct){
//     $pay_period = getPayPeriod($con);
//     $Qry=new Query();
//     $Qry->table="tblpayroll";
//     $Qry->selected="SUM(units) AS ctr";
//     $Qry->fields="class_id=19 AND idstatus=1";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){

//             return intval($row['ctr']);
//         }
//     }
//     return 0;
// }
function getHoursLeaves($con, $idacct,$year){
    $Qry=new Query();
    $Qry->table="tbltimeleaves";
    $Qry->selected="SUM(hrs) AS lvhrs";
    $Qry->fields="YEAR(`date`)='".$year."' AND stat=1";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return intval($row['lvhrs']);
        }
    }
    return 0;
}

// function getScheWH($con, $idacct){
//     $pay_period = getPayPeriod($con);
//     $Qry=new Query();
//     $Qry->table="vw_data_timesheet";
//     $Qry->selected="SUM(absent) as ctr";
//     $Qry->fields="id>0";
//     // $Qry->fields="work_date >= '".$param->dateFrom."' AND work_date <= '".$param->dateTo."'";
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
    $Qry->fields    = "YEAR(`work_date`)='".$year."' AND shifthrs IS NOT NULL and shifthrs <> ' ' and shifthrs NOT LIKE '0%'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return intval($row['shifthrs']);
        }
    }
    return 0;
}


?>