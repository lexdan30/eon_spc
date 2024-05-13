<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date=SysDate();
$time=SysTime();
$return = null;	
$data = array();
if( !empty($param->accountid) ){
	$Qry 			= new Query();	
	$Qry->table     = "vw_payperiod_all";
	$Qry->selected  = "*";
	$Qry->fields    = "id>0 ORDER BY pay_date,type DESC";
	$rs				= $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){
		while($row=mysqli_fetch_array($rs)){
			$icon = "fa fa-unlock";
			if( (int)$row['tkstatus'] == 1 ){
				$icon = "fa fa-lock";
			}

			if($row['type'] == 'ho'){
				$row['type'] = 'Local Employee';
			}
			if($row['type'] == 'helper'){
				$row['type'] = 'Helper';
			}
			if($row['type'] == 'hajap'){
				$row['type'] = 'Japanese';
			}
			if($row['type'] == 'hajapc'){
				$row['type'] = 'Japanese Conversion';
			}

			$data[] = array(
				"period_start" 	=> $row['period_start'],
				"period_end"		=> $row['period_end'],
				"remaining_period" 	=> $row['remaining_period'],
				"cutoff_period"		=> $row['cutoff_period'],
				"payroll_period"  	=> $row['payroll_period'],
				"pay_period" 		=> $row['pay_period'],
				"pay_date" 			=> $row['pay_date'],
				"daysno" 			=> $row['daysno'],
				"headno"			=> $row['headno'],
				"dateclosed"  		=> $row['dateclosed'],
				"closedby" 			=> $row['closedby'],
				"stat" 				=> $row['stat'],
				"type" 				=> $row['type'],
				"tkprocess" 		=> $row['tkprocess'],
				"payprocess" 		=> $row['payprocess'],
				"tkstatus" 			=> $row['tkstatus'],
				"icon"				=> $icon
			);
		}
	}
}
$return = json_encode($data);

print $return;
mysqli_close($con);


?>