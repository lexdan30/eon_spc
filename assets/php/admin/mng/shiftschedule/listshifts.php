<?php
require_once('../../../logger.php');
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

    $param = json_decode(file_get_contents('php://input'));
    $return = null;

    if(!empty($param->accountid)){
        $data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblshift";
		$Qry->selected  = "id, `name`";
		$Qry->fields    = "id>0 AND isdefault!=2";
		$rs = $Qry->exe_SELECT($con);
		Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"name"		=> $row['name']					
				);
			}
        }
        $return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'error'));
    }

print $return;
mysqli_close($con);

?>