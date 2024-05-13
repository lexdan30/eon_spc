<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	

$search='';

if( !empty( $param['type'] ) ){ $search=" AND type =  '".$param['type']."' "; }
if( !empty( $param['desc'] ) ){ $search.=" AND (`desc` like   '%".$param['desc']."%' OR code like   '%".$param['desc']."%' OR loan_mode like   '%".$param['desc']."%' OR type like   '%".$param['desc']."%')"; }
// if( strlen($param['stats']) > 0  ){ $search.=" AND stats  =  '".$param['stats']."' ";    }
if($param['stats'] == "" || $param['stats'] == '1'){
    $search.= "AND stats  =  1 order by `desc` asc";
}else if($param['stats'] == '2'){
    $search.= " order by `desc` asc";
}else{
    $search.= "AND stats  =  0 order by `desc` asc";
}


$where = $search;

if( $param['length'] !='' ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}


$Qry = new Query();	
$Qry->table     = "tblloantype";
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
		if($row['stats'] == 1){
            $stats = 'ACTIVE';
        }else{
            $stats = 'INACTIVE';
        }
        $data["data"][] = array(
            'id'                    => (int)$row['id'],
            "code"                  => $row['code'],
            "desc"         	        => $row['desc'],
            "stats"         	    => $stats,
            "type"         	        => $row['type'],
            "loan_mode"	            => $row['loan_mode'],
            "interest_percentage"	=> $row['interest_percentage'],
            "app_first"	            => $row['app_first'],
            "app_second"	        => $row['app_second'],
            "app_sp"	            => $row['app_sp'],
            "app_fp"	            => $row['app_fp'],
            "priority"	            => $row['priority'],
            "debit"	                => $row['debit'],
            "credit"	            => $row['credit']
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
	$Qry->table ="tblloantype";
	$Qry->selected ="*";
	$Qry->fields ="id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>






