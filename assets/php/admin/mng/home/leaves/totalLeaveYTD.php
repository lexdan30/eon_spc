<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$year = date("Y");
$month = 1;
$data = array();
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

// $Qry = new Query();	 
// $Qry->table     = "tblpayroll";
// $Qry->selected  = "SUM(units) AS tot_units, YEAR(pay_date) AS yr,id_acct,dept_id";
// $Qry->fields    = "YEAR(pay_date)='".$year."' AND class_id=19 AND dept_id IN (".$ids.")";

// $rs = $Qry->exe_SELECT($con);

// while($row=mysqli_fetch_array($rs)){

//     $data[] = array( 
//         "tot_units"         => $row['tot_units'],
//         "yr"                => $row['yr'],
//         "getTotAmt"         => getTotAmt($con, $ids),
//         // "getEmpInfo"        => getEmpInfo($con, $row['id_acct'])

        

//     );
// }
$Qry = new Query();	
$Qry->table     = "tbltimeleaves";
$Qry->selected  = "SUM(hrs) AS lvhrs, YEAR(`date`) AS yr,COUNT(idacct) AS ctr";
$Qry->fields    = "YEAR(`date`)='".$year."'  and stat = 1 AND idacct IN (".$str.")";

$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
while($row=mysqli_fetch_assoc($rs)){
    if(empty($row['lvhrs'])){
        $hrs = 0;
    }else{
        $hrs = $row['lvhrs'];
    }
    $data[] = array( 
        "tot_units"         => $hrs,
        "yr"                => $row['yr'],
        "getTotAmt"         => $row['ctr']
        // "getTotAmt"         => getTotAmt($con, $str)
        // "getEmpInfo"        => getEmpInfo($con, $row['id_acct'])
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


// function getEmpInfo($con, $idacct){
//     $Qry=new Query();
//     $Qry->table="vw_dataemployees";
//     $Qry->selected="*";
//     $Qry->fields="id='".$idacct."'";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
			
// 			$data[] = array(
// 				"costcenter" => $row['costcenter'],
//                 "job_loc" 	 => $row['job_loc'],
//                 "departments" 	 => $row['business_unit'],


// 			);

//         }
//     }
//     return $data;
// }

//Total YTD Leave Amount
function getTotAmt($con, $ids){
	if( !empty( $ids ) ){
		$year = date("Y");
		$Qry=new Query();
		$Qry->table="tblpayrolltotal";
        $Qry->selected="SUM(ytd) as ytd";
		$Qry->fields="dept_id IN (".$ids.") AND ytd IS NOT NULL AND ytd <> ' ' AND pay_yr ='".$year."'AND id_class=19";
		$rs=$Qry->exe_SELECT($con);
        Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getToAmt');
		if( mysqli_num_rows($rs) >= 1 ){
				$data[]=array(
					"amount"    => mysqli_fetch_assoc($rs)['ytd'],
				);
			
		}else{
			$data[]=array(
				"amount"    => "0.00"
			);
		}
	}else{
		$data[]=array(
			"amount"    => "0.00"
		);
	}
    return  $data;
}


?>