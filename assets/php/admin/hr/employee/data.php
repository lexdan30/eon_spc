<?php

require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 


$param = $_POST;
$return = null;	

$search='';
if( !empty( $param['empid'] ) ){ $search=" AND empid like   '%".$param['empid']."%' "; }
if( !empty( $param['empname'] ) ){ $search.=" AND empid like   '%".$param['empname']."%' "; }
if( !empty( $param['type'] ) ){ $search.=" AND etypeid =   '".$param['type']."' "; }
if( empty( $param['type'] ) ){ $search.= ' AND etypeid =  1 '; }
if( !empty( $param['type'] ) && $param['type'] == 4 ){ $search= ''; }
if( !empty( $param['idlvl'] ) ){ $search.=" AND idlvl =   '".$param['idlvl']."' "; }
if( !empty( $param['idloc'] ) ){ $search.=" AND idloc =   '".$param['idloc']."' "; }
if( !empty( $param['idpaygrp'] ) ){ $search.=" AND idpaygrp =   '".$param['idpaygrp']."' "; }
if( !empty( $param['idpaystat'] ) ){ $search.=" AND paystat =   '".$param['idpaystat']."' "; }
if( !empty( $param['dept'] ) ){
	 $search.= getChild($con,$param['dept']);
}
if( !empty( $param['unit'] ) ){
	$arr_id = array();
	$arr 	= getHierarchy($con,$param['unit']);
	array_push( $arr_id, $param['unit'] );
	if( !empty( $arr["nodechild"] ) ){
		$a = getChildNode($arr_id, $arr["nodechild"]);
		if( !empty($a) ){
			foreach( $a as $v ){
				array_push( $arr_id, $v );
			}
		}
	}
	if( count($arr_id) == 1 ){
		$ids 			= $arr_id[0];
	}else{
		$ids 			= implode(",",$arr_id);
	}
	$search.=" AND idunit in (".$ids.") "; 
}
$where = $search;


if( !empty( $param["order"][0]["column"] ) && (int)$param["order"][0]["column"] > 0 ){
	$cols_array = array("pic", "empid","empname", "type", "etype" );
	$search=$search." ORDER BY " . $cols_array[ $param["order"][0]["column"] ] . " " . $param["order"][0]["dir"]." ";	
}



if( $param['length'] !='' ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "*";
$Qry->fields    = "id!=1 ".$search;
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
		
		$path = 'assets/images/undefined.webp?'.time();
		if( !empty( $row['pic'] ) ){
			$path = 'assets/php/admin/hr/employee/pix/'.$row['pic'].'?'.time();
		}
		
		
        $data["data"][] = array(
            'id'        => (int)$row['id'],
            'empid'     => $row['empid'],
			'empname'   => $row['empname'],
			'dept'   	=> getDepartment($con, $row['idunit']),
            'type'    	=> ucwords(strtolower($row['type'])),
			'etype'		=> ucwords(strtolower($row['etype'])),
			'pic'		=> $path
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
	$Qry->table ="vw_dataemployees";
	$Qry->selected ="*";
	$Qry->fields ="id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

function getChild($con,$dept){
	$arr_id = array();
	$arr 	= getHierarchy($con,$dept);
	array_push( $arr_id, $dept );
	if( !empty( $arr["nodechild"] ) ){
		$a = getChildNode($arr_id, $arr["nodechild"]);
		if( !empty($a) ){
			foreach( $a as $v ){
				array_push( $arr_id, $v );
			}
		}
	}
	if( count($arr_id) == 1 ){
		$ids 			= $arr_id[0];
	}else{
		$ids 			= implode(",",$arr_id);
	}
	$search = " AND idunit in (".$ids.") "; 
	return $search;
}

?>