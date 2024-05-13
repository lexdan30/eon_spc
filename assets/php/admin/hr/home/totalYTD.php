<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
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



// $Qry = new Query();	
// $Qry->table     = "tblpayroll";
// $Qry->selected  = "SUM(units) AS tot_units, YEAR(pay_date) AS yr,id_acct";
// $Qry->fields    = "YEAR(pay_date)='".$year."' AND class_id=18";

// $rs = $Qry->exe_SELECT($con);

// while($row=mysqli_fetch_array($rs)){

//     $data[] = array( 
//         "tot_units"         => $row['tot_units'],
//         "yr"                => $row['yr'],
//         "getTotAmt"        => getTotAmt($con, $row['id_acct'])

//     );
// }
$Qry = new Query();	
$Qry->table     = "vw_overtime_approval";
$Qry->selected  = "SUM(IF(approve_hr = 'PLANNED HOUR',planned_hrs,act_hrs)) AS tot_units, YEAR(`date`) AS yr,idacct,COUNT(idacct) AS ctr";
$Qry->fields    = "YEAR(`date`)='".$year."' AND stat=1";
$rs = $Qry->exe_SELECT($con);

while($row=mysqli_fetch_array($rs)){
	if(empty($row['tot_units'])){
		$hrs = 0;
	}else{
		$hrs = $row['tot_units'];
	}
    $data[] = array( 
        "tot_units"         => $hrs,
        "yr"                => $row['yr'],
        "count"                => $row['ctr'],
        "getTotAmt"        => getTotAmt($con, $row['idacct'])

    );
}


$return = json_encode($data);

print $return;
mysqli_close($con);


function getTotAmt($con, $idacct){
	if( !empty( $idacct ) ){
		$year = date("Y");
		$Qry=new Query();
		$Qry->table="tblpayrolltotal";
		$Qry->selected="*";
		$Qry->fields="id_acct IN (".$idacct.") AND ytd IS NOT NULL AND ytd <> ' ' AND pay_yr ='".$year."'AND id_class=18";
		$rs=$Qry->exe_SELECT($con);
		if( mysqli_num_rows($rs) >= 1 ){
			while($row=mysqli_fetch_array($rs)){
				
				$data[]=array(
					"amount"    => $row['ytd'],
				);
			}
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