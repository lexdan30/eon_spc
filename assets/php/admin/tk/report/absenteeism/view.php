<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$data  = array();
$date  = SysDateDan();
$time  = SysTime();
$pay_period = getPayPeriod($con);


$Qry 			= new Query();	
$Qry->table     = "vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID=de.id";
$Qry->selected  = "de.id,de.empid, de.empname, de.post, SUM(dt.absent) AS awol, COUNT(dt.absent) AS awolCounter, de.concat_sup_fname_lname AS manager";
$Qry->fields    = "(dt.idleave is null or dt.idleave = '') and (dt.in IS NULL OR dt.in = '') and (dt.out IS NULL OR dt.out = '') and dt.idshift !=4
and (dt.work_date between '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') group by dt.empID";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        $data[] = array( 
            "id"        	 => $row['id'],
            "empid"			 => $row['empid'],
            "empname" 		 => $row['empname'],
            "post"           => $row['post'],
            "manager"        => $row['manager'],
            "awolCounter"    => $row['awolCounter'],
            "awol"           => $row['awol'],
            "date"           => $date,
            "time"           => date ("H:i:s A",strtotime($time))


			
        );
        $return = json_encode($data);
    }
}else{
    $return = json_encode(array());
}




print $return;
mysqli_close($con);
?>