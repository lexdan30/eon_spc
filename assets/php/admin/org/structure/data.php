<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php');  

$data = array();
$nodeid = $_GET['id'];

$Qry = new Query();	
$Qry->table     = "tblbunits";
$Qry->selected  = "*";
if( $nodeid == "#" ){   
    $Qry->fields    = "idunder IS NULL AND unittype = 1 AND isactive = 1";
}else{
	$test = explode("_",$nodeid);
	$nodeid = $test[0];
	if( !empty($test[1]) ){
		$Qry->fields    = "isactive = 1 AND underalias='".$test[1]."' AND idunder = ".$nodeid;
	}else{
		$Qry->fields    = "isactive = 1 AND idunder = ".$nodeid;
	}
}

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id"        => $row['id'].'_'.$row['underalias'],
            "text" 	    => $row['name'],
			"alias"		=> $row['underalias'],
            "children" 	=> boolChildren($con, $row['id'], $row['unittype'], $row['underalias']),
            "unittype"  => $row['unittype']
        );
    }
}elseif( $nodeid != "#" && !empty( $_GET['alias'] ) ){
	$Qry2 = new Query();	
	$Qry2->table     = "2hris".$_GET['alias'].".tblbunits as a";
	$Qry2->selected  = "a.*";
	
	$test = explode("_",$_GET['id']);
	$nodeid = $test[0];
	
	$Qry2->fields    = "a.isactive = 1 AND underalias='".$test[1]."' AND a.idunder = ".$nodeid;
	$rs2 = $Qry2->exe_SELECT($con);
	if(mysqli_num_rows($rs2)>= 1){
		while($row2=mysqli_fetch_array($rs2)){
			$data[] = array( 
				"id"        => $row2['id'].'_'.$row2['underalias'],
				"text" 	    => $row2['name'],
				"alias"		=> $row2['underalias'],
				"children" 	=> boolChildren($con, $row2['id'], $row2['unittype'], $row2['underalias']),
				"unittype"  => $row2['unittype']
			);
		}
	}
}

$return =  json_encode($data);
print $return;
mysqli_close($con);

function boolChildren($con, $id, $unittype, $alias){
	$Qry = new Query();	
	$Qry->table     = "2hris".$alias.".tblbunits as a";
	$Qry->selected  = "a.*";
	$Qry->fields    = "a.isactive = 1 AND a.idunder=".$id;
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
	   return true;
	}
	return false;
}

?>