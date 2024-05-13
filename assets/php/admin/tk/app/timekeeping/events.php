<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$return = null;	
$data[] = array();
$flag = "eventphp";
$flag2 = "";


$Qry = new Query();	
$Qry->table     = "vw_overtime_approval";
$Qry->selected  = "remarks, id, creator, `date`, ot_status, docnumber, planned_date_start, planned_date_end, planned_time_start, planned_time_end";

if($param->date == ''){
    $Qry->fields    = "CAST(idacct AS INT) = '".$param->accountid."' 
                        AND ( year( CURRENT_DATE()) = year(planned_date_start)
                        and month( CURRENT_DATE()) = month(planned_date_start) )";
}else{
    $date = $param->date;
    $Qry->fields    = "CAST(idacct AS INT) = '".$param->accountid."' 
                    AND ( year('". $date ."') = year(planned_date_start)
                    and month('". $date ."') = month(planned_date_start) )";
}

//$Qry->fields    = "idacct = '".$param->accountid."'  AND YEAR(date) = YEAR(CURRENT_DATE())  GROUP BY id, `date`,stat";
$Qry->fields    = "CAST(idacct AS INT) = '".$param->accountid."'  GROUP BY id, DATE(`date`),stat";
$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));

if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        $start= $row['planned_date_start'];
        $end = $row['planned_date_end'];
        $startt= $row['planned_time_start'];
        $endt = $row['planned_time_end'];

        $data[] = array( 
            "application"       => 'overtime',
            "remarks"           => $row['remarks'],
            "title" 			=> 'Overtime',
            "ids" 			    => $row['id'],
			"creator"			=> $row['creator'],
            "start" 			=> $start,
            "end" 		    	=> $end,
            "backgroundColor"   => '#008080',
            "status"            => $row['ot_status'],
            "startt" 			=> $startt,
            "endt" 		    	=> $endt,
            'sort'              => 9,
            'ticketno'          => $row['docnumber'],
            'flag'              => $flag,
            'hrs'               => getHrs($con,$row['id'])

        );
    }
}

$Qry2 = new Query();	
$Qry2->table     = "vw_attendance_application";
$Qry2->selected  = "sstime, sftime, `date`, adj_status, creator, id, docnumber, file, ftime, stime,stat,
approver1,approver2,approver3,approver4,approver1_stat, approver2_stat, approver3_stat, approver4_stat";

if($param->date == ''){
    $Qry2->fields    = "CAST(idacct AS INT) = '".$param->accountid."' 
                        AND ( year( CURRENT_DATE()) = year(date)
                        and month( CURRENT_DATE()) = month(date) )";
}else{
    $date = $param->date;
    $Qry2->fields    = "CAST(idacct AS INT) = '".$param->accountid."' 
                    AND ( year('". $date ."') = year(date)
                    and month('". $date ."') = month(date) )";
}

$rs2 = $Qry2->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs2));
if(mysqli_num_rows($rs2)>= 1){
    while($row2=mysqli_fetch_assoc($rs2)){
        $start= $row2['date'];
        $end = $row2['date'];


        $row2['remarks'] = 'Attendance Adjustment <br>';
        $remarks2='';

        if($row2['stat'] == 3){
            $applevel = 0;
            $appvrname ='';
            if(empty($row2['approver1_stat']) && empty($row2['approver2_stat'])){
                $applevel = 1;
                if(strpos( $row2['approver1'], ',')){ 
                    $appvrname = getmultiNames( $con, $row2['approver1'] );
                }else{ 
                    $appvrname = getAccountName( $con, $row2['approver1'] );
                }  
            }elseif(($row2['approver1_stat']) && empty($row2['approver2_stat'])){
                $applevel = 2;
                if(strpos( $row2['approver2'], ',')){ 
                    $appvrname = getmultiNames( $con, $row2['approver2'] );
                }else{ 
                    $appvrname = getAccountName( $con, $row2['approver2'] );
                }   
            }elseif(($row2['approver2_stat']) && empty($row2['approver3_stat'])){
                $applevel = 3;
                if(strpos( $row2['approver3'], ',')){ 
                    $appvrname = getmultiNames( $con, $row2['approver3'] );
                }else{ 
                    $appvrname = getAccountName( $con, $row2['approver3'] );
                }    
            }elseif(($row2['approver3_stat']) && empty($row2['approver4_stat'])){
                $applevel = 4;
                if(strpos( $row2['approver4'], ',')){ 
                    $appvrname = getmultiNames( $con, $row2['approver4'] );
                }else{ 
                    $appvrname = getAccountName( $con, $row2['approver4'] );
                }    
            } 
            $remarks2 = '<br>' . 'Pending Approver '.$applevel.': '.$appvrname; 
        }

        $row2['remarks'] = $row2['remarks'];

        $data[] = array( 
            "application"       => 'attendance',
            "title" 			=> 'Attendance Adjustment',
            "remarks"           => date('h:i a', strtotime($row2['stime'])) .' - ' . date('h:i a', strtotime($row2['ftime'])).$remarks2,
            "ids" 			    => $row2['id'],
			"creator"			=> $row2['creator'],
            "start" 			=> $start,
            "end" 		    	=> $end,
            "backgroundColor"   => '#8a2be2',
            "status"            => $row2['adj_status'],
            "file"              => $row2['file'],
            'sort'              => 9,
            'ticketno'          => $row2['docnumber'],
            'flag'              => $flag,
            'start_time'        =>$row2['stime'],
            'end_time'          =>$row2['ftime'],
        );
    }
}


$Qry3 = new Query();	
$Qry3->table     = "vw_shift_application";
$Qry3->selected  = "remarks, id, creator, shift_status, docnumber, newshift, `date`";

if($param->date == ''){
    $Qry3->fields    = "CAST(idacct AS INT) = '".$param->accountid."' 
                        AND ( year( CURRENT_DATE()) = year(date)
                        and month( CURRENT_DATE()) = month(date) )";
}else{
    $date = $param->date;
    $Qry3->fields    = "CAST(idacct AS INT) = '".$param->accountid."' 
                    AND ( year('". $date ."') = year(date)
                    and month('". $date ."') = month(date) )";
}

$rs3 = $Qry3->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs3));
if(mysqli_num_rows($rs3)>= 1){
    while($row3=mysqli_fetch_assoc($rs3)){
        $start= $row3['date'];
        $end = $row3['date'];

        $row3['remarks'] =  $row3['newshift'];


        $data[] = array( 
            "application"       => 'changeshift',
            "title" 			=> 'Change Shift',
            "remarks"           => $row3['remarks'],
            "ids" 			    => $row3['id'],
			"creator"			=> $row3['creator'],
            "start" 			=> $start,
            "end" 		    	=> $end,
            "backgroundColor"   => '#40e0d0',
            "status"            => $row3['shift_status'],
            'sort'              => 9,
            'ticketno'          => $row3['docnumber'],
            'flag'              => $flag
        );
    }
}


$Qry4 = new Query();	
$Qry4->table     = "tbltimeobtrip";
$Qry4->selected  = "id, `date`, stat, start_time, end_time, remarks, creator,approver1,approver2,approver3,approver4,
approver1_stat, approver2_stat, approver3_stat, approver4_stat, creator, docnumber, `file`";

if($param->date == ''){
    $Qry4->fields    = "CAST(idacct AS INT) = '".$param->accountid."' 
                        AND ( year( CURRENT_DATE()) = year(date)
                        and month( CURRENT_DATE()) = month(date) )";
}else{
    $date = $param->date;
    $Qry4->fields    = "CAST(idacct AS INT) = '".$param->accountid."' 
                    AND ( year('". $date ."') = year(date)
                    and month('". $date ."') = month(date) )";
}
$Qry4->fields    = $Qry4->fields    . " and stat!='4'";
$rs4 = $Qry4->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs4));
if(mysqli_num_rows($rs4)>= 1){
    while($row4=mysqli_fetch_assoc($rs4)){
        $start= $row4['date'];
        $end = $row4['date'];

        if($row4['stat'] == 3){
            $row4['stat'] = 'PENDING';
        }
        if($row4['stat'] == 2){
            $row4['stat'] = 'DECLINED';
        }
        if($row4['stat'] == 1){
            $row4['stat'] = 'APPROVED';
        }
        // if($row4['statcounter'] == 5){
        //     $row4['statcounter'] = 'Cancellation';
        // }

        //$row4['remarks'] = $row4['remarks'] . '<br>' .  date('h:i a', strtotime($row4['start_time'])) . ' - ' . date('h:i a', strtotime($row4['end_time']));
       

        if($row4['start_time'] == '0' || $row4['end_time'] == '0'){

            if($row4['stat'] == 'PENDING'){
                $applevel = 0;
                $appvrname ='';
                if(empty($row4['approver1_stat']) && empty($row4['approver2_stat'])){
                    $applevel = 1;
                    if(strpos( $row4['approver1'], ',')){ 
                        $appvrname = getmultiNames( $con, $row4['approver1'] );
                    }else{ 
                        $appvrname = getAccountName( $con, $row4['approver1'] );
                    }  
                }elseif(($row4['approver1_stat']) && empty($row4['approver2_stat'])){
                    $applevel = 2;
                    if(strpos( $row4['approver2'], ',')){ 
                        $appvrname = getmultiNames( $con, $row4['approver2'] );
                    }else{ 
                        $appvrname = getAccountName( $con, $row4['approver2'] );
                    }   
                }elseif(($row4['approver2_stat']) && empty($row4['approver3_stat'])){
                    $applevel = 3;
                    if(strpos( $row4['approver3'], ',')){ 
                        $appvrname = getmultiNames( $con, $row4['approver3'] );
                    }else{ 
                        $appvrname = getAccountName( $con, $row4['approver3'] );
                    }    
                }elseif(($row4['approver3_stat']) && empty($row4['approver4_stat'])){
                    $applevel = 4;
                    if(strpos( $row4['approver4'], ',')){ 
                        $appvrname = getmultiNames( $con, $row4['approver4'] );
                    }else{ 
                        $appvrname = getAccountName( $con, $row4['approver4'] );
                    }   
                }
                $row4['remarks'] = $row4['remarks']. '<br>' . 'Pending approver - '.$applevel.' '.$appvrname; 
            }else{ 
                $row4['remarks'] = $row4['remarks'];
            }

        }else{
            
            if($row4['stat'] == 'PENDING'){
                $applevel = 0;
                $appvrname ='';
                if(empty($row4['approver1_stat']) && empty($row4['approver2_stat'])){
                    $applevel = 1;
                    if(strpos( $row4['approver1'], ',')){ 
                        $appvrname = getmultiNames( $con, $row4['approver1'] );
                    }else{ 
                        $appvrname = getAccountName( $con, $row4['approver1'] );
                    }
                }elseif(($row4['approver1_stat']) && empty($row4['approver2_stat'])){
                    $applevel = 2;
                    if(strpos( $row4['approver2'], ',')){ 
                        $appvrname = getmultiNames( $con, $row4['approver2'] );
                    }else{ 
                        $appvrname = getAccountName( $con, $row4['approver2'] );
                    } 
                }elseif(($row4['approver2_stat']) && empty($row4['approver3_stat'])){
                    $applevel = 3;
                    if(strpos( $row4['approver3'], ',')){ 
                        $appvrname = getmultiNames( $con, $row4['approver3'] );
                    }else{ 
                        $appvrname = getAccountName( $con, $row4['approver3'] );
                    }  
                }elseif(($row4['approver3_stat']) && empty($row4['approver4_stat'])){
                    $applevel = 4;
                    if(strpos( $row4['approver4'], ',')){ 
                        $appvrname = getmultiNames( $con, $row4['approver4'] );
                    }else{ 
                        $appvrname = getAccountName( $con, $row4['approver4'] );
                    }  
                }
                $row4['remarks'] = $row4['remarks'] . '<br>' .  date('h:i a', strtotime($row4['start_time'])) . ' - ' . date('h:i a', strtotime($row4['end_time'])) . '<br>' . 'Pending Approver '.$applevel.': '.$appvrname; 
            }else{
                $row4['remarks'] = $row4['remarks'] . '<br>' .  date('h:i a', strtotime($row4['start_time'])) . ' - ' . date('h:i a', strtotime($row4['end_time'])) ;
            }
        }
        
        $data[] = array( 
            "application"       => 'obtrip',
            "title" 			=> 'Official Business Trip',
            "ids" 			    => $row4['id'],
			"creator"			=> $row4['creator'],
            "start" 			=> $start,
            "end" 		    	=> $end,
            "remarks"           => $row4['remarks'],
            "backgroundColor"   => '#00bbf0',
            "status"            => $row4['stat'],
            'sort'              => 9,
            "file"              => $row4['file'],
            'ticketno'          => $row4['docnumber'],
            'start_time'          => $row4['start_time'],
            'end_time'          => $row4['end_time'],
            'flag'              => $flag
            // 'statcounter'          => $row4['statcounter']

        );
    }
}


$Qry5 = new Query();	
$Qry5->table     = "tblappcancel";
$Qry5->selected  = "id, idacct, `date`, `status`, `type`, docnumber, origin";

if($param->date == ''){
    $Qry5->fields    = "CAST(idacct AS INT) = '".$param->accountid."' 
                        AND ( year( CURRENT_DATE()) = year(date)
                        and month( CURRENT_DATE()) = month(date) )";
}else{
    $date = $param->date;
    $Qry5->fields    = "CAST(idacct AS INT) = '".$param->accountid."' 
                    AND ( year('". $date ."') = year(date)
                    and month('". $date ."') = month(date) )";
}
$Qry5->fields    = $Qry5->fields    . " and status!='4'";
$rs5 = $Qry5->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs5));
if(mysqli_num_rows($rs5)>= 1){
        while($row5=mysqli_fetch_assoc($rs5)){
        $start= $row5['date'];
        $end = $row5['date'];

        if($row5['status'] == 3){
            $row5['status'] = 'PENDING';
        }
        if($row5['status'] == 3){
            $row5['status'] = 'DECLINED';
        }
        if($row5['status'] == 1){
            $row5['status'] = 'APPROVED';
        }

        if($row5['type'] == 'obtrip'){
            $row5['title'] = 'Official Business Trip - Cancellation';
            $flag2 = "timekeeping";
            //$row5['remarks'] = $row5['remarks'] . '<br>' .  date('h:i a', strtotime($row5['start_time'])) . ' - ' . date('h:i a', strtotime($row5['end_time']));

        }
        if($row5['type'] == 'attendance'){
            $row5['title'] = 'Attendance Ajustment - Cancellation';
            $flag2 = "timekeeping";
        }
        if($row5['type'] == 'overtime'){
            $row5['title'] = 'Overtime - Cancellation';
            $flag2 = "timekeeping";
        }
        if($row5['type'] == 'changeshift'){
            $row5['title'] = 'Change Shift - Cancellation';
            $flag2 = "timekeeping";
        }
        if($row5['type'] == 'Sick Leave'){
            $row5['title'] = 'Sick Leave - Cancellation';
            $flag2 = "leaves";
        }
        if($row5['type'] == 'Vacation Leave'){
            $row5['title'] = 'Vacation Leave - Cancellation';
            $flag2 = "leaves";
        }
        if($row5['type'] == 'Leave Without Pay'){
            $row5['title'] = 'Leave Without Pay - Cancellation';
            $flag2 = "leaves";
        }
        if($row5['type'] == 'Solo Parent Leave'){
            $row5['title'] = 'Solo Parent Leave - Cancellation';
            $flag2 = "leaves";
        }
        if($row5['type'] == 'Compensatory Leave'){
            $row5['title'] = 'Compensatory Leave - Cancellation';
            $flag2 = "leaves";
        }
        if($row5['type'] == 'Paternity Leave'){
            $row5['title'] = 'Paternity Leave - Cancellation';
            $flag2 = "leaves";
        }
        if($row5['type'] == 'Special Leave'){
            $row5['title'] = 'Special Leave - Cancellation';
            $flag2 = "leaves";
        }
        if($row5['type'] == 'Birthday Leave'){
            $row5['title'] = 'Birthday Leave - Cancellation';
            $flag2 = "leaves";
        }
        if($row5['type'] == 'Emergency Leave'){
            $row5['title'] = 'Emergency Leave - Cancellation';
            $flag2 = "leaves";
        }
        if($row5['type'] == 'Magna Carta Leave'){
            $row5['title'] = 'Magna Carta Leave - Cancellation';
            $flag2 = "leaves";
        }
        if($row5['type'] == 'Bereavement Leave'){
            $row5['title'] = 'Bereavement Leave - Cancellation';
            $flag2 = "leaves";
        }
        if($row5['type'] == 'Maternity Leave'){
            $row5['title'] = 'Maternity Leave - Cancellation';
            $flag2 = "leaves";
        }
        
        $data[] = array(  
            "application"       => $row5['type'],
            "title" 			=> $row5['title'],
            "ids" 			    => $row5['id'],
			"creator"			=> $row5['idacct'],
            "start" 			=> $start,
            "end" 		    	=> $end,
            //"remarks"           => $row5['remarks'],
            "backgroundColor"   => '#63615e',
            "status"            => $row5['status'],
            'sort'              => 9,
            'ticketno'          => $row5['docnumber'],
            'origin'            => $row5['origin'],
            'flag2'              => $flag2
            
        );
    }
   
}

function getHrs($con,$id){
    $Qry = new Query();	
    $Qry->table     = "tbltimeovertime";
    $Qry->selected  = "approve_hr, planhrs, hrs";
    $Qry->fields    = "id='".$id."'";
    $rs = $Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getHrs');
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){

            if($row['approve_hr'] == '1')
				{
					$hrs = $row['planhrs'];
				}
				else if($row['approve_hr'] == '2'){
					$hrs = $row['hrs'];
				}
            return $hrs;
        }
    }
    return '';
}


$return =  json_encode($data);
print $return;
mysqli_close($con);

?>