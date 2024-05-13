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
$Qry->fields    = "id > 0 AND (emp_action IS NOT NULL) ORDER BY empname";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        $getDisciplinaryActions = getDisciplinaryActions($con, $row['id']);

        $data[] = array( 
            "id"        	        => $row['id'],
            "empid"			        => $row['empid'],
            "empname" 		        => $row['empname'],
            "post" 		            => ucwords($row['post']),
            "getDisciplinaryActions"=> $getDisciplinaryActions,
            "date"                  => $date,
            "time"                  => date ("H:i:s A",strtotime($time)),


			
        );
        $return = json_encode($data);
    }
}else{
    $return = json_encode(array('status'=>'error'));
}

function getDisciplinaryActions($con, $idacct){
    $Qry=new Query();
    $Qry->table="tblaccountdisact";
    $Qry->selected="*";
    $Qry->fields="id>0 AND idacct='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){

            //Format date for display
            $date_format=date_create($row['date']);
            
            $data[] = array(
                'emp_action'  =>$row['emp_action'],
                'penalty'   =>$row['penalty'],
                "pen_date" => date_format($date_format,"m/d/Y"),
            );
        }
        return $data;
    }
    return null;
}


print $return;
mysqli_close($con);
?>