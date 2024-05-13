<?php 

// session_start();
// $auth = isset($_SESSION['isAuth']) ? $_SESSION['isAuth']: false;
// if($auth){

 	require_once('../../../activation.php');
    require_once('../../../classPhp.php');
    $conn = new connector();
	$con = $conn->connect();
	
	$date=SysDate();
	$param = json_decode(file_get_contents('php://input'));
	if( !empty($param->accountid)){

    $search='';

	 if( !empty( $param->empid ) ){ $search=$search." AND empid like 	'%".$param->empid."%' "; }

		//Search Department
		if( !empty( $param->address ) ){
			$arr_id = array();
			$arr 	= getHierarchy($con,$param->address);
			array_push( $arr_id, $param->address );
			if( !empty( $arr["nodechild"] ) ){
				$a = getChildNode($arr_id, $arr["nodechild"]);
				if( !empty($a) ){
					foreach( $a as $v ){
						array_push( $arr_id, $v );
					}
				}
			}
			if( count($arr_id) == 1 ){
				$ids 			= $arr_id[0];
			}else{
				$ids 			= implode(",",$arr_id);
			}
			$search.=" AND idunit in (".$ids.") "; 
		}

	 if( !empty($param->contact)){ $search=$search. "AND cnumber like '%".$param->contact."%' "; }


	 

	$Qry = new Query();	
	$Qry->table     = "vw_dataemployees";
	$Qry->selected  = "*";
	$Qry->fields    = "id>0 AND id!=1".$search." ORDER BY empname ASC";
	$rs = $Qry->exe_SELECT($con);
	$where = $Qry->fields;	
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_array($rs)){
			
			
			

            $data[] = array(
							//   "" => $row['id'],
							  "EMPLOYEE ID" 	=> $row['empid'],
							  "EMPLOYEE NAME"   => ucwords(strtolower($row['empname'])),
							  "ADDRESS"  		=> str_replace("#","No. ",$row['addr_st']), 
							  "CONTACT NUMBER" =>  $row['cnumber'],
                             

                    );
		}
		
		$return = json_encode($data);
	}else{
		$return = json_encode(array('status'=>'empty'));
	}

print $return;	
mysqli_close($con);
}

?>