<?php 
 	require_once('../../../../activation.php');
    require_once('../../../../classPhp.php');
    $conn = new connector();
    $con = $conn->connect();
    $param = $_POST;
	$search='';
	
	//View branch
	// if( !empty($param['accountbranch']) ){
	// 	$search=$search." AND branch = '".$param['accountbranch']."'";
	// }

	$where = $search;

	if( $param['length'] !='' ){
		$search=$search." LIMIT ".$param['length'];	
	}
	if( $param['start'] !='' ){
		$search=$search." OFFSET ".$param['start'];
	}

    	$Qry = new Query();	
		$Qry->table     = "vw_data_approversetup";
		$Qry->selected  = "id, approver_type_1a, approver_type_1b, approver_type_2a, approver_type_2b, approver_type_3a, approver_type_3b, approver_type_4a, approver_type_4b, approver_type_5a, approver_type_5b, approver_type_6a, approver_type_6b, approver_type_7a, approver_type_7b, fullname1a, fullname1b, fullname2a, fullname2b, fullname3a, fullname3b, fullname4a, fullname4b, fullname5a, fullname5b, fullname6a, fullname6b, fullname7a, fullname7b, idstatus, iddescription";
		$Qry->fields    = "id>0 and idform=2".$search;
		$rs = $Qry->exe_SELECT($con);
		$recFiltered = getTotalRows($con,$where);

		//
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
				$status = '';
				if($row['iddescription'] == 'ACTIVE'){
					$status = 'ACTIVE';
                }
                if($row['iddescription'] == 'BLOCK'){
                    $status = 'BLOCK';
				}
				if($row['iddescription'] == 'INACTIVE'){
                    $status = 'INACTIVE';
				}

				$data["data"][] = array(
                    //Informations
					"id"        			=> $row['id'],
					"approver_type_1a"    	=> $row['approver_type_1a'],
					"approver_type_1b" 		=> $row['approver_type_1b'],
					"approver_type_2a"		=> $row['approver_type_2a'],
                    "approver_type_2b"		=> $row['approver_type_2b'],
                    "approver_type_3a"		=> $row['approver_type_3a'],
                    "approver_type_3b"		=> $row['approver_type_3b'],
					"approver_type_4a"		=> $row['approver_type_4a'],
					"approver_type_4b"		=> $row['approver_type_4b'],
					"approver_type_5a"		=> $row['approver_type_5a'],
					"approver_type_5b"		=> $row['approver_type_5b'],
					"approver_type_6a"		=> $row['approver_type_6a'],
					"approver_type_6b"		=> $row['approver_type_6b'],
					"approver_type_7a"		=> $row['approver_type_7a'],
					"approver_type_7b"		=> $row['approver_type_7b'],
		            "fullname1a"    		=> (empty(trim($row['fullname1a'])) ? ' ' : $row['fullname1a']),
					"fullname1b" 			=> (empty(trim($row['fullname1b'])) ? ' ' : $row['fullname1b']),
					"fullname2a"			=> (empty(trim($row['fullname2a'])) ? ' ' : $row['fullname2a']),
                    "fullname2b"			=> (empty(trim($row['fullname2b'])) ? ' ' : $row['fullname2b']),
                    "fullname3a"			=> (empty(trim($row['fullname3a'])) ? ' ' : $row['fullname3a']),
                    "fullname3b"			=> (empty(trim($row['fullname3b'])) ? ' ' : $row['fullname3b']),
					"fullname4a"			=> (empty(trim($row['fullname4a'])) ? ' ' : $row['fullname4a']),
					"fullname4b"			=> (empty(trim($row['fullname4b'])) ? ' ' : $row['fullname4b']),
					"fullname5a"			=> (empty(trim($row['fullname5a'])) ? ' ' : $row['fullname5a']),
					"fullname5b"			=> (empty(trim($row['fullname5b'])) ? ' ' : $row['fullname5b']),
					"fullname6a"			=> (empty(trim($row['fullname6a'])) ? ' ' : $row['fullname6a']),
					"fullname6b"			=> (empty(trim($row['fullname6b'])) ? ' ' : $row['fullname6b']),
					"fullname7a"			=> (empty(trim($row['fullname7a'])) ? ' ' : $row['fullname7a']),
					"fullname7b"			=> (empty(trim($row['fullname7b'])) ? ' ' : $row['fullname7b']),
					"idstatus"				=> ($row['idstatus']),
					"iddescription"			=> ($row['iddescription']),
					"status"				=>	$status
		        );
			}		
			$return = json_encode($data);
		}else {
			//ig search nmo sa table nya way pareha naay mo gwas no data available
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
	$Qry->table ="vw_data_approversetup";
	$Qry->selected ="*";
	$Qry->fields ="id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>