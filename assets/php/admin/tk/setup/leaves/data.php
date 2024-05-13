<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	

$search='';

if( !empty( $param['name'] ) ){ $search=" AND name like   '%".$param['name']."%' "; }
if( !empty( $param['type'] ) ){ $search.=" AND idtype =   '".$param['type']."' "; }
if( !empty( $param['convertible'] ) ){ $search.=" AND isconvertible =   '".$param['convertible']."' "; }
if( !empty( $param['active'] ) ){ $search.=" AND active =   '".$param['active']."' "; }

$where = $search;

if( $param['length'] !='' ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "vw_dataleavestype";
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
        $data["data"][] = array(
            'id'        		=> (int)$row['id'],
            'name'     			=> $row['name'],
            'hours'   			=> $row['hours'],
            'idtype'    		=> $row['idtype'],
			'type'				=> $row['type'],
			'isconvertible'		=> $row['isconvertible'],
			'convertible'		=> $row['convertible'],
			'idactive'			=> $row['idactive'],
			'active'			=> $row['active']
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
	$Qry->table ="vw_dataleavestype";
	$Qry->selected ="*";
	$Qry->fields ="id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>