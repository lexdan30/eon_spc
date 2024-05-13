<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	

$search='';

if( array_key_exists('idsuperior', $param) && !empty($param['idsuperior']) ){ 
	if( array_key_exists('units', $param) && !empty($param['units']) ){
		$search=$search." AND (idsuperior 	= '".$param['idsuperior']."' OR idsuperior in (".$param['units'].") ) ";  
	}else{
		$search=$search." AND idsuperior 	= '".$param['idsuperior']."' ";  
	}
}
if( !empty( $param['acct'] ) ){ $search=$search." AND idacct 	= '".$param['acct']."' "; }
if( !empty( $param['docu'] ) ){ $search=$search." AND docnumber like '%".$param['docu']."%' "; }
if( $param['appstat'] == '3' || empty($param['appstat']) ){
	$search=$search." AND stat = '3' "; 
}elseif( $param['appstat'] == '4' ){
	$search=$search." AND stat IN (1,2,3) "; 
}else{
	$search=$search." AND stat = '".$param['appstat']."' "; 
}

if(!empty($param['from'])){
	if(( $param['flag']) == '1'){
		if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search; }
		if( !empty( $param['from'] ) && empty( $param['to'] ) ){ $search=$search; } 
	} 
	else{
		if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['to']."')"; }
		if( !empty( $param['from'] ) && empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['from']."')"; }
	}
}else{
	$data = array( 
		"period"		=> getPayPeriodts($con),
	);

	if(( $param['flag']) == '1'){
		// if( !empty($data['period']['pay_start'] ) && !empty( $data['period']['pay_end'] ) ){ $search=$search; }
		// if( !empty($data['period']['pay_start'] ) && empty( $data['period']['pay_end'] ) ){ $search=$search; }
		if( !empty( $data['period']['pay_start'] ) && !empty( $data['period']['pay_end'] ) ){ $search=$search." AND (date between '".$data['period']['pay_start']."' AND '".$data['period']['pay_end']."')"; }
		if( !empty( $data['period']['pay_start'] ) && empty($data['period']['pay_end'] ) ){ $search=$search." AND (date between '".$data['period']['pay_start']."' AND '".$data['period']['pay_start']."')"; }
	} 
	else{
		if( !empty( $data['period']['pay_start'] ) && !empty( $data['period']['pay_end'] ) ){ $search=$search." AND (date between '".$data['period']['pay_start']."' AND '".$data['period']['pay_end']."')"; }
		if( !empty( $data['period']['pay_start'] ) && empty($data['period']['pay_end'] ) ){ $search=$search." AND (date between '".$data['period']['pay_start']."' AND '".$data['period']['pay_start']."')"; }
	}
}

//sort nga funtion sa table
if( $param['order'][0]['column'] !='' ){//default 
	$arrCols = array("",
					"empname", 
					"date",
					"", 
					"actual_hrs",
					"remarks",
					"ot_status");//mao ra ang mailisan na declare na sa ubos php
	// if( $param['order'][0]['column'] !='' ){//default 
	// 	$arrCols = array("",
	// 					"empname", 
	// 					"date",
	// 					"",
	// 					"planned_hrs", 
	// 					"actual_hrs",
	// 					"remarks",
	// 					"ot_status");
	//$search=$search." ORDER BY ". $arrCols[$param['order'][0]['column']] ." ".$param['order'][0]['dir'].", ".$arrCols[$param['order'][1]['column']] ." ".$param['order'][1]['dir'];//default
	$search=$search." ORDER BY ". $arrCols[$param['order'][0]['column']] ." ".$param['order'][0]['dir'];//default
}

$where = $search;

if( (int)$param['length'] >= 0 ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' && (int)$param['length'] >= 0 ){ 
    $search=$search." OFFSET ".$param['start'];
}



$Qry = new Query();	
$Qry->table     = "vw_overtime_approval"; 
$Qry->selected  = "*";
$Qry->fields    = "id>0".$search; // AND ot_status = 'PENDING'
$rs = $Qry->exe_SELECT($con);
$recFiltered = getTotalRows($con,$where);
if(mysqli_num_rows($rs)>= 1){
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs), 
        "recordsFiltered"=> $recFiltered, 
        "qry"=>$Qry->fields,
        "data"=>array()
    );
    while($row=mysqli_fetch_array($rs)){
		
		if(empty($row['planned_start']) && empty($row['planned_end']) ){
			$drange = 'Early OT';
		}else{
			$ot_s = date('Y-m-d h:i A', strtotime($row['planned_start']));
			$arr_s = explode(" ",$row['planned_start']);
			if( $arr_s[0] == $row['date'] ){
				$ot_s = date('h:i A', strtotime($arr_s[1]));
			}
			
			$ot_f = date('Y-m-d h:i A', strtotime($row['planned_end']));
			$arr_f = explode(" ",$row['planned_end']); 
			if( $arr_f[0] == $row['date'] ){
				$ot_f = date('h:i A', strtotime($arr_f[1]));
			}
			$drange = $ot_s. ' to ' .$ot_f;
		}


		$schedInfo = getschedinfo($con,$row['idacct'],$row['date']);
		if($row['ot_status'] == 'APPROVED' && (!empty($row['act_hrs']) && $row['act_hrs'] != '0.00')){
			$acthrs = $row['act_hrs'];
		}else{
			//$acthrs = getActualHours( $con,$row['idacct'],$row['date']);
			$acthrs = getActualHours( $con,$row['idacct'],$row['date'],$row['planned_date_end'],$schedInfo);
		}
		
		
        $data["data"][] = array(
            'id'        		=> (int)$row['id'],
			'docnumber'			=> $row['docnumber'],	            
			'idacct'			=> $row['idacct'],
			'empid'				=> $row['empid'],
			'empname'			=> $row['empname'],
			'date'				=> $row['date'],
			//'start_time'		=> $row['start_time'],
			//'end_time'			=> $row['end_time'],  
			// 'units'				=> $row['actual_hrs'],
			'units'				=> $acthrs,
			'units2'			=> $row['planned_hrs'],
			'remarks'			=> $row['remarks'],
			//'file'				=> $row['file'],
			'file'				=> '',//$row['file'] ? $row['file'] : '',
			'ot_status'			=> ucwords(strtolower($row['ot_status'])),
			'appr_hour_stat'	=> $row['approve_hr'],
			'date_approve'		=> $row['date_approve'],
			'ot_app'			=> $drange
		);
    }
    $return =  json_encode($data);
}else{
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs), 
        "recordsFiltered"=> mysqli_num_rows($rs),
		"qry"=>$Qry->fields,
        "data"=>array()
    );
    $return =  json_encode($data); 
} 

print $return;
mysqli_close($con);

function getTotalRows($con,$search){
	$Qry = new Query();	
	$Qry->table     = "vw_overtime_approval"; 
	$Qry->selected ="count(*) as total";
	$Qry->fields ="id > 0".$search;
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			return $row['total'];
		}	
	}
	return 0;
}

function getActualHours($con,$idacct,$date,$fdate,$schedInfo){
	$sdate			= $date;
	$fdate			=  $fdate;

	if($schedInfo['fallonweekdayholiday']){
		$stime			=substr(str_replace(' ', '', $schedInfo['timein']),0,5);
		$ftime			= substr(str_replace(' ', '', $schedInfo['timeout']),0,5);
	}else{
		$stime			=substr(str_replace(' ', '', $schedInfo['out']),0,5);
		$ftime			= substr(str_replace(' ', '', $schedInfo['actouthr']),0,5);
	}
	

	print_r($stime . ' = ' . $ftime);
	if((!empty($stime) && !empty($ftime))){
		
		if($schedInfo['fallonweekdayholiday'] && $schedInfo['site'] != '1' && (strtotime( $sdate.' '.$stime.'' ) < strtotime( $sdate.' '.'07:00'.'' )) &&  $schedInfo['driver'] == false){
			$stime = '07:00';
		} 
		//return $stime .' - ' . $ftime;
	
		if($schedInfo['flexi'] && $schedInfo['site'] != '1' && $schedInfo['joblevel'] == 'Mgr' &&$schedInfo['fallonweekdayholiday'] == false){ 
			if((strtotime( $fdate.' '.$ftime.'' ) >= strtotime( $sdate.' '.'18:00'.'' ))){
				$hrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.$ftime.'' ) - strtotime( $sdate.' '.$stime.'' )) / ( 60 * 60 )) - 1 );
			}elseif((strtotime( $fdate.' '.$ftime.'' ) < strtotime( $sdate.' '.'18:00'.'' )) && (strtotime( $fdate.' '.$ftime.'' ) >= strtotime( $sdate.' '.'17:00'.'' ))){
				$hrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.'17:00'.'' ) - strtotime( $sdate.' '.$stime.'' )) / ( 60 * 60 )) );
				$ftime			= '17:00'; 
			}else{
				$hrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.$ftime.'' ) - strtotime( $sdate.' '.$stime.'' )) / ( 60 * 60 )) );
			}
		}elseif($schedInfo['fallonweekdayholiday'] && $schedInfo['joblevel'] == 'RnF'){
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
			$arr_wdbreak = array(); 

			$Qry2 = new Query();	
			$Qry2->table ="tblpreference";	
			$Qry2->selected ="value";
			$Qry2->fields ="alias='WDB'"; 
			$rs2 = $Qry2->exe_SELECT($con);
			if(mysqli_num_rows($rs2)>= 1){
				if($row2=mysqli_fetch_array($rs2)){
					$arr_wdbreak = explode(',',$row2['value']); 
				}
			}

			if(in_array($idacct,$arr_wdbreak)){
				if((strtotime( $fdate.' '.$ftime.'' ) >= strtotime( $sdate.' '.'18:00'.'' ))){
					$hrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.$ftime.'' ) - strtotime( $sdate.' '.$stime.'' )) / ( 60 * 60 )) - 1 );
				}else{
					$hrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.$ftime.'' ) - strtotime( $sdate.' '.$stime.'' )) / ( 60 * 60 )) );
				}
			} else{
				$hrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.$ftime.'' ) - strtotime( $sdate.' '.$stime.'' )) / ( 60 * 60 )) );
			}
			//$hrs		= sprintf('%0.2f', ((strtotime( $fdate.' '.$ftime.'' ) - strtotime( $sdate.' '.$stime.'' )) / ( 60 * 60 )) );
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

		$preotres = $schedInfo['preot_unit'];

		if(!empty($preotres)){
			$preot = sprintf('%0.2f',floatval($preotres));
			$hrs = sprintf('%0.2f', floatval($hrs) + floatval($preot));
		}
	
		return $hrs;
	}else{
		//print_r('im here for jhoanna');
		$preotres = $schedInfo['preot_unit'];

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
				  if($joblevel == 'Mgr' && $row['site']!=1 && $row['idshift'] !='96'){
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
				"timein" =>$timein,	
				"timeout" =>$timeout,		
				"actdouthr"  => $dtimeout,	
				"actouthr"  => $timeout,				
				"br1"	=>  $row['breakin'],
				"br2"	=>  $row['breakout'],
				"site"=>  $site,
				"joblevel"=> $joblevel,
				"driver"=> false,//$driver,
				"fallonweekdayholiday"=> false, //getWeekendOrHoliday($con, $id,  $date),
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
   AS logoutschedref,  `e`.`site` AS `site`,  `e`.`idlvl` AS `idlvl`,`a`.date_out
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
			  "timein" =>$timein,	
			  "timeout" =>$timeout,	
			  "actdouthr"  => $dtimeout,	
			  "actouthr"  => $timeout,	
			  //"br1"	=> $row['breakoutref'],
			  //"br2"	=> $row['breakinref'],
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

  function getWeekendOrHoliday($con, $id, $date){
	$Qry = new Query();	
	$Qry->table     = "vw_datacurrentworkdates2 AS a
	LEFT JOIN tblaccountjob AS i ON i.idacct = a.id
	LEFT JOIN tblholidays AS g ON g.date = a.work_date 
	LEFT JOIN tblholidaytype AS h ON h.id = g.idtype";
	$Qry->selected  = "a.work_date, h.id as holiday_id, DAYNAME('".$date."') as nameDay, 
						g.provcode AS holidayProvid,
						g.munid AS holidayMunid,
						i.munid AS employeeMunid,
						i.provcode As employeeProvid";
	$Qry->fields    = "a.id = '".$id."' AND a.`work_date` = '".$date."'";
	
	$rs = $Qry->exe_SELECT($con);
	
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			if($row['nameDay'] == 'Saturday' || $row['nameDay'] == 'Sunday' || $row['holiday_id']){
				if($row['holiday_id'] && !empty($row['holidayProvid'])){
					if(empty($row['holidayMunid']) && $row['employeeProvid'] == $row['holidayProvid'])
					{
						return true; //provincial holiday
					}elseif($row['holidayMunid'] == $row['employeeMunid']){
						return true; //municipal
					}
				}else{
					return true;//legal and weekend
				}
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