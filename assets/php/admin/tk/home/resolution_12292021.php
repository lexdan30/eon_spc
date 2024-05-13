<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$pay_period = getPayPeriod($con);
$search ='';

if( !empty( $param->dept ) ){ $search=$search." AND idunit = '".$param->dept."' "; }





$data = array();
$Qry = new Query();	
$Qry->table     = "vw_resocenter";
$Qry->selected  = "reso_date,empid,empname,reso_txt,idunit";
$Qry->fields    = "(reso_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') ".$search;

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
    $data[] = array( 
        "idunit" 		=> $row['idunit'],
        "reso_date" 	=> $row['reso_date'],
        "pic" 			=> $row['empid'],
        "empname" 		=> $row['empname'],
        "reso_txt" 		=> $row['reso_txt'],
        
     
    );
}
    $return = json_encode($data);
}else{
	$return = json_encode(array('q'=>$Qry->fields));
}


print $return;
mysqli_close($con);
?>