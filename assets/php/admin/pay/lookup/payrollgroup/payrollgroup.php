<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	

$search='';
if( !empty( $param['description'] ) ){ $search.=" AND (`group` like '%".$param['description']."%' OR `code` like '%".$param['description']."%' OR `period` like '%".$param['description']."%')"; }

if($param['flags'] == "" || $param['flags'] == '1'){
    $search.= "AND flags  =  1";
}else if($param['flags'] == '2'){
    $search.= " ";
}else{
    $search.= "AND flags  =  0";
}

$where = $search;

if( $param['length'] !='' ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "tblpaygrp";
$Qry->selected  = "*";
$Qry->fields    = "id>0 ".$search;
$rs = $Qry->exe_SELECT($con);
$recFiltered = getTotalRows($con,$where);
if(mysqli_num_rows($rs)>= 1){
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs),
        "recordsFiltered"=> $recFiltered,
        "qry"=>$Qry->fields,
        "data"=>array()
    );
    while($row=mysqli_fetch_array($rs)){
		if($row['flags'] == 1){
            $stats = 'ACTIVE';
        }else{
            $stats = 'INACTIVE';
        }
        $data["data"][] = array(
            'id'            => (int)$row['id'],
            'code'          => $row['code'],
            'group'          => $row['group'],
            'period'          => $row['period'],
            'flags'          => $stats
        );
    }
    $return =  json_encode($data);
}else{
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs),
        "recordsFiltered"=> mysqli_num_rows($rs),
        "data"=>array()
    );
    $return =  json_encode($data);
}

print $return;
mysqli_close($con);

function getTotalRows($con,$search){
	$Qry = new Query();	
	$Qry->table ="tblpaygrp";
	$Qry->selected ="*";
	$Qry->fields ="id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>