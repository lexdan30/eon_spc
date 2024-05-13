<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

$Qry 			= new Query();	
$Qry->table     = "tbltimelogsconf";
$Qry->selected  = "*";
$Qry->fields    = "id='".$param->id."'";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_array($rs)){
        $data = array( 
            'id'    	=> $row['id'],
            'alias'  	=> $row['alias'],
            'emp'		=> explode(",",$row['emp']),
			'wdate'		=> explode(",",$row['wdate']),
			'wtime'		=> explode(",",$row['wtime']),
			'atype'		=> explode(",",$row['atype']),
			'ain'		=> $row['ain'],
			'aout'		=> $row['aout'],
			'bin'		=> $row['bin'],
			'bout'		=> $row['bout'],
			'descript'	=> $row['descript'],
			'note'		=> $row['note']
        );
    }
}

$return = json_encode($data);
print $return;
mysqli_close($con);
?>