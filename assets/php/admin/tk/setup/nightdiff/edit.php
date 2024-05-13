<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

if(!empty($param->accountid)){
    $Qry           = new Query();
    $Qry->table    = "tblnightpremium";
    $Qry->selected = "stime='".$param->info->nightin."',
                        checkstime='".$param->info->nextin."',
                        ftime='".$param->info->nextout."',
                        checkftime='".$param->info->nightout."', 
                        auto='".$param->info->auto."'";
    
    $Qry->fields   = "id='".$param->info->id."'";                    
    $checke = $Qry->exe_UPDATE($con);
    
    if($checke){
        $return = json_encode(array("status"=>"success"));
    }else{
        $return = json_encode(array('status'=>'error'));
    }				
		
}else{
	 $return = json_encode(array('status'=>'notloggedin'));
}


print $return;
mysqli_close($con);
?>