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
$Qry->fields    = "id > 0 AND (equi_tools IS NOT NULL) ORDER BY empname";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        
        $getPropertyAccountability = getPropertyAccountability($con, $row['id']);

        $data[] = array( 
            "id"        	        => $row['id'],
            "empid"			        => $row['empid'],
            "empname" 		        => $row['empname'],
            "post" 		            => ucwords($row['post']),
            "getPropertyAccountability"=> $getPropertyAccountability,
            "date"                  => $date,
            "time"                  => date ("H:i:s A",strtotime($time)),


			
        );
        $return = json_encode($data);
    }
}else{
    $return = json_encode(array('status'=>'error'));
}

function getPropertyAccountability($con, $idacct){
    $Qry=new Query();
    $Qry->table="tblaccountpropacc";
    $Qry->selected="*";
    $Qry->fields="id>0 AND idacct='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){

        //Format date for display
        $date_issued_format=date_create($row['date_issued']);
        // $date_returned_format=date_create($row['date_returned']);
        
        
        if(!empty($row['date_returned'])){
            $date_returned_format=date_create($row['date_returned']);
            $date_returned_format=date_format($date_returned_format,"m/d/Y ");
        }else{
            $date_returned_format = '';
        }

            $data[] = array(
                'equi_tools' => $row['equi_tools'],
                'serial'	 => $row['serial'],
                'quantity'	 => $row['quantity'],
                'date_issued'=> date_format($date_issued_format,"m/d/Y"),
                'date_returned'=> $date_returned_format
            );
        }
        return $data;
    }
    return null;
}


print $return;
mysqli_close($con);
?>