<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$search = '';
$currdate =  SysDate();
$curmonthyear = date("F Y",strtotime($currdate)); 

if( !empty( $param->month ) ){ 
    if($curmonthyear !=  $param->month){
        $time = strtotime($param->month);
        $newformat = date('Y-m-d',$time);
        $search=" AND YEAR(monthparam) = YEAR('" . $newformat . "') AND MONTH(monthparam) = MONTH('" . $newformat . "') "; 
    }
}




$Qry = new Query();	
$Qry->table     = "tblservicecharge";
$Qry->selected  = "*";
$Qry->fields = "id>0". $search;


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id" 	            => $row['id'],
            "docnum" 	        => $row['docnumber'],
            "payitemid"     => $row['payitemid'],
            "headcount"             => $row['headcount'],
            "totalhours"             => $row['totalhours'],
            "hourlyrate"          => $row['hourlyrate'],
            "total"             => $row['total'],
            "appliedto"            => $row['appliedto'],
            "monthparam"           => $row['monthparam'],
            "releasedate"           => $row['releasedate'],
            "remarks"              => $row['remarks']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);
?>