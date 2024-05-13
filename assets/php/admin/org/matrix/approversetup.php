<?php
require_once('../../../activation.php');
$param 	= $_POST;
$conn = new connector();	
if( (int)$param['conn'] == 1 ){	
	$con = $conn->connect();
}else{
	$varcon = "connect".(int)$param['conn'];
	$con = $conn->$varcon();
}

require_once('../../../classPhp.php');  


$search	='';
$return = null;	
$where  = $search;

if( $param['length'] !='' ){
	$search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
	$search=$search." OFFSET ".$param['start'];
}

$Qry 				= new Query();	
$Qry->table     	= "vw_appmatrix AS a LEFT JOIN tblbunits AS b ON a.`idunit` = b.id LEFT JOIN tblbunitstype AS c ON b.`unittype` = c.id ";
$Qry->selected  	= "a.*, c.type as unit_type";
$Qry->fields    	= "a.id>0 ORDER BY b.unittype DESC ".$search;
$rs 				= $Qry->exe_SELECT($con);
$recFiltered 		= getTotalRows($con,$where);

if(mysqli_num_rows($rs)>= 1){ // if 1 e execute and while if 0 sa else
	$data = array( 
		"draw"=> $param['draw'],
		"recordsTotal"=> mysqli_num_rows($rs),
		"recordsFiltered"=> $recFiltered,
		"qry"=>$Qry->fields,
		"data"=>array()
	);
	while($row=mysqli_fetch_array($rs)){
		
		//Status
		$status = 'ACTIVE';
		if($row['idstatus'] == '2'){
			$status = 'BLOCKED';
		}
		
		
		$data["data"][] = array(			
			"id" 					=> 		$row['id'],
			"idunit" 				=> 		$row['idunit'],
			"bunit"					=> 		$row['bunit'],
			"unit_type"				=>		$row['unit_type'],
			"ctr_approvers" 		=> 		$row['ctr_approver'],
			"ctr_approver"	 		=> 		$row['ctr_approver'],
			"approver_type_1a" 		=> 		$row['approver_type_1a'],
			"approver_type_1b" 		=> 		$row['approver_type_1b'],
			"approver_type_1c" 		=> 		$row['approver_type_1c'],
			"approver_unit_1a" 		=> 		$row['approver_unit_1a'],
			"approver_unit_1b" 		=> 		$row['approver_unit_1b'],
			"approver_unit_1c" 		=> 		$row['approver_unit_1c'],
			"approver_1a" 			=> 		$row['approver_1a'],
			"approver_1b" 			=> 		$row['approver_1b'],
			"approver_1c" 			=> 		$row['approver_1c'],
			"unit_1a"				=>		$row['unit_1a'],  		
			"unit_1b"				=>		$row['unit_1b'],		
			"unit_1c"				=>		$row['unit_1c'],		
			"approvername_1a"		=>		$row['approvername_1a'], 
			"approvername_1b"		=>		$row['approvername_1b'],
			"approvername_1c"		=>		$row['approvername_1c'],
			
			"approver_type_2a" 		=> 		$row['approver_type_2a'],
			"approver_type_2b" 		=> 		$row['approver_type_2b'],
			"approver_type_2c" 		=> 		$row['approver_type_2c'],
			"approver_unit_2a" 		=> 		$row['approver_unit_2a'],
			"approver_unit_2b" 		=> 		$row['approver_unit_2b'],
			"approver_unit_2c" 		=> 		$row['approver_unit_2c'],
			"approver_2a" 			=> 		$row['approver_2a'],
			"approver_2b" 			=> 		$row['approver_2b'],
			"approver_2c" 			=> 		$row['approver_2c'],
			"unit_2a"				=>		$row['unit_2a'],  		
			"unit_2b"				=>		$row['unit_2b'],		
			"unit_2c"				=>		$row['unit_2c'],		
			"approvername_2a"		=>		$row['approvername_2a'], 
			"approvername_2b"		=>		$row['approvername_2b'],
			"approvername_2c"		=>		$row['approvername_2c'],
			
			"approver_type_3a" 		=> 		$row['approver_type_3a'],
			"approver_type_3b" 		=> 		$row['approver_type_3b'],
			"approver_type_3c" 		=> 		$row['approver_type_3c'],
			"approver_unit_3a" 		=> 		$row['approver_unit_3a'],
			"approver_unit_3b" 		=> 		$row['approver_unit_3b'],
			"approver_unit_3c" 		=> 		$row['approver_unit_3c'],
			"approver_3a" 			=> 		$row['approver_3a'],
			"approver_3b" 			=> 		$row['approver_3b'],
			"approver_3c" 			=> 		$row['approver_3c'],
			"unit_3a"				=>		$row['unit_3a'],  		
			"unit_3b"				=>		$row['unit_3b'],		
			"unit_3c"				=>		$row['unit_3c'],		
			"approvername_3a"		=>		$row['approvername_3a'], 
			"approvername_3b"		=>		$row['approvername_3b'],
			"approvername_3c"		=>		$row['approvername_3c'],

			"approver_type_4a" 		=> 		$row['approver_type_4a'],
			"approver_type_4b" 		=> 		$row['approver_type_4b'],
			"approver_type_4c" 		=> 		$row['approver_type_4c'],
			"approver_unit_4a" 		=> 		$row['approver_unit_4a'],
			"approver_unit_4b" 		=> 		$row['approver_unit_4b'],
			"approver_unit_4c" 		=> 		$row['approver_unit_4c'],
			"approver_4a" 			=> 		$row['approver_4a'],
			"approver_4b" 			=> 		$row['approver_4b'],
			"approver_4c" 			=> 		$row['approver_4c'],
			"unit_4a"				=>		$row['unit_4a'],  		
			"unit_4b"				=>		$row['unit_4b'],		
			"unit_4c"				=>		$row['unit_4c'],		
			"approvername_4a"		=>		$row['approvername_4a'], 
			"approvername_4b"		=>		$row['approvername_4b'],
			"approvername_4c"		=>		$row['approvername_4c'],
			
			"approver_type_5a" 		=> 		$row['approver_type_5a'],
			"approver_type_5b" 		=> 		$row['approver_type_5b'],
			"approver_type_5c" 		=> 		$row['approver_type_5c'],
			"approver_unit_5a" 		=> 		$row['approver_unit_5a'],
			"approver_unit_5b" 		=> 		$row['approver_unit_5b'],
			"approver_unit_5c" 		=> 		$row['approver_unit_5c'],
			"approver_5a" 			=> 		$row['approver_5a'],
			"approver_5b" 			=> 		$row['approver_5b'],
			"approver_5c" 			=> 		$row['approver_5c'],
			"unit_5a"				=>		$row['unit_5a'],  		
			"unit_5b"				=>		$row['unit_5b'],		
			"unit_5c"				=>		$row['unit_5c'],		
			"approvername_5a"		=>		$row['approvername_5a'], 
			"approvername_5b"		=>		$row['approvername_5b'],
			"approvername_5c"		=>		$row['approvername_5c'],

			"approver_type_6a" 		=> 		$row['approver_type_6a'],
			"approver_type_6b" 		=> 		$row['approver_type_6b'],
			"approver_type_6c" 		=> 		$row['approver_type_6c'],
			"approver_unit_6a" 		=> 		$row['approver_unit_6a'],
			"approver_unit_6b" 		=> 		$row['approver_unit_6b'],
			"approver_unit_6c" 		=> 		$row['approver_unit_6c'],
			"approver_6a" 			=> 		$row['approver_6a'],
			"approver_6b" 			=> 		$row['approver_6b'],
			"approver_6c" 			=> 		$row['approver_6c'],
			"unit_6a"				=>		$row['unit_6a'],  		
			"unit_6b"				=>		$row['unit_6b'],		
			"unit_6c"				=>		$row['unit_6c'],		
			"approvername_6a"		=>		$row['approvername_6a'], 
			"approvername_6b"		=>		$row['approvername_6b'],
			"approvername_6c"		=>		$row['approvername_6c'],
			
			"approver_type_7a" 		=> 		$row['approver_type_7a'],
			"approver_type_7b" 		=> 		$row['approver_type_7b'],
			"approver_type_7c" 		=> 		$row['approver_type_7c'],
			"approver_unit_7a" 		=> 		$row['approver_unit_7a'],
			"approver_unit_7b" 		=> 		$row['approver_unit_7b'],
			"approver_unit_7c" 		=> 		$row['approver_unit_7c'],
			"approver_7a" 			=> 		$row['approver_7a'],
			"approver_7b" 			=> 		$row['approver_7b'],
			"approver_7c" 			=> 		$row['approver_7c'],
			"unit_7a"				=>		$row['unit_7a'],  		
			"unit_7b"				=>		$row['unit_7b'],		
			"unit_7c"				=>		$row['unit_7c'],		
			"approvername_7a"		=>		$row['approvername_7a'], 
			"approvername_7b"		=>		$row['approvername_7b'],
			"approvername_7c"		=>		$row['approvername_7c'],

			"approver_type_8a" 		=> 		$row['approver_type_8a'],
			"approver_type_8b" 		=> 		$row['approver_type_8b'],
			"approver_type_8c" 		=> 		$row['approver_type_8c'],
			"approver_unit_8a" 		=> 		$row['approver_unit_8a'],
			"approver_unit_8b" 		=> 		$row['approver_unit_8b'],
			"approver_unit_8c" 		=> 		$row['approver_unit_8c'],
			"approver_8a" 			=> 		$row['approver_8a'],
			"approver_8b" 			=> 		$row['approver_8b'],
			"approver_8c" 			=> 		$row['approver_8c'],
			"unit_8a"				=>		$row['unit_8a'],  		
			"unit_8b"				=>		$row['unit_8b'],		
			"unit_8c"				=>		$row['unit_8c'],		
			"approvername_8a"		=>		$row['approvername_8a'], 
			"approvername_8b"		=>		$row['approvername_8b'],
			"approvername_8c"		=>		$row['approvername_8c'],
			
			"approver_from_1b" 		=> $row['approver_from_1b'],
			"approver_to_1b" 		=> $row['approver_to_1b'],
			"approver_from_1c" 		=> $row['approver_from_1c'],
			"approver_to_1c" 		=> $row['approver_to_1c'],
			"approver_from_2b" 		=> $row['approver_from_2b'],
			"approver_to_2b" 		=> $row['approver_to_2b'],
			"approver_from_2c" 		=> $row['approver_from_2c'],
			"approver_to_2c" 		=> $row['approver_to_2c'],
			"approver_from_3b" 		=> $row['approver_from_3b'],
			"approver_to_3b" 		=> $row['approver_to_3b'],
			"approver_from_3c" 		=> $row['approver_from_3c'],
			"approver_to_3c" 		=> $row['approver_to_3c'],
			"approver_from_4b" 		=> $row['approver_from_4b'],
			"approver_to_4b" 		=> $row['approver_to_4b'],
			"approver_from_4c"		=> $row['approver_from_4c'],
			"approver_to_4c" 		=> $row['approver_to_4c'],
			"approver_from_5b" 		=> $row['approver_from_5b'],
			"approver_to_5b" 		=> $row['approver_to_5b'],
			"approver_from_5c" 		=> $row['approver_from_5c'],
			"approver_to_5c" 		=> $row['approver_to_5c'],
			"approver_from_6b" 		=> $row['approver_from_6b'],
			"approver_to_6b" 		=> $row['approver_to_6b'],
			"approver_from_6c" 		=> $row['approver_from_6c'],
			"approver_to_6c" 		=> $row['approver_to_6c'],
			"approver_from_7b" 		=> $row['approver_from_7b'],
			"approver_to_7b" 		=> $row['approver_to_7b'],
			"approver_from_7c" 		=> $row['approver_from_7c'],
			"approver_to_7c" 		=> $row['approver_to_7c'],
			"approver_from_8b" 		=> $row['approver_from_8b'],
			"approver_to_8b"		=> $row['approver_to_8b'],
			"approver_from_8c" 		=> $row['approver_from_8c'],
			"approver_to_8c" 		=> $row['approver_to_8c'],	
			
			"status" 				=> 		$row['idstatus'],
			"status"				=>		$status
		);
		
		
	}
	$return = json_encode($data);
}else {
	$data = array( 
		"draw"=> $param['draw'],
		"recordsTotal"=> mysqli_num_rows($rs),
		"recordsFiltered"=> mysqli_num_rows($rs),
		"data"=>array()
	);
	$return =  json_encode($data);
}


print $return;
mysqli_close($con);

function getTotalRows($con,$search){
	$Qry = new Query();	
	$Qry->table ="vw_appmatrix";
	$Qry->selected ="*";
	$Qry->fields ="id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}



?>