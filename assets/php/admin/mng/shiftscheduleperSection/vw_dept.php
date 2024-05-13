<?php
require_once('../../../logger.php');
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
    $Qry->table     = "(SELECT id, `name`, unittype, isactive FROM tblbunits) as a LEFT JOIN (SELECT id, `type` FROM tblbunitstype) AS c ON (a.unittype = c.id)";
    $Qry->selected  = "a.id, a.name, c.type as unit_type";
	if( (int)$param->idtype == 6 ){
		$Qry->fields    = "a.isactive=1 AND a.unittype in ( 1 )";
	}else if( (int)$param->idtype == 2 ){
        $samelevel = (int)$param->idtype+1;
		$Qry->fields    = "a.isactive=1 AND a.unittype IN (". (int)$param->idtype.",". $samelevel .")";
	}else{
		$Qry->fields    = "a.isactive=1 AND a.unittype =  ". $param->idtype;
	}
    if( !empty( $param->id ) ){
        $Qry->fields= $Qry->fields . " AND a.id not in ('". $param->id ."')";
    }
    $rs = $Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
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