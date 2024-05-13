<?php
session_start();
$param  = json_decode(file_get_contents('php://input'));


if($param->db != ''){
    $_SESSION['selectedcomp'] = "2hrisnwmh2";//$param->db;
}else{
    $_SESSION['selectedcomp'] = "2hrisnwmh2";
}

$data = array("status"=> 'success');

 $return =  json_encode($data);
?>