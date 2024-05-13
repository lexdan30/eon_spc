<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));


$search='';
$counter = 0;
if( !empty( $param->filter->machine_name ) ){ $search=" AND machine_name =   '". $param->filter->machine_name ."' "; }

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
$Qry->table     = "tblmachine_master";
$Qry->selected  = "*";
$Qry->fields    = "machine_name != '' ".$search." ORDER BY id  LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
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
        if( !file_exists( "../admin/hr/employee/pix/".$row['machine_pic'] ) || empty($row['machine_pic']) ){
            $img_prof = "assets/php/admin/hr/employee/pix/".$row['machine_pic']."?".time();
        }else{
            $img_prof = "assets/php/admin/hr/employee/pix/".$row['machine_pic']."?".time();
        } 
        $data[] = array(
            'id'            => $counter,
            'ids'           => $row['machine_name'],
            "machine_pic"           =>$img_prof,
            "machine_code"  =>$row['machine_code'],
            "machine_name"	=> $row['machine_name'],
            "description"	=> $row['description'],
            "locator_code"	    => $row['locator_code'],
            "location"	    => $row['location'],
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
	$Qry->table ="tblmachine_master";
	$Qry->selected ="*";
	$Qry->fields ="machine_name != '' ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}
?>