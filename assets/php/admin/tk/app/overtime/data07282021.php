<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	

$search='';

if( array_key_exists('idsuperior', $param) && !empty($param['idsuperior']) ){ 
	if( array_key_exists('units', $param) && !empty($param['units']) ){
		$search=$search." AND (idsuperior 	= '".$param['idsuperior']."' OR idsuperior in (".$param['units'].") ) ";  
	}else{
		$search=$search." AND idsuperior 	= '".$param['idsuperior']."' ";  
	}
}
if( !empty( $param['acct'] ) ){ $search=$search." AND idacct 	= '".$param['acct']."' "; }
if( !empty( $param['docu'] ) ){ $search=$search." AND docnumber like '%".$param['docu']."%' "; }
if( !empty( $param['appstat'] ) ){ $search=$search." AND stat = '".$param['appstat']."' "; }
if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['to']."')"; }

$where = $search;

//sort nga funtion sa table
if( $param['order'][0]['column'] !='' ){//default 
	$arrCols = array("",
					"empname",
					"date",
					"","planned_hrs",
					"actual_hrs",
					"remarks",
					"ot_status");//mao ra ang mailisan na declare na sa ubos php
	$search=$search." ORDER BY ". $arrCols[$param['order'][0]['column']] ." ".$param['order'][0]['dir'];//default
}


if( (int)$param['length'] >= 0 ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' && (int)$param['length'] >= 0 ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "vw_overtime_application";
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
		
		$ot_s = date('Y-m-d h:i A', strtotime($row['planned_start']));
		$arr_s = explode(" ",$row['planned_start']);
		if( $arr_s[0] == $row['date'] ){
			$ot_s = date('h:i A', strtotime($arr_s[1]));
		}
		
		$ot_f = date('Y-m-d h:i A', strtotime($row['planned_end']));
		$arr_f = explode(" ",$row['planned_end']);
		if( $arr_f[0] == $row['date'] ){
			$ot_f = date('h:i A', strtotime($arr_f[1]));
		}
		
        $data["data"][] = array(
            'id'        		=> (int)$row['id'],
			'docnumber'			=> $row['docnumber'],	            
			'idacct'			=> $row['idacct'],
			'empid'				=> $row['empid'],
			'empname'			=> $row['empname'],
			'date'				=> $row['date'],
			'start_time'		=> $row['start_time'],
			'end_time'			=> $row['end_time'],
			'units'				=> $row['actual_hrs'],
			'units2'			=> $row['planned_hrs'],
			'remarks'			=> $row['remarks'],
			'file'				=> $row['file'],
			'ot_status'			=> ucwords(strtolower($row['ot_status'])),
			'appr_hour_stat'	=> $row['approve_hr'],
			'date_approve'		=> $row['date_approve'],
			'ot_app'			=> $ot_s. ' to ' .$ot_f
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

function getTotalRows($con,$search){
	$Qry = new Query();	
	$Qry->table ="vw_overtime_application";
	$Qry->selected ="*";
	$Qry->fields ="id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>