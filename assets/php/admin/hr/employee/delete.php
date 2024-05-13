<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));


if( !empty( $param->idacct ) ){
	if( !checkAssignExist( $con, $param->idacct ) ){
		$idacct			= $param->idacct;
		
		$Qry 			= new Query();	
		$Qry->table     = "tblaccount";
		$Qry->fields    = "id='".$idacct."'";
		$ch 			= $Qry->exe_DELETE($con);
		
		$Qrytet 		= new Query();	
		$Qrytet->table  = "tblaccountet";
		$Qrytet->fields = "idacct='".$idacct."'";
		$chtet 			= $Qrytet->exe_DELETE($con);
		
		$Qryted 		= new Query();	
		$Qryted->table  = "tblaccountedubg";
		$Qryted->fields = "idacct='".$idacct."'";
		$chted 			= $Qryted->exe_DELETE($con);
		
		$Qrytem 		= new Query();	
		$Qrytem->table  = "tblaccountemphis";
		$Qrytem->fields = "idacct='".$idacct."'";
		$chtem 			= $Qrytem->exe_DELETE($con);
		
		$return = json_encode(array("status"=>"success"));
	}else{
		$return = json_encode(array("status"=>"invalid"));
	}
}else{
	$return = json_encode(array("status"=>"invalid"));
}

print $return;
mysqli_close($con);

function checkAssignExist( $con, $idacct ){
	$Qry = new Query();	
	$Qry->table     = "tblaccountjob";
	$Qry->selected  = "*";
	$Qry->fields    = "idacct='".$idacct."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		return true;
	}
	return false;
}

?>