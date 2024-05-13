<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();
$pay_period = getPayPeriod($con);

$search ='';

if( !empty( $param->empid ) ){ $search=$search." AND empid like '%".$param->empid."%' "; }

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

$Qry->selected  = "empid,empname,work_date,absent,lvapprove";
$Qry->fields    ="absent NOT LIKE '0%'".$search." ORDER BY work_date, empname";


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>0){

    while($row=mysqli_fetch_assoc($rs)){

        $data[] = array( 
           
            "empid"			 => $row['empid'],
            "empname" 		 => $row['empname'],
            "work_date"       => $row['work_date'],
            "absent"         => $row['absent'],
            "lvapprove"         => is_null($row['lvapprove'])?'AWOL':'LEAVE'
   
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