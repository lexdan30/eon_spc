<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));


$data = array();	
$Qry = new Query();	
if(getTypes($con, $param->accountid) == 'Helper'){
	$Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblpayperiod_helper AS b ON pr.idpayperiod = b.id";
}
else if(getTypes($con, $param->accountid) == 'Japanese'){
	$Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblpayperiod_japanese AS b ON pr.idpayperiod = b.id";
}
else if(getTypes($con, $param->accountid) == 'Japanese Conversion'){
	$Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblpayperiod_japaneseconversion AS b ON pr.idpayperiod = b.id";
}
else{
	$Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblpayperiod AS b ON pr.idpayperiod = b.id";
}		
$Qry->selected  = "b.`period_start`, b.`period_end`, b.`pay_date`, pr.*,CONCAT(`a`.`lname`,IFNULL(CONCAT(' ',`a`.`suffix`),''),', ',`a`.`fname`,' ',SUBSTR(`a`.`mname`,1,1),'. ') AS `empname`, a.empid AS empid";

	$Qry->fields    = "pr.idacct =   '". $param->accountid ."' AND b.stat = 1  ORDER BY idpayperiod DESC";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
	while($row=mysqli_fetch_array($rs)){
		$data[] = array( 
			"id"        => $row['id'],
			"pay_date"	=> $row['pay_date']
		);
	}
	$return = json_encode($data);
}else{
	$return = json_encode(array());
	
}
    print $return;
    mysqli_close($con);

	function getTypes($con, $account_id){
		$Qry = new Query();	
		$Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id";
		$Qry->selected  = "type";
		$Qry->fields    = "pr.idacct = '" .$account_id. "'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){					
				return $row['type'];
			}
		}
		return 0;
	}
    
?>




            