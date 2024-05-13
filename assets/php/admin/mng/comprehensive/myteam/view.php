<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

$time  = SysTime();


$dept = getIdUnit($con,$param->accountid);
// print_r($dept);
$ids='0';
//Get Managers Under person
if( !empty( $dept ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$dept);
    if( !empty( $arr["nodechild"] ) ){     
        $ids = join(',', flatten($arr['nodechild']));
    } else {
        $ids = '0';
    }
}

// print_r($ids);

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
// $Qry->selected  = "id, idunit, empname, pic, CONCAT(addr_st,', ',addr_area,', ',addr_city,', ',addr_prov,', ',addr_code) AS home_add , email, pnumber, fnumber, cnumber, emergency_name, emergency_number ";
$Qry->selected  = 
"id, pic, empname, post, email,
pnumber ,fnumber, cnumber, emergency_name, emergency_number,
addr_st, addr_area, addr_city, addr_prov, addr_code";
$Qry->fields    = "id != '".$param->accountid."' AND (idsuperior='".$param->accountid."' or idunit IN (".$ids.")) ORDER BY empname ASC ";

$rs 			= $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){

        $address = '';
		if( !empty( $row['addr_st'] ) ){
			$address = $address .  $row['addr_st'] . ',';
		}
		if( !empty( $row['addr_area'] ) ){
			$address = $address .  $row['addr_area'] . ',';
		}
		if( !empty( $row['addr_city'] ) ){
			$address = $address .  $row['addr_city'] . ',';
		}
		if( !empty( $row['addr_prov'] ) ){
			$address = $address .  $row['addr_prov'] . ',';
		}
		if( !empty( $row['addr_code'] ) ){
			$address = $address .  $row['addr_code'] . ',';
		}
		$address = substr($address,0, strlen($address)-1);

		if( empty($row['pic']) ){
			$row['pic'] = "undefined.webp";
		}
        $data[] = array( 
            // "id"        	    => $row['id'],
            "staff_id"          => $row['id'],
            "pic"			    => $row['pic'],
            "empname" 		    => $row['empname'],
            "post" 		        => $row['post'],
            "home_add" 		    => $address,
            "email" 		    => $row['email'],
            "pnumber" 		    => $row['pnumber'],
            "fnumber" 		    => $row['fnumber'],
            "cnumber" 		    => $row['cnumber'],
            "emergency_name" 	=> $row['emergency_name'],
            "emergency_number" 	=> $row['emergency_number']


			
        );
        $return = json_encode($data);
    }
}else{
    // $return = json_encode(array('status'=>'error'));
    $return = json_encode($data);
}


print $return;
mysqli_close($con);


function getIdUnit($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getIdUnit');
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['idunit'];
    }
    return null;
}



?>