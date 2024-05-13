<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));


$search='';
$counter = 0;
// if( !empty( $param->filter->alias ) ){ $search=" AND idclass =   '". $param->filter->alias ."' "; }
// if( !empty( $param->filter->name ) ){ $search.=" AND (name like '%".$param->filter->name."%' OR alias like '%". $param->filter->name."%') "; }

// if(!empty($param->filter->stats)){ 
//     if($param->filter->stats == "Active") {
//         $search.= "AND stats  =  1";
//     }elseif($param->filter->stats == "Inactive") {
//         $search.= "AND stats  =  0";
//     }elseif($param->filter->stats == "All") {
//         $search.= "AND stats IN (1,0)";
//     }else{
//         $search.= "AND stats  =  1";
//     }
// }else{
//     $search.= "AND stats  =  1";
// }

$where = $search;

$Qry = new Query();	
$Qry->table     = "tblplannerdata_master";
$Qry->selected  = "*";
$Qry->fields    = "kanban_id != '' ".$search." ORDER BY kanban_id  LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($param->pagination->currentPage>1){
        $counter = ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
    }
    while($row=mysqli_fetch_array($rs)){
        $counter++;
        //(int)$row['id'],
        if($row['stats'] == 1){
            $stats = 'Active';
        }else{
            $stats = 'Inactive';
        }
        
        $data[] = array(
            'id'            => $counter,
            'ids'           => $row['kanban_id'],
            "kanban_id"     => 'KB00'.$row['kanban_id'],
            "wo_no"         => $row['wo_no'],
            "prod_no"	    => $row['prod_no'],
            "prod_qty"	    => $row['prod_qty'],
            "stats"	        => $row['stats'], 
            "stats"	        => $stats
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
	$Qry->table ="tblplannerdata_master";
	$Qry->selected ="*";
	$Qry->fields ="kanban_id != '' ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}
?>