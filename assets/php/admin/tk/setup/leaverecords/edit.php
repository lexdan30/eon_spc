<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));

if(!empty($param->accountid)){
    if(!empty($param->info->add_acct)){


        $Qry3           = new Query();
        $Qry3->table    = "tblaccountleaves AS al LEFT JOIN vw_dataemployees AS de ON al.idacct = de.id LEFT JOIN tblleaves AS tl ON al.idleave = tl.id";
        $Qry3->selected = "al.idacct='".$param->info->idacct."' ";
        // $Qry3->selected = "name='".$param->info->name."',date='".$param->info->date."',idtype='".$param->info->idtype."'";
        $Qry3->fields   = "id='".$param->info->id."'";
        $checke = $Qry3->exe_UPDATE($con);
        if($checke){
            $return = json_encode(array("status"=>"success"));
        }else{
            $return = json_encode(array('status'=>'error'));
        }	




    }else{
		$return = json_encode(array('status'=>'name'));
	}


}else{
    $return = json_encode(array('status'=>'notloggedin'));
}





print $return;
mysqli_close($con);

?>