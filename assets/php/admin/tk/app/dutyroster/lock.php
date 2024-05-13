<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry=new Query();
$Qry->table="tbldutyrosterstat";
$Qry->selected="*";
$Qry->fields="id_department = '".$param->department."'  AND id_payperiod = '".$param->payperiod."'";
$rs=$Qry->exe_SELECT($con);

if(mysqli_num_rows($rs)>=1){
    $Qry2           = new Query();
    $Qry2->table    = "tbldutyrosterstat";
    $Qry2->selected = "status='".$param->stat."',  date_close ='" . SysDate() . "', time_close='" . SysTime() . "'";
    $Qry2->fields   = "id_department = '".$param->department."'  AND id_payperiod = '".$param->payperiod."'";                    
    $checke = $Qry2->exe_UPDATE($con);

    if($checke){
        if($param->stat == 1){
            $data = 'locked';
        }else{
            $data = 'unlocked';
        }
    }else{
        $data = array('status'=>'error');
    }	
   
}else{


    $Qry2           = new Query();
    $Qry2->table    = "tbldutyrosterstat";
    $Qry2->selected = "status,id_department,id_payperiod,date_close,time_close";
    $Qry2->fields   = "'".$param->stat."',
                        '".$param->department."',
                        '".$param->payperiod."',
                        '" . SysDate() . "',
                        '" . SysTime() . "'";                   
    $checke = $Qry2->exe_INSERT($con);


    if($checke){
        if($param->stat == 1){
            $data = 'locked';
        }else{
            $data = 'unlocked';
        }
    }else{
        $data = array('status'=>'error');
    }	
   
}


$return =  json_encode($data);
print $return;
mysqli_close($con);
?>