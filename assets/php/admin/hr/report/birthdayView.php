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
$Qry->fields    = "id > 0";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
       
        //Format date for display
        $bday_date_format=date_create($row['bdate']);
        $data[] = array( 
            "id"        	        => $row['id'],
            "empid"			        => $row['empid'],
            "empname" 		        => (($row['empname'])),
            "birthdate" 		    => date_format($bday_date_format,"m/d/Y"),
            "department" 		    => ucwords(strtolower($row['business_unit'])),
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