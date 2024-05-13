<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$return = null;	
$search='';
if( array_key_exists('accountid', $param) && !empty($param['accountid']) ){ $search=$search." AND la.idsuperior 	= '".$param['accountid']."' ";  }
if( !empty( $param['acct'] ) ){ $search=$search." AND la.idacct 	= '".$param['acct']."' "; }
if( !empty( $param['docu'] ) ){ $search=$search." AND la.docnumber like '%".$param['docu']."%' "; }
if( !empty( $param['appstat'] ) ){ $search=$search." AND la.stat = '".$param['appstat']."' "; }
if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['to']."')"; }
if( !empty( $param['from'] ) && empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['from']."')"; }

$where = $search;

if( $param['length'] !='' ){
    $search=$search." ORDER BY date ASC LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}


$dept = getIdUnit($con,$param['accountid']);


//Get Managers Under person
$ids=0;if( !empty( $dept ) ){
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
}


$Qry = new Query();	
$Qry->table     = "vw_leave_application AS la LEFT JOIN vw_dataemployees AS de ON la.idacct = de.id";
$Qry->selected  = "la.date, la.docnumber, la.leave_name, la.leave_type, la.idacct, la.empname,la.idsuperior, de.idunit, la.hrs, la.stat, la.leave_status";
$Qry->fields    = "la.idleave=1 AND de.idunit IN (".$ids.")".$search;
$rs = $Qry->exe_SELECT($con);
$recFiltered = getTotalRows($con,$ids,$where);
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
			'date'			    => $row['date'],	
            'docnumber'     	=> $row['docnumber'],
            'leave_name'   		=> $row['leave_name'],
            'leave_type'    	=> $row['leave_type'],
			'empname'			=> $row['empname'],
			'hrs'			    => $row['hrs'],
			'leave_status'		=> $row['leave_status'],

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
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['idunit'];
        }
    }
    return null;
}

function getTotalRows($con,$ids,$search){
	$Qry = new Query();	
    $Qry->table     = "vw_leave_application AS la LEFT JOIN vw_dataemployees AS de ON la.idacct = de.id";
    $Qry->selected  = "la.date, la.docnumber, la.leave_name, la.leave_type, la.idacct, la.empname,la.idsuperior, de.idunit, la.hrs, la.stat, la.leave_status";
    $Qry->fields    = "la.idleave=1 AND de.idunit IN (".$ids.")".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}



?>