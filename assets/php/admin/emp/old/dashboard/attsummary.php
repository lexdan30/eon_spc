<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param 		= json_decode(file_get_contents('php://input'));
$curr_date 	= SysDatePadLeft();
$data		= array();

if(!empty($param->accountid)){
	
	$Qry 			= new Query();	
	$Qry->table     = "vw_data_timesheet";
	$Qry->selected  = "IFNULL(SUM(reghrs),0) AS tot_reg, IFNULL(SUM(late),0) AS tot_late, IFNULL(SUM(absent),0) AS tot_absent, IFNULL(SUM(leavehrs),0) AS tot_leave";
	$Qry->fields    = "work_date BETWEEN '".$param->period_start."' AND '".$param->period_end."' ORDER BY work_date ASC";
	$rs 			= $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			
			$absent_hrs = floatval( number_format($row['tot_absent'],2) );
			if( $absent_hrs > (int)$absent_hrs ){
				$absent_hrs	= number_format($absent_hrs,2);
			}
			
			$reg_hrs	= floatval( number_format($row['tot_reg'],2) );
			if( $reg_hrs > (int)$reg_hrs ){
				$reg_hrs	= number_format($reg_hrs,2);
			}
			
			$late_hrs	= floatval( number_format($row['tot_late'],2) );
			if( $late_hrs > (int)$late_hrs ){
				$late_hrs	= number_format($late_hrs,2);
			}
			
			$leave_hrs	= floatval( number_format($row['tot_leave'],2) );
			if( $leave_hrs > (int)$leave_hrs ){
				$leave_hrs	= number_format($leave_hrs,2);
			}
			
			$reg_day = floatval( $reg_hrs ) / 8;
			if( $reg_day > (int)$reg_day ){
				$reg_day	= number_format($reg_day,2);
			}
			
			$late_mins = floatval( $late_hrs) * 60;
			if( $late_mins > (int)$late_mins ){
				$late_mins	= number_format($late_mins,2);
			}
			
			$absent_day = floatval( $absent_hrs ) / 8;
			if( $absent_day > (int)$absent_day ){
				$absent_day	= number_format($absent_day,2);
			}
			
			$leave_day = floatval( $leave_hrs ) / 8;
			if( $leave_day > (int)$leave_day ){
				$leave_day	= number_format($leave_day,2);
			}
			
			$data = array(
				"reg_hrs"		=>	$reg_hrs,
				"late_hrs"		=>	$late_hrs,
				"absent_hrs"	=>	$absent_hrs,
				"leave_hrs"		=>	$leave_hrs,
				"reg_day"		=>  $reg_day,
				"late_mins"		=>  $late_mins,
				"absent_day"	=>  $absent_day,
				"leave_day"		=>  $leave_day,
				"curr_date"		=>	$curr_date,
				"curr_late"		=>	getCurrentLate($con, $curr_date, $param->accountid),
				"curr_in"		=>	getCurrentIn( $con, $curr_date, $param->accountid ),
				"bdates"		=> 	getBirthDates($con, $curr_date)
			);
		}
		$return = json_encode($data);
	}
	
}else{
	$return = json_encode(array('status'=>'error'));
}

print $return;
mysqli_close($con);

function getCurrentIn($con, $work_date, $idacct){
	$Qry 			= new Query();	
	$Qry->table     = "vw_data_timesheet as a";
	$Qry->selected  = "a.in";
	$Qry->fields    = "work_date = '".$work_date."' AND idacct = '".$idacct."'  ";
	$rs 			= $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			if( !empty( $row['in'] ) ){
				return date_format(date_create($row['in']), "H:i A");
			}else{
				return "00:00 AM";
			}
		}
	}
	return "00:00 AM";
}

function getCurrentLate($con, $work_date, $idacct){
	$Qry 			= new Query();	
	$Qry->table     = "vw_data_timesheet";
	$Qry->selected  = "late";
	$Qry->fields    = "work_date = '".$work_date."' AND idacct = '".$idacct."'  ";
	$rs 			= $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			if( !empty( $row['late'] ) ){
				$num = floatval( number_format($row['late'],2) ) * 60;
				if( $num > (int)$num ){
					return number_format($num,2);
				}else{
					return $num;
				}
			}else{
				return "0";
			}
		}
	}
	return "0";
}

?>