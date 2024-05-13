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
$Qry->table     = "tblaccountplan as a";
$Qry->selected  = "a.id, a.idcreator, a.event_title,a.efrom,a.eto,a.type, a.canview";
$Qry->fields    = "find_in_set('".$param->accountid."',a.canview) AND ((MONTH(a.efrom) = MONTH(CURRENT_DATE())
AND YEAR(a.efrom) = YEAR(CURRENT_DATE())) OR (MONTH(a.eto) = MONTH(CURRENT_DATE())
AND YEAR(a.eto) = YEAR(CURRENT_DATE())))";


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $id = $row['id'];
        $title = $row['event_title'];
        $start= $row['efrom'];
        $end = $row['eto'];

        $data[] = array( 
            "id" 			    => $id,
            "title" 			=> $title,
            "start" 			=> $start,
            "end" 		    	=> $end,
            "backgroundColor"   => $row['type'],
            "idcreator"         => $row['idcreator'],
            "canview"           => $row['canview']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);
?>