<?php
require_once('../../../logger.php');
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$return = null;	
$search='';


if( array_key_exists('accountid', $param) && !empty($param['accountid']) ){
	// if( array_key_exists('units', $param) && !empty($param['units']) ){
	// 	$search=$search." AND (idsuperior 	= '".$param['accountid']."' OR idsuperior in (".$param['units'].") ) "; 
	// }else{
		$search=$search." AND idsuperior 	= '".$param['accountid']."' "; 
	//}
}
if( !empty( $param['acct'] ) ){ $search=$search." AND idacct 	= '".$param['acct']."' "; }
if( !empty( $param['docu'] ) ){ $search=$search." AND docnumber like '%".$param['docu']."%' "; }
// if( !empty( $param['appstat'] ) ){ $search=$search." AND stat = '".$param['appstat']."' "; }
if( $param['appstat'] == '3' || empty($param['appstat']) ){
	$search=$search." AND stat = '3' "; 
}elseif( $param['appstat'] == '4' ){
	$search=$search." AND stat IN (1,2,3) "; 
}else{
	$search=$search." AND stat = '".$param['appstat']."' "; 
}


// if(( $param['flag']) == '1'){
//     if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search; }
//     if( !empty( $param['from'] ) && empty( $param['to'] ) ){ $search=$search; }
// }
// else{
//     if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['to']."')"; }
//     if( !empty( $param['from'] ) && empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['from']."')"; }
    
// }
if(!empty($param['from'])){
	if(( $param['flag']) == '1'){
		if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search; }
		if( !empty( $param['from'] ) && empty( $param['to'] ) ){ $search=$search; } 
	} 
	else{
		if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['to']."')"; }
		if( !empty( $param['from'] ) && empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['from']."')"; }
	}
}else{
	$data = array( 
		"period"		=> getPayPeriodts($con),
    );

	if(( $param['flag']) == '1'){
		// if( !empty($data['period']['pay_start'] ) && !empty( $data['period']['pay_end'] ) ){ $search=$search; }
        // if( !empty($data['period']['pay_start'] ) && empty( $data['period']['pay_end'] ) ){ $search=$search; }
        if( !empty( $data['period']['pay_start'] ) && !empty( $data['period']['pay_end'] ) ){ $search=$search." AND (date between '".$data['period']['pay_start']."' AND '".$data['period']['pay_end']."')"; }
		if( !empty( $data['period']['pay_start'] ) && empty($data['period']['pay_end'] ) ){ $search=$search." AND (date between '".$data['period']['pay_start']."' AND '".$data['period']['pay_start']."')"; }
	} else{
		if( !empty( $data['period']['pay_start'] ) && !empty( $data['period']['pay_end'] ) ){ $search=$search." AND (date between '".$data['period']['pay_start']."' AND '".$data['period']['pay_end']."')"; }
		if( !empty( $data['period']['pay_start'] ) && empty($data['period']['pay_end'] ) ){ $search=$search." AND (date between '".$data['period']['pay_start']."' AND '".$data['period']['pay_start']."')"; }
	}
}

$where = $search;

//sort nga funtion sa table
if( $param['order'][0]['column'] !='' ){//default 
	$arrCols = array("",
					"empname",
					"date",
					"",
					"",
					"",
					"shift_status");//mao ra ang mailisan na declare na sa ubos php
	$search=$search." ORDER BY ". $arrCols[$param['order'][0]['column']] ." ".$param['order'][0]['dir'];//default
}


if( (int)$param['length'] >= 0 ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' && (int)$param['length'] >= 0 ){
    $search=$search." OFFSET ".$param['start'];
}


$dept = getIdUnit($con,$param['accountid']);

$ids='0';
//Get Managers Under person
if( !empty( $dept ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$dept);
    if( !empty( $arr["nodechild"] ) ){     
        $ids = join(',', flatten($arr['nodechild']));
    } else {
        $ids = '0';
    }
}

$Qry = new Query();	
$Qry->table     = "vw_shift_application";
$Qry->selected  = "
idsuperior, approver1_name, id, `date`,
docnumber, empname, oldshift, newshift, shift_status, remarks, approver1";
//$Qry->fields    = "sa.stat=3 AND de.idunit IN (".$ids.") ".$search;
$Qry->fields    = "id>0 ".$search;

$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
$recFiltered = getTotalRows($con,$ids,$where);
if(mysqli_num_rows($rs)>= 1){
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs),
        "recordsFiltered"=> $recFiltered,
        "qry"=>$Qry->fields,
        "data"=>array()
    );
    
    while($row=mysqli_fetch_assoc($rs)){
        $data["data"][] = array(     
            'id'                =>$row['id'],       
			'date'			    => $row['date'],	
            'docnumber'     	=> $row['docnumber'],
            'empname'   		=> $row['empname'],
			'shift_old'    		=> $row['oldshift'],
            'shift_name'    	=> $row['newshift'],				
			'shift_status'		=> ucwords(strtolower($row['shift_status'])),
			'remarks'			=> $row['remarks'],
            'lock'			    => checktklock($con,$row['date']) //timekeeping is closed // AD
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


function getIdUnit($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getIdUnit');
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['idunit'];
    }
    return null;
}

function getTotalRows($con,$ids,$search){
	$Qry = new Query();	
	$Qry->table ="vw_shift_application";
	$Qry->selected ="id";
	//$Qry->fields ="sa.stat=3 AND de.idunit IN (".$ids.") ".$search;
	$Qry->fields ="id>0 ".$search;
	$rs = $Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTotalRows');
	return mysqli_num_rows($rs);
}

?>