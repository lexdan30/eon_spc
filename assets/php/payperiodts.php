<?php
require_once('activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('classPhp.php');

$param = json_decode(file_get_contents('php://input'));
$data = array();	

$Qry=new Query();
$Qry->table="vw_payperiod_all";
$Qry->selected="*";

if( (int)$param->f == 1 ){ //prev
	$Qry->fields="pay_date <= '".$param->pay_date."' ORDER BY pay_date DESC,type LIMIT 1 OFFSET 1";
}elseif( (int)$param->f == 2 ){ //nxt
	$Qry->fields="pay_date >= '".$param->pay_date."' ORDER BY pay_date ASC,type LIMIT 1 OFFSET 1 ";
}


$rs=$Qry->exe_SELECT($con);
//echo $Qry->fields;
if(mysqli_num_rows($rs)>=1){
	while($row=mysqli_fetch_array($rs)){
		$data = array( 
			"id"        => $row['id'],
			"pay_start"	=> $row['period_start'],
			"pay_end"	=> $row['period_end'],
			"pay_date"	=> $row['pay_date'],
			"hascontri" => $row['hascontri'],
			"pay_stat"	=> $row['stat'],
			"type"		=> $row['type']
		);
	}
}

$return = json_encode($data);    
print $return;
mysqli_close($con);
?>