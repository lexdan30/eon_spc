<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data = array();
$search = '';

if( !empty( $param['name'] ) ){ $search=$search." AND name '%".$param['name']."%' ";}

if( !empty( $param['stype'] ) ){ $search=$search." AND stype '%".$param['stype']."%' ";}

$Qry            = new Query();
$Qry->table     = "tblshift";
$Qry->selected  = "stype,name";

if( !empty($Qry->fields->reghrs >= 8.00 )){
    $param->stype = "stype";
}
if( !empty($Qry->fields->reghrs >= 9.00 )){
    $param->stype = "stype";
}
if( !empty($Qry->fields->reghrs >= 4.00 )){
    $param->stype = "stype";
}

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs >= 1 )){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array(

            "stype"  =   $row['stype'],
            "name"   =   $row['name'],
            
        )
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array($Qry->fields));
	
}
print $return;
mysqli_close($con);
?>