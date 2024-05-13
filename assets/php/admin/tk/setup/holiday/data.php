<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	

if( !empty( $param['alias'] )){
    $search='';
}else{
    $search='  order by `date`';
}


if( !empty( $param['name'] ) ){ 
    if( !empty( $param['alias'] ) ){
        $search=" AND id =  '".$param['name']."' "; 
    }else{
        $search=" AND id =  '".$param['name']."' order by `date`"; 
    }
}
if( !empty( $param['alias'] ) ){ $search= $search." AND idtype like   '%".$param['alias']."%'  order by `date`"; }

$where = $search;

if( $param['length'] !='' ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "vw_dataholidays";
$Qry->selected  = "*";
$Qry->fields    = "YEAR(date) = YEAR(CURDATE()) ".$search;
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
        if($row['location'] == 'Provincial'){
          
            $location =getProvince($con,$row['provcode']);
        }else{
            $location = $row['location'];
        }

        $data["data"][] = array(
            'id'    	=> $row['id'],
            'name'  	=> ucwords(strtolower($row['name']),' '),
            'date'		=> date('F j, Y', strtotime($row['date'])),
			'idtype'	=> $row['idtype'],
            'type'		=> $row['type'],
            'loc'		=> $location
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
	$Qry->table ="vw_dataholidays";
	$Qry->selected ="*";
	$Qry->fields ="YEAR(date) = YEAR(CURDATE())".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

function getProvince($con,$prov){
	$Qry = new Query();	
	$Qry->table ="tblprovince";
	$Qry->selected ="provDesc";
	$Qry->fields ="provCode='".$prov."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['provDesc'];
        }
    }
}

?>