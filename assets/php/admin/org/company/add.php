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


$date=SysDate();
$time=SysTime();

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

	if( array_key_exists('file',$_FILES) ){
		$extMove 		= pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		$save_name	 	= "1.".$extMove;
		$folder_path 	= $param['targetPath'];
		move_uploaded_file($_FILES["file"]["tmp_name"], $folder_path.$save_name);
		$Qry33           = new Query();
		$Qry33->table    = "tblcompany";
		$Qry33->selected = "filename='".$save_name."'";
		$Qry33->fields   = "id=1";                        
		$checke33 		 = $Qry33->exe_UPDATE($con);
	}

	$return = json_encode(array('status'=>'pdf downloaded'));
	
}else{
    $return = json_encode(array('status'=>'error'));
}

print $return;
mysqli_close($con);
?>