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

if( !empty($param['search']['value']) ){
	$search= $search." AND CONCAT(b.empid, ' ',b.empname, ' ', a.work_date, ' ', a.time_in,' ', a.time_out) like '%".$param['search']['value']."%' ";
}

$where = $search;

$search=$search." ORDER BY a.work_date ASC ";
if( $param['length'] !='' ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "vw_datatimelogs AS a INNER JOIN vw_dataemployees AS b ON a.acct_id = b.id";
$Qry->selected  = "b.empid, b.empname AS emp ,a.work_date, a.time_in, a.time_out, CONCAT(b.empid, ' ',b.empname, ' ', a.work_date, ' ', a.time_in,' ', a.time_out) AS srch";
$Qry->fields    = "a.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' ".$search;
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
			'empid'		=> $row['empid'],
            'emp'   	=> $row['emp'],
            'work_date' => $row['work_date'],
            'time_in'	=> ( $row['time_in'] ? date("h:i:s a",strtotime($row['time_in'])) : '' ),
			'time_out'	=> ( $row['time_out'] ? date("h:i:s a",strtotime($row['time_out'])) : '' )
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
	$Qry->table     = "vw_datatimelogs AS a INNER JOIN vw_dataemployees AS b ON a.acct_id = b.id";
	$Qry->selected ="b.empid, b.empname AS emp ,a.work_date, a.time_in, a.time_out, CONCAT(b.empid, ' ',b.empname, ' ', a.work_date, ' ', a.time_in,' ', a.time_out) AS srch";
	$Qry->fields    = "a.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' ".$search;		
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>