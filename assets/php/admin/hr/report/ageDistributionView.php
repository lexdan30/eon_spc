<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$data  = array();
$date  = SysDateDan();
$SysDate = SysDate();
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

        //GET EMPLOYEE AGE
        // // SysDate explode Year
        // $sysdate_explode = explode("-", $SysDate);
        // $sysdate_explode_year = $sysdate_explode[0];

        // // Birthdate 
        // $birthdate_explode = explode("-", $row['bdate']);
        // $birthdate_explode_year = $birthdate_explode[0];
        
        // $age = $sysdate_explode_year - $birthdate_explode_year;

        $data[] = array( 
            "id"        	        => $row['id'],
            "empid"			        => $row['empid'],
            "empname" 		        => (($row['empname'])),
            "department" 		    => ucwords($row['business_unit']),
            "birthdate" 		    => date_format($bday_date_format,"m/d/Y"),           
            "date"                  => $date,
            "age"                   => $row['age'],
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