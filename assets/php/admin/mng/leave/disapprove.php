<?php
require_once('../../../logger.php');
require_once('../../../activation.php');
$conn = new connector();
$con  = $conn->connect();
require_once('../../../classPhp.php');

$param       = json_decode(file_get_contents('php://input'));
$date_action = date("Y-m-d", strtotime(SysDate()));

foreach ($param->info as $key => $value) {
    $idleave   = $value->idleave;
    $leaveid   = $value->leaveid;
    $apprseq   = $value->approversequence;

    $apprcount = $value->approvercount;
    $hrs       = $value->hours;
    $appid       = $value->appid;

     $remarks   = $value->remarks;
     $period_start       = $value->period_start;
     $period_end       = $value->period_end;
     $grace_hour       = $value->grace_hour;
     $date       = $value->date;

     $Qry        = new Query();
     $Qry->table = "tbltimeleaves";

     if ($apprseq == 1) {
         $Qry->selected = "stat = '2',
                             date_approve ='" . SysDate() . "',
                             approver1_stat='2',
                             approver1_date='" . SysDate() . "',
                             approver1_reason='" . $remarks . "',
                             approver1_time='" . SysTime() . "'";
     }
     if ($apprseq == 2) {
         $Qry->selected = "stat = '2',
                             date_approve ='" . SysDate() . "',
                             approver2_stat='2',
                             approver2_date='" . SysDate() . "',
                             approver2_reason='" . $remarks . "',
                             approver2_time='" . SysTime() . "'";    
     }
     if ($apprseq == 3) {
         $Qry->selected = "stat = '2',
                             date_approve ='" . SysDate() . "',
                             approver3_stat='2',
                             approver3_date='" . SysDate() . "',
                             approver3_reason='" . $remarks . "',
                             approver3_time='" . SysTime() . "'";
     }
     if ($apprseq == 4) {
         $Qry->selected = "stat = '2',
                             date_approve ='" . SysDate() . "',
                             approver4_stat='2',
                             approver4_date='" . SysDate() . "',
                             approver4_reason='" . $remarks . "',
                             approver4_time='" . SysTime() . "'";
     }

     $date_approve = SysTime();

     $Qry->fields = "id='" . $idleave . "'";
     $checke =  $Qry->exe_UPDATE($con);

     if($checke){
        $Qry2        = new Query();
        $Qry2->table = "tblaccountleaves";
        $Qry2->selected = "pending_bal = pending_bal - ".$hrs."";
        $Qry2->fields = "idacct = '".$appid ."' AND idleave =  '".$leaveid ."'";
        $checke2 =  $Qry2->exe_UPDATE($con);

        $period_start = strtotime( $period_start );			
        $period_end   = date("Y-m-d",strtotime('+'.$grace_hour.' hours', strtotime( $period_end ))) ;
        if($apprseq > 1){
            if($leaveid == 1 || $leaveid == 9 || $leaveid == 11 || $leaveid == 5 || $leaveid == 21 || $leaveid == 12 || $leaveid == 23){
                //convert to LWOP
                if(convertibletoVL($con,$appid,$hrs)){
                    //start update
                    if ($apprcount == $apprseq) {
                        $Qry12        = new Query();
                        $Qry12->table = "tblaccountleaves";
                        $Qry12->selected = "pending_bal = pending_bal + ".$hrs."";
                        $Qry12->fields = "idacct = '".$appid ."' AND idleave =  '2'";
                        $checke12 =  $Qry12->exe_UPDATE($con);

                        $Qry8        = new Query();
                        $Qry8->table = "tbltimeleaves";
                
                        if ($apprseq == 1) {
                            $Qry8->selected = "stat = '1',
                                                date_approve ='" . SysDate() . "',
                                                approver1_stat='1',
                                                approver1_date='" . SysDate() . "',
                                                approver1_time='" . SysTime() . "',
                                                idleave='2',
                                                remarks=CONCAT(remarks,' - ','Converted to VL')";    
                        }
                        if ($apprseq == 2) {
                            $Qry8->selected = "stat = '1',
                                                date_approve ='" . SysDate() . "',
                                                approver2_stat='1',
                                                approver2_date='" . SysDate() . "',
                                                approver2_time='" . SysTime() . "',
                                                idleave='2',
                                                remarks=CONCAT(remarks,' - ','Converted to VL')";      
                        }
                        if ($apprseq == 3) {
                            $Qry8->selected = "stat = '1',
                                                date_approve ='" . SysDate() . "',
                                                approver3_stat='1',
                                                approver3_date='" . SysDate() . "',
                                                approver3_time='" . SysTime() . "',
                                                idleave='2',
                                                remarks=CONCAT(remarks,'-',Convert to VL)";
                        }
                        if ($apprseq == 4) {
                            $Qry8->selected = "stat = '1',
                                                date_approve ='" . SysDate() . "',
                                                approver4_stat='1',
                                                approver4_date='" . SysDate() . "',
                                                approver4_time='" . SysTime() . "',
                                                idleave='2',
                                                remarks=CONCAT(remarks,' - ','Converted to VL')";    
                        }
    
                        $date_approve = SysTime();
                        $Qry8->fields = "id='" . $idleave . "'";
                        $checke8 =  $Qry8->exe_UPDATE($con);
    
                        if($checke8){
                            $Qry13        = new Query();
                            $Qry13->table = "tblaccountleaves";
                            $Qry13->selected = "used = used + ".$hrs.", pending_bal = pending_bal - ".$hrs."";
                            $Qry13->fields = "idacct = '".$appid ."' AND idleave =  '2'";
                            $checke13 =  $Qry13->exe_UPDATE($con);
                        }
                    }
                }else{
                    //convert to LWOP
                    if(convertibletoLWOP($con,$appid,$hrs)){
                        //start update
                        if ($apprcount == $apprseq) {
                            
                            $Qry14        = new Query();
                            $Qry14->table = "tblaccountleaves";
                            $Qry14->selected = "pending_bal = pending_bal + ".$hrs."";
                            $Qry14->fields = "idacct = '".$appid ."' AND idleave =  '3'";
                            $checke14 =  $Qry14->exe_UPDATE($con);

                            $Qry8        = new Query();
                            $Qry8->table = "tbltimeleaves";

                            if ($apprseq == 1) {
                                $Qry8->selected = "stat = '1',
                                                    date_approve ='" . SysDate() . "',
                                                    approver1_stat='1',
                                                    approver1_date='" . SysDate() . "',
                                                    approver1_time='" . SysTime() . "',
                                                    idleave='3',
                                                    remarks=CONCAT(remarks,' - ','Converted to LWOP')";    
                            }
                            if ($apprseq == 2) {
                                $Qry8->selected = "stat = '1',
                                                    date_approve ='" . SysDate() . "',
                                                    approver2_stat='1',
                                                    approver2_date='" . SysDate() . "',
                                                    approver2_time='" . SysTime() . "',
                                                    idleave='3',
                                                    remarks=CONCAT(remarks,' - ','Converted to LWOP')";      
                            }
                            if ($apprseq == 3) {
                                $Qry8->selected = "stat = '1',
                                                    date_approve ='" . SysDate() . "',
                                                    approver3_stat='1',
                                                    approver3_date='" . SysDate() . "',
                                                    approver3_time='" . SysTime() . "',
                                                    idleave='3',
                                                    remarks=CONCAT(remarks,'-',Convert to LWOP)";
                            }
                            if ($apprseq == 4) {
                                $Qry8->selected = "stat = '1',
                                                    date_approve ='" . SysDate() . "',
                                                    approver4_stat='1',
                                                    approver4_date='" . SysDate() . "',
                                                    approver4_time='" . SysTime() . "',
                                                    idleave='3',
                                                    remarks=CONCAT(remarks,' - ','Converted to LWOP')";    
                            }

                            $date_approve = SysTime();
                            $Qry8->fields = "id='" . $idleave . "'";
                            $checke8 =  $Qry8->exe_UPDATE($con);

                            if($checke8){
                                $Qry15        = new Query();
                                $Qry15->table = "tblaccountleaves";
                                $Qry15->selected = "used = used + ".$hrs.", pending_bal = pending_bal - ".$hrs."";
                                $Qry15->fields = "idacct = '".$appid ."' AND idleave =  '3'";
                                $checke15 =  $Qry15->exe_UPDATE($con);
                            }
                        }
                    }
                }
            }
        }
        //for SL,EL,BL,PL,WL,ML,CL = (idleave:1,9,11,5,21,12,23) convertion if leave is not 0
        //for Kajima requirement
        

       if( $date_action > $period_end ){
            $timesheet_data = getTimeSheetData($con, $appid, $date);
            if( !empty( $timesheet_data ) ){
                $curr_pay_period = getLatePayPeriod($con, $date_action);
                if( addToBackuptimesheet($con,$timesheet_data) ){
                    if( (int)updateTimesheetLates($con, $date, $appid) == 1 ){
                        if( !checkLateApproveExists($con, $timesheet_data['id'] ) ){
                            //$curr_pay_period = getLatePayPeriod($con, $date_action);					
                            $Qry4           = new Query();
                            $Qry4->table    = "tbltimeleaves";
                            $Qry4->selected = "id_payperiod='".$curr_pay_period['id']."'";
                            $Qry4->fields   = "id='".$appid."'";                    
                            $checke4 		= $Qry4->exe_UPDATE($con);
                            if( $checke4 ){
                                $return = insertLateApprove($con, $timesheet_data['id'], SysDate(), $curr_pay_period['pay_date'], $curr_pay_period['id']);
                            }
                        }
                    }					
                }else{
                    $return = json_encode(array("status"=>"success","w"=>"not added to backup","e"=>$curr_pay_period));
                    print $return;
                    mysqli_close($con);
                    return;
                }
            }
            
        }else{	
            $timesheet_data = getTimeSheetData($con, $appid, $date);
            if( !empty( $timesheet_data ) ){
                if( addToBackuptimesheet($con,$timesheet_data) ){
                    $return = updateTimesheetLates($con, $date, $appid);
                }
            }
        }
    }
}
 

$return = json_encode(array(
    "status" => "success"
)); 

print $return;
mysqli_close($con);

function convertibletoVL($con,$idacct,$hrs){
    $Qry = new Query();	
    $Qry->table     = "vw_leavesummary";
    $Qry->selected  = "balance, pending";
    $Qry->fields    = "idacct='".$idacct."' AND idleave = '2'";
    $rs = $Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'convertibletoVL');
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            $balance_hrs	= floatval( number_format($row['balance'],2) );
			if( $balance_hrs > (int)$balance_hrs ){
				$balance_hrs = number_format($balance_hrs,2);
			}

            $pending_hrs	= floatval( number_format($row['pending'],2) );
			if( $pending_hrs > (int)$pending_hrs ){
				$pending_hrs = number_format($pending_hrs,2);
            }

            $available_hrs	= floatval($balance_hrs - ( $pending_hrs));
			if( $available_hrs > (int)$available_hrs ){
				$available_hrs	= number_format($available_hrs,2);
			}
            
            $pending_day = floatval( $pending_hrs ) / 8;
			if( $pending_day > (int)$pending_day ){
				$pending_day	= number_format($pending_day,2);
			}

            $balance_day = floatval( $balance_hrs ) / 8;
			if( $balance_day > (int)$balance_day ){
				$balance_day	= number_format($balance_day,2);
			}


            $available_day	= floatval($balance_day - ( $pending_day));
			if( $available_day > (int)$available_day ){
				$available_day	= number_format($available_day,2);
            }

            if( $available_hrs >= $hrs ){
                return true;
            }else{
                return false;
            }
        }
    }
    return false;
}

function convertibletoLWOP($con,$idacct,$hrs){
    $Qry = new Query();	
    $Qry->table     = "vw_leavesummary";
    $Qry->selected  = "balance, pending";
    $Qry->fields    = "idacct='".$idacct."' AND idleave = '3'";
    $rs = $Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'convertibletoLWOP');
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            $balance_hrs	= floatval( number_format($row['balance'],2) );
			if( $balance_hrs > (int)$balance_hrs ){
				$balance_hrs = number_format($balance_hrs,2);
			}

            $pending_hrs	= floatval( number_format($row['pending'],2) );
			if( $pending_hrs > (int)$pending_hrs ){
				$pending_hrs = number_format($pending_hrs,2);
            }

            $available_hrs	= floatval($balance_hrs - ( $pending_hrs));
			if( $available_hrs > (int)$available_hrs ){
				$available_hrs	= number_format($available_hrs,2);
			}
            
            $pending_day = floatval( $pending_hrs ) / 8;
			if( $pending_day > (int)$pending_day ){
				$pending_day	= number_format($pending_day,2);
			}

            $balance_day = floatval( $balance_hrs ) / 8;
			if( $balance_day > (int)$balance_day ){
				$balance_day	= number_format($balance_day,2);
			}


            $available_day	= floatval($balance_day - ( $pending_day));
			if( $available_day > (int)$available_day ){
				$available_day	= number_format($available_day,2);
            }

            if( $available_hrs >= $hrs ){
                return true;
            }else{
                return false;
            }
        }
    }
    return false;
}

function updateTimesheetLates($con, $date, $idacct, $updateShift=NULL){
    $return_me = 0;
    $pay_period = getPayPeriod($con);
    $shift_cols = array("monday"	=>"idmon,mon,mon_in,mon_out,mon_brkin,mon_brkout", 
                        "tuesday"	=>"idtue,tue,tue_in,tue_out,tue_brkin,tue_brkout",
                        "wednesday"	=>"idwed,wed,wed_in,wed_out,wed_brkin,wed_brkout",
                        "thursday"	=>"idthu,thu,thu_in,thu_out,thu_brkin,thu_brkout",
                        "friday"	=>"idfri,fri,fri_in,fri_out,fri_brkin,fri_brkout",
                        "saturday"	=>"idsat,sat,sat_in,sat_out,sat_brkin,sat_brkout", 
                        "sunday"	=>"idsun,sun,sun_in,sun_out,sun_brkin,sun_brkout");
    //process
    $Qry = new Query();	
    $Qry->table     = "
    (SELECT id, work_date FROM vw_datacurrentworkdates2) as a LEFT JOIN
    (SELECT `date`, id FROM vw_dataholidays) AS b ON (a.work_date = b.date)";    $Qry->selected  = "a.work_date, a.id AS acct_id, b.id AS holiday_id";
    $Qry->fields    = "a.work_date = '".$date."' and a.id='".$idacct."'";
    $rs = $Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'updateTimesheetLates');
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            if( empty( $updateShift ) ){
                $shift_field= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];				
                $shift_info = getDateShiftData( $con, $row['acct_id'], $shift_field, $row['work_date'] );
            }else{
                $shift_info = getShiftIDData( $con, $updateShift );
            }
            
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
            $date_in 	= $row['work_date'];
            $date_out 	= $row['work_date'];
            $date_shiftin 	= $row['work_date'];
            $date_shiftout 	= $row['work_date'];
            $date_shiftbrkin= $row['work_date'];
            $date_shiftbrkout= $row['work_date'];
                                    
            $regconti=0;
            
            if( strtotime( $date_shiftin.' '.$shiftin ) > strtotime( $date_shiftout.' '.$shiftout ) ){
                $date_shiftout 	= date('Y-m-d', strtotime("+1 day", strtotime($date_shiftout)));
            }
            if( strtotime( $date_shiftbrkin.' '.$shiftbrkin ) > strtotime( $date_shiftbrkout.' '.$shiftbrkout ) ){
                $date_shiftbrkout 	= date('Y-m-d', strtotime("+1 day", strtotime( $date_out )));
            }
            
            if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
                $reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
                $regconti=1;
            }else{
                $reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin))) / ( 60 * 60 ));
                $regconti=2;
            }
            
            if( strtotime($date_shiftout.' '.$shiftout) < strtotime($date_shiftin.' '.$shiftin) ){
                $date_out = date('Y-m-d', strtotime("+1 day", strtotime( $row['work_date']  )));
                $reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin))) / ( 60 * 60 ));
                $regconti=99;
            }		
            
            $acthrs		= '0';
            $excess		= '0';
            $absent		= $reghrs;
            $late		= '0';
            $ut			= '0';
            $ot 		= '0';
            $np 		= '0';
            
            //Adjustment Applications
            $aa_data = getAttendanceApplication( $con, $row['work_date'], $row['acct_id'], $pay_period['pay_start'], date("Y-m-d", strtotime('+'.$pay_period['grace_hour'].' hours', strtotime($pay_period['pay_end']))));
            
            //Leave Applications
            $leave_data = getLeaveApplication( $con, $row['work_date'], $row['acct_id'],  $pay_period['pay_start'], date("Y-m-d", strtotime('+'.$pay_period['grace_hour'].' hours', strtotime($pay_period['pay_end'])))  );
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
                    if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
                        $absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
                    }else{
                        $absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
                    }
                }else{
                    if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
                        $reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
                        $regconti=4;
                    }else{
                        $reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
                        $regconti=5;
                    }
                    $absent		= '0';
                }
            }
            
            $Qry2 = new Query();	
            $Qry2->table     = "vw_datatimelogs";
            $Qry2->selected  = "acct_id, work_date, date_in, time_in, date_out, CASE WHEN STR_TO_DATE(CONCAT(date_out,' ', time_out), \"%M %d %Y\") < STR_TO_DATE(CONCAT(date_in,' ', time_in), \"%M %d %Y\") THEN NULL ELSE time_out END AS time_out";
            $Qry2->fields    = "work_date = '".$row['work_date']."' AND acct_id='".$row['acct_id']."' ";
            $rs2 = $Qry2->exe_SELECT($con);
            Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'updateTimesheetLates(Qry2)');
            if(mysqli_num_rows($rs2)>= 1){
                $filter_acthrs=0;
                if($row2=mysqli_fetch_assoc($rs2)){	
                    $date_in	= $row2['date_in'];
                    $date_out	= $row2['date_out'];
                    if( !empty($aa_data['id']) ){
                        if( empty( $row2['time_in'] ) ){
                            $in			= $aa_data['stime'];
                            $date_in	= $aa_data['date'];
                        }else{
                            $in			= $row2['time_in'];
                            $date_in	= $row2['date_in'];
                        }					
                        if( empty( $row2['time_out'] ) ){
                            $out		= $aa_data['ftime'];
                            $date_out	= $aa_data['date'];
                        }else{						
                            $out		= $row2['time_out'];
                            $date_out	= $row2['date_out'];						
                        }
                        if( !empty($out) && !empty($in) ){
                            $acthrs		= sprintf('%0.2f', (strtotime($date_out.' '.$out) - strtotime($date_in.' '.$in)) / ( 60 * 60 ));
                        }
                        $absent		= '0';						
                    }
                    if( empty($leave_data['id']) &&  empty($aa_data['id']) ){	
                        $in			= $row2['time_in'];
                        $out		= $row2['time_out'];
                        $date_in	= $row2['date_in'];
                        $date_out	= $row2['date_out'];
                        if( !empty($out) && !empty($in) ){
                            $acthrs		= sprintf('%0.2f', (strtotime($date_out.' '.$out) - strtotime($date_in.' '.$in)) / ( 60 * 60 ));
                        }
                        $absent		= '0';	
                    }
                    if( !empty( $row['holiday_id'] ) ){ // DETERMINE IF HOLIDAY
                        if( (int)getBeforeHoliday($con, $row['work_date'], $row['acct_id']) > 0 ){
                            if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
                                $absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
                            }else{
                                $absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
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
                        $condi=0;
                        if( strtotime( $date_in.' '.$in ) > strtotime( $date_shiftin.' '.$shiftin ) && strtotime( $date_in.' '.$in ) < strtotime( $date_shiftout.' '.$shiftout ) ){ // TIME IN IS GREATER THAN SHIFT IN
                            $late   = sprintf('%0.2f', (strtotime($date_in.' '.$in) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 )); $condi=1;
                        }
                        if( !empty($shiftbrkout) && strtotime( $date_in.' '.$in ) > strtotime( $date_shiftbrkout.' '.$shiftbrkout ) ){ // TIMEIN GREATER THAN BREAKOUT
                            $late   = sprintf('%0.2f', (strtotime($date_in.' '.$in) - strtotime($date_shiftbrkout.' '.$shiftbrkout)) / ( 60 * 60 ));
                            $absent = sprintf('%0.2f', (strtotime($date_shiftbrkin.' '.$shiftbrkin) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 )); $condi=2;
                        }
                        if( !empty($shiftbrkin) && !empty($shiftbrkout) && 
                            ((strtotime( $date_in.' '.$in ) >= strtotime( $date_shiftbrkin.' '.$shiftbrkin ) && strtotime( $date_in.' '.$in ) <= strtotime( $date_shiftbrkout.' '.$shiftbrkout )) || 
                            (strtotime( $date_out.' '.$out ) >= strtotime( $date_shiftbrkin.' '.$shiftbrkin ) && strtotime( $date_out.' '.$out ) <= strtotime( $date_shiftbrkout.' '.$shiftbrkout )))  ){ // TIMEIN IN BETWEEN BREAK SCHEDULE
                            $late	= '0'; $condi=3;
                            if( strtotime( $date_in.' '.$in ) > strtotime( $date_shiftin.' '.$shiftin ) && strtotime( $date_in.' '.$in ) < strtotime( $date_shiftout.' '.$shiftout ) ){ // TIME IN IS GREATER THAN SHIFT IN
                                $late   = sprintf('%0.2f', (strtotime($date_in.' '.$in) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 )); $condi=4;
                            }						
                            $absent = sprintf('%0.2f', (strtotime($date_shiftbrkin.' '.$shiftbrkin) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ));
                        }					
                        if( strtotime( $date_in.' '.$in ) >  strtotime( $date_shiftout.' '.$shiftout ) ){
                            $absent = $reghrs;
                            $acthrs	= '0';
                            $excess	= '0';
                        }				
                        //UNDERTIME
                        if( strtotime( $date_shiftout.' '.$shiftout ) > strtotime( $date_out.' '.$out ) ){	// TIME OUT IS LESSER THAN SHIFT OUT
                            $ut   	= sprintf('%0.2f', (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_out.' '.$out)) / ( 60 * 60 ));
                        }
                        if( strtotime($date_out.' '.$out) >= strtotime($date_shiftbrkin.' '.$shiftbrkin) && strtotime($date_out.' '.$out) <= strtotime($date_shiftbrkout.' '.$shiftbrkout)  ){ // TIME OUT BETWEEN BREAK SCHEDULE
                            $ut   	= '0';
                            
                        }	
                        if( strtotime( $date_shiftbrkin.' '.$shiftbrkin ) > strtotime( $date_out.' '.$out ) ){	// TIME OUT IS LESSER THAN BREAK IN
                            $absent = $reghrs;
                            $ut		= '0';
                            $late	= '0';
                            $acthrs	= '0';
                            $regconti=6;
                        }
                        if( ( strtotime( $date_in.' '.$in ) >= strtotime( $date_shiftbrkin.' '.$shiftbrkin ) && strtotime( $date_in.' '.$in ) <= strtotime( $date_shiftbrkout.' '.$shiftbrkout )) && strtotime($date_out.' '.$out) < strtotime($date_shiftout.' '.$shiftout) ){ // TIME IN BETWEEN BREAK SCHEDULE AND TIME OUT LESS THAN SHIFT SCHED
                            $absent = $reghrs;
                            $ut		= '0';
                            $late	= '0';
                            $acthrs	= '0';
                            $regconti=7;
                        }	
                    }
                }
            }else{
                if( !empty($aa_data['id']) ){
                    $in			= $aa_data['stime'];
                    $out		= $aa_data['ftime'];
                    $date_in	= $aa_data['date'];
                    $date_out	= $aa_data['date'];
                    if( !empty($out) && !empty($in) ){
                        $acthrs		= sprintf('%0.2f', (strtotime($date_out.' '.$out) - strtotime($date_in.' '.$in)) / ( 60 * 60 ));
                    }
                    $absent		= '0';	
                }			
                if( !empty($aa_data['id']) ){
                    if( strtotime( $date_in.' '.$in ) > strtotime( $date_shiftin.' '.$shiftin ) ){
                        $late   = sprintf('%0.2f', (strtotime($date_in.' '.$in) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ));
                    }
                    if( strtotime( $date_shiftout.' '.$shiftout ) > strtotime( $date_out.' '.$out ) ){
                        $ut   	= sprintf('%0.2f', (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_out.' '.$out)) / ( 60 * 60 ));
                    }
                }
            }
            if( strtotime( $acthrs ) > strtotime($reghrs) ){
                $excess	= sprintf('%0.2f', (strtotime($date_out.' '.$out) - strtotime($date_shiftout.' '.$shiftout)) / ( 60 * 60 ));			
            }
            if( $excess < 0 ){ $excess=0; }
            
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
            $date_shift_out = $date_shiftout;
            $date_night_p   = $date_shiftin;		
            $np	= '0';
            if( !empty( $date_out ) && !empty( $date_in ) && $absent == '0'){			
                if( strtotime( $date_out. ' ' .$out ) > strtotime($date_night_p . ' ' . $nightp) ){
                    if( strtotime( $date_shiftout. ' ' .$shiftout ) > strtotime($date_out. ' ' .$out) ){
                        $np	= sprintf('%0.2f', ( strtotime( $date_out. ' ' .$out ) - strtotime($date_night_p . ' ' . $nightp)) / ( 60 * 60 ));
                    }else{
                        $np	= sprintf('%0.2f', ( strtotime( $date_shiftout. ' ' .$shiftout ) - strtotime($date_night_p . ' ' . $nightp)) / ( 60 * 60 ));
                    }
                }
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
                "id_payperiod"  =>  $pay_period['id'],
                "date_in"		=>  $date_in,
                "date_out"		=>  $date_out
            );


            
            if( checkTimeSheetExists($con, $idacct, $date ) ){			 
                $return = updateTimeSheetRec($con, $data);
                $return_me = 1;
            }
        }			
    }
    return $return_me;
}

?>