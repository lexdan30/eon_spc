<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	
$search='';

// if( !empty( $param['code'] ) ){ $search=" AND idlcass =  '".$param['code']."' "; }
// if( !empty( $param['name'] ) ){ $search.=" AND name like   '%".$param['name']."%' "; }

// if($param['flags'] == "" || $param['flags'] == 1){
//     $search.= " AND flags  =  1";
// }
// if($param['flags'] == '0'){
//     $search.= " AND flags  =  0";
// }

$where = $search;

if( $param['length'] !='' ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "tblclasstrans";
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
            'id'            => (int)$row['id'],
            "idlcass"       => $row['idlcass'],
            "alias"         => $row['alias'],
            "name"	        => $row['name'],
            "debit"	        => $row['debit'],
            "credit"	    => $row['credit'],
            "rate"	        => $row['rate'],
            "isdef"	        => $row['isdef'],
            "determine"	    => $row['determine'],
            "ottype"	    => $row['ottype'],
            "flags"	        => $row['flags']
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
	$Qry->table ="tblclasstrans";
	$Qry->selected ="*";
	$Qry->fields ="id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>