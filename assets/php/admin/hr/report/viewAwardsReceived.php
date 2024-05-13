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
$Qry->fields    = "id > 0 AND (awards_title IS NOT NULL) ORDER BY empname";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        $getAwardsReceived = getAwardsReceived($con, $row['id']);

        $data[] = array( 
            "id"        	        => $row['id'],
            "empid"			        => $row['empid'],
            "empname" 		        => $row['empname'],
            "post" 		            => ucwords($row['post']),
            "getAwardsReceived"     => $getAwardsReceived,
            "date"                  => $date,
            "time"                  => date ("H:i:s A",strtotime($time)),


			
        );
        $return = json_encode($data);
    }
}else{
    $return = json_encode(array('status'=>'error'));
}

function getAwardsReceived($con, $idacct){
    $Qry=new Query();
    $Qry->table="tblaccountawards";
    $Qry->selected="*";
    $Qry->fields="id>0 AND idacct='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            //Format date for display
            $date_format=date_create($row['date']);
            $data[] = array(
                'title'    =>$row['title'],
                "awards_date" => date_format($date_format,"m/d/Y"),
            );
        }
        return $data;
    }
    return null;
}


print $return;
mysqli_close($con);
?>