<?php
require_once('../../../activation.php');
$param = $_POST;
$conn = new connector();
if( (int)$param['conn'] == 1 ){	
	$con = $conn->connect();
}else{
	$varcon = "connect".(int)$param['conn'];  
	$con = $conn->$varcon();
	$concorp = $conn->connect();
}

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
	$param['event']['event_title'] 	= ((str_replace("'","",$param['event']['title'])));
	$param['event']['desc'] 		= str_replace("'","",$param['event']['desc']);	
	
	$param['event']['end'] = date('Y-m-d', strtotime("+1 day", strtotime( $param['event']['end'] )));
	
	$Qry3           = new Query();
	$Qry3->table    = "tblcompanyact";
	$Qry3->selected = "isall='".$param['event']['isall']."',event_title='".$param['event']['title']."', efrom='".$param['event']['start']."', eto='".$param['event']['end']."', event_desc='".$param['event']['desc']."' ";
	$Qry3->fields   = "id='".$param['event']['id']."'";                        
	$checke = $Qry3->exe_UPDATE($con);
	if($checke){
		if( array_key_exists('file',$_FILES) ){
			$pix = 1;
			$extMove 		= pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
			$save_name	 	= $param['event']['id'].".".$extMove;
			$folder_path 	= $param['targetPath'];
			move_uploaded_file($_FILES["file"]["tmp_name"], $folder_path.$save_name);
			$path = $folder_path.$save_name;
		}
		$return = json_encode(array('status'=>'success'));

		// email to all employees
		$email = getdummyAllmails($con);
		date("YW", strtotime("2011-01-07"));
		$mailSubject = "HRIS 2.0 - Organizational Data -> Company Activities";
		$mailBody = "<h4>"."Updated Event: ".$param['event']['event_title']."</h4>";
		$mailBody .= 'Date: '.date("F j, Y", strtotime($param['event']['start'])).' to '.date("F j, Y", strtotime($param['event']['end']));
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
	//$Qry->fields    = "email IN ('lexdan30@gmail.com','alexis.curaraton@N-Pax.com')";
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