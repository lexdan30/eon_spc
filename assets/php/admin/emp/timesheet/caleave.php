<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$return = null;	
$data	 	= array();
$pie_labels	= array();
$pie_data	= array();
$pie_colour = array();

if( empty($param->date) ){
    $param->date =date('Y-m-01');
}

if( !empty( $param->accountid ) ){
	$Qry 			= new Query();	
	$Qry->table     = "tblaccountleaves AS a  LEFT JOIN tblleaves AS b ON a.`idleave` = b.id";
	$Qry->selected  = "a.`idacct`, b.id AS idleave, b.name AS leavename, b.imgicon, b.color, b.alias, ( SELECT COUNT(c.id) FROM  tbltimeleaves AS c WHERE c.cancelby is null AND c.idleave = b.id AND c.idacct = a.`idacct` AND (c.`date` >= '".date("Y-m-01", strtotime($param->date) )."' AND  c.`date` <= '".date('Y-m-t', strtotime($param->date) )."') ) AS ctr";
	$Qry->fields    = "a.`idacct`= '".$param->accountid."' ";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		while($row=mysqli_fetch_array($rs)){
			array_push( $pie_labels, $row['alias'] );
			array_push( $pie_data, $row['ctr'] );
			array_push( $pie_colour, $row['color'] );
		}
	}
	$data = array(
		"lbl" 	=> $pie_labels,
		"ctr"	=> $pie_data,
		"colour"=> $pie_colour,
		"sum"	=> (int)array_sum($pie_data)
	);
	$return =  json_encode($data);
}else{
	$return = json_encode(array('status'=>'error'));
}

$return =  json_encode($data);
print $return;
mysqli_close($con);

?>