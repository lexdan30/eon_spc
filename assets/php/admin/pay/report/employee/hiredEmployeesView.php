<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$data  = array();
$date  = SysDateDan();
$time  = SysTime();



$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "*";
$Qry->fields    = "id > 0 AND id != 1";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){

        $counter = 0;
    while($row=mysqli_fetch_array($rs)){
        //Format date for display
        $hired_date_format=date_create($row['hdate']);
            $counter = $counter + 1;
        $data[] = array( 
            "id"        	        => $counter,
            "empid"			        => $row['empid'],
            "empname" 		        => $row['empname'],
            "department_code" 		=> ucwords(strtolower($row['business_unit_code'])),
            "department" 		    => ucwords($row['business_unit']),
            "position" 		        => ucwords($row['post']),
            "hire_date"             => date_format($hired_date_format,"m/d/Y"),            
            "date"                  => $date,
            "time"                  => date ("H:i:s A",strtotime($time)),
			
        );
        $return = json_encode($data);
    }
}else{
    $return = json_encode(array('status'=>'error'));
}


print $return;
mysqli_close($con);
?>