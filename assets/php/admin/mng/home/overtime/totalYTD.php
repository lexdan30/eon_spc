<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$year = date("Y");
$month = 1;

$str =  $param->accountid;

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


// $Qry = new Query();	
// $Qry->table     = "tblpayroll";
// $Qry->selected  = "SUM(units) AS tot_units, YEAR(pay_date) AS yr,id_acct";
// $Qry->fields    = "YEAR(pay_date)='".$year."' AND class_id=18 AND dept_id IN (".$ids.")";
// $rs = $Qry->exe_SELECT($con);

// while($row=mysqli_fetch_array($rs)){

//     $data[] = array( 
//         "tot_units"         => $row['tot_units'],
//         "yr"                => $row['yr'],
//         "getTotAmt"        => getTotAmt($con, $row['id_acct'], $ids)

//     );
// }

$Qry = new Query();	
$Qry->table     = "vw_overtime_approval";
$Qry->selected  = "SUM(IF(approve_hr = 'PLANNED HOUR',planned_hrs,act_hrs)) AS tot_units, YEAR(`date`) AS yr,idacct,COUNT(idacct) AS ctr";
$Qry->fields    = "YEAR(`date`)='".$year."' AND stat=1 AND idacct IN (".$str.")";
$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
while($row=mysqli_fetch_assoc($rs)){

    $data[] = array( 
        "tot_units"         => $row['tot_units'],
        "yr"                => $row['yr'],
        "count"                => $row['ctr'],
        "getTotAmt"        => getTotAmt($con, $row['idacct'], $ids)

    );
}


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


function getTotAmt($con, $idacct,$ids){
    $data=array();
    $year = date("Y");
    $Qry=new Query();
    $Qry->table="tblpayrolltotal";
    $Qry->selected="IFNULL(SUM(ytd),0) as ytd";
    $Qry->fields="ytd IS NOT NULL AND pay_yr ='".$year."'AND id_class=18 AND dept_id IN (".$ids.")";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTotAmt');
    if( mysqli_num_rows($rs) >= 1 ){
        while($row=mysqli_fetch_assoc($rs)){
            
            $data[] =array(
                "amount"    => $row['ytd'],
            );
        }
    }
    return  $data;
}

?>