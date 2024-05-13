<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$return = null;	

if($param->date == ''){
    $date = 'CURRENT_DATE()';
}else{
    $date = $param->date;
}

$Qry = new Query();	
$Qry->table     = "vw_overtime_application";
$Qry->selected  = "*";
$Qry->fields    = "CAST(idacct AS INT) = '".$param->accountid."' AND ((MONTH(planned_date_start) = MONTH($date)
AND YEAR(planned_date_start) = YEAR($date)) OR (MONTH(planned_date_end) = MONTH($date)
AND YEAR(planned_date_end) = YEAR($date)))";
$rs = $Qry->exe_SELECT($con);

if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $start= $row['planned_date_start'];
        $end = $row['planned_date_end'];
        $startt= $row['planned_time_start'];
        $endt = $row['planned_time_end'];

        $data[] = array( 
            "application"       => 'overtime',
            "title" 			=> 'Overtime',
            "id" 			    => $row['id'],
            "start" 			=> $start,
            "end" 		    	=> $end,
            "backgroundColor"   => '#f09401',
            "status"            => $row['ot_status'],
            "startt" 			=> $startt,
            "endt" 		    	=> $endt,

        );
    }
}

$Qry2 = new Query();	
$Qry2->table     = "vw_attendance_application";
$Qry2->selected  = "*";
$Qry2->fields    = "CAST(idacct AS INT) = '".$param->accountid."'  AND ((MONTH(date) = MONTH(CURRENT_DATE())
AND YEAR(date) = YEAR(CURRENT_DATE())) OR (MONTH(date) = MONTH(CURRENT_DATE())
AND YEAR(date) = YEAR(CURRENT_DATE())))";
$rs2 = $Qry2->exe_SELECT($con);

if(mysqli_num_rows($rs2)>= 1){
    while($row2=mysqli_fetch_array($rs2)){
        $start= $row2['date'];
        $end = $row2['date'];

        $data[] = array( 
            "application"       => 'attendance',
            "title" 			=> 'Attendance Adjustment',
            "id" 			    => $row2['id'],
            "start" 			=> $start,
            "end" 		    	=> $end,
            "backgroundColor"   => '#da3e28',
            "status"            => $row2['adj_status']
        );
    }
}




$return =  json_encode($data);
print $return;
mysqli_close($con);

?>