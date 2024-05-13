<?php
require_once('../../../activation.php');

$param = $_GET;

$conn = new connector();	
if( (int)$param['conn'] == 1 ){	
	$con = $conn->connect();
}else{
	$varcon = "connect".(int)$param['conn'];
	$con = $conn->$varcon();
}

require_once('../../../classPhp.php');  

$data = array();

$Qry = new Query();	
$Qry->table     = "tblbunits";
$Qry->selected  = "*";
$Qry->fields    = "idunder IS NULL AND unittype = 1 AND isactive = 1";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        if( !empty( boolChildren($con, $row['id'], $row['unittype'], $row['underalias']) ) ){
			$data = array( 
				"title" 	=> $row['name'],
				"name"		=> getCompanyPic($con),
				"children" 	=> boolChildren($con, $row['id'], $row['unittype'], $row['underalias'])
			);
		}else{
			$data = array( 
				"title" 	=> $row['name'],
				"name"		=> ''
			);
		}
    }
}

$return =  json_encode($data);
print $return;
mysqli_close($con);

function boolChildren($con, $id, $unittype, $alias){
	$data = array();
	$Qry = new Query();	
	$Qry->table     = "tblbunits as a";
	$Qry->selected  = "a.*";
	$Qry->fields    = "a.isactive = 1 AND a.idunder=".$id;
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_array($rs)){

			$empdata = getHeadPicName($con, $row['idhead'],$row['id']); 
		    if( !empty( boolChildren($con, $row['id'], $row['unittype'], $row['underalias']) ) ){
			
				$data[] = array( 
					"title" 	=> $row['name'],
					"name"		=> getHeadPicName($con, $row['idhead'], $row['id']),
					"idunit"	=> $row['id'],
					"children" 	=> boolChildren($con, $row['id'], $row['unittype'], $row['underalias'])
				);
			}else{
				$data[] = array( 
					"title" 	=> $row['name'],
					"name"		=>   getHeadPicName($con, $row['idhead'], $row['id'])
				);
			}
		}
	}
	return $data;
}



?>