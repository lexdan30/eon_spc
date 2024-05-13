<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$return = null;	
$data[] = array();

$Qry = new Query();	
$Qry->table     = "vw_overtime_application";
$Qry->selected  = "*";

if($param->date == ''){
    $Qry->fields    = "idacct = '".$param->accountid."' 
                        AND ( year( CURRENT_DATE()) = year(planned_date_start)
                        and month( CURRENT_DATE()) = month(planned_date_start) )";
}else{
    $date = $param->date;
    $Qry->fields    = "idacct = '".$param->accountid."' 
                    AND ( year('". $date ."') = year(planned_date_start)
                    and month('". $date ."') = month(planned_date_start) )";
}

$Qry->fields    = "idacct = '".$param->accountid."'  AND YEAR(date) = YEAR(CURRENT_DATE())";
$rs = $Qry->exe_SELECT($con);

if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
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
            'sort'              => 10

        );
    }
}

$Qry2 = new Query();	
$Qry2->table     = "vw_attendance_application";
$Qry2->selected  = "*";

if($param->date == ''){
    $Qry2->fields    = "idacct = '".$param->accountid."' 
                        AND ( year( CURRENT_DATE()) = year(date)
                        and month( CURRENT_DATE()) = month(date) )";
}else{
    $date = $param->date;
    $Qry2->fields    = "idacct = '".$param->accountid."' 
                    AND ( year('". $date ."') = year(date)
                    and month('". $date ."') = month(date) )";
}

$rs2 = $Qry2->exe_SELECT($con);

if(mysqli_num_rows($rs2)>= 1){
    while($row2=mysqli_fetch_array($rs2)){
        $start= $row2['date'];
        $end = $row2['date'];


        $row2['remarks'] = 'Attendance Adjustment <br>';


        $data[] = array( 
            "application"       => 'attendance',
            "title" 			=> 'Attendance Adjustment',
            "remarks"           => $row2['remarks'],
            "ids" 			    => $row2['id'],
			"creator"			=> $row2['creator'],
            "start" 			=> $start,
            "end" 		    	=> $end,
            "backgroundColor"   => '#8a2be2',
            "status"            => $row2['adj_status'],
            "file"              => $row2['file'],
            'sort'              => 9
        );
    }
}


$Qry3 = new Query();	
$Qry3->table     = "vw_shift_application";
$Qry3->selected  = "*";

if($param->date == ''){
    $Qry3->fields    = "idacct = '".$param->accountid."' 
                        AND ( year( CURRENT_DATE()) = year(date)
                        and month( CURRENT_DATE()) = month(date) )";
}else{
    $date = $param->date;
    $Qry3->fields    = "idacct = '".$param->accountid."' 
                    AND ( year('". $date ."') = year(date)
                    and month('". $date ."') = month(date) )";
}

$rs3 = $Qry3->exe_SELECT($con);

if(mysqli_num_rows($rs3)>= 1){
    while($row3=mysqli_fetch_array($rs3)){
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
            'sort'              => 9
        );
    }
}

$Qry4 = new Query();	
$Qry4->table     = "tbltimeobtrip";
$Qry4->selected  = "*";

if($param->date == ''){
    $Qry4->fields    = "idacct = '".$param->accountid."' 
                        AND ( year( CURRENT_DATE()) = year(date)
                        and month( CURRENT_DATE()) = month(date) )";
}else{
    $date = $param->date;
    $Qry4->fields    = "idacct = '".$param->accountid."' 
                    AND ( year('". $date ."') = year(date)
                    and month('". $date ."') = month(date) )";
}
$Qry4->fields    = $Qry4->fields    . " and stat!='4'";
$rs4 = $Qry4->exe_SELECT($con);


if(mysqli_num_rows($rs4)>= 1){
    while($row4=mysqli_fetch_array($rs4)){
        $start= $row4['date'];
        $end = $row4['date'];

        if($row4['stat'] == 3){
            $row4['stat'] = 'PENDING';
        }
        if($row4['stat'] == 3){
            $row4['stat'] = 'DECLINED';
        }
        if($row4['stat'] == 1){
            $row4['stat'] = 'APPROVED';
        }

        $row4['remarks'] = $row4['remarks'] . '<br>' .  date('h:i a', strtotime($row4['start_time'])) . ' - ' . date('h:i a', strtotime($row4['end_time']));

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
            'sort'              => 9
        );
    }
}


$return =  json_encode($data);
print $return;
mysqli_close($con);

?>