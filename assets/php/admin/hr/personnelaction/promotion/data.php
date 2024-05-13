<?php 
	require_once('../../../../activation.php');
	$conn = new connector();	
	$con = $conn->connect();
	require_once('../../../../classPhp.php'); 
	$param = $_POST;
	$date=SysDate();
	$search="";
	$fullname='';

	if( !empty($param['searchID']) ){
		$search=$search." AND id = '".$param['searchID']."'";
	}

	if( !empty($param['datecreate']) ){
		$search=$search." AND date_created = '".$param['datecreate']."'";
	}

	if( !empty($param['effdate']) ){
		$search=$search." AND effectivedate = '".$param['effdate']."'";
	}

	if( !empty($param['acct']) ){
		$search=$search." AND requestor = '".$param['acct']."'";
	}
	
	$where = $search;

	$search=$search." ORDER BY CONCAT(date_created,' ',time_created) ASC ";//order by date and time created

	if( $param['length'] !='' ){
		$search=$search." LIMIT ".$param['length'];	
	}
	if( $param['start'] !='' ){
		$search=$search." OFFSET ".$param['start'];
	}

    	$Qry = new Query();	
		$Qry->table     = "vw_data_promotion";
		$Qry->selected  = "*";
		// $Qry->fields    = "id>0 AND idstatus in ('3', '5', '7') ".$search;
		$Qry->fields    = "id>0 AND idstatus = 3 ".$search;
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

				$pending = 1;
				if(!empty($row['approver1_status'])){
					$pending = 2;
				}
				if(!empty($row['approver2_status'])){
					$pending = 3;
				}
				if(!empty($row['approver3_status'])){
					$pending = 4;
				}
				if(!empty($row['approver4_status'])){
					$pending = 5;
				}
				if(!empty($row['approver5_status'])){
					$pending = 6;
				}
				if(!empty($row['approver6_status'])){
					$pending = 7;
				}
				
				// if(empty($row['fullname'])){
				// 	$fullname = getFullname_sp_account($con, $row['createdby']);
				// }else{
				// 	$fullname = $row['fullname'];
				// }

				// $branch_location = getBranch_sp_account($con, $row['createdby']);

				$dateCreated = strtotime($row['date_created']);
				$SysDate 	 = strtotime($date);
				$timeDiff	 = abs($SysDate - $dateCreated);
				$numberDays  = $timeDiff/86400; //86400 seconds in one day
				// and you might want to convert to integer
				$numberDays  = intval($numberDays);

				/* Old Status */
				//Status
				// $status = 'PENDING';
				
				// if($row['idstatus'] == '1'){
				// 	$status = 'APPROVED';
                // }
                // if($row['idstatus'] == '2'){
                //     $status = 'DISAPPROVED';
				// }
				// if($row['idstatus'] == '4'){
                //     $status = 'CANCELLED';
				// }
				// if($row['idstatus'] == '5'){
                //     $status = 'RELEASED';
				// }
				// if($row['idstatus'] == '6'){
                //     $status = 'RETURNED';
				// }
				// if($row['idstatus'] == '7'){
                //     $status = 'FOR RELEASE';
				// }
				/* Old Status */
				
				$data["data"][] = array(
					//ang display sa sa mga information sa table
		            "id"    				=> $row['id'],
					"refferenceno" 			=> $row['refferenceno'],
					"empid" 				=> $row['empid'],
					"empname" 				=> $row['empname'],
					"effectivedate"			=> $row['effectivedate'],
					"createdby"				=> $row['createdby'],
					"date_created"			=> $row['date_created'],
					"time_created"			=> $row['time_created'],
					"pending"				=> $pending,
					"idstatus"				=> $row['idstatus']
		        );
			}		
			$return = json_encode($data);
		}else {
			//ig search nmo sa table nya way pareha naay mo gwas no data available
			$data = array( 
		        "draw"=> $param['draw'],
		        "recordsTotal"=> mysqli_num_rows($rs),
		        "recordsFiltered"=> mysqli_num_rows($rs),
				"data"=>array(),
				"qry"=>$Qry->fields
		    );
		    $return =  json_encode($data);
		}


	print $return;
	mysqli_close($con);


	function getTotalRows($con,$search){
		$Qry = new Query();	
		$Qry->table ="vw_data_promotion";
		$Qry->selected ="*";
		$Qry->fields    = "id>0 ".$search;
		$rs = $Qry->exe_SELECT($con);
		return mysqli_num_rows($rs);
	}

	//Get Full Name Created By From service_portal.sp_account
	function getFullname_sp_account($con, $employeeid){
		$Qry=new Query();
		$Qry->table="service_portal.sp_account";
		$Qry->selected="CONCAT(first_name, ' ', last_name) AS fullname";
		$Qry->fields="employeeid='".$employeeid."'";
		$rs=$Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>=1){
			while($row=mysqli_fetch_array($rs)){
				return $row['fullname'];
			}
		}
		return '';
	}

	//Get Branch Created By From service_portal.sp_account
	function getBranch_sp_account($con, $employeeid){
		$Qry=new Query();
		$Qry->table="service_portal.sp_account";
		$Qry->selected="branch";
		$Qry->fields="employeeid='".$employeeid."' GROUP BY employeeid LIMIT 1";
		$rs=$Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>=1){
			while($row=mysqli_fetch_array($rs)){
				return $row['branch'];
			}
		}
		return '';
	}

?>