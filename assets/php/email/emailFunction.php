<?php

function _EMAILDIRECT_LATERALTRANSFER($recipients,$subject,$body,$ticketnumber){
	//return json_encode(array('status'=>'success','sendto'=>$recipients)); //To stop sending email (DELETE THIS AFTER TESTING)


	require_once("class.phpmailer.php");	
	require_once('../../../../activation.php'); 
	$conn = new connector();	
	$con = $conn->connect();
	require_once('../../../../classPhp.php');

	$unique_email = array();	
	foreach($recipients as $recipient)
	{
		foreach($recipient as $email => $name)
		{
			if(!in_array(strtolower($email),$unique_email)){				
				array_push($unique_email,strtolower($email));
			}
		}
	}
	 $id ='';
	$idstatus='';
	$Qry=new Query();
    $Qry->table="tblforms01";
    $Qry->selected="id,idstatus";
    $Qry->fields="refferenceno='".$ticketnumber."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        if($row=mysqli_fetch_array($rs)){
          
			 $id = $row['id'];	
			 $idstatus = $row['idstatus'];	

        }
    }

	foreach($unique_email as $key => $email)
	{

		require("emailSetup.php");
		$mail->ClearAddresses();
		$mail->AddAddress($email,'');
		$mail->Subject = $subject;
		$mail->IsHTML(true);
		$mail->Body = $body;		
		$mail->Body .= "<br />";
		if( !empty($id) && $idstatus==3 ){
			$mail->Body .= "<a href='https://172.19.0.2/eon_spc/#/lateral-transfer---current---?req=".$id."&p=curr'> Click here to Login!</a>";
		}else{
			$mail->Body .= "<a href='https://172.19.0.2/eon_spc/#/login?'> Click here to Login!</a>";
		}
		$mail->Body .= "<br /><br />";
		$mail->Body .="******************************************<br /> ";
		$mail->Body .="<div style='margin-left:50px'>PLEASE DON'T REPLY</div> ";
		$mail->Body .="******************************************<br />";
		//$mail->Send();
	
	}


	return json_encode(array('status'=>'success','sendto'=>$recipients, 'refno'=>"$ticketnumber"));
}

function _EMAILDIRECT_WAGEINCREASE($recipients,$subject,$body,$ticketnumber){
	//return json_encode(array('status'=>'success','sendto'=>$recipients)); //To stop sending email (DELETE THIS AFTER TESTING)


	require_once("class.phpmailer.php");	
	require_once('../../../../activation.php'); 
	$conn = new connector();	
	$con = $conn->connect();
	require_once('../../../../classPhp.php');

	$unique_email = array();	
	foreach($recipients as $recipient)
	{
		foreach($recipient as $email => $name)
		{
			if(!in_array(strtolower($email),$unique_email)){				
				array_push($unique_email,strtolower($email));
			}
		}
	}
	 $id ='';
	$idstatus='';
	$Qry=new Query();
    $Qry->table="tblforms02";
    $Qry->selected="id,idstatus";
    $Qry->fields="refferenceno='".$ticketnumber."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        if($row=mysqli_fetch_array($rs)){
          
			 $id = $row['id'];
			 $idstatus = $row['idstatus'];	

        }
    }

	foreach($unique_email as $key => $email)
	{
		require("emailSetup.php");
		$mail->ClearAddresses();
		$mail->AddAddress($email,'');
		$mail->Subject = $subject;
		$mail->IsHTML(true);
		$mail->Body = $body;		
		$mail->Body .= "<br />";
		if( !empty($id) && $idstatus==3 ){
			$mail->Body .= "<a href='https://172.19.0.2/eon_spc/#/wage-increase---current---?req=".$id."&p=curr'> Click here to Login!</a>";
		}else{
			$mail->Body .= "<a href='https://172.19.0.2/eon_spc/#/login?'> Click here to Login!</a>";
		}
		$mail->Body .= "<br /><br />";
		$mail->Body .="******************************************<br /> ";
		$mail->Body .="<div style='margin-left:50px'>PLEASE DON'T REPLY</div> ";
		$mail->Body .="******************************************<br />";
		//$mail->Send();
	
	}


	return json_encode(array('status'=>'success','sendto'=>$recipients, 'refno'=>"$ticketnumber"));
}

function _EMAILDIRECT_PROMOTION($recipients,$subject,$body,$ticketnumber){
	//return json_encode(array('status'=>'success','sendto'=>$recipients)); //To stop sending email (DELETE THIS AFTER TESTING)


	require_once("class.phpmailer.php");	
	require_once('../../../../activation.php'); 
	$conn = new connector();	
	$con = $conn->connect();
	require_once('../../../../classPhp.php');

	$unique_email = array();	
	foreach($recipients as $recipient)
	{
		foreach($recipient as $email => $name)
		{
			if(!in_array(strtolower($email),$unique_email)){				
				array_push($unique_email,strtolower($email));
			}
		}
	}
	 $id ='';
	$idstatus='';
	$Qry=new Query();
    $Qry->table="tblforms03";
    $Qry->selected="id,idstatus";
    $Qry->fields="refferenceno='".$ticketnumber."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        if($row=mysqli_fetch_array($rs)){
          
			 $id = $row['id'];	
			 $idstatus = $row['idstatus'];

        }
    }

	foreach($unique_email as $key => $email)
	{
		require("emailSetup.php");
		$mail->ClearAddresses();
		$mail->AddAddress($email,'');
		$mail->Subject = $subject;
		$mail->IsHTML(true);
		$mail->Body = $body;		
		$mail->Body .= "<br />";
		if( !empty($id) && $idstatus==3 ){
			$mail->Body .= "<a href='https://172.19.0.2/eon_spc/#/promotion-and-upgradation---current---?req=".$id."&p=curr'> Click here to Login!</a>";
		}else{
			$mail->Body .= "<a href='https://172.19.0.2/eon_spc/#/login?'> Click here to Login!</a>";
		}
		$mail->Body .= "<br /><br />";
		$mail->Body .="******************************************<br /> ";
		$mail->Body .="<div style='margin-left:50px'>PLEASE DON'T REPLY</div> ";
		$mail->Body .="******************************************<br />";
		//$mail->Send();
	
	}


	return json_encode(array('status'=>'success','sendto'=>$recipients, 'refno'=>"$ticketnumber"));
}
function _EMAILDIRECT_CASHADVANCE($recipients,$subject,$body,$ticketnumber){
	//return json_encode(array('status'=>'success','sendto'=>$recipients)); //To stop sending email (DELETE THIS AFTER TESTING)


	require_once("class.phpmailer.php");
	require_once('../../../activation.php');
	$conn = new connector();
	$con = $conn->connect();
	require_once('../../../classPhp.php');

	$unique_email = array();
	foreach($recipients as $recipient){
		foreach($recipient as $email => $name){
			if(!in_array(strtolower($email),$unique_email)){
				array_push($unique_email,strtolower($email));
			}
		}
	}
	 $id ='';
	$idstatus='';
	$Qry=new Query();
	$Qry->table="tblforms04";
	$Qry->selected="id,idstatus";
	$Qry->fields="refferenceno='".$ticketnumber."'";
	$rs=$Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){
		if($row=mysqli_fetch_array($rs)){
			$id = $row['id'];
			$idstatus = $row['idstatus'];
		}
	}

	foreach($unique_email as $key => $email){
		require("emailSetup.php");
		$mail->ClearAddresses();
		$mail->AddAddress($email,'');
		$mail->Subject = $subject;
		$mail->IsHTML(true);
		$mail->Body = $body;
		$mail->Body .= "<br />";
		if( !empty($id) && $idstatus==3 ){
			$mail->Body .= "<a href='https://172.19.0.2/eon_spc/#/admin/emp/app/cashadvance-current?req=".$id."&p=curr'> Click here to Login!</a>";
		}else{
			$mail->Body .= "<a href='https://172.19.0.2/eon_spc/#/login?'> Click here to Login!</a>";
		}
		$mail->Body .= "<br /><br />";
		$mail->Body .="******************************************<br /> ";
		$mail->Body .="<div style='margin-left:50px'>PLEASE DON'T REPLY</div> ";
		$mail->Body .="******************************************<br />";
		//$mail->Send();
	}


	return json_encode(array('status'=>'success','sendto'=>$recipients, 'refno'=>"$ticketnumber"));
}

function _EMAILDIRECT_ENDCONTRACT($email,$subject,$body,$idacct){
	require_once("class.phpmailer.php");	
	require_once('../../../activation.php'); 
	$conn = new connector();	
	$con = $conn->connect(); 
	require_once('../../../classPhp.php');

	$id ='';
	$emailstatus='';
	$Qry=new Query();
    $Qry->table="tblaccount";
    $Qry->selected="id,email_endcontract";
    $Qry->fields="empid='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        if($row=mysqli_fetch_array($rs)){
			 $id = $row['id'];
			 $emailstatus = $row['email_endcontract'];	
        }
	}
	
	if($emailstatus=='0'){
		require("emailSetup.php"); 
		$mail->AddAddress($email,'');
		$mail->Subject = $subject;
		$mail->IsHTML(true);
		$mail->Body = $body;		
		$mail->Body .= "<br />";
		if( !empty($id) && $emailstatus=='0' ){
			$mail->Body .= "<a href='http://172.19.0.2/eon_spc/#/admin/hr/dashboard'> Click here to Login!</a>";
		}else{
			$mail->Body .= "<a href='http://172.19.0.2/eon_spc/#/login?'> Click here to Login!</a>";
		}
		$mail->Body .= "<br /><br />";
		$mail->Body .="******************************************<br /> ";
		$mail->Body .="<div style='margin-left:40px'>PLEASE DON'T REPLY</div> ";
		$mail->Body .="******************************************<br />";
		//$mail->Send();

		$Qry2           = new Query();
		$Qry2->table    = "tblaccount";
		$Qry2->selected = "email_endcontract ='1'";
		$Qry2->fields   = "empid='".$idacct."'";                     
		$checke2 = $Qry2->exe_UPDATE($con);
		if($checke2){
			return json_encode(array('status'=>'success','sendto'=>$email, 'refno'=>"$idacct"));
		}else{
			return json_encode(array('status'=>'error'));
		}
	}
	return json_encode(array('status'=>'error'));
}

function _EMAILDIRECT_CERTIFICATES($email,$subject,$body,$idacct,$path){
	require_once("class.phpmailer.php");	
	require_once('../activation.php'); 
	$conn = new connector();	
	$con = $conn->connect();
	require_once('../classPhp.php');

	require("emailSetup.php"); 
	$mail->AddAddress($email,'');
	$mail->Subject = $subject;
	$mail->IsHTML(true);
	$mail->Body = $body;		
	$mail->Body .= "<br />";
	if(!empty($idacct)){
		$mail->Body .= "<a href='http://172.19.0.2/eon_spc/#/admin/hr/cln'> Click here to Login!</a>";
	}else{
		$mail->Body .= "<a href='http://172.19.0.2/eon_spc/#/login?'> Click here to Login!</a>";
	}
	$mail->Body .= "<br /><br />";
	$mail->Body .="******************************************<br /> ";
	$mail->Body .="<div style='margin-left:40px'>PLEASE DON'T REPLY</div> ";
	$mail->Body .="******************************************<br />";
	$mail->AddAttachment($path); 
	//$stat = $mail->Send();

	if($stat){
		return true;
	}else{
		return false;
	}
}

function _EMAILDIRECT_ACTIVITIES($email,$subject,$body,$idacct,$path){
	require_once("class.phpmailer.php");	
	require_once('../../../activation.php'); 
	$conn = new connector();	
	$con = $conn->connect();
	require_once('../../../classPhp.php');

	require("emailSetup.php"); 
	//$unique_lv = array();

	foreach($email as $key => $emails){
		$mail->ClearAddresses();
		$mail->AddAddress($emails['email'],'');
		$mail->Subject = $subject;
		$mail->IsHTML(true);
		$mail->Body = $body;		
		$mail->Body .= "<br />";
		if(!empty($idacct)){
			$mail->Body .= "<a href='http://192.168.2.50/eon_spc/#/admin/org/activities'> Click here to Login!</a>";
		}else{
			$mail->Body .= "<a href='http://192.168.2.50/eon_spc/#/login?'> Click here to Login!</a>";
		}
		$mail->Body .= "<br /><br />";
		$mail->Body .="******************************************<br /> ";
		$mail->Body .="<div style='margin-left:40px'>PLEASE DON'T REPLY</div> ";
		$mail->Body .="******************************************<br />";
		$mail->AddAttachment($path); 
		//$stat = $mail->Send();
	}
	
	//print_r($unique_lv);
	if($stat){
		return true;
	}else{
		return false;
	}
}

function _EMAILDIRECT_RESETPASSWORD($email,$subject,$body,$idacct){
	require_once("class.phpmailer.php");	
	require_once('../activation.php'); 
	$conn = new connector();	
	$con = $conn->connect();
	require_once('../classPhp.php');

	require("emailSetup.php"); 
	$mail->AddAddress($email,'');
	$mail->Subject = $subject;
	$mail->IsHTML(true);
	$mail->Body = $body;		
	$mail->Body .= "<br />";
	if(!empty($idacct)){
		$mail->Body .= "<a href='http://172.19.0.2/eon_spc/#/login?'> Click here to Login!</a>";
	}else{
		$mail->Body .= "<a href='http://172.19.0.2/eon_spc/#/login?'> Click here to Login!</a>";
	}
	$mail->Body .= "<br /><br />";
	$mail->Body .="******************************************<br /> ";
	$mail->Body .="<div style='margin-left:40px'>PLEASE DON'T REPLY</div> ";
	$mail->Body .="******************************************<br />";
	//$stat = $mail->Send();

	if($stat){
		return true;
	}else{
		return false;
	}
}

function _EMAILDIRECT_TEMP($email,$subject,$body,$idacct){
	require_once("class.phpmailer.php");	
	require_once('../../../activation.php'); 
	$conn = new connector();	
	$con = $conn->connect();
	require_once('../../../classPhp.php');

	$id = $idacct;
	require("emailSetup.php"); 
	$mail->AddAddress($email,'');
	$mail->Subject = $subject;
	$mail->IsHTML(true);
	$mail->Body = $body;		
	$mail->Body .= "<br />";
	if( !empty($id)){
		$mail->Body .= "<a href='http://172.19.0.2/eon_spc/#/admin/hr/dashboard'> Click here to Login!</a>";
	}else{
		$mail->Body .= "<a href='http://172.19.0.2/eon_spc/#/login?'> Click here to Login!</a>";
	}
	$mail->Body .= "<br /><br />";
	$mail->Body .="******************************************<br /> ";
	$mail->Body .="<div style='margin-left:40px'>PLEASE DON'T REPLY</div> ";
	$mail->Body .="******************************************<br />";
	//$mail->Send();
}

function _EMAILDIRECT_TEMPTOMANY($email,$subject,$body,$idacct){
	require_once("class.phpmailer.php");	
	require_once('../../../activation.php'); 
	$conn = new connector();	
	$con = $conn->connect();
	require_once('../../../classPhp.php');

	$id = $idacct;
	require("emailSetup.php"); 

	foreach($email as $key => $emails){
		$mail->ClearAddresses();
		$mail->AddAddress($emails['email'],'');
		$mail->Subject = $subject;
		$mail->IsHTML(true);
		$mail->Body = $body;		
		$mail->Body .= "<br />";
		if( !empty($id)){
			$mail->Body .= "<a href='http://172.19.0.2/eon_spc/#/admin/hr/dashboard'> Click here to Login!</a>";
		}else{
			$mail->Body .= "<a href='http://172.19.0.2/eon_spc/#/login?'> Click here to Login!</a>";
		}
		$mail->Body .= "<br /><br />";
		$mail->Body .="******************************************<br /> ";
		$mail->Body .="<div style='margin-left:40px'>PLEASE DON'T REPLY</div> ";
		$mail->Body .="******************************************<br />";
		//$mail->Send(); 
	}
}

function _EMAILSALARY_PROFILE($email,$subject,$body,$idacct){
	//$email= 'alexis.curaraton@N-Pax.com'; 
	//$email= 'brian.ortiz@N-Pax.com';
	$email='cris.bacaycay@N-Pax.com';
	require_once("class.phpmailer.php");	
	require_once('../../../activation.php'); 
	
	$conn = new connector();	
	$con = $conn->connect();

	require_once('../../../classPhp.php');

	require("emailSetup.php"); 
	$mail->AddAddress($email,'');
	$mail->Subject = $subject;
	$mail->IsHTML(true);
	$mail->Body = $body;		
	$mail->Body .= "<br />";
	if(!empty($idacct)){
		$mail->Body .= "<a href='http://172.19.0.2/eon_spc/#/admin/hr/employees'> Click here to Login!</a>";
	}else{
		$mail->Body .= "<a href='http://172.19.0.2/eon_spc/#/login?'> Click here to Login!</a>";
	}
	$mail->Body .= "<br /><br />";
	$mail->Body .="******************************************<br /> ";
	$mail->Body .="<div style='margin-left:40px'>PLEASE DON'T REPLY</div> ";
	$mail->Body .="******************************************<br />";
	//$stat = $mail->Send();

	// if($stat){
	// 	return true;
	// }else{
	// 	return false;
	// }
}

function _EMAILDIRECT_APPLICATION($email,$subject,$body,$idacct){
	$email= 'alexis.curaraton@N-Pax.com';
	//$email= 'brian.ortiz@N-Pax.com';
	require_once("class.phpmailer.php");	
	require_once('../../../../activation.php'); 
	
	$conn = new connector();	
	$con = $conn->connect();

	require_once('../../../../classPhp.php');
	$email= 'alexis.curaraton@N-Pax.com';
	require("emailSetup.php"); 
	$mail->AddAddress($email,'');
	$mail->Subject = $subject;
	$mail->IsHTML(true);
	$mail->Body = $body;		
	$mail->Body .= "<br />";
	if(!empty($idacct)){
		$mail->Body .= "<a href='http://192.168.2.50/mph/#/admin/emp/timekeeping2'> Click here to Login!</a>";
	}else{
		$mail->Body .= "<a href='https://192.168.2.50/mph/#/login?'> Click here to Login!</a>";
	}
	$mail->Body .= "<br /><br />";
	$mail->Body .="******************************************<br /> ";
	$mail->Body .="<div style='margin-left:40px'>PLEASE DON'T REPLY</div> ";
	$mail->Body .="******************************************<br />";
	// $stat = $mail->Send();

	// if($stat){
	// 	return true;
	// }else{
	// 	return false;
	// }
}

function _EMAILDIRECT_APPLICATIONAPPR($email,$subject,$body,$idacct){
	$email= 'alexis.curaraton@N-Pax.com';
	//$email= 'brian.ortiz@N-Pax.com';
	
	require_once("class.phpmailer.php");	
	require_once('../../../activation.php'); 
	
	$conn = new connector();	
	$con = $conn->connect();

	require_once('../../../classPhp.php');

	require("emailSetup.php"); 
	$mail->AddAddress($email,'');
	$mail->Subject = $subject;
	$mail->IsHTML(true);
	$mail->Body = $body;		
	$mail->Body .= "<br />";
	if(!empty($idacct)){
		$mail->Body .= "<a href='http://192.168.2.50/mph/#/admin/emp/timekeeping2'> Click here to Login!</a>";
	}else{
		$mail->Body .= "<a href='https://192.168.2.50/mph/#/login?'> Click here to Login!</a>";
	}
	$mail->Body .= "<br /><br />";
	$mail->Body .="******************************************<br /> ";
	$mail->Body .="<div style='margin-left:40px'>PLEASE DON'T REPLY</div> ";
	$mail->Body .="******************************************<br />";
	// $stat = $mail->Send();

	// if($stat){
	// 	return true;
	// }else{
	// 	return false;
	// }
}

function _EMAILDIRECT_CONTROLLINE($email,$subject,$body,$idacct){
	$idacct = '';  
	require_once("class.phpmailer.php");	
	require_once('../../activation.php'); 
	$conn = new connector();	
	$con = $conn->connect(); 
	require_once('../../classPhp.php');
	
	require("emailSetup.php"); 
	$mail->AddAddress($email,'');
	$mail->Subject = $subject;
	$mail->IsHTML(true);
	$mail->Body = $body;		
	$mail->Body .= "<br />";
	if( !empty($idacct)){
		$mail->Body .= "<a href='http://192.168.1.110:81/eon_spc/#/realtime/andon'> Click here to Login!</a>";
	}else{
		$mail->Body .= "<a href='http://192.168.1.110:81/eon_spc/#/'> Click here to Login!</a>";
	}
	$mail->Body .= "<br /><br />";
	$mail->Body .="******************************************<br /> ";
	$mail->Body .="<div style='margin-left:40px'>PLEASE DON'T REPLY</div> ";
	$mail->Body .="******************************************<br />";
	$stat = $mail->Send(); 
	if($stat){
		return true;
	}else{
		return false;
	}
}


?>