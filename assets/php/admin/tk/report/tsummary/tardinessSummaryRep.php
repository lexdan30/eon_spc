<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();
$pay_period = getPayPeriod($con);

$search ='';
$search2 =' GROUP BY empid';
if( !empty( $param->empid ) ){ 
    $search=$search." AND empid like '%".$param->empid."%' "; 
}

if( !empty($param->d_from) && !empty($param->d_to)){
    $search=$search." AND work_date BETWEEN DATE('".$param->d_from."') AND DATE('".$param->d_to."') ";
}

$Qry 			= new Query();	
if($param->typeemp == "Local Employee"){
    $Qry->table = "vw_timesheetfinal_ho";
}elseif($param->typeemp == "Japanese"){
    $Qry->table = "vw_timesheetfinal_japanese";
}elseif($param->typeemp == "Helper"){
    $Qry->table = "vw_timesheetfinal_helper";
}elseif($param->typeemp == "Japanesecon"){
    $Qry->table = "vw_timesheetfinal_japanesec";
}else{
    $Qry->table = "vw_timesheetfinal";
}


$Qry->selected  = "empid,empname,late, COUNT(late) AS frequency,SUM(late) AS TotalLate";
$Qry->fields    ="late NOT LIKE '0%'  ".$search.$search2;


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>0){
    
    while($row=mysqli_fetch_assoc($rs)){

        $data[] = array( 
            "empid"		 => $row['empid'],
            "empname" 	 => $row['empname'],
            "freq" 		 => $row['frequency'],
            "duration"   => round($row['TotalLate'], 2) . " Hour"
        );
    }

    $return = json_encode($data);

}
else {
    $return = json_encode(array());
}

print $return;
mysqli_close($con);
?>