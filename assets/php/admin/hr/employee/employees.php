<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 


$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "*";

if($param->emptype == 4){
    $Qry->fields    = "id > 0 ORDER BY empname ASC ";
}else{
    $Qry->fields    = "etypeid='".  $param->emptype ."' ORDER BY empname ASC ";
}
$rs = $Qry->exe_SELECT($con);
//echo $Qry->fields;
if(mysqli_num_rows($rs)>=1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id"        => $row['id'],
            "idaccttype"=> $row['idaccttype'],
            "empid" 	=> $row['empid'],
            "empname"	=> $row['empname'],
            "emplbl"	=> $row['empid']." ".$row['empname'],
            "idsuperior"=> $row['idsuperior'],
            "superior"	=> $row['superior'],
            "idpos"		=> $row['idpos'],
            "post"		=> $row['post']
        );
    }
    $return = json_encode($data);
}else{
    $return = json_encode(array('status'=>'empty'));
}
    
print $return;
mysqli_close($con);
?>