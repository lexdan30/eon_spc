<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));


$search='';
$counter = 0;
if( !empty( $param->filter->emp_id ) ){ $search=" AND emp_id =   '". $param->filter->emp_id ."' "; }
if( !empty( $param->filter-> operators_name) ){ $search=" AND operators_name =   '". $param->filter->operators_name ."' "; }
if( !empty( $param->filter->position ) ){ $search=" AND position =   '". $param->filter->position ."' "; }


if(!empty($param->filter->stats)){ 
    if($param->filter->stats == "Active") {
        $search.= "AND stats  =  1";
    }elseif($param->filter->stats == "Inactive") {
        $search.= "AND stats  =  0";
    }elseif($param->filter->stats == "All") {
        $search.= "AND stats IN (1,0)";
    }else{
        $search.= "AND stats  =  1";
    }
}else{
    $search.= "AND stats  =  1";
}

$where = $search;

$Qry = new Query();	
$Qry->table     = "tbloperator_master";
$Qry->selected  = "*";
$Qry->fields    = "id>0 ".$search." ORDER BY id  LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($param->pagination->currentPage>1){
        $counter = ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
    }
    while($row=mysqli_fetch_array($rs)){
        $counter++; 
        if($row['stats'] == 1){
            $stats = 'Active';
        }else{
            $stats = 'Inactive';
        }
        
        $data[] = array(
            'id'                    => $row['id'],
            "emp_id"                => $row['emp_id'],
            "operators_name"        => $row['operators_name'],
            "position"              => $row['position'],
            "stats"	                => $row['stats'], 
            "stats"	                => $stats
        );
    }

    $myData = array('status' => 'success', 'result' => $data, 'totalItems' => getTotal($con , $where));
	$return = json_encode($myData);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);

function getTotal($con,$search){
	$Qry = new Query();	
	$Qry->table ="tbloperator_master";
	$Qry->selected ="*";
	$Qry->fields ="emp_id != '' ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}
?>