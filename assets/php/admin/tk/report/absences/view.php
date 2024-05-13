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
$Qry->selected  = "*";
$Qry->fields    = "(dt.idleave IS NULL OR dt.idleave = '') AND (dt.in IS NULL OR dt.in = '') AND (dt.out IS NULL OR dt.out = '') AND dt.idshift !=4 AND (dt.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."')";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        //Format date for display
        $date_format=date_create($row['date']);

        $data[] = array( 
            "id"        	 => $row['id'],
            "empid"			 => $row['empid'],
            "empname" 		 => $row['empname'],
            "foc_date"       => date_format($date_format,"m/d/Y"),
            "absent"         => $row['absent'],
            "superior"       => $row['concat_sup_fname_lname'],
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