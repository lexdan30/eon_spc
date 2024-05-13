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

$Qry = new Query();	
$Qry->table     = "tblbunits";
$Qry->selected  = "id, `name`, alias, costcenter, deputy1, deputy2, scheduler, idunder, site, unittype, isactive";
$Qry->fields    = "id='".$param->id."'";
$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
	$arr_id = array();
    if($row=mysqli_fetch_assoc($rs)){
		$arr 	= getHierarchy($con,$row['id']);

		array_push( $arr_id, $row['id'] );
		if( !empty( $arr["nodechild"] ) ){
			$a = getChildNode($arr_id, $arr["nodechild"]);
			if( !empty($a) ){
				foreach( $a as $v ){
					array_push( $arr_id, $v );
				}
			}
		}

		$data = array( 
            "id"        	=> $row['id'],
            "name" 	    	=> $row['name'],
            "alias" 		=> $row['alias'],
			"costcenter"	=> $row['costcenter'],
			"deputy1"		=> $row['deputy1'],
			"deputy2"		=> $row['deputy2'],
			"head" 	    	=> $row['scheduler'],
			"prev_head"     => $row['scheduler'],
            "dept" 	    	=> $row['idunder'],
			"location" 	    => $row['site'],
            "utype" 		=> $row['unittype'],
			"origtype"		=> $row['unittype'],
            "stat" 	    	=> $row['isactive'],
			"hierarchy"		=> $arr,
			"ids"			=> $arr_id
        );
    }
}
        
$return = json_encode($data);

print $return;
mysqli_close($con);

?>