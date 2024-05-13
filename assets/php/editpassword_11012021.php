<?php
require_once('activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('classPhp.php');
require_once("/email/emailFunction.php");

	$param = json_decode(file_get_contents('php://input'));
	
	if(!empty($param->id)){	
		$Qry1 = new Query();	
		$Qry1->table = "recruit_accounts";
		$Qry1->selected = "*";
		$Qry1->fields = "id='".$param->id."' AND password='".md5($param->oldpassword)."'";
		$rs1 = $Qry1->exe_SELECT($con);
		if( sqlsrv_num_rows($rs1)>0 ){
			if( empty($param->newpassword) || empty($param->confirmpassword)){
				$return = json_encode(array('status'=>'passwordblank'));
			}else{
				if( md5($param->newpassword) != md5($param->confirmpassword) ){
					$return = json_encode(array('status'=>'passwordnotmatch'));
				}else{
					$Qry = new Query();	
					$Qry->table = "recruit_accounts";
					$Qry->selected ="password='".md5($param->newpassword)."'";
					$Qry->fields = "id='".$param->id."'";
					$check = $Qry->exe_UPDATE($con);
					if( $check ){
						$email = $param->email;
						$lname = $param->lname;
						$fname = $param->fname;						
						$applicant[] = array( $email => $fname.' '.$lname );	
						$mailSubject = "Password Changed ".getCompanyName()." Recruitment System";
						$mailBody ="Dear ".$param->fname." ".$param->lname."<br/><br/>";
						$mailBody .="Your password has been changed, below are your new credentials<br/><br/>";
						$mailBody .="Your Username/Email is: ".$param->email."<br/>";
						$mailBody .="Your Password is: ".$param->newpassword."<br/><br/>";
						//$mailBody .="All the best,<br/>DBIC DEV";							
						$return = _EMAIL($applicant,array(),$mailSubject,$mailBody,null);
					}else{
						$return = json_encode(array('status'=>'error'));
					}					
				}
			}
		}else{
			$return = json_encode(array('status'=>'passwordnotfound'));
		}
	}else{
		$return = json_encode(array('status'=>'error'));
	}
	
print $return;	
sqlsrv_close($con);
?>