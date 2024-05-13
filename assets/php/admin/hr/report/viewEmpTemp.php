<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php');  

$param = $_POST;
$data  = array();
$date  = SysDateDan();
$time  = SysTime(); 
$date1 = SysDatePadLeft();
//print_r($date1);

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees AS de LEFT JOIN vw_data_timesheet AS dt ON de.id = dt.idacct";
$Qry->selected  = "de.id, de.empid, de.empname, de.business_unit,dt.temp,dt.work_date";
$Qry->fields    = "dt.work_date = '".$date1."' AND (dt.temp IS NOT NULL OR dt.temp != '') ORDER BY de.empname";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        $data[] = array( 
            "id"        	    => $row['id'],
            "empid"			    => $row['empid'],
            "empname" 		    => $row['empname'],
            "temp" 		        => $row['temp'],
            "department"        => ucwords(strtolower($row['business_unit'])),
            "date"              => $row['work_date'],
            "time"              => date ("H:i:s A",strtotime($time)),
			
        );
        $return = json_encode($data);
    }
}else{
    $return = json_encode(array('status'=>'error'));
}


print $return;
mysqli_close($con);
?>