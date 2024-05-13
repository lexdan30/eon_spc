<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$return = null;	
$pay_period = getPayPeriod($con);
$search='';
/*
if( !empty( $param['name'] ) ){ $search=" AND name like   '%".$param['name']."%' "; }
if( !empty( $param['alias'] ) ){ $search=" AND alias like   '%".$param['alias']."%' "; }
*/
$where = $search;

if( $param['length'] !='' ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "vw_dataloglink";
$Qry->selected  = "*";
$Qry->fields    = "(work_date between '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' ) ".$search;
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
            'empid'    	=> $row['empid'],
            'empname'  	=> $row['empname'],
			'unit'		=> $row['classification'],
			'shift'		=> $row['shift'],
			'work_date'	=> $row['work_date'],
			'date_in1'	=> $row['date_in'],
			'time_in1'	=> $row['time_in'],
			'date_out1'	=> $row['date_out'],
			'time_out1'	=> $row['time_out'],
			'date_in2'	=> $row['date_in2'],
			'time_in2'	=> $row['time_in2'],
			'date_out2'	=> $row['date_out2'],
			'time_out2'	=> $row['time_out2']
        );
    }
    $return =  json_encode($data);
}else{
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs),
        "recordsFiltered"=> mysqli_num_rows($rs),
		"qry"=>$Qry->fields,
        "data"=>array()
    );
    $return =  json_encode($data);
}

print $return;
mysqli_close($con);

function getTotalRows($con,$search,$pay_period){
	$Qry = new Query();	
	$Qry->table ="vw_dataloglink";
	$Qry->selected ="*";
	$Qry->fields    = "(work_date between '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' ) ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>