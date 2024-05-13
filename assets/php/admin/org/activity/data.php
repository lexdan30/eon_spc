<?php
require_once('../../../activation.php');
$param = json_decode(file_get_contents('php://input'));
$conn = new connector();
if( (int)$param->conn == 1 ){	
	$con = $conn->connect();
}else{
	$varcon = "connect".(int)$param->conn;
	$con = $conn->$varcon();
	$concorp = $conn->connect();
}

require_once('../../../classPhp.php'); 


$data = array();

$Qry = new Query();	
$Qry->table     = "tblcompanyact AS a LEFT JOIN vw_dataemployees AS b ON a.`idcreator` = b.`id`";
$Qry->selected  = "a.id, a.`idcomp`,a.`idcreator`, b.`empname`, a.`event_title`, a.`event_desc` , a.`efrom`,a.`eto`,a.`isactive`,a.`isyear`, a.isall";
$Qry->fields    = "a.id > 0 and a.isactive = 1";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
		$pix = 0;
		$title = $row['event_title'];
		if( file_exists( "./file/".$row['id'].".pdf" ) ){
            $pix = 1;
			
        }
        $data[] = array( 
            "id"        	=> $row['id'],
			"title" 		=> $title,
			"start" 		=> $row['efrom'],
			"end" 	    	=> $row['eto'],
			"finish" 	    => $row['eto'],
			"isactive" 	    => $row['isactive'],
			"isyear" 	    => $row['isyear'],
			"idcreator"		=> $row['idcreator'],
			"creator"		=> $row['empname'],
			"description"	=> $row['event_desc'],
			"isall"			=> $row['isall'],
			"pix"			=> $pix
        );
    }
}
        
$return = json_encode($data);

print $return;
mysqli_close($con);
?>