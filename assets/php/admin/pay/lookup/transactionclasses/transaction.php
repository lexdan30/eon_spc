<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	
$search='';

if( !empty( $param['description'] ) ){ $search.=" AND (description like '%".$param['description']."%' OR alias like '%".$param['description']."%') "; }
if($param['flags'] == "" || $param['flags'] == '1'){
    $search.= "AND flags  =  1 order by description asc";
}
if($param['flags'] == '2'){
    $search.= "  order by description asc";
}
if($param['flags'] == '0'){
    $search.= "AND flags  =  0 order by description asc";
}

if($param['transactiontype'] == '1'){
    $search.= " AND transactiontype  =  1";
}
if($param['transactiontype'] == '0'){
    $search.= " AND transactiontype  =  0";
}

$where = $search;

if( $param['length'] !='' ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "tblclass";
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
            "alias"         => $row['alias'],
            "description"   => $row['description'],
            "istax"         => $row['istax'],
            "multi"         => $row['multi'],
            "transactiontype"   => $row['transactiontype'],
            "flags"	        => $stats
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
	$Qry->table ="tblclass";
	$Qry->selected ="*";
	$Qry->fields ="id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>