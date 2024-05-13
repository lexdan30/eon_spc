<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$pay_period = getPayPeriod($con);
$return = null;	

$search='';
/*
if( !empty( $param['acct'] ) ){ $search= $search." AND empID = '".$param['acct']."' ";}
if( !empty( $param['dfrom'] ) && empty($param['dto']) ){ $search= $search." AND work_date =DATE('".$param['dfrom']."') "; }
if( !empty( $param['dfrom'] ) && !empty($param['dto']) ){ $search=$search." AND work_date BETWEEN DATE('".$param['dfrom']."') AND DATE('".$param['dto']."') "; }
*/
if( !empty($param['search']['value']) ){
	$search= $search." AND CONCAT(a.empname, ' ',a.labor_type, ' ', a.pay_date, ' ', a.empid) like '%".$param['search']['value']."%' ";
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
$Qry->table     = "vw_payregister AS a";
$Qry->selected  = "*";
$Qry->fields    = "id_acct>0 ".$search;
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
			'id_acct'		=> $row['id_acct'],
			'pay_date'  	=> $row['pay_date'],
			'labor_type' 	=> $row['labor_type'],
			'empid'			=> $row['empid'],
            'empname'   	=> $row['empname'],
			'basic'			=> number_format($row['basic'],2),
			'und'			=> number_format($row['und'],2),
            'ot'			=> number_format($row['ot'],2),
			'np'			=> number_format($row['np'],2),
			'lvs'			=> number_format($row['lvs'],2),
			'gross'			=> number_format($row['gross'],	2),		
			'sss'			=> number_format($row['sss'],2),
			'ibig'			=> number_format($row['ibig'],2),
			'hlth'			=> number_format($row['hlth'],2),
			'withtax'		=> number_format($row['withtax'],2),
			'netpay'		=> number_format($row['netpay'],2)
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
	$Qry->table     = "vw_payregister as a";
	$Qry->selected ="a.id_acct";
	$Qry->fields    = "id_acct>0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>