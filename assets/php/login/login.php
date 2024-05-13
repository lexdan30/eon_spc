<?php
require_once('../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

if( !empty( $param->uname ) ){
	if( !empty( $param->passw ) ){		
		$Qry = new Query();	
		$Qry->table     = "users";
		$Qry->selected  = "*, 'atob'";
		$Qry->fields    = "username='".$param->uname."' AND pword='".md5($param->passw)."' "; 
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){ 
				$data = array( 
					"id"        	=> $row['id'],
					"empid"			=> $row['empid'],
					"username"		=> $row['username'],
					"fname"			=> $row['fname'],
					"lname"			=> $row['lname'],
					"mname"			=> $row['mname'],
					"position"		=> $row['position'],
					"joblvl"		=> $row['joblvl'],
					"user_type"		=> $row['user_type'],
					"secret"		=> $row['atob'],
					"status"		=> "success"
				);
			}
			$return = json_encode($data);
		}else{
			$return = json_encode(array("status"=>"notfound",'errcode' => mysqli_error($con)));
		}
	}else{
		$return = json_encode(array("status"=>"nopass"));
	}	
}else{
	$return = json_encode(array("status"=>"nouname"));
}

print $return;
mysqli_close($con);

?>