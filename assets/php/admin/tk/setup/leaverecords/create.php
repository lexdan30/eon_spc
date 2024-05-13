<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

if(!empty($param->accountid)){
    if(!empty($param->add_acct)){
        if(!empty($param->add_entitlement)){
            if(!empty($param->add_leavetype)){
                
                    if( (int)$param->add_leavetype == 10 || (int)$param->add_leavetype == 12 || (int)$param->add_leavetype == 22 && $param->info->sex == "M" ){
                        $return = json_encode(array('status'=>'leave_male'));
                        print $return;	
                        return;
                    }
                    else if((int)$param->add_leavetype == 5 && $param->info->sex == "F" ){
                        $return = json_encode(array('status'=>'leave_female'));
                        print $return;	
                        mysqli_close($con);
                        return;
                    }else if((int)$param->add_leavetype == 21 && (int)$param->info->civilstat > 1){
                        $return = json_encode(array('status'=>'notsingle'));
                        print $return;	
                        mysqli_close($con);
                        return;
                    }
                
                if( checkLeaveType($con,$param->add_leavetype, $param->add_acct) ){
                    $return = json_encode(array("status"=>"exists1"));
                    print $return;
                    mysqli_close($con);
                    return;
                }


                $Qry3           = new Query();
                $Qry3->table    = "tblaccountleaves";
                $Qry3->selected = "idacct,idleave,entitle";
                $Qry3->fields   = "'".$param->add_acct."',
                                    '".$param->add_leavetype."',
                                    '".$param->add_entitlement."'";
                $checke = $Qry3->exe_INSERT($con);
                if($checke){
                    $return = json_encode(array("status"=>"success"));
                }else{
                    $return = json_encode(array('status'=>'error'));
                }	

            }else{
                    $return = json_encode(array('status'=>'idtype'));
                }
        }else{
            $return = json_encode(array('status'=>'entitle'));
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