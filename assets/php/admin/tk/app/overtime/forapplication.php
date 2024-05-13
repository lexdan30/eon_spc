<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date_create=SysDate();
$time_create=SysTime();
$time 	   = time();

$shiftout = array(); 

$Qry = new Query();	
$Qry->table     = "vw_timesheet";
$Qry->selected  = "stime,ftime,idshift,breakin,breakout,site,idlvl,timein,timeout,aatimein,aatimeout,csstatus,cs,defaultsched,holiday,aastatus,shifttype,stime,ftime,sstime,sftime";
$Qry->fields = "id = '" .$param->accountid . "' AND work_date = '" . $param->info . "'";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){ 
    if($row=mysqli_fetch_assoc($rs)){
    if($row['idshift'] != '89' && $row['idshift'] != '93'){
		$preot = false;
		$preot_unit = '';
		$preot_min = '';
		//$shiftout = $row['ftime']; 
		if($row['stime'] != null){
			$site =$row['site']; 
		}else{
			$site =getflexinfo($con, $param->accountid, $param->info)['site'];
		}

		if($row['idlvl'] >= 10){
			$joblevel = 'RnF';
		}elseif($row['idlvl'] <10){
			$joblevel = 'Mgr';
		}else{
			$joblevel = '';
		}

		// if(getPositionId($con, $param->accountid) == '112'){ 
		// 	$driver = true;
		// }else{
		$driver = false;
		// }
 
		// if(getWeekendOrHoliday($con, $param->accountid, $param->info) == false){
		// 	if($joblevel == 'Mgr' && $row['site']!=1 && $row['idshift'] !='96'){
		// 		$out='18:00:00'; //ignore this during save trapping
		// 	}else{
		// 		$out=$row['ftime'];
		// 	}
		// }else{
		$out=$row['ftime'];
		// }
		//cs
		if($row['csstatus'] == '1'){
			$csinfo = getcs($con, $row['cs']);
			$row['stime'] = $csinfo['in'];
			$out=$csinfo['out'];
		}
		//in actual vs aa
		if(!empty($row['timein'])){
			$timein = $row['timein'];
		}else{
			$timein = '';
		}
		if(!empty($row['aatimein'])){
			$timein = $row['aatimein'];
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

		// if($row['idshift'] == '88'){
		// 	$preot = true;
		// 	$preot_unit = getPreOT($con, $param->accountid, $param->info)["earlyot"];
		// 	$preot_min = getPreOT($con, $param->accountid, $param->info)["earlyotm"]. ' minute/s';
		// 	if($preot_unit == '0.0000' || $preot_unit == '0.00' || empty($preot_unit)){
		// 		$preot_unit = '';
		// 		$preot_min = '';
		// 	}
		// }

		//changeshift
		if($row['csstatus'] == '1'){ 
			$csstat = 'cs_approved';
		}elseif($row['csstatus'] == '3'){
			$csstat = 'cs_pending'; 
		}else{
			$csstat = '';
		}

		//aastatus
		if($row['aastatus'] == '1'){
			$aastat = 'aa_approved';
		}elseif($row['aastatus'] == '3'){
			$aastat = 'aa_pending';
		}else{
			$aastat = ''; 
		}

		//brokenshift
		$brokenshift = ($row['shifttype'] == 'Broken Schedule') ? true : false; 
		
		$jobalias =  jobalias($con, $row['idlvl']);
		if($jobalias == 'GS' || $jobalias == 'MS'){
			$isHoliday = getWeekendOrHoliday($con, $param->accountid, $param->info);
			$isSecRD = issecrestday($con, $param->accountid, $param->info); 
		}else{
			$isHoliday = false; // auto false for DH and SE
			$isSecRD = false; // auto false for DH and SE
		}
		
        $shiftout = array(				
          "in"	=> $row['stime'],
		  "out"  => $out,
		  "timein" =>$timein,	
		  "timeout" =>$timeout,					
          "br1"	=>  $row['breakin'],
		  "br2"	=>  $row['breakout'],
		  "site"=>  $site,
		  "joblevel"=> $joblevel,
		  "jobalias"=> $jobalias,
		  "driver"=> $driver,
		  "fallonweekdayholiday"=> false,//getWeekendOrHoliday($con, $param->accountid, $param->info),
		  "holiday_ctoearning"=> $isHoliday,
		  "secrestday_ctoearning"=> $isSecRD,
		  "flexi" => false,
		  "preot" => $preot,
		  "preot_unit" => $preot_unit, 
		  "preot_min" => $preot_min,
		  "brokenshift" => $brokenshift,
		  "holiday" => $row['holiday'],
		  "csstat" => $csstat, // for OT trapping new
		  "aastat" => $aastat, // for OT trapping new,
		  "shalfout" => $row['sftime']
		);
		
	 }
	  else{
        $shiftout = getflexinfo($con, $param->accountid, $param->info);
      }
    }

    $return = json_encode($shiftout); 
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);

function getPreOT($con, $id, $date){ // unused function
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

function getcs($con, $id){
	$data = array();
	$Qry = new Query();	
	$Qry->table     = "tblshift";
	$Qry->selected  = "stime, ftime";
	$Qry->fields    = "id='".$id."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_assoc($rs)){
			$data = array( 
				"in"  => $row['stime'],					
				"out"	=> $row['ftime']
			  );
		}
	}
	return $data;
}

function getflexinfo($con, $id, $date){
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
  $Qry->selected      = "  `k1`.`date` AS `aadate_in`,
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

			$driver = false;
			// not applicable trapp during otsave
			//in actual vs aa
			if(!empty($row['timein'])){
				$timein = $row['timein'];
			}else{
				$timein = '';
			}
			if(!empty($row['aatimein'])){
				$timein = $row['aatimein'];
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
          $data = array( 
            "in"  => $row['loginschedref'],					
			"out"	=> $row['logoutschedref'],
			"timein" =>$timein,	
			"timeout" =>$timeout,
            "br1"	=> $row['breakoutref'],
			"br2"	=> $row['breakinref'],
			"site"=>  $row['site'],
		  	"driver"=> $driver,
			"joblevel"=> $joblevel,
			"fallonweekdayholiday"=> getWeekendOrHoliday($con, $id, $date),
			"flexi" => true,
			"preot" => false,
			"preot_unit" => '',
			"preot_min" => ''
          );
      }
  }

  return $data;
}

function getWeekendOrHoliday($con, $id, $date){
	$Qry = new Query();	
	$Qry->table     = "
	(SELECT work_date, id FROM vw_datacurrentworkdates2) AS a
	 LEFT JOIN (SELECT munid, provcode, idacct FROM tblaccountjob) AS i ON 
	  (i.idacct = a.id)
	 LEFT JOIN (SELECT `date`, idtype, provcode, munid FROM tblholidays) AS g ON 
	  (g.date = a.work_date) 
	 LEFT JOIN (SELECT id FROM tblholidaytype) AS h ON 
	  (h.id = g.idtype)";
	  $Qry->selected  = "
	  a.work_date,
	  h.id AS holiday_id,
	  DAYNAME('".$date."') AS nameDay, 
	  g.provcode AS holidayProvid,
	  g.munid AS holidayMunid,
	  i.munid AS employeeMunid,
	  i.provcode AS employeeProvid";
	$Qry->fields    = "a.id = '".$id."' AND a.`work_date` = '".$date."'";
	
	$rs = $Qry->exe_SELECT($con);
	
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_assoc($rs)){ 
			// if($row['nameDay'] == 'Saturday' || $row['nameDay'] == 'Sunday' || $row['holiday_id']){
			// 	if($row['holiday_id'] && !empty($row['holidayProvid'])){
			// 		if(!empty($row['holidayProvid'])){
			// 			if(empty($row['holidayMunid']) && $row['employeeProvid'] == $row['holidayProvid'])
			// 			{
			// 				return true; //provincial holiday
			// 			}elseif($row['holidayMunid'] == $row['employeeMunid']){
			// 				return true; //municipal
			// 			}
			// 		}
			// 	}else{
			// 		return true; //legal and weekend
			// 	}
			// }
			if($row['holiday_id']){
				return true;
			}

		}
	}
	return false;
}

function issecrestday($con, $id, $date){
	$Qry = new Query();	
	$Qry->table     = "vw_timesheetfinal";
	$Qry->selected  = "COUNT(work_date) AS `count`,holidaytype,DATE_ADD('".$date."', INTERVAL  -WEEKDAY('".$date."'-INTERVAL 1 DAY)-1 DAY) first_range,
	DATE_ADD(DATE_ADD('".$date."', INTERVAL -WEEKDAY('".$date."'-INTERVAL 1 DAY)-1 DAY), INTERVAL 6 DAY) last_range";
	$Qry->fields    = "tid = '".$id."' AND FPidshift = 4 AND work_date BETWEEN DATE_ADD('".$date."', INTERVAL  -WEEKDAY('".$date."'-INTERVAL 1 DAY)-1 DAY)
	AND DATE_ADD(DATE_ADD('".$date."', INTERVAL  -WEEKDAY('".$date."'-INTERVAL 1 DAY)-1 DAY), INTERVAL 6 DAY)";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_assoc($rs)){  

			if($row['holidaytype'] && $row['count']>1){
				if(checkmaxrd($con, $id, $date, $row['first_range'], $row['last_range'])){
					return true;
				}
			} 
		}
	}
	return false;
}

function checkmaxrd($con,$id,$date,$firstdate,$lastdate){
	$Qry = new Query();	
	$Qry->table     = "vw_timesheetfinal";
	$Qry->selected  = "work_date";
	$Qry->fields    = "tid='".$id."' AND FPidshift = 4 AND work_date BETWEEN '".$firstdate."' AND '".$lastdate."' ORDER BY work_date DESC LIMIT 1";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_assoc($rs)){
			if($row['work_date']==$date){
				return true;
			} 
		}
	}
	return false;
} 

function getPositionId($con, $id){
	$Qry = new Query();	
	$Qry->table     = "vw_dataassign";
	$Qry->selected  = "idpos";
	$Qry->fields    = "idacct='".$id."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		return mysqli_fetch_assoc($rs)['idpos'];
	}
	return '';
} 

function jobalias($con,$lvl){
	$Qry = new Query();	
	$Qry->table     = "tbljoblvl";
	$Qry->selected  = "alias";
	$Qry->fields    = "id='".$lvl."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_assoc($rs)){ 
			return $row['alias']; 
		}
	}
	return '';
} 
?>