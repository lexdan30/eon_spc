<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

    // $param 	= $_POST;
    $param = json_decode(file_get_contents('php://input'));
    $date 	= SysDate();
    $time 	= SysTime();
    $return = null;


    if(!empty($param->accountid)){

            $Qry 			= new Query();	
            $Qry->table 	= "tblchangereq";  
            $Qry->selected 	= "id_status = 2";
            $Qry->fields 	= "id = '".$param->approve->id."'";
            $checkentry 	= $Qry->exe_UPDATE($con); 

            $return = json_encode(array("status"=>"success"));

    }else{
        $return = json_encode(array('status'=>'error'));
    }

print $return;
mysqli_close($con);


?>