<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data = array();

$ids = implode(",",$param->id);

$Qry = new Query();	
$Qry->table     = "vw_overtime_approval";
$Qry->selected  = "*";
$Qry->fields    = "id in (".$ids.") AND ot_status = 'PENDING'";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
	$app1 = 0;
	$app2 = 0;
	$data = array(
		"idacct"		=> '',
		"date"			=> '',
		"reason"		=> '',
		"stat"			=> '',
		"ctr_1"			=> $app1,
		"ctr_2"			=> $app2,
		"data"			=> array()
	);
	
    while($row=mysqli_fetch_array($rs)){
		$numhrs = ''; 
		$curr_appr = '2';
		if( empty($row['approver1_stat']) && empty($row['approver2_stat']) && (int)$row['stat'] != 4 ){
			$curr_appr = '1';
			$app1++;
		}elseif( !empty($row['approver1_stat']) && empty($row['approver2_stat']) && (int)$row['stat'] != 4 ){
			$curr_appr = '2';
			$app2++;
		}

		
		if($row['actual_hrs'] == '0.00'){
			$numhrs = 'disableApproved'; 
		}else{
			if(floatval($row['planned_hrs']) > floatval($row['actual_hrs'])){
				$numhrs = 'disablePlanned'; 
			}
		}
		
        $data["data"][] = array( 
            "id"        			=> $row['id'],
            "docnumber" 			=> $row['docnumber'],			
            "idacct" 				=> $row['idacct'],
			"idunit"				=> $row['idunit'],
			"idsuperior"			=> $row['idsuperior'],
            "empid" 				=> $row['empid'],			
            "empname" 				=> $row['empname'],
            "date" 					=> $row['date'],
			"planned_date_start"	=> $row['planned_date_start'],
			"planned_date_end"		=> $row['planned_date_end'],
			"planned_time_start"	=> date('h:i A', strtotime($row['planned_time_start'])),
			"planned_time_end"		=> date('h:i A', strtotime($row['planned_time_end'])),
			"planned_start"			=> $row['planned_start'],
			"planned_end"			=> $row['planned_end'],
			"planned_hrs"			=> $row['planned_hrs'],
			"actual_start"			=> $row['actual_start'],
			"actual_end"			=> $row['actual_end'],
			"actual_hrs"			=> getActualHours( $con,$row['idacct'],$row['date']), //$row['actual_hrs'],	
			"aa_approved"			=> checkApprovedAa($con,$row['idacct'],$row['date']),			
            "start_time" 			=> date('h:i A', strtotime($row['start_time'])),
			"end_time" 				=> date('h:i A', strtotime($row['end_time'])),		
			"remarks" 				=> $row['remarks'],
			"file" 					=> $row['file'],
			"curr_stat"				=> $row['stat'],
			"stat" 					=> $row['stat'],
			"ot_status"				=> $row['ot_status'],
			"date_approve"			=> $row['date_approve'],
			"approve_hr"			=> $row['approve_hr'],
			"id_payperiod"			=> $row['id_payperiod'],
			"period_start"			=> $row['period_start'],
			"period_end"			=> $row['period_end'],
			"grace_hour"			=> $row['grace_hour'],
			"reason"				=> '',
			"curr_appr"				=> $curr_appr,
			"numhrs"				=> $numhrs
        );
    }
	$data['ctr_1'] = $app1;
	$data['ctr_2'] = $app2;
}
        
$return = json_encode($data);

print $return;
mysqli_close($con);

function checkApprovedAa($con, $idacct, $date){
	$Qry = new Query();	
	$Qry->table     = "tbltimeadjustment";
	$Qry->selected  = "*";
	$Qry->fields    = "idacct='".$idacct."' AND `date`='".$date."' AND stat ='1' ";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		return true;
	}
	return false;
}
function getActualHours( $con,$idacct,$date){ 
	$sdate			= $date;
	$fdate			=  getschedinfo($con,$idacct,$date)['actdouthr'];
	$stime			=substr(str_replace(' ', '', getschedinfo($con,$idacct,$date)['out']),0,5);
	$ftime			= substr(str_replace(' ', '', getschedinfo($con,$idacct,$date)['actouthr']),0,5);

	if(!empty($stime) && !empty($ftime)){
		if(getschedinfo($con,$idacct, $sdate)['fallonweekdayholiday'] && getschedinfo($con,$idacct, $sdate)['site'] != '1' && (strtotime( $sdate.' '.$stime.'' ) < strtotime( $sdate.' '.'07:00'.'' )) &&  getschedinfo($con,$idacct, $sdate)['driver'] == false){
			$stime = '07:00';
		} 
	
		if(getschedinfo($con,$idacct, $sdate)['flexi'] && getschedinfo($con,$idacct, $sdate)['site'] != '1' && getschedinfo($con,$idacct, $sdate)['joblevel'] == 'Mgr' && getschedinfo($con,$idacct, $sdate)['fallonweekdayholiday'] == false){ 
			if((strtotime( $fdate.' '.$ftime.'' ) > strtotime( $sdate.' '.'18:00'.'' ))){
				$hrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.$ftime.'' ) - strtotime( $sdate.' '.$stime.'' )) / ( 60 * 60 )) - 1 );
			}elseif((strtotime( $fdate.' '.$ftime.'' ) < strtotime( $sdate.' '.'18:00'.'' )) && (strtotime( $fdate.' '.$ftime.'' ) > strtotime( $sdate.' '.'17:00'.'' ))){
				$hrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.'17:00'.'' ) - strtotime( $sdate.' '.$stime.'' )) / ( 60 * 60 )) );
				$ftime			= '17:00'; 
			}else{
				$hrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.$ftime.'' ) - strtotime( $sdate.' '.$stime.'' )) / ( 60 * 60 )) );
			}
		}elseif(getschedinfo($con,$idacct, $sdate)['fallonweekdayholiday'] && getschedinfo($con,$idacct, $sdate)['joblevel'] == 'RnF'){
			if((strtotime( $sdate.' '.$stime.'' ) < strtotime( $sdate.' '.'12:00'.'' )) && (strtotime( $fdate.' '.$ftime.'' ) > strtotime( $sdate.' '.'13:00'.'' ))){
				$hrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.$ftime.'' ) - strtotime( $sdate.' '.$stime.'' )) / ( 60 * 60 )) - 1 );
			}elseif((strtotime( $sdate.' '.$stime.'' ) < strtotime( $sdate.' '.'12:00'.'' )) && ((strtotime( $fdate.' '.$ftime.'' ) < strtotime( $sdate.' '.'13:00'.'' )) && (strtotime( $fdate.' '.$ftime.'' ) > strtotime( $sdate.' '.'12:00'.'' )))){
				$hrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.'12:00'.'' ) - strtotime( $sdate.' '.$stime.'' )) / ( 60 * 60 )) );
				$ftime = '12:00';
			}elseif((strtotime( $sdate.' '.$stime.'' ) < strtotime( $sdate.' '.'13:00'.'' )) && ((strtotime( $fdate.' '.$stime.'' ) > strtotime( $sdate.' '.'12:00'.'' )) && (strtotime( $fdate.' '.$ftime.'' ) > strtotime( $sdate.' '.'13:00'.'' )))){
				$hrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.$ftime.'' ) - strtotime( $sdate.' '.'13:00'.'' )) / ( 60 * 60 )) );
				$stime = '13:00';
			}else{
				$hrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.$ftime.'' ) - strtotime( $sdate.' '.$stime.'' )) / ( 60 * 60 )) );
			}
		}else{
			$hrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.$ftime.'' ) - strtotime( $sdate.' '.$stime.'' )) / ( 60 * 60 )) );
		}
		$splice = (explode('.', $hrs, 2));
		if($splice[1] >= 00 && $splice[1] < 25){
			$hrs = $splice[0] . '.' . '00';
		}elseif($splice[1] >= 25 && $splice[1] < 50){
			$hrs = $splice[0] . '.' . '25';
		}elseif($splice[1] >= 50 && $splice[1] < 75){
			$hrs = $splice[0] .'.' . '50';
		}elseif($splice[1] >= 75 && $splice[1] <= 99){
			$hrs = $splice[0] . '.' . '75';
		}

		$preotres = getschedinfo($con,$idacct, $sdate)['preot_unit'];

		if(!empty($preotres)){
			$preot = sprintf('%0.2f',floatval($preotres));
			$hrs = sprintf('%0.2f', floatval($hrs) + floatval($preot));
		}
	
		return $hrs;
	}else{
		$preotres = getschedinfo($con,$idacct, $sdate)['preot_unit'];

		if(!empty($preotres)){
			$preot = sprintf('%0.2f',floatval($preotres));
			$hrs = $preot;
		}else{
			$hrs = '0.00';
		}
		return $hrs;
	}
}

function getschedinfo($con, $id, $date){ 
	$shiftout = array();
	$Qry = new Query();	
	$Qry->table     = "vw_timesheet";
	$Qry->selected  = "stime,ftime, idshift,breakin,breakout,site,idlvl,date_in,timein,date_out,timeout,aatimein,aatimeout,aadate_out";
	$Qry->fields = "id = '" .$id . "' AND work_date = '" . $date . "'";
	
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_assoc($rs)){
			if($row['idshift'] != '89' && $row['idshift'] != '93'){
			  //$shiftout = $row['ftime']; 
			  if($row['stime'] != null){
				  $site =$row['site'];
			  }else{
				  $site =getschedinfoFlex($con, $id,  $date)['site'];
			  }
	  
			  if($row['idlvl'] == 6){
				  $joblevel = 'RnF';
			  }elseif($row['idlvl'] == 2 || $row['idlvl'] == 3){
			  $joblevel = 'Mgr';
			  }else{
			  $joblevel = '';
			  }
	  
			  if(getPositionId($con, $id) == '112'){
				  $driver = true;
			  }else{
				  $driver = false;
			  }
	  
			  if(getWeekendOrHoliday($con, $id,  $date) == false){
				  if($joblevel == 'Mgr' && $row['site']!=1){
					  $out='18:00:00'; 
				  }else{
					  $out=$row['ftime'];
				  }
			  }else{
				  $out=$row['ftime'];
			  }
			  
			 if(!empty($row['timeout']) && (strtotime( $date.' '.substr($out,0,5).'' ) < strtotime($row['date_out'].' '.$row['timeout'].'' ))){
				if(!empty($out) && getWeekendOrHoliday($con, $id,  $date) == false){
					$startot = $out;
				}else{
					$startot = $row['timein'];
				}
			 }else{
				$startot = '';
			 }

			 if(!empty($row['aatimeout'])){
				if((strtotime( $date.' '.substr($out,0,5).'' ) < strtotime($row['aadate_out'].' '.$row['aatimeout'].'' ))){
					if(!empty($out) && getWeekendOrHoliday($con, $id,  $date) == false){
						$startot = $out;
					}else{
						$startot = $row['aatimein'];
					}
				 }else{
					$startot = '';
				}
			 }

			 //out actual vs aa
			if(!empty($row['timeout'])){
				$timeout = $row['timeout'];
			}else{
				$timeout = '';
			}
			if(!empty($row['aatimeout'])){
				$timeout = $row['aatimeout'];
			}
			//dout actual vs aa
			if(!empty($row['date_out'])){
				$dtimeout = $row['date_out'];
			}else{
				$dtimeout = '';
			}
			if(!empty($row['aadate_out'])){
				$dtimeout = $row['aadate_out'];
			}

			$preot_unit = '';

			if($row['idshift'] == '88'){
				$preot_unit = getPreOT($con, $id,  $date)["earlyot"];
				if($preot_unit == '0.0000' || $preot_unit == '0.00' || empty($preot_unit)){
					$preot_unit = '';
				}
			}

			  $shiftout = array(				
				"in"	=> $row['stime'],
				"out"  => $startot,	
				"actdouthr"  => $dtimeout,	
				"actouthr"  => $timeout,				
				"br1"	=>  $row['breakin'],
				"br2"	=>  $row['breakout'],
				"site"=>  $site,
				"joblevel"=> $joblevel,
				"driver"=> $driver,
				"fallonweekdayholiday"=> getWeekendOrHoliday($con, $id,  $date),
				"preot_unit" => $preot_unit,
				"flexi" => false
			  );
			  
			}else{
			  $shiftout = getschedinfoFlex($con, $id,  $date);
			}
		  }
	}
	return $shiftout;
}

function getPreOT($con, $id, $date){
	$data = array();
	$Qry = new Query();	
	$Qry->table     = "vw_timesheetfinal";
	$Qry->selected  = "*";
	$Qry->fields    = "tid='".$id."' AND work_date = '" . $date . "'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			$data = array( 
				"earlyot"  => $row['earlyot'],					
				"earlyotm"	=> $row['earlyotm']
			  );
		}
	}
	return $data;
}

function getschedinfoFlex($con, $id, $date){ 
	$data = array();
	$Qry = new Query();	
	$Qry->table         = "
	vw_datacurrentworkdates2 AS a1 
LEFT JOIN tbltimesheet AS a ON a1.id = a.idacct AND a1.work_date = a.date
LEFT JOIN `tbltimeadjustment` `k1` ON (`k1`.`idacct` = `a1`.`id` AND `k1`.`date` = `a1`.`work_date` AND `k1`.`stat` = 1)
LEFT JOIN tblpayperiod AS j ON `a1`.`work_date` BETWEEN `j`.`period_start` AND `j`.`period_end`
LEFT JOIN tblaccountjob AS e ON e.idacct = a1.id
LEFT JOIN tblholidays AS c ON 
(c.date = a1.work_date AND (c.regcode IS NULL OR c.regcode = '') AND (c.provcode IS NULL OR c.provcode ='') AND (c.munid IS NULL OR c.munid ='')) OR 
(c.date = a1.work_date AND IF( (c.regcode IS NOT NULL OR c.regcode != '') AND (c.munid IS NULL OR c.munid ='')
				  ,c.regcode = e.regcode AND c.provcode = e.provcode AND (c.munid IS NULL OR c.munid ='')
				  ,c.regcode = e.regcode AND c.provcode = e.provcode AND c.munid = e.munid)
  
)
LEFT JOIN tblholidaytype AS d ON d.id = c.idtype 

LEFT JOIN tbltimeadjustment AS k ON k.idacct = a1.id AND k.date =  a1.work_date AND k.stat != 4  AND k.id NOT IN (SELECT idapp FROM `tblappcancel` WHERE `type` = 'attendance' AND STATUS = 1 AND date_approve BETWEEN `j`.`period_start` AND `j`.`period_end`)
LEFT JOIN tbltimeleaves AS l ON l.idacct = a1.id AND l.date =  a1.work_date AND l.stat != 4 AND l.id NOT IN (SELECT idapp FROM `tblappcancel` WHERE `type` LIKE '%LEAVE%' AND STATUS = 1 AND date_approve BETWEEN `j`.`period_start` AND `j`.`period_end`)
LEFT JOIN  tbltimeovertime AS m ON m.idacct = a1.id AND m.date =  a1.work_date AND m.stat != 4 AND m.id NOT IN (SELECT idapp FROM `tblappcancel` WHERE `type` = 'overtime' AND STATUS = 1 AND date_approve BETWEEN `j`.`period_start` AND `j`.`period_end`)
LEFT JOIN  tbltimeobtrip AS p ON p.idacct = a1.id AND p.date =  a1.work_date AND p.stat != 4 AND p.id NOT IN (SELECT idapp FROM `tblappcancel` WHERE `type` = 'obtrip' AND STATUS = 1 AND date_approve BETWEEN `j`.`period_start` AND `j`.`period_end`)
LEFT JOIN  tbltimeshift AS q ON q.idacct = a1.id AND q.date =  a1.work_date AND q.stat != 4 AND q.id NOT IN (SELECT idapp FROM `tblappcancel` WHERE `type` = 'changeshift' AND STATUS = 1 AND date_approve BETWEEN `j`.`period_start` AND `j`.`period_end`)
LEFT JOIN tblleaves AS r ON r.id = l.idleave

LEFT JOIN tblshift AS v ON v.id = q.idshift
LEFT JOIN tblaccount AS o ON o.id = a1.id 	
LEFT JOIN tblcalendar AS t ON t.id = e.wshift
LEFT JOIN tblshift AS s ON s.id = (CASE 
				WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Sunday' THEN t.shiftsun
				WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Monday' THEN t.shiftmon
				WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Tuesday' THEN t.shifttue
				WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Wednesday' THEN t.shiftwed
				WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Thursday' THEN t.shiftthu
				WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Friday' THEN t.shiftfri
				WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Saturday' THEN t.shiftsat
				ELSE a.idshift
			END)
LEFT JOIN tblshift AS fpshift ON fpshift.id = (CASE 
			WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Sunday' THEN IF(q.stat = 1 AND q.approver1_date BETWEEN `j`.`period_start` AND `j`.`period_end`, q.idshift,t.shiftsun)
				WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Monday' THEN IF(q.stat = 1 AND q.approver1_date BETWEEN `j`.`period_start` AND `j`.`period_end`, q.idshift,t.shiftmon)
				WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Tuesday' THEN IF(q.stat = 1 AND q.approver1_date BETWEEN `j`.`period_start` AND `j`.`period_end`, q.idshift,t.shifttue)
				WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Wednesday' THEN IF(q.stat = 1 AND q.approver1_date BETWEEN `j`.`period_start` AND `j`.`period_end`, q.idshift,t.shiftwed)
				WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Thursday' THEN IF(q.stat = 1 AND q.approver1_date BETWEEN `j`.`period_start` AND `j`.`period_end`, q.idshift,t.shiftthu)
				WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Friday' THEN IF(q.stat = 1 AND q.approver1_date BETWEEN `j`.`period_start` AND `j`.`period_end`, q.idshift,t.shiftfri)
				WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Saturday' THEN IF(q.stat = 1 AND q.approver1_date BETWEEN `j`.`period_start` AND `j`.`period_end`, q.idshift,t.shiftsat)
				ELSE IF(q.stat = 1 AND q.approver1_date BETWEEN `j`.`period_start` AND `j`.`period_end`, q.idshift,a.idshift)
			END)
LEFT JOIN tblshift AS u ON u.id = IF(q.stat = 1 AND q.approver1_date BETWEEN `j`.`period_start` AND `j`.`period_end`
					,q.idshift
					,(CASE 
						WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Sunday' THEN t.shiftsun
						WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Monday' THEN t.shiftmon
						WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Tuesday' THEN t.shifttue
						WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Wednesday' THEN t.shiftwed
						WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Thursday' THEN t.shiftthu
						WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Friday' THEN t.shiftfri
						WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Saturday' THEN t.shiftsat
						ELSE a.idshift
					END)
				) 
LEFT JOIN tblbunits AS w ON e.idunit = w.id
LEFT JOIN tblposition AS z ON z.id = e.idpos";
	$Qry->selected      = " `k1`.`date` AS `aadate_in`,
	IF(CAST(`k1`.`stime` AS TIME) < CAST(`k1`.`ftime` AS TIME) 
		AND (`k1`.`ftime` <> '' OR `k1`.`ftime` IS NOT NULL), `k1`.`date`,`k1`.`date` + INTERVAL 1 DAY) AS `aadate_out`,
	 `k1`.`stime` AS `aatimein`,
	 `k1`.`ftime` AS `aatimeout`,
	 a.timein AS timein,
	 a.timeout AS timeout,
   IF(`k1`.`stime` IS NULL OR `k1`.`stime` = '',
	 (CASE 
	   WHEN a.timein IS NULL OR a.timein = '' THEN `fpshift`.`stime`
	   WHEN s.flexi = 1 AND CAST(a.timein AS TIME) > CAST(`fpshift`.`stime` AS TIME) AND CAST(a.timein AS TIME) < CAST( ADDTIME( `fpshift`.`stime`, u.graceperiod ) AS TIME ) THEN a.timein 
	   WHEN s.flexi = 1 AND CAST(a.timein AS TIME) > CAST(`fpshift`.`stime` AS TIME) AND CAST(a.timein AS TIME) > CAST( ADDTIME( `fpshift`.`stime`, u.graceperiod ) AS TIME ) THEN CAST( ADDTIME(`fpshift`.`stime`, '04:00') AS TIME ) 
	   WHEN s.flexi = 0 AND CAST(a.timein AS TIME) >= CAST(fpshift.penaltyin AS TIME) THEN '13:00' 
	   ELSE `fpshift`.`stime` 
	 END )
	 ,
	 (CASE
	   WHEN s.flexi = 1 AND CAST(`k1`.`stime` AS TIME) > CAST(`fpshift`.`stime` AS TIME) AND CAST(`k1`.`stime` AS TIME) < CAST( ADDTIME( `fpshift`.`stime`, u.graceperiod ) AS TIME ) THEN `k1`.`stime` 
	   WHEN s.flexi = 1 AND CAST(`k1`.`stime` AS TIME) > CAST(`fpshift`.`stime` AS TIME) AND CAST(`k1`.`stime` AS TIME) > CAST( ADDTIME( `fpshift`.`stime`, u.graceperiod ) AS TIME ) THEN CAST( ADDTIME(`fpshift`.`stime`, '04:00') AS TIME ) 
	   WHEN s.flexi = 0 AND CAST(`k1`.`stime` AS TIME) >= CAST(fpshift.penaltyin AS TIME) THEN '13:00' 
	   ELSE `fpshift`.`stime` 
	 END )
	 )
	 AS loginschedref,
	 IF(`k1`.`stime` IS NULL OR `k1`.`stime` = ''
   ,
   CAST( ADDTIME( ( CASE 
			 WHEN a.timein IS NULL  OR a.timein = ''  THEN `fpshift`.`ftime`  WHEN s.flexi = 1 AND CAST(a.timein AS TIME) > CAST(`fpshift`.`stime` AS TIME) AND CAST(a.timein AS TIME) < CAST( ADDTIME( `fpshift`.`stime`, u.graceperiod ) AS TIME ) THEN ADDTIME( `fpshift`.`ftime`, CAST(a.timein AS TIME) - CAST(`fpshift`.`stime` AS TIME) ) 
			 WHEN s.flexi = 1 AND CAST(a.timein AS TIME) > CAST(`fpshift`.`stime` AS TIME) AND CAST(a.timein AS TIME) > CAST( ADDTIME( `fpshift`.`stime`, u.graceperiod ) AS TIME ) THEN CAST(`fpshift`.`ftime` AS TIME) 
			 WHEN s.flexi = 0 AND CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(a1.work_date, ' ', fpshift.penaltyout) AS DATETIME) THEN '12:00' 
			 ELSE `fpshift`.`ftime` 
		 END ), 
		 (CASE 
			 WHEN a.timein IS NULL  OR a.timein = '' THEN '00:00' 
			 WHEN (DAYNAME(a1.work_date) = 'Sunday' OR DAYNAME(a1.work_date) = 'Saturday' OR d.type IS NOT NULL) THEN '00:00'
			 WHEN w.location = 2 AND fpshift.id = 4 THEN '00:00' 
			 WHEN e.idlvl IN (2, 3) AND w.location = 2 AND CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) > CAST(CONCAT(a1.work_date, ' ', '18:00') AS DATETIME) THEN '00:00'
			 WHEN e.idlvl IN (2, 3) AND w.location = 2 AND s.flexi = 1 AND CAST(a.timein AS TIME) > CAST('08:00' AS TIME) AND CAST(a.timeout AS TIME) BETWEEN CAST('17:00' AS TIME) AND CAST('18:00' AS TIME) THEN '00:00' 
			 WHEN e.idlvl IN (2, 3) AND w.location = 2 AND s.flexi = 1 AND CAST(a.timein AS TIME) > CAST('08:00' AS TIME) AND CAST(a.timeout AS TIME) > CAST('18:00' AS TIME) THEN '00:00'
			 ELSE '00:00' 
		 END )
   ) AS TIME )
   ,
   CAST( ADDTIME( ( CASE 
			 WHEN s.flexi = 1 AND CAST(`k1`.`stime` AS TIME) > CAST(`fpshift`.`stime` AS TIME) AND CAST(`k1`.`stime` AS TIME) < CAST( ADDTIME( `fpshift`.`stime`, u.graceperiod ) AS TIME ) THEN ADDTIME( `fpshift`.`ftime`, CAST(`k1`.`stime` AS TIME) - CAST(`fpshift`.`stime` AS TIME) ) 
			 WHEN s.flexi = 1 AND CAST(`k1`.`stime` AS TIME) > CAST(`fpshift`.`stime` AS TIME) AND CAST(`k1`.`stime` AS TIME) > CAST( ADDTIME( `fpshift`.`stime`, u.graceperiod ) AS TIME ) THEN CAST(`fpshift`.`ftime` AS TIME) 
			 WHEN s.flexi = 0 AND CAST(CONCAT(IF(CAST(`k1`.`stime` AS TIME) < CAST(`k1`.`ftime` AS TIME), `k1`.`date`,`k1`.`date` + INTERVAL 1 DAY), ' ', `k1`.`ftime`) AS DATETIME) < CAST(CONCAT(a1.work_date, ' ', fpshift.penaltyout) AS DATETIME) THEN '12:00' 
			 ELSE `fpshift`.`ftime` 
		 END ), 
		 (CASE 
			 WHEN (DAYNAME(a1.work_date) = 'Sunday' OR DAYNAME(a1.work_date) = 'Saturday' OR d.type IS NOT NULL) THEN '00:00'
			 WHEN w.location = 2 AND fpshift.id = 4 THEN '00:00' 
			 WHEN e.idlvl IN (2, 3) AND w.location = 2 AND CAST(CONCAT(IF(CAST(`k1`.`stime` AS TIME) < CAST(`k1`.`ftime` AS TIME), `k1`.`date`,`k1`.`date` + INTERVAL 1 DAY), ' ', `k1`.`ftime`) AS DATETIME) > CAST(CONCAT(a1.work_date, ' ', '18:00') AS DATETIME) THEN '00:00'
			 WHEN e.idlvl IN (2, 3) AND w.location = 2 AND s.flexi = 1 AND CAST(`k1`.`stime` AS TIME) > CAST('08:00' AS TIME) AND CAST(`k1`.`ftime` AS TIME) BETWEEN CAST('17:00' AS TIME) AND CAST('18:00' AS TIME) THEN '00:00' 
			 WHEN e.idlvl IN (2, 3) AND w.location = 2 AND s.flexi = 1 AND CAST(`k1`.`stime` AS TIME) > CAST('08:00' AS TIME) AND CAST(`k1`.`ftime` AS TIME) > CAST('18:00' AS TIME) THEN '00:00'
			 ELSE '00:00' 
		 END )
   ) AS TIME )
   
   )
   AS logoutschedref,
   (CASE 
	 WHEN (a.timein IS NULL OR a.timeout IS NULL OR a.timein = '' OR a.timeout = '') THEN `fpshift`.`breakin` 
	 WHEN s.flexi = 0 AND CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) >= CAST(CONCAT(a1.work_date, ' ', fpshift.penaltyin) AS DATETIME) THEN NULL 
	 WHEN s.flexi = 0 AND CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(a1.work_date, ' ', fpshift.penaltyout) AS DATETIME) THEN NULL 
	 ELSE `fpshift`.`breakin` 
   END)   
   AS breakinref,
   ( CASE 
	 WHEN a.timein IS NULL OR a.timeout IS NULL OR a.timein = '' OR a.timeout = '' THEN `fpshift`.`breakout` 
	 WHEN s.flexi = 0 AND CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) >= CAST(CONCAT(a1.work_date, ' ', fpshift.penaltyin) AS DATETIME) THEN NULL 
	 WHEN s.flexi = 0 AND CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(a1.work_date, ' ', fpshift.penaltyout) AS DATETIME) THEN NULL 
	 ELSE `fpshift`.`breakout` 
   END ) AS breakoutref,  `e`.`site` AS `site`,  `e`.`idlvl` AS `idlvl`,`a`.date_out
	";
	$Qry->fields        = "e.id IS NOT NULL AND a1.id = '".$id."' AND a1.work_date = '".$date."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			//return $row['logoutschedref'];
			if($row['idlvl'] == 6){
			  $joblevel = 'RnF'; 
			}elseif($row['idlvl'] == 2 || $row['idlvl'] == 3){
			  $joblevel = 'Mgr';
			}else{
			  $joblevel = '';
			}
  
			if(getPositionId($con, $id) == '112'){ 
			  $driver = true;
			  }else{
				  $driver = false;
			  }
			  // not applicable trapp during otsave

			  if(!empty($row['timeout']) && (strtotime( $date.' '.substr($row['logoutschedref'],0,5).'' ) < strtotime($row['date_out'].' '.$row['timeout'].'' ))){
				if(!empty($row['logoutschedref']) && getWeekendOrHoliday($con, $id,  $date) == false){
					$startot = $row['logoutschedref'];
				}else{
					$startot = $row['timein'];
				}
			 }else{
				$startot = '';
			 }

			 if(!empty($row['aatimeout'])){
				if((strtotime( $date.' '.substr($row['logoutschedref'],0,5).'' ) < strtotime($row['aadate_out'].' '.$row['aatimeout'].'' ))){
					if(!empty($row['logoutschedref']) && getWeekendOrHoliday($con, $id,  $date) == false){
						$startot = $row['logoutschedref'];
					}else{
						$startot = $row['aatimein'];
					}
				}else{
					$startot = '';
				}
			 }

			//out actual vs aa
			if(!empty($row['timeout'])){
				$timeout = $row['timeout'];
			}else{
				$timeout = '';
			}
			if(!empty($row['aatimeout'])){
				$timeout = $row['aatimeout'];
			}
			//dout actual vs aa
			if(!empty($row['date_out'])){
				$dtimeout = $row['date_out'];
			}else{
				$dtimeout = '';
			}
			if(!empty($row['aadate_out'])){
				$dtimeout = $row['aadate_out'];
			}

			$data = array( 
			  "in"  => $row['loginschedref'],					
			  "out"	=> $startot,
			  "actdouthr"  => $dtimeout,	
			  "actouthr"  => $timeout,	
			  "br1"	=> $row['breakoutref'],
			  "br2"	=> $row['breakinref'],
			  "site"=>  $row['site'],
			  "driver"=> $driver,
			  "joblevel"=> $joblevel,
			  "fallonweekdayholiday"=> getWeekendOrHoliday($con, $id, $date),
			  "preot_unit" => '',
			  "flexi" => true
			);
		}
	}
  
	return $data;
  }

  function getWeekendOrHoliday($con, $id, $date){
	$Qry = new Query();	
	$Qry->table     = "vw_datacurrentworkdates2 AS a
	LEFT JOIN tbltimesheet AS b ON b.date = a.work_date AND b.idacct = a.id
	LEFT JOIN tblaccountjob AS i ON i.idacct = a.id
	LEFT JOIN tblcalendar AS e ON e.id = i.wshift
	LEFT JOIN (SELECT `id` AS `id`,
					  `date` AS `date`,
					  `idacct` AS `idacct`,
					  `idleave` AS `idleave`,
					  `leave_names` AS `NAME`,
					  `leave_type` AS `type`,
					   SUM(`hrs`) AS `hrs`,
					   `stat` AS `stat` 
					  FROM `vw_leave_application2` 
					  GROUP BY `idacct`, `date`) AS `lv` ON `a`.`work_date` = `lv`.`date` AND `lv`.`idacct` = `a`.`id`
	LEFT JOIN (SELECT `id` AS `id`,
			  `idacct` AS `idacct`,
					  `date` AS `date`,
					   SUM(`hrs`) AS `hrs`,
			  `stat` AS `stat`,
					  `date_approve` AS `date_approve` 
				  FROM `vw_attendance_application` 
				  GROUP BY `idacct`, `date`) `adj` 
				  ON `a`.`work_date` = `adj`.`date` AND `adj`.`idacct` = `a`.`id`
	LEFT JOIN (SELECT `id` AS `id`,
				  `docnumber` AS `docnumber`,
				  `creator` AS `creator`,
				  `idacct` AS `idacct`,
				  `empid` AS `empid`,
				  `empname` AS `empname`,
				  `idsuperior` AS `idsuperior`,
				  `idunit` AS `idunit`,
				  `date` AS `date`,
				  `remarks` AS `remarks`,
				  `file` AS `FILE`,
				  `stat` AS `stat`,
				  `oldidshift` AS `oldidshift`,
				  `idshift` AS `idshift`,
				  `newshift` AS `newshift`,
				  `shiftin` AS `shiftin`,
				  `shiftout` AS `shiftout`,
				  `break` AS `break`,
				  `oldshift` AS `oldshift`,
				  `shift_status` AS `shift_status`,
				  `date_create` AS `date_create`,
				  `approver1` AS `approver1`,
				  `approver1_name` AS `approver1_name`,
				  `approver1_reason` AS `approver1_reason`,
				  `date_approve` AS `date_approve`,
				  `time_approve` AS `time_approve`,
				  `id_payperiod` AS `id_payperiod`,
				  `period_start` AS `period_start`,
				  `period_end` AS `period_end`,
				  `grace_hour` AS `grace_hour`,
				  `datetimeapprove` AS `datetimeapprove` 
				FROM `vw_shift_application2`) `tts` 
				ON `tts`.`idacct` = `a`.`id` AND `tts`.`date` = `a`.`work_date`
	LEFT JOIN (SELECT `id` AS `id`,
					  `docnumber` AS `docnumber`,
					  `creator` AS `creator`,
					  `idacct` AS `idacct`,
					  `shift_id` AS `shift_id`,
					  `name` AS `NAME`,
					  `shift_stime` AS `shift_stime`,
					  `shift_ftime` AS `shift_ftime`,
					  `break` AS `break`,
					  `date` AS `date`,
					  `sdate` AS `sdate`,
					  `fdate` AS `fdate`,
					  `overtime_stime` AS `overtime_stime`,
					  `overtime_ftime` AS `overtime_ftime`,
					  `planhrs` AS `planhrs`,
					  `hrs` AS `hrs`,
					  `remarks` AS `remarks`,
					  `file` AS `FILE`,
					  `stat` AS `stat`,
					  `ot_stat` AS `ot_stat`,
					  `date_create` AS `date_create`,
					  `date_approve` AS `date_approve`,
					  `holiday_date` AS `holiday_date`,
					  `ot_type` AS `ot_type`,
					  `reg_ot_rate` AS `reg_ot_rate`,
					  `rd_ot_rate` AS `rd_ot_rate`,
					  `spcl_hol_rate` AS `spcl_hol_rate`,
					  `legal_hol_rate` AS `legal_hol_rate`,
					  `spcl_rd_rate` AS `spcl_rd_rate`,
					  `legal_rd_rate` AS `legal_rd_rate`,
					  `reg_ot8_rate` AS `reg_ot8_rate`,
					  `rd_ot8_rate` AS `rd_ot8_rate`,
					  `spcl_hol8_rate` AS `spcl_hol8_rate`,
					  `legal_hol8_rate` AS `legal_hol8_rate`,
					  `spcl_rd8_rate` AS `spcl_rd8_rate`,
					  `legal_rd8_rate` AS `legal_rd8_rate`,
					  `reg_np_rate` AS `reg_np_rate`,
					  `legal_np_rate` AS `legal_np_rate`,
					  `rd_np_rate` AS `rd_np_rate`,
					  `spcl_rd_np_rate` AS `spcl_rd_np_rate`,
					  `legal_rd_np_rate` AS `legal_rd_np_rate`,
					  `spcl_np_rate` AS `spcl_np_rate`,
					  `reg_ot` AS `reg_ot`,
					  `rd_ot` AS `rd_ot`,
					  `spcl_hol` AS `spcl_hol`,
					  `legal_hol` AS `legal_hol`,
					  `spcl_rd` AS `spcl_rd`,
					  `legal_rd` AS `legal_rd`,
					  `reg_ot8` AS `reg_ot8`,
					  `rd_ot8` AS `rd_ot8`,
					  `spcl_hol8` AS `spcl_hol8`,
					  `legal_hol8` AS `legal_hol8`,
					  `spcl_rd8` AS `spcl_rd8`,
					  `legal_rd8` AS `legal_rd8`,
					  `reg_np` AS `reg_np`,
					  `legal_np` AS `legal_np`,
					  `spcl_np` AS `spcl_np`,
					  `rd_np` AS `rd_np`,
					  `spcl_rd_np` AS `spcl_rd_np`,
					  `legal_rd_np` AS `legal_rd_np`,
					  `sl_rd_rate` AS `sl_rd_rate`,
					  `sl_hol_rate` AS `sl_hol_rate`,
					  `sl_rd8_rate` AS `sl_rd8_rate`,
					  `sl_hol8_rate` AS `sl_hol8_rate`,
					  `sl_rd_np_rate` AS `sl_rd_np_rate`,
					  `sl_np_rate` AS `sl_np_rate`,
					  `sl_hol` AS `sl_hol`,
					  `sl_rd` AS `sl_rd`,
					  `sl_hol8` AS `sl_hol8`,
					  `sl_rd8` AS `sl_rd8`,
					  `sl_np` AS `sl_np`,
					  `sl_rd_np` AS `sl_rd_np` 
					FROM `vw_overtime_application_2` GROUP BY `idacct`, `date`) `vl` 
					ON `a`.`work_date` = `vl`.`date` AND `vl`.`idacct` = `a`.`id`
	LEFT JOIN (SELECT `tbltimeobtrip`.`id` AS `id`,
				 `tbltimeobtrip`.`idacct` AS `idacct`,
				 `tbltimeobtrip`.`remarks` AS `remarks`,
				 `tbltimeobtrip`.`date` AS `date`,
				 `tbltimeobtrip`.`stat` AS `stat` 
			  FROM `tbltimeobtrip` 
			  WHERE ISNULL(`tbltimeobtrip`.`cancelby`)) `tto` 
			  ON `tto`.`idacct` = `a`.`id` AND `tto`.`date` = `a`.`work_date`
	LEFT JOIN tblaccount AS c ON c.id = a.id
	LEFT JOIN tblshift AS d ON d.id = b.idshift 
	
	LEFT JOIN tblshift AS f ON f.id = (CASE 
						WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Sunday' THEN e.shiftsun
						WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Monday' THEN e.shiftmon
						WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Tuesday' THEN e.shifttue
						WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Wednesday' THEN e.shiftwed
						WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Thursday' THEN e.shiftthu
						WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Friday' THEN e.shiftfri
						WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Saturday' THEN e.shiftsat
						ELSE b.idshift
					END)
	LEFT JOIN tblholidays AS g ON g.date = a.work_date 
	LEFT JOIN tblholidaytype AS h ON h.id = g.idtype";
	$Qry->selected  = "a.work_date, h.id as holiday_id, DAYNAME('".$date."') as nameDay";
	$Qry->fields    = "a.id = '".$id."' AND a.`work_date` = '".$date."'";
	
	$rs = $Qry->exe_SELECT($con);
	
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			if($row['nameDay'] == 'Saturday' || $row['nameDay'] == 'Sunday' || $row['holiday_id']){
				return true;
			}
		}
	}
	return false;
}

function getPositionId($con, $id){
	$Qry = new Query();	
	$Qry->table     = "vw_dataassign";
	$Qry->selected  = "*";
	$Qry->fields    = "idacct='".$id."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			return $row['idpos'];
		}
	}
	return '';
}

?>