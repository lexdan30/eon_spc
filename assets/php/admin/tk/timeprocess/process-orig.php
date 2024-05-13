<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$date=SysDate();
$time=SysTime();
$return = null;	

$pay_period = getPayPeriod($con);
$shift_cols = array("monday"	=>"idmon,mon,mon_in,mon_out,mon_brkin,mon_brkout", 
					"tuesday"	=>"idtue,tue,tue_in,tue_out,tue_brkin,tue_brkout",
					"wednesday"	=>"idwed,wed,wed_in,wed_out,wed_brkin,wed_brkout",
					"thursday"	=>"idthu,thu,thu_in,thu_out,thu_brkin,thu_brkout",
					"friday"	=>"idfri,fri,fri_in,fri_out,fri_brkin,fri_brkout",
					"saturday"	=>"idsat,sat,sat_in,sat_out,sat_brkin,sat_brkout", 
					"sunday"	=>"idsun,sun,sun_in,sun_out,sun_brkin,sun_brkout");

//upload file
if( array_key_exists('file',$_FILES) ){
	$valid_formats = array("txt");	
	$name = $_FILES['file']['name'];
	if ($_FILES['file']['error'] == 4) {
		$return = json_encode(array('status'=>'error','on'=>'img_check'));
		print $return;	
		mysqli_close($con);
		return;
	}
	if ($_FILES['file']['error'] == 0) {
		if(!in_array(pathinfo(strtolower($name), PATHINFO_EXTENSION), $valid_formats) ){
			$return = json_encode(array('status'=>'error-upload-type'));
			print $return;	
			mysqli_close($con);
			return;
		}
	}
	
	$folder_path 	= $param['targetPath'];	
	$t				= strtotime($date).time();	
	$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
	$save_name		= $pay_period['pay_date'].'.'.$extMove;	
	move_uploaded_file($_FILES["file"]["tmp_name"], $folder_path.$save_name);
		
}else{
	$return = json_encode(array('status'=>'nofile','on'=>'img_check'));
	print $return;	
	mysqli_close($con);
	return;
}


$file = fopen($folder_path.$save_name,"r");
while(! feof($file)){
  $txt_file = fgets($file);
  $arr_data	= explode(" ",$txt_file); 
  $ret = insertToTimeLogs($con, $arr_data);
}
fclose($file);

//process
$Qry = new Query();	
$Qry->table     = "vw_datacurrentworkdates2 as a LEFT JOIN vw_dataholidays AS b ON a.work_date = b.date";
$Qry->selected  = "a.work_date, a.id AS acct_id, b.id AS holiday_id";
$Qry->fields    = "a.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' ORDER BY a.work_date ASC";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
	while($row=mysqli_fetch_array($rs)){
		
		$shift_field= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];
		$shift_info = getDateShiftData( $con, $row['acct_id'], $shift_field, $row['work_date'] );
		


		$idacct 	= $row['acct_id'];
		$day		= date("l", strtotime($row['work_date']));
		$date		= $row['work_date'];

		$idshift	= $shift_info[0];
		$shiftin	= $shift_info[2];
		$shiftout	= $shift_info[3];
		$shiftbrkin	= $shift_info[4];
		$shiftbrkout= $shift_info[5];
		$in			= '';
		$out		= '';
		
		
		if((strtotime($shiftout) - strtotime($shiftin)) > 8 ){
			$reghrs		= sprintf('%0.2f',((strtotime($shiftout) - strtotime($shiftin)) - (strtotime($shiftbrkout) - strtotime($shiftbrkin))) / ( 60 * 60 ));
		}else{
			$reghrs		= sprintf('%0.2f',((strtotime($shiftout) - strtotime($shiftin))) / ( 60 * 60 ));
		}
		
		
		$acthrs		= '0';
		$excess		= '0';
		$absent		= $reghrs;
		$late		= '0';
		$ut			= '0';
		$ot 		= '0';
		$np 		= '0';
		
		//Adjustment Applications
		$aa_data = getAttendanceApplication( $con, $row['work_date'], $row['acct_id'], $pay_period['pay_start'], date("Y-m-d", strtotime('+'.$pay_period['grace_hour'].' hours', strtotime($pay_period['pay_end'])))  );
		
		
		
		//Leave Applications
		$leave_data = getLeaveApplication( $con, $row['work_date'], $row['acct_id'], $pay_period['pay_start'], date("Y-m-d", strtotime('+'.$pay_period['grace_hour'].' hours', strtotime($pay_period['pay_end'])))  );
		if( !empty($leave_data['id']) ){
			if( (int)$leave_data['idtype'] == 1 ){ //PAID LEAVE
				$reghrs		= $leave_data['hrs'];
				$absent		= $reghrs - $acthrs ;
			}else{
				$absent		= $leave_data['hrs'];
				$acthrs		= '0';
				$reghrs		= '0';
			}
		}
		
		if( !empty( $row['holiday_id'] ) ){ // DETERMINE IF HOLIDAY
			if( (int)getBeforeHoliday($con, $row['work_date'], $row['acct_id']) > 0 ){				
				$reghrs		= '0';
				if( (strtotime($shiftout) - strtotime($shiftin)) > 8 ){
					$absent		= sprintf('%0.2f',((strtotime($shiftout) - strtotime($shiftin)) - (strtotime($shiftbrkout) - strtotime($shiftbrkin))) / ( 60 * 60 ));
				}else{
					$absent		= sprintf('%0.2f',((strtotime($shiftout) - strtotime($shiftin)) ) / ( 60 * 60 ));
				}
			}else{
				if( (strtotime($shiftout) - strtotime($shiftin)) > 8 ){
					$reghrs		= sprintf('%0.2f',((strtotime($shiftout) - strtotime($shiftin)) - (strtotime($shiftbrkout) - strtotime($shiftbrkin))) / ( 60 * 60 ));
				}else{
					$reghrs		= sprintf('%0.2f',((strtotime($shiftout) - strtotime($shiftin)) ) / ( 60 * 60 ));
				}
				$absent		= '0';
			}
		}
		
		$Qry2 = new Query();	
		$Qry2->table     = "vw_datatimelogs";
		$Qry2->selected  = "acct_id, work_date, time_in, CASE WHEN time_out < time_in THEN NULL ELSE time_out END AS time_out";
		$Qry2->fields    = "work_date = '".$row['work_date']."' AND acct_id='".$row['acct_id']."' ";
		$rs2 = $Qry2->exe_SELECT($con);
		if(mysqli_num_rows($rs2)>= 1){
			$filter_acthrs=0;
			if($row2=mysqli_fetch_array($rs2)){	
				
				if( !empty($aa_data['id']) ){
					if( empty( $row2['time_in'] ) ){
						$in			= $aa_data['stime'];						
					}else{
						$in			= $row2['time_in'];
					}					
					if( empty( $row2['time_out'] ) ){
						$out		= $aa_data['ftime'];
					}else{						
						$out		= $row2['time_out'];	
					}
					if( !empty($out) && !empty($in) ){
						$acthrs		= sprintf('%0.2f', (strtotime($out) - strtotime($in)) / ( 60 * 60 ));
					}
					$absent		= '0';						
				}
				
				
				
				if( empty($leave_data['id']) &&  empty($aa_data['id']) ){	
					$in			= $row2['time_in'];
					$out		= $row2['time_out'];						
					if( !empty($out) && !empty($in) ){
						$acthrs		= sprintf('%0.2f', (strtotime($out) - strtotime($in)) / ( 60 * 60 ));
						$absent		= '0';		
					}else{
						if((strtotime($shiftout) - strtotime($shiftin)) > 8 ){
							$absent		= sprintf('%0.2f',((strtotime($shiftout) - strtotime($shiftin)) - (strtotime($shiftbrkout) - strtotime($shiftbrkin))) / ( 60 * 60 ));
						}else{
							$absent		= sprintf('%0.2f',((strtotime($shiftout) - strtotime($shiftin)) ) / ( 60 * 60 ));
						}
						$acthrs		= '0';
					}
				}
				
				if( !empty( $row['holiday_id'] ) ){ // DETERMINE IF HOLIDAY
					if( (int)getBeforeHoliday($con, $row['work_date'], $row['acct_id']) > 0 ){
						if((strtotime($shiftout) - strtotime($shiftin)) > 8 ){
							$absent		= sprintf('%0.2f',((strtotime($shiftout) - strtotime($shiftin)) - (strtotime($shiftbrkout) - strtotime($shiftbrkin))) / ( 60 * 60 ));
						}else{
							$absent		= sprintf('%0.2f',((strtotime($shiftout) - strtotime($shiftin)) ) / ( 60 * 60 ));
						}
						$reghrs		= '0';
						$acthrs		= '0';
						$excess		= '0';						
						$late		= '0';
						$ut			= '0';
					}else{
						$absent		= '0';
						$reghrs		= '0';
						$acthrs		= '0';
						$excess		= '0';						
						$late		= '0';
						$ut			= '0';
					}
				}
				
				
				if( empty($leave_data['id'])  ){
					//LATES
					if( strtotime( $in ) > strtotime( $shiftin ) && strtotime( $in ) < strtotime( $shiftout ) ){ // TIME IN IS GREATER THAN SHIFT IN
						$late   = sprintf('%0.2f', (strtotime($in) - strtotime($shiftin)) / ( 60 * 60 ));
					}
					if( !empty($shiftbrkout) && strtotime( $in ) > strtotime( $shiftbrkout ) ){ // TIMEIN GREATER THAN BREAKOUT
						$late   = sprintf('%0.2f', (strtotime($in) - strtotime($shiftbrkout)) / ( 60 * 60 ));
						$absent = sprintf('%0.2f', (strtotime($shiftbrkin) - strtotime($shiftin)) / ( 60 * 60 ));
					}
					if( !empty($shiftbrkin) && !empty($shiftbrkout) && 
						((strtotime( $in ) >= strtotime( $shiftbrkin ) && strtotime( $in ) <= strtotime( $shiftbrkout )) || 
						(strtotime( $out ) >= strtotime( $shiftbrkin ) && strtotime( $out ) <= strtotime( $shiftbrkout )))  ){ // TIMEIN IN BETWEEN BREAK SCHEDULE
						$late	= '0';
						if( strtotime( $in ) > strtotime( $shiftin ) && strtotime( $in ) < strtotime( $shiftout ) ){ // TIME IN IS GREATER THAN SHIFT IN
							$late   = sprintf('%0.2f', (strtotime($in) - strtotime($shiftin)) / ( 60 * 60 ));
						}						
						$absent = sprintf('%0.2f', (strtotime($shiftbrkin) - strtotime($shiftin)) / ( 60 * 60 ));
					}					
					if( strtotime( $in ) >  strtotime( $shiftout ) ){
						$absent = $reghrs;
						$acthrs	= '0';
						$excess	= '0';
					}					
					
					//UNDERTIME
					if( strtotime( $shiftout ) > strtotime( $out ) ){	// TIME OUT IS LESSER THAN SHIFT OUT
						$ut   	= sprintf('%0.2f', (strtotime($shiftout) - strtotime($out)) / ( 60 * 60 ));
					}
					if( strtotime($out) >= strtotime($shiftbrkin) && strtotime($out) <= strtotime($shiftbrkout)  ){ // TIME OUT BETWEEN BREAK SCHEDULE
						$ut   	= '0';
						
					}	
					if( strtotime( $shiftbrkin ) > strtotime( $out ) ){	// TIME OUT IS LESSER THAN BREAK IN
						$absent = $reghrs;
						$ut		= '0';
						$late	= '0';
						$acthrs	= '0';
					}
					if( ( strtotime( $in ) >= strtotime( $shiftbrkin ) && strtotime( $in ) <= strtotime( $shiftbrkout )) && strtotime($out) < strtotime($shiftout) ){ // TIME IN BETWEEN BREAK SCHEDULE AND TIME OUT LESS THAN SHIFT SCHED
						$absent = $reghrs;
						$ut		= '0';
						$late	= '0';
						$acthrs	= '0';
					}	
				}
			}
		}else{
			if( !empty($aa_data['id']) ){
				$in			= $aa_data['stime'];
				$out		= $aa_data['ftime'];	
				if( !empty($out) && !empty($in) ){
					$acthrs		= sprintf('%0.2f', (strtotime($out) - strtotime($in)) / ( 60 * 60 ));
				}
				$absent		= '0';	
			}			
			if( !empty($aa_data['id']) ){
				if( strtotime( $in ) > strtotime( $shiftin ) ){
					$late   = sprintf('%0.2f', (strtotime($in) - strtotime($shiftin)) / ( 60 * 60 ));
				}
				if( strtotime( $shiftout ) > strtotime( $out ) ){
					$ut   	= sprintf('%0.2f', (strtotime($shiftout) - strtotime($out)) / ( 60 * 60 ));
				}
			}
		}
		
		if( strtotime( $acthrs ) > strtotime($reghrs) ){
			$excess	= sprintf('%0.2f', (strtotime($out) - strtotime($shiftout)) / ( 60 * 60 ));
		}
		
		// REG HOURS MINUS LATE, UT, ABSENT
		if( empty($leave_data['id'])  ){
			if( $reghrs > 0 ){
				$reghrs	= $reghrs - ( $late + $ut + $absent );
			}			
			$reghrs = sprintf('%0.2f', $reghrs );
		}
		
		// NIGHT PREMIUM
		$nightp = getNightPremium($con);
		$workd  = $row['work_date'];
		$date_shift_out = date('Y-m-d', strtotime($workd));
		$date_night_p   = date('Y-m-d', strtotime($workd));
		if( strtotime( $shiftin ) > strtotime( $shiftout ) ){
			$date_shift_out = date('Y-m-d', strtotime($workd. ' + 1 days'));

		}
		if( strtotime( $date_shift_out. ' ' .$shiftout ) > strtotime($date_night_p . ' ' . $nightp) ){
			$np	= sprintf('%0.2f', ( strtotime( $date_shift_out. ' ' .$shiftout ) - strtotime($date_night_p . ' ' . $nightp)) / ( 60 * 60 ));
		}

		if( $idshift == 4  ){	// REST DAY ID
			$reghrs	= '0';
			$acthrs	= '0';
			$excess	= '0';
			$absent	= '0';
			$late	= '0';
			$ut		= '0';
			$np 	= '0';
		}	

		// OT HOUR CALCULATION
		$ot_data = getOtApplication( $con, $row['work_date'], $row['acct_id'], $pay_period['pay_start'], date("Y-m-d", strtotime('+'.$pay_period['grace_hour'].' hours', strtotime($pay_period['pay_end'])))  );
		$ot_type = "W";
		if( !empty($ot_data['id']) ){
			$ot = $ot_data['hrs'];
			//Check Holiday in vw_dataholidays using ot_date and get ot_type
			$ot_type = getHolidayAlias($con,$ot_data['date']);
			if(  $ot_data['shift_id'] == 4 ){				
				if($ot_type == 'L'){
					$ot_type = 'A';
				}elseif($ot_type == 'S'){
					$ot_type = 'B';
				}elseif($ot_type == 'Y'){
					$ot_type = 'D';
				}else{
					$ot_type = 'R';
				}				
			}
			//CASE WHEN and get id,rate, compute ot hours in tblclasstrans
			$classtrans = getClassTrans ($con,$ot_type);

			foreach($classtrans as $key => $value){
				if ($value['determine'] == 'N') {
					//himo functio to insert in tblotbreakdown
					if($ot <= 8){
						insertOtRate($con,$ot_data['id'],$ot,$value['rate'],'N');						
					}else{
						insertOtRate($con,$ot_data['id'],8,$value['rate'],'N');						
					}					
				}

				if($value['determine'] == 'G'){
					if($ot <= 8){						
						insertOtRate ($con,$ot_data['id'],0,$value['rate'],'G');					
					}else{
						insertOtRate($con,$ot_data['id'],$ot-8,$value['rate'],'G');
					}
				}

				if($value['determine'] == 'P'){			
					$ot_start = $ot_data['sdate']." ".$ot_data['overtime_stime'];
					$ot_end   = $ot_data['fdate']." ".$ot_data['overtime_ftime'];
					$ot_mins  = ((strtotime($ot_end) - strtotime($ot_start)) / 60);
					$npStart = $ot_data['sdate']." ".$nightp;
					
					$nphours = (strtotime( date('Y-m-d H:i:s', strtotime($ot_start . " +".$ot_mins." minutes")) ) - strtotime($npStart)) / 3600;
					if( floatval($nphours) > 0 ){
						if( floatval($nphours) >= 8 ){
							$nphours = 8;
						}
					}else{
						$nphours = 0;
					}
					insertOtRate ($con,$ot_data['id'],$nphours,$value['rate'],'P');
				}			 
			}
		}

		$data = array();
		
		$data = array(
			"idacct" 		=>	$idacct, 	
			"day"			=>	$day,		
			"date"			=>	$date,		
			"idshift"		=>	$idshift,	
			"shiftin"		=>	$shiftin,	
			"shiftout"		=>	$shiftout,	
			"in"			=>	$in,			
			"out"			=>	$out,				
			"reghrs"		=>	sprintf('%0.2f',$reghrs),		
			"acthrs"		=>	sprintf('%0.2f',$acthrs),		
			"excess"		=>	sprintf('%0.2f',$excess),		
			"absent"		=>	sprintf('%0.2f',$absent),
			"late"			=>  sprintf('%0.2f',$late),
			"ut"			=>  sprintf('%0.2f',$ut),
			"ot"			=>  sprintf('%0.2f',$ot),
			"np"			=>  sprintf('%0.2f',$np),
			"id_payperiod"  =>  $pay_period['id']
		);


		
		if( checkTimeSheetExists($con, $idacct, $date ) ){			 
			$return = updateTimeSheetRec($con, $data);			
		}else{
			$return = insertTimeSheetRec($con, $data);
		}
	}
	$return =  json_encode($data);

}else{
	$return = json_encode(array('status'=>'error'));
}



print $return;
mysqli_close($con);


function insertToTimeLogs($con, $arr_data){	
	$txt_acct = trim($arr_data[0]);
    $txt_date = trim($arr_data[1]);
    $txt_time = trim($arr_data[2]);
    $txt_type = trim($arr_data[3]);
	
	$Qry           = new Query();
	$Qry->table    = "tbltimelogs";
	$Qry->fields   = "acct_id='".$txt_acct."' AND work_date='".$txt_date."' AND time_type='".$txt_type."' AND work_time='".$txt_time."' ";
	$check         = $Qry->exe_DELETE($con);
	
	
	$Qry3           = new Query();
	$Qry3->table    = "tbltimelogs";
	$Qry3->selected = "acct_id,work_date,work_time,time_type";
	$Qry3->fields   = "'".$txt_acct."',
					   '".$txt_date."',
					   '".$txt_time."',
					   '".$txt_type."'";
	$checke = $Qry3->exe_INSERT($con);
	if($checke){
		return  json_encode(array("status"=>"success"));
	}else{
		return  json_encode(array('status'=>'error',"err"=>mysqli_error($con)));
	}
}

?>