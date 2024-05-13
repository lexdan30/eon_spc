<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 
require_once('../../../../email/emailFunction.php');

session_start();
$param = $_POST;
$random = $param['random'];

if( array_key_exists('file',$_FILES) ){
    $valid_formats = array("jpg", "png", "jpeg", "pdf");	
    foreach ($_FILES['file']['name'] as $f => $name) { 
        if ($_FILES['file']['error'][$f] == 4) {
            $return = json_encode(array('status'=>'error','on'=>'img_check'));
            $Qry = new Query();	
                $Qry->table ="tbltimeobtrip";	
                $Qry->selected ="file=NULL";
                $Qry->fields ="`file` NOT LIKE 'req%'";
                $checked = $Qry->exe_UPDATE($con);
            print $return;	
            mysqli_close($con);
            return;
        }
        if ($_FILES['file']['error'][$f] == 0) {
            if(!in_array(pathinfo(strtolower($name), PATHINFO_EXTENSION), $valid_formats) ){
                $return = json_encode(array('status'=>'error-upload-type'));
                $Qry = new Query();	
                $Qry->table ="tbltimeobtrip";	
                $Qry->selected ="file=NULL";
                $Qry->fields ="`file` NOT LIKE 'req%'";
                $checked = $Qry->exe_UPDATE($con);
                print $return;	
                mysqli_close($con);
                return;
            }
        }
    }
}

$fileuploads = 0;
$arrayofIDs = getRandomIDs($con,$random);

foreach( $arrayofIDs  as $keys=>$value ){
    $lastID = $value['id'];
    if( array_key_exists('file',$_FILES) ){
        if($fileuploads != 0){
            $folder = $_SESSION['foldername'];
            updateData($con,$folder,$lastID);
        }else{
            $folder_path = $param['targetPath'].'req-'.$lastID;
            $folder_name = 'req-'.$lastID;
            if( !is_dir($folder_path) ){
                mkdir($folder_path);				
            }
            $_SESSION['foldername'] = $folder_name;	
            $check = updateData($con,$folder_name,$lastID);
            if( $check ){																	
                $fileCtr=1;
                foreach ($_FILES['file']['name'] as $f => $name) {
                    $t=strtotime($date_create).time();	
                    $extMove = pathinfo($name, PATHINFO_EXTENSION);
                    $save_name	= $fileCtr.'-'.$t.'.'.$extMove;	
                    move_uploaded_file($_FILES["file"]["tmp_name"][$f], $folder_path.'/'.$save_name);
                    $fileCtr++;
                }																	
            }	
        }									
    }
    $fileuploads++;
}

$Qry = new Query();	
$Qry->table ="tbltimeobtrip";	
$Qry->selected ="file=NULL";
$Qry->fields ="`file` NOT LIKE 'req%'";
$checked = $Qry->exe_UPDATE($con);
if($checked){
    $return = json_encode(array('status'=>'success'));
}else{
    $return = json_encode(array('status'=>'no deletion'));
}

print $return;
mysqli_close($con);

function updateData($con,$folder_name,$lastID){	
	$Qry = new Query();	
	$Qry->table ="tbltimeobtrip";	
	$Qry->selected ="file='".$folder_name."'";
	$Qry->fields ="id='".$lastID."'";
	return $Qry->exe_UPDATE($con);
}

function getRandomIDs($con,$random){
    $data = array();
    $Qry = new Query();	
	$Qry->table ="tbltimeobtrip";	
	$Qry->selected ="id";
	$Qry->fields ="file='".$random."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $data[] = array("id"    => $row['id']);
        }
    }
    return $data;
}
?>