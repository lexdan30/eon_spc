<?php
require_once('../../../activation.php');
require_once('../../../classPhp.php');  
$param = $_POST;
$conn = new connector();	
$con = $conn->connect();
 


$return = null;	

$search='';
if( !empty( $param['alias'] ) ){ $search=" AND alias like   '%".$param['alias']."%' "; }
if( !empty( $param['name'] ) ){ $search.=" AND name like   '%".$param['name']."%' "; }


$where = $search;
$Qry = new Query();	
$Qry->table     = "tbldevices";
$Qry->selected  = "*";
$Qry->fields    = "id>0 ORDER BY ID ASC".$search;
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
            'id'            	=> (int)$row['id'],
            'serial_no'     	=> $row['serial_no'],
            'location'      	=> $row['location'],
			'realtime_update'	=> $row['realtime_update'],
			'stats'  		    => $row['stats'],
        );
    }
}else{
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs),
        "recordsFiltered"=> mysqli_num_rows($rs),
        "data"=>array()
    );
}
$return =  json_encode($data);

print $return;
mysqli_close($con);

function getTotalRows($con,$search){
	$Qry = new Query();	
	$Qry->table ="tbldevices";
	$Qry->selected ="*";
	$Qry->fields ="id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>