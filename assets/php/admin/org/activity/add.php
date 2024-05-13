<?php
require_once('../../../activation.php');
$param = $_POST;
$conn = new connector();	

// if( $param['conn'] == 1 ){	
// 	$con = $conn->connect();
// }else{
// 	$varcon = "connect".(int)$param['conn'];
// 	$con = $conn->$varcon();
// 	$concorp = $conn->connect();
// }
$con = $conn->connect();
require_once('../../../classPhp.php'); 
require_once('../../../email/emailFunction.php');

$date=SysDate();
$time=SysTime();
$path = '';

if(!empty($param['accountid'])){
	
	if( array_key_exists('file',$_FILES) ){
		$valid_formats = array("pdf");	
		if ($_FILES['file']['error'] == 4) {
			$return = json_encode(array('status'=>'error','on'=>'img_check'));
			print $return;	
			mysqli_close($con);
			return;
		}
		if ($_FILES['file']['error'] == 0) {
			if(!in_array(pathinfo(strtolower($_FILES['file']['name']), PATHINFO_EXTENSION), $valid_formats) ){
				$return = json_encode(array('status'=>'error-upload-type'));
				print $return;	
				mysqli_close($con);
				return;
			}
		}
	}
	
	$param['event']['event_title'] 	= ((str_replace("'","",$param['event']['event_title'])));
	$param['event']['desc'] 		= str_replace("'","",$param['event']['desc']);	
	
	$Qry3           = new Query();
	$Qry3->table    = "tblcompanyact";
	$Qry3->selected = "isall,date_create,idcomp, event_title, efrom, eto, idcreator, event_desc";
	$Qry3->fields   = "'".$param['event']['isall']."','".$date."','1', '".$param['event']['event_title']."', '".$param['event']['efrom']."', '".date('Y-m-d', strtotime("+1 day", strtotime( $param['event']['eto'] )))."', '".$param['accountid']."', '".$param['event']['desc']."' ";                        
	$checke 		= $Qry3->exe_INSERT($con);
	if($checke){
		$last_id = $con->insert_id;
		$pix = 0;
		if( array_key_exists('file',$_FILES) ){
			$pix = 1;
			$extMove 		= pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
			$save_name	 	= $last_id.".".$extMove;
			$folder_path 	= $param['targetPath'];
			move_uploaded_file($_FILES["file"]["tmp_name"], $folder_path.$save_name);
			$path = $folder_path.$save_name;
			$Qry33           = new Query();
			$Qry33->table    = "tblcompanyact";
			$Qry33->selected = "filename='".$save_name."'";
			$Qry33->fields   = "id='".$last_id."'";                         
			$checke33 		 = $Qry33->exe_UPDATE($con);
		}
		$data = array( 
            "id"        	=> $last_id,
            "title" 		=> $param['event']['event_title'],
            "start" 		=> $param['event']['efrom'],
            "end" 	    	=> date('Y-m-d', strtotime("+1 day", strtotime( $param['event']['eto'] ))),
			"description"	=> strip_tags($param['event']['desc']),
			"isall"			=> $param['event']['isall'],
			"pix"			=> $pix
        );
		$return = json_encode($data);

		// email to all employees
		$email = getdummyAllmails($con);
		date("YW", strtotime("2011-01-07"));
		$mailSubject = "HRIS 2.0 - Organizational Data -> Company Activities";
		$mailBody = "<h4>"."Event: ".$param['event']['event_title']."</h4>";
		$mailBody .= 'Date: '.date("F j, Y", strtotime($param['event']['efrom'])).' to '.date("F j, Y", strtotime($param['event']['eto']));
		$mailBody .= '<br /><br />Details: '.$param['event']['desc'];
		$mailBody .="<br />This is a system generated notification.<br /><br />";
		$stat = _EMAILDIRECT_ACTIVITIES($email,$mailSubject, $mailBody,$idacct='1', $path);
		if($stat){
			$return = json_encode(array('status'=>'success'));
		}else{
			$return = json_encode(array('error'=>'error'));
		}
	}else{
		 $return = json_encode(array('status'=>'error'));
	}
	
}else{
    $return = json_encode(array('status'=>'notloggedin'));
}

print $return;
mysqli_close($con);

function getAllmails($con){
	$data = array();
    $Qry = new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "email";
	$Qry->fields    = "email is not null AND email <> ''";
	 $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
			$data[] = array(
				"email"		=> $row['email']
			);
		}
		return $data;
    }
    return '';
}

function getdummyAllmails($con){
	$data = array();

	$data[] = array(
		"email"		=> 'fenie.ylanan@gmail.com'
	);
	$data[] = array(
		"email"		=> 'alexis.curaraton@gmail.com'
	);
	$data[] = array(
		"email"		=> 'brian.ortiz@gmail.com'
	);
	return $data;
}
?>