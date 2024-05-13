<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

if( empty($param->dstart) ){
	date_default_timezone_set('Asia/Manila');
	$info 		= getdate();
	$date 		= $info['mday'];
	$month 		= $info['mon'];
	$year 		= $info['year'];
	$date_s 	= $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-01";
}else{
	$arr_s 		= explode("-",$param->dstart);
	$date_s 	= $arr_s[0]."-".str_pad($arr_s[1],2,"0",STR_PAD_LEFT)."-01";
	$year 		= $arr_s[0];
}

$date_e 	= date("Y-m-t", strtotime($date_s));
$data 		= array();
$arr_dates	= array();

$Qry = new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "empid, empname, bdate, post,business_unit";
// if( empty($param->dstart) ){
// 	$Qry->fields    = "bdate is not null ORDER BY DATE_FORMAT(bdate, '%c-%d') asc";
// }else{
	$Qry->fields    = "DAY(bdate) = DAY(NOW()) AND MONTH(bdate) = MONTH(NOW()) ORDER BY DATE_FORMAT(bdate, '%c-%d') asc";
// // }
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
		$d_arr= explode("-", $row['bdate']);
		$bdate= $year."-".$d_arr[1]."-".$d_arr[2];
		$data[] = array( 
            "id" 			    => $row['empid'],
			"title" 			=> $row['empname'],
			"start" 			=> $bdate,
            "description" 		=> 'Birthday',
			"bunit"				=> $row['business_unit'],
            "position" 		    => $row['post']
        );
		// if( !in_array( $bdate, $arr_dates ) ){
		// 	array_push( $arr_dates, $bdate );
		// 	$data[] = array( 
		// 		"title" 			=> '',
		// 		"start" 			=> $bdate,
        //         "description" 		=> 'Birthday',
        //         "position" 		    => $row['post']
		// 	);
		// }
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array('w'=>$Qry->fields));
	
}
print $return;
mysqli_close($con);
?>