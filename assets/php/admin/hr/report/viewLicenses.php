<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$data  = array();
$date  = SysDateDan();
$time  = SysTime();


$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "*";
$Qry->fields    = "id > 0 AND (idpassport IS NOT NULL OR license_drive IS NOT NULL OR license_prc IS NOT NULL) ORDER BY empname ASC";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        //Format date for display
        $hired_date_format=date_create($row['hdate']);

        $data[] = array( 
            "id"        	        => $row['id'],
            "empid"			        => $row['empid'],
            "empname" 		        => $row['empname'],
            "idpassport"            => $row['idpassport'],
            "license_drive"         => $row['license_drive'],
            "license_prc"           => $row['license_prc'],
            "date"                  => $date,
            "time"                  => date ("H:i:s A",strtotime($time))


			
        );
        $return = json_encode($data);
    }
}else{
    $return = json_encode(array());
}


print $return;
mysqli_close($con);
?>