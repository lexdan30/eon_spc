<?php
require_once('../../../activation.php');
$param = json_decode(file_get_contents('php://input'));
$conn = new connector();	
if( (int)$param->conn == 1 ){	
	$con = $conn->connect();
}else{
	$varcon = "connect".(int)$param->conn;
	$con = $conn->$varcon();
}
require_once('../../../classPhp.php'); 
$data = array();

if( !empty( $param->idtype ) ){    	
    $Qry = new Query();	
    $Qry->table     = "tblbunits as a LEFT JOIN tblbunitstype AS c ON a.unittype = c.id";
    $Qry->selected  = "a.id, a.name, c.type as unit_type";
	if( (int)$param->idtype == 6 ){
		$Qry->fields    = "a.isactive=1 AND a.unittype in ( 1 )";
	}else{
		$Qry->fields    = "a.isactive=1 AND a.unittype =  ". $param->idtype;
	}
    if( !empty( $param->id ) ){
        $Qry->fields= $Qry->fields . " AND a.id not in ('". $param->id ."')";
    }
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $data[] = array( 
                "id"    => $row['id'],
                "name" 	=> $row['name'],
				"unit_type"	=> $row['unit_type']
            );
        }
    }
}
        
$return = json_encode($data);

print $return;
mysqli_close($con);
?>