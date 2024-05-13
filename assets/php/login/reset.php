<?php
require_once('../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../classPhp.php'); 
require_once('../email/emailFunction.php');

$param = json_decode(file_get_contents('php://input'));

if( !empty( $param->uname ) ){
	if( !empty( $param->sss ) ){		
		$Qry = new Query();	
		$Qry->table     = "vw_dataemployees";
		$Qry->selected  = "*";
		$Qry->fields    = "etypeid='1' AND username='".$param->uname."' AND idsss='".$param->sss."' ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){	
				$data = array(
					"status"		=> "success"
                );
                $email = $row['email'];
                if(!empty($email)){
                    $newpass = newPass($con);
                    $Qry2 = new Query();	
                    $Qry2->table ="tblaccount";
                    $Qry2->selected ="password='".md5($newpass)."'";
                    $Qry2->fields ="id='".$row['id']."'";
                    $check = $Qry2->exe_UPDATE($con);
                    if($check){
                        $mailSubject = "HRIS 2.0 - Reset Password";
                        $mailBody = "<h4>New Password:  <span style='color:#007FFF'><i>".$newpass."</i></span></h4>";
                        $mailBody .="<br />This is a system generated notification.<br /><br />";
                        $stat = _EMAILDIRECT_RESETPASSWORD($email,$mailSubject, $mailBody,$idacct='1');
                        if($stat){
                            $return = json_encode(array('status'=>'success','sendto'=>$email));
                        }else{
                            $return = json_encode(array('error'=>'error'));
                        }
                    }else{
                        $return = json_encode(array('status'=>'error'));
                    }
                }else{
                    $return = json_encode(array("status"=>"noemail"));
                }
			}
		}else{
			$return = json_encode(array("status"=>"notfound"));
		}
	}else{
		$return = json_encode(array("status"=>"nosss"));
	}	
}else{
	$return = json_encode(array("status"=>"nouname"));
}

print $return;
mysqli_close($con);

function newPass($con){
    $length = 8;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>