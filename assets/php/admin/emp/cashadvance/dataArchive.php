<?php 
	require_once('../../../activation.php');
	$conn = new connector();	
	$con = $conn->connect();
	require_once('../../../classPhp.php'); 
	$param = $_POST;
	$date=SysDate();
	$search="";

	$isApprover = isApprover($con, $param['accountid']);

	if($isApprover==false){
		$search=$search." AND empid = '".$param['accteid']."'";
	}

	if( !empty($param['searchID']) ){
		$search=$search." AND id = '".$param['searchID']."'";
	}

	if( !empty($param['docid']) ){ 
		$search=$search." AND refferenceno like '%".$param['docid']."%' "; 
	}

	if( !empty($param['datecreate']) ){
		$search=$search." AND datecreated = '".$param['datecreate']."'";
	}

	if( !empty($param['effdate']) ){
		$search=$search." AND effectivedate = '".$param['effdate']."'";
	}

	if( !empty($param['acct']) ){
		$search=$search." AND empname = '".$param['acct']."'";
	}
	
	$where = $search;

	$search=$search." ORDER BY CONCAT(datecreated,' ',timecreated) ASC ";//order by date and time created

	if( $param['length'] !='' ){
		$search=$search." LIMIT ".$param['length'];	
	}
	if( $param['start'] !='' ){
		$search=$search." OFFSET ".$param['start'];
	}

    	$Qry = new Query();	
		$Qry->table     = "tblforms04";
		$Qry->selected  = "*";
		// $Qry->fields    = "id>0 AND idstatus in ('3', '5', '7') ".$search;
		$Qry->fields    = "id>0 AND idstatus!=3 ".$search;
		$rs = $Qry->exe_SELECT($con);
		$recFiltered = getTotalRows($con,$where);

		//
		if(mysqli_num_rows($rs)>= 1){ // if 1 e execute and while if 0 sa else
			$data = array( 
		        "draw"=> $param['draw'],
		        "recordsTotal"=> mysqli_num_rows($rs),
				"recordsFiltered"=> $recFiltered,
				"isApprover"=> $isApprover,
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

				$dateCreated = strtotime($row['datecreated']);
				$SysDate 	 = strtotime($date);
				$timeDiff	 = abs($SysDate - $dateCreated);
				$numberDays  = $timeDiff/86400; //86400 seconds in one day
				// and you might want to convert to integer
				$numberDays  = intval($numberDays);
				
				$data["data"][] = array(
					//ang display sa sa mga information sa table
		            "id"    				=> $row['id'],
					"refferenceno" 			=> $row['refferenceno'],
					"empid" 				=> $row['empid'],
					"empname" 				=> $row['empname'],
					"datecreated"			=> $row['datecreated'],
					"timecreated"			=> $row['timecreated'],
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
		$Qry->table ="tblforms04";
		$Qry->selected ="*";
		$Qry->fields    = "id>0 ".$search;
		$rs = $Qry->exe_SELECT($con);
		return mysqli_num_rows($rs);
	}

	function isApprover($con, $id){
		$Qry=new Query();
		$Qry->table="tblaccount";
		$Qry->selected="id";
		$Qry->fields="id='".$id."'";
		$rs=$Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>=1){
			while($row=mysqli_fetch_array($rs)){
				$QryA=new Query();
				$QryA->table="tblformsetup";
				$QryA->selected="id";
				$QryA->fields="idform='4' AND (approver_1a='".$row['id']."' OR approver_1b='".$row['id']."' OR approver_2a='".$row['id']."' OR approver_2b='".$row['id']."' OR approver_4a='".$row['id']."' OR approver_4b='".$row['id']."' OR approver_5a='".$row['id']."' OR approver_5b='".$row['id']."')";
				$rsA=$QryA->exe_SELECT($con);
				if(mysqli_num_rows($rsA)>=1){
					return true;
				}
			}
			
		}
		return false;
	}

?>