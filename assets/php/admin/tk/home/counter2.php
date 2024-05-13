<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$aa		= array(
			"pending"  => getCounts($con,$param,3,"vw_attendance_application"),
			"approved" => getCounts($con,$param,1,"vw_attendance_application"),
			"declined" => getCounts($con,$param,2,"vw_attendance_application"),
			"canceled" => getCounts($con,$param,4,"vw_attendance_application")
		  );

$ot		= array(
			"pending"  => getCounts($con,$param,3,"vw_overtime_application"),
			"approved" => getCounts($con,$param,1,"vw_overtime_application"),
			"declined" => getCounts($con,$param,2,"vw_overtime_application"),
			"canceled" => getCounts($con,$param,4,"vw_overtime_application")
		  );

$data = array( 
	"aa"				=> $aa,
	"aa_total"			=> $aa["pending"] + $aa["approved"] + $aa["declined"] + $aa["canceled"],
	"ot"				=> $ot,
	"ot_total"			=> $ot["pending"] + $ot["approved"] + $ot["declined"] + $ot["canceled"],
	"df"				=> $param->start_date,
	"dt"				=> $param->end_date
);
$return = json_encode($data);
print $return;
mysqli_close($con);

function getCounts($con,$param,$stat,$tbl){
	$Qry = new Query();	
	$Qry->table     = $tbl;
	$Qry->selected  = "count(id) as ctr";
	$Qry->fields    = "stat='".$stat."' AND date BETWEEN '".$param->start_date."' AND '".$param->end_date."' ";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			return (int)$row['ctr'];
		}
	}else{
		return 0;
	}
}

?>