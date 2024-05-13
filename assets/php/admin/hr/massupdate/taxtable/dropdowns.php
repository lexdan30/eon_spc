<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

    $param = json_decode(file_get_contents('php://input'));
    $return = null;

    if(!empty($param->accountid)){
        $data[] = getPayRevenues($con);
        $data[] = getPayGroups($con);
        $return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'error'));
    }

print $return;
mysqli_close($con);

?>