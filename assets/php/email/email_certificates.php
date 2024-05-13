<?php
require_once('../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../classPhp.php');
require_once('emailFunction.php');
    $param = json_decode(file_get_contents('php://input'));
    $email = $param->email;

    $path = 'emailsent/'.$param->path;

    if ( save_html_to_file($param->doc,$path) ){
        $mailSubject = "HRIS 2.0 - Certificates, Letter & Notices";
        $mailBody = "<h4>Certificates, Letter & Notices</h4>";
        $mailBody .= $param->doc;
        $mailBody .="<br />This is a system generated notification.<br /><br />";
        $stat = _EMAILDIRECT_CERTIFICATES($email,$mailSubject, $mailBody,$idacct='1', $path);
        if($stat){
            $return = json_encode(array('status'=>'success','sendto'=>getEmpmail($con,$email)));
        }else{
            $return = json_encode(array('error'=>'error'));
        }
    }else {
        $return = json_encode(array('error'=>'error'));
    }

print $return;
mysqli_close($con);

function save_html_to_file($content, $path){
    return (bool) file_put_contents($path, $content);
}

function getEmpmail($con, $email){
    $Qry = new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "empname";
    $Qry->fields    = "email='".$email."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){					
            return $row['empname'];
        }
    }
    return '';
}
?>