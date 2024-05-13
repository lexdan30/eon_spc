<?php

// session_start();
// $auth = isset($_SESSION['isAuth']) ? $_SESSION['isAuth']: false;
// if($auth){

require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
if( !empty($param->accountid)){
$data  = array();
$date  = SysDateDan();
$time  = SysTime();

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees AS de
                    LEFT JOIN tbljoblvl AS jl
                    ON de.idlvl = jl.id";
$Qry->selected  = "de.empid,de.empname ,de.business_unit AS Department,CONCAT(de.joblvl,' ',jl.alias) AS `Rank/Level`,de.cnumber,de.addr_st";
$Qry->fields    = "de.id > 0 AND de.id!=1 ORDER BY de.empname ASC";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id"        	        => $row['id'],
            "empid"			        => $row['empid'],
            "empname" 		        => (($row['empname'])),
            "department" 		    => ucwords($row['Department']),
            "joblvl"               => $row['Rank/Level'],
            "address"               => $row['addr_st'],
            "contact"               => $row['cnumber'],
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
}

?>