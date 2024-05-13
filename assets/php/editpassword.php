<?php
require_once('activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('classPhp.php');

	$param = json_decode(file_get_contents('php://input'));
	
	if(!empty($param->oldpassword)){
		if(!empty($param->newpassword) && !empty($param->confirmpassword)){	
			if($param->oldpassword != md5($param->confirm_oldpassword)){
				$return = json_encode(array('status'=>'oldpassworddidnotmatch'));	
			}

			else if($param->newpassword!=$param->confirmpassword){
				$return = json_encode(array('status'=>'passwordnotmatch'));	
			}
			else{
				$Qry = new Query();	
				$Qry->table ="tblaccount";
				$Qry->selected ="password='".md5($param->newpassword)."' , pw_flag='0', pw_resetdate= '".Sysdate()."'";
				$Qry->fields ="id='".$param->id."'";
				$check = $Qry->exe_UPDATE($con);
				if($check){
					$return = json_encode(array('status'=>'success'));	
					
				}else{
					$return = json_encode(array('status'=>'failed'));
				}
			}
		}
		else{
			$return = json_encode(array('status'=>'required'));		
		}
	}else{
		$return = json_encode(array('status'=>'error'));
	}
	print $return;
	
mysqli_close($con);
?>