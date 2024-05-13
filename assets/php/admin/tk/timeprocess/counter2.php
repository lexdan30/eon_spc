<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$idpayperiod = array(  
    "period"		=> getPayPeriod($con),
);

$date = $idpayperiod['period']['pay_start'];
$date1 = $idpayperiod['period']['pay_end'];

$ids='';
$data = array();
if( !empty( $param->info->class ) ){
    $ids = $param->info->class; 
}

$Qry 			= new Query();	

if($idpayperiod['period']['type'] == 'Helper'){
    $Qry->table     = "vw_timesheetfinal_helper";
}else if($idpayperiod['period']['type'] == 'Japanese' || $idpayperiod['period']['type'] == 'Japanese Conversion'){
    $Qry->table     = "vw_timesheetfinal_japanese";
}else{
    $Qry->table     = "vw_timesheetfinal";
}


$Qry->selected  = "pic,work_date,idunit,empname,
                    IF(shifttype = 'Regular Schedule' OR shifttype='Compressed Schedule'
                        ,(CASE 
                            WHEN aastatus = 3 THEN 'Pending Approval Attendance Adjustment'
                            WHEN obtripstatus = 3 THEN 'Pending Approval Official Business Trip'
                            WHEN leaveappstatus = 3 THEN CONCAT('Pending Approval ', leavename)
                            WHEN csstatus = 3 THEN 'Pending Approval Change Shift'
                            WHEN otstatus = 3 THEN 'Pending Approval Overtime'
                            WHEN aastatus = 1 THEN NULL
                            WHEN obtripstatus = 1 THEN NULL
                            WHEN leaveappstatus = 1 THEN NULL
                            WHEN csstatus = 1 THEN NULL
                            WHEN otstatus = 1 THEN  NULL
                            WHEN FPidshift = 4 THEN NULL
                            WHEN (timein IS NUll or timein = '') AND (timeout IS NUll or timeout = '')  THEN 'No Timelogs'
                            WHEN (timein IS NUll or timein = '')  THEN 'No Time In'
                            WHEN (timeout IS NULL OR timeout = '')  THEN 'No Time Out'
                            ELSE  NULL	
                        END) 
                        ,(CASE 
                            WHEN aastatus = 3 THEN 'Pending Approval Attendance Adjustment'   
                            WHEN obtripstatus = 3 THEN 'Pending Approval Official Business Trip'
                            WHEN leaveappstatus = 3 THEN CONCAT('Pending Approval ', leavename)
                            WHEN csstatus = 3 THEN 'Pending Approval Change Shift'
                            WHEN otstatus = 3 THEN 'Pending Approval Overtime'
                            WHEN aastatus = 1 THEN NULL
                            WHEN obtripstatus = 1 THEN NULL
                            WHEN leaveappstatus = 1 THEN NULL
                            WHEN csstatus = 1 THEN NULL
                            WHEN otstatus = 1 THEN  NULL
                            WHEN FPidshift = 4 THEN NULL
                            WHEN (timein IS NUll or timein = '') AND (timeout IS NUll or timeout = '') AND (timein2 IS NUll or timein2 = '') AND (timeout2 IS NUll or timeout2 = '')  THEN 'No Timelogs'
                            WHEN ( (timein IS NUll or timein = '') OR (timein2 IS NULL OR timein2 = '')) AND ( (timeout IS NULL OR timeout = '') OR (timeout2 IS NULL OR timeout2 = ''))  THEN 'No Time In and Out'
                            WHEN (timein IS NUll or timein = '') OR (timein2 IS NUll or timein2 = '')  THEN 'No Time In'
                            WHEN (timeout IS NUll or timeout = '') OR (timeout2 IS NUll or timeout2 = '')  THEN 'No Time Out'
                            ELSE  NULL	
                        END)
                    ) as resolution";
                
if(!empty( $param->info->employee)){
    $Qry->fields    = "tid = '".$param->info->employee."' AND  work_date BETWEEN '".$date."' AND '".$date1."' AND  IF(shifttype = 'Regular Schedule' OR shifttype='Compressed Schedule'
                                                                                                                ,(CASE 
                                                                                                                    WHEN aastatus = 3 THEN 'Pending Approval Attendance Adjustment'
                                                                                                                    WHEN obtripstatus = 3 THEN 'Pending Approval Official Business Trip'
                                                                                                                    WHEN leaveappstatus = 3 THEN CONCAT('Pending Approval ', leavename)
                                                                                                                    WHEN csstatus = 3 THEN 'Pending Approval Change Shift'
                                                                                                                    WHEN otstatus = 3 THEN 'Pending Approval Overtime'
                                                                                                                    WHEN aastatus = 1 THEN NULL
                                                                                                                    WHEN obtripstatus = 1 THEN NULL
                                                                                                                    WHEN leaveappstatus = 1 THEN NULL
                                                                                                                    WHEN csstatus = 1 THEN NULL
                                                                                                                    WHEN otstatus = 1 THEN  NULL
                                                                                                                    WHEN FPidshift = 4 THEN NULL
                                                                                                                    WHEN (timein IS NULL OR timein = '') AND (timeout IS NULL OR timeout = '')  THEN 'No Timelogs'
                                                                                                                    WHEN (timein IS NULL OR timein = '')  THEN 'No Time In'
                                                                                                                    WHEN (timeout IS NULL OR timeout = '')  THEN 'No Time Out'
                                                                                                                    ELSE  NULL	
                                                                                                                END) 
                                                                                                                ,(CASE 
                                                                                                                    WHEN aastatus = 3 THEN 'Pending Approval Attendance Adjustment'   
                                                                                                                    WHEN obtripstatus = 3 THEN 'Pending Approval Official Business Trip'
                                                                                                                    WHEN leaveappstatus = 3 THEN CONCAT('Pending Approval ', leavename)
                                                                                                                    WHEN csstatus = 3 THEN 'Pending Approval Change Shift'
                                                                                                                    WHEN otstatus = 3 THEN 'Pending Approval Overtime'
                                                                                                                    WHEN aastatus = 1 THEN NULL
                                                                                                                    WHEN obtripstatus = 1 THEN NULL
                                                                                                                    WHEN leaveappstatus = 1 THEN NULL
                                                                                                                    WHEN csstatus = 1 THEN NULL
                                                                                                                    WHEN otstatus = 1 THEN  NULL
                                                                                                                    WHEN FPidshift = 4 THEN NULL
                                                                                                                    WHEN (timein IS NULL OR timein = '') AND (timeout IS NULL OR timeout = '') AND (timein2 IS NULL OR timein2 = '') AND (timeout2 IS NULL OR timeout2 = '')  THEN 'No Timelogs'
                                                                                                                    WHEN ( (timein IS NULL OR timein = '') OR (timein2 IS NULL OR timein2 = '')) AND ( (timeout IS NULL OR timeout = '') OR (timeout2 IS NULL OR timeout2 = ''))  THEN 'No Time In and Out'
                                                                                                                    WHEN (timein IS NULL OR timein = '') OR (timein2 IS NULL OR timein2 = '')  THEN 'No Time In'
                                                                                                                    WHEN (timeout IS NULL OR timeout = '') OR (timeout2 IS NULL OR timeout2 = '')  THEN 'No Time Out'
                                                                                                                    ELSE  NULL	
                                                                                                                END)
                                                                                                            ) IS NOT NULL ORDER by work_date";
}else if($ids == ''){
    $Qry->fields    = "work_date BETWEEN '".$date."' AND '".$date1."' AND  IF(shifttype = 'Regular Schedule' OR shifttype='Compressed Schedule'
                                                                                    ,(CASE 
                                                                                        WHEN aastatus = 3 THEN 'Pending Approval Attendance Adjustment'
                                                                                        WHEN obtripstatus = 3 THEN 'Pending Approval Official Business Trip'
                                                                                        WHEN leaveappstatus = 3 THEN CONCAT('Pending Approval ', leavename)
                                                                                        WHEN csstatus = 3 THEN 'Pending Approval Change Shift'
                                                                                        WHEN otstatus = 3 THEN 'Pending Approval Overtime'
                                                                                        WHEN aastatus = 1 THEN NULL
                                                                                        WHEN obtripstatus = 1 THEN NULL
                                                                                        WHEN leaveappstatus = 1 THEN NULL
                                                                                        WHEN csstatus = 1 THEN NULL
                                                                                        WHEN otstatus = 1 THEN  NULL
                                                                                        WHEN FPidshift = 4 THEN NULL
                                                                                        WHEN (timein IS NULL OR timein = '') AND (timeout IS NULL OR timeout = '')  THEN 'No Timelogs'
                                                                                        WHEN (timein IS NULL OR timein = '')  THEN 'No Time In'
                                                                                        WHEN (timeout IS NULL OR timeout = '')  THEN 'No Time Out'
                                                                                        ELSE  NULL	
                                                                                    END) 
                                                                                    ,(CASE 
                                                                                        WHEN aastatus = 3 THEN 'Pending Approval Attendance Adjustment'   
                                                                                        WHEN obtripstatus = 3 THEN 'Pending Approval Official Business Trip'
                                                                                        WHEN leaveappstatus = 3 THEN CONCAT('Pending Approval ', leavename)
                                                                                        WHEN csstatus = 3 THEN 'Pending Approval Change Shift'
                                                                                        WHEN otstatus = 3 THEN 'Pending Approval Overtime'
                                                                                        WHEN aastatus = 1 THEN NULL
                                                                                        WHEN obtripstatus = 1 THEN NULL
                                                                                        WHEN leaveappstatus = 1 THEN NULL
                                                                                        WHEN csstatus = 1 THEN NULL
                                                                                        WHEN otstatus = 1 THEN  NULL
                                                                                        WHEN FPidshift = 4 THEN NULL
                                                                                        WHEN (timein IS NULL OR timein = '') AND (timeout IS NULL OR timeout = '') AND (timein2 IS NULL OR timein2 = '') AND (timeout2 IS NULL OR timeout2 = '')  THEN 'No Timelogs'
                                                                                        WHEN ( (timein IS NULL OR timein = '') OR (timein2 IS NULL OR timein2 = '')) AND ( (timeout IS NULL OR timeout = '') OR (timeout2 IS NULL OR timeout2 = ''))  THEN 'No Time In and Out'
                                                                                        WHEN (timein IS NULL OR timein = '') OR (timein2 IS NULL OR timein2 = '')  THEN 'No Time In'
                                                                                        WHEN (timeout IS NULL OR timeout = '') OR (timeout2 IS NULL OR timeout2 = '')  THEN 'No Time Out'
                                                                                        ELSE  NULL	
                                                                                    END)
                                                                                ) IS NOT NULL ORDER BY CONCAT(empname,work_date)";
}else{
    $Qry->fields    = "idunit = '".$ids."' AND work_date BETWEEN '".$date."' AND '".$date1."' AND  IF(shifttype = 'Regular Schedule' OR shifttype='Compressed Schedule'
                                                                                                        ,(CASE 
                                                                                                            WHEN aastatus = 3 THEN 'Pending Approval Attendance Adjustment'
                                                                                                            WHEN obtripstatus = 3 THEN 'Pending Approval Official Business Trip'
                                                                                                            WHEN leaveappstatus = 3 THEN CONCAT('Pending Approval ', leavename)
                                                                                                            WHEN csstatus = 3 THEN 'Pending Approval Change Shift'
                                                                                                            WHEN otstatus = 3 THEN 'Pending Approval Overtime'
                                                                                                            WHEN aastatus = 1 THEN NULL
                                                                                                            WHEN obtripstatus = 1 THEN NULL
                                                                                                            WHEN leaveappstatus = 1 THEN NULL
                                                                                                            WHEN csstatus = 1 THEN NULL
                                                                                                            WHEN otstatus = 1 THEN  NULL
                                                                                                            WHEN FPidshift = 4 THEN NULL
                                                                                                            WHEN (timein IS NULL OR timein = '') AND (timeout IS NULL OR timeout = '')  THEN 'No Timelogs'
                                                                                                            WHEN (timein IS NULL OR timein = '')  THEN 'No Time In'
                                                                                                            WHEN (timeout IS NULL OR timeout = '')  THEN 'No Time Out'
                                                                                                            ELSE  NULL	
                                                                                                        END) 
                                                                                                        ,(CASE 
                                                                                                            WHEN aastatus = 3 THEN 'Pending Approval Attendance Adjustment'   
                                                                                                            WHEN obtripstatus = 3 THEN 'Pending Approval Official Business Trip'
                                                                                                            WHEN leaveappstatus = 3 THEN CONCAT('Pending Approval ', leavename)
                                                                                                            WHEN csstatus = 3 THEN 'Pending Approval Change Shift'
                                                                                                            WHEN otstatus = 3 THEN 'Pending Approval Overtime'
                                                                                                            WHEN aastatus = 1 THEN NULL
                                                                                                            WHEN obtripstatus = 1 THEN NULL
                                                                                                            WHEN leaveappstatus = 1 THEN NULL
                                                                                                            WHEN csstatus = 1 THEN NULL
                                                                                                            WHEN otstatus = 1 THEN  NULL
                                                                                                            WHEN FPidshift = 4 THEN NULL
                                                                                                            WHEN (timein IS NULL OR timein = '') AND (timeout IS NULL OR timeout = '') AND (timein2 IS NULL OR timein2 = '') AND (timeout2 IS NULL OR timeout2 = '')  THEN 'No Timelogs'
                                                                                                            WHEN ( (timein IS NULL OR timein = '') OR (timein2 IS NULL OR timein2 = '')) AND ( (timeout IS NULL OR timeout = '') OR (timeout2 IS NULL OR timeout2 = ''))  THEN 'No Time In and Out'
                                                                                                            WHEN (timein IS NULL OR timein = '') OR (timein2 IS NULL OR timein2 = '')  THEN 'No Time In'
                                                                                                            WHEN (timeout IS NULL OR timeout = '') OR (timeout2 IS NULL OR timeout2 = '')  THEN 'No Time Out'
                                                                                                            ELSE  NULL	
                                                                                                        END)
                                                                                                    ) IS NOT NULL ORDER by work_date";
}

$rs 			= $Qry->exe_SELECT($con);

if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        if($row['resolution']){
            $path = 'assets/images/undefined.webp?'.time();

            if( !empty( $row['pic'] ) ){
                $path = 'assets/php/admin/hr/employee/pix/'.$row['pic'].'?'.time();
            }
            
            $data[] = array(
                "date" 		=> $row['work_date'],
                "unit"		=> getUnit($con,$row['idunit']),
                "name"  	=> $row['empname'],
                "pic"		=> $path,
                "txt" 		=> $row['resolution']
            );
        }
       
    }

    $myData = array('status' => 'success', 
                    'result' => $data
                );
    $return = json_encode($myData);
}else{
    $return = json_encode(array('error' => mysqli_error($con)));
}

    

$return = json_encode($data);

print $return;
mysqli_close($con);


function getUnit($con,$idunit){
    $Qry = new Query();	
    $Qry->table     = "tblbunits";
    $Qry->selected  = "name";
    $Qry->fields = "id='".$idunit."'";
    $rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
              return $row['name'];
        }
    }
}

?>