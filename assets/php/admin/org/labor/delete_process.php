<?php
require_once('../../../activation.php');

$param = json_decode(file_get_contents('php://input'));
$conn = new connector();	
if( (int)$param->conn == 1 ){	
	$con = $conn->connect();
}else{
	$varcon = "connect".(int)$param->conn;
	$con = $conn->$varcon();
}
require_once('../../../classPhp.php'); 




if(!empty($param->accountid)){
    if(!empty($param->id) ){
        if(!checkAcctLabor($con, $param->id)){
            $Qry3           = new Query();
            $Qry3->table    = "tbllabortype";
            $Qry3->fields   = "id='".$param->id."'";
            $checke         = $Qry3->exe_DELETE($con);
            if($checke){
                $return = json_encode(array("status"=>"success"));
            }else{
                $return = json_encode(array('status'=>'error'));
            }
        }else{
            $return = json_encode(array('status'=>'notvalid'));
        }
    }else{
        $return = json_encode(array('status'=>'error'));
    }
}else{
    $return = json_encode(array('status'=>'notloggedin'));
}

print $return;
mysqli_close($con);
?>