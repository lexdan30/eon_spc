<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data = array();

if( !empty( $param->accountid ) ){
	if( !empty( $param->idacct ) ){
		if( !empty( $param->workdate ) ){			
			$Qry = new Query();	
			$Qry->table     = "vw_data_timesheet";
			$Qry->selected  = "*";
			$Qry->fields    = "empID = '".$param->idacct."' AND work_date = '".$param->workdate."' ";
			$rs = $Qry->exe_SELECT($con);
			if(mysqli_num_rows($rs)>= 1){					
				if($row=mysqli_fetch_array($rs, MYSQLI_ASSOC)){					
					$data = $row;
				}
				$return = json_encode($data);				
			}else{
				$return = json_encode(array('status'=>'empty'));
			}
		}else{
			$return = json_encode(array('status'=>'workdate'));
		}
	}else{
		$return = json_encode(array('status'=>'idacct'));
	}
}else{
	$return = json_encode(array('status'=>'notloggedin'));
}
        
$return = json_encode($data);
print $return;
mysqli_close($con);
?>