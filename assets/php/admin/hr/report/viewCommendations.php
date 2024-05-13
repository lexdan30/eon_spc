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
$Qry->fields    = "id > 0 AND commendation_employeraction is not null AND commendation_incentive is not null ORDER BY empname ASC";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        $commends = getCommends($con,$row['id']);

        $data[] = array( 
            "id"        	        => $row['id'],
            "empid"			        => $row['empid'],
            "empname" 		        => $row['empname'],
            "commends"              => $commends,
            "date"                  => $date,
            "time"                  => date ("H:i:s A",strtotime($time))
        );
        $return = json_encode($data);
    }
}else{
    $return = json_encode(array('status'=>'error'));
}

function getCommends($con, $idacct){
    $Qry=new Query();
    $Qry->table="tblaccountcom";
    $Qry->selected="*";
    $Qry->fields="id>0 AND idacct='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            $data[] = array(
                'employeraction'    =>$row['employeraction'],
                'incentive'	        =>$row['incentive']
            );
        }
        return $data;
    }
    return null;
}

print $return;
mysqli_close($con);
?>