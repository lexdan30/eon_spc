<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$pay_period = getPayPeriod($con);
$return = null;	

$search='';
/*
if( !empty( $param['acct'] ) ){ $search= $search." AND empID = '".$param['acct']."' ";}
if( !empty( $param['dfrom'] ) && empty($param['dto']) ){ $search= $search." AND work_date =DATE('".$param['dfrom']."') "; }
if( !empty( $param['dfrom'] ) && !empty($param['dto']) ){ $search=$search." AND work_date BETWEEN DATE('".$param['dfrom']."') AND DATE('".$param['dto']."') "; }
*/
if( !empty($param['id_acct']) ){ $search= $search." AND a.id_acct = '".$param['id_acct']."' AND a.idstatus='2' "; }
if( !empty($param['search']['value']) ){
	$search= $search." AND CONCAT(a.empid, ' ',a.empname, ' ', a.pay_date) like '%".$param['search']['value']."%' ";
}

$where = $search;

$search=$search." ORDER BY a.pay_date DESC ";
if( $param['length'] !='' ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "vw_netpaydata AS a";
$Qry->selected  = "*";
$Qry->fields    = "id>0 ".$search;
$rs = $Qry->exe_SELECT($con);
$recFiltered = getTotalRows($con,$where,$pay_period);
if(mysqli_num_rows($rs)>= 1){
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs),
        "recordsFiltered"=> $recFiltered,
        "qry"=>$Qry->fields,
        "data"=>array()
    );
    while($row=mysqli_fetch_array($rs)){
		
        $data["data"][] = array(
			'id'		=> $row['id'],
			'id_acct'	=> $row['id_acct'],
			'empid'		=> $row['empid'],
            'emp'   	=> $row['empname'],
			'id_paydate'=> $row['id_paydate'],
            'pay_date'  => $row['pay_date'],
			'amt'		=> number_format($row['amt'],2)
        );
    }
    $return =  json_encode($data);
}else{
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs),
        "recordsFiltered"=> mysqli_num_rows($rs),
        "data"=>array(),
		"qry"=>$Qry->fields
    );
    $return =  json_encode($data);
}

print $return;
mysqli_close($con);

function getTotalRows($con,$search,$pay_period){
	$Qry = new Query();	
	$Qry->table     = "vw_netpaydata as a";
	$Qry->selected ="a.id";
	$Qry->fields    = "id>0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>