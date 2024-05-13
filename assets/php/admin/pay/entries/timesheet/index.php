<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$data = array( 
    "draw"=> $param['draw'],
    "data"=>array()
);


$data["data"][] = array(
    'id' => 1,
    'name' => 2,
    'orgunit' => 3,
    'joblvl' => 4,
    'reghrs' => 5,
    'abs' => 6,
    'late' => 7,
    'und' => 8,
    'adv' => 9,
    'rwdot' =>10 ,
    'rwdot8' => 11,
    'shwdot' => 12,
    'shwdnp' => 13,
    'lhbp' => 14,
    'lhwdot' => 15,
    'lhwdot8' => 16,
    'rdot' => 17,
    'rdot8' => 18,
    'np10610' => 19,
    'lhrdot' => 20,
    'lhrdot8' => 21,
    'shrdot' => 22,
    'shrdot8' => 23,
    'shrdnp' => 24,
    'rwdotnp' => 25,
    'lhwdnp' => 26,
    'lhrdnp' => 27,
    'rdnp' => 28,
    'shwdot' => 29,
    'ma' => 30,
    'adate' =>31 ,
    'pdate' => 32,
    'remarks' => 34
);


$return =  json_encode($data);
print $return;
mysqli_close($con);
?>