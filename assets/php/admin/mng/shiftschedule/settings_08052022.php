<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php');

    $param = json_decode(file_get_contents('php://input'));

    $data = array();

    $Qry=new Query();
    $Qry->table="tblbunits";
    $Qry->selected="scheduler";

    if($param->accountid != getidhead($con,$param->businessunit)){
        $Qry->fields="idhead='".$param->accountid."'";
        $rs=$Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>=1){
            while($row=mysqli_fetch_array($rs)){
                $data = array(
                    "idacct" 	=> $row['scheduler'],
                    "empaname"  => getEmployeeName($con,(int)$row['scheduler'])
                );
            }
            $return = json_encode($data);
        }else{
            $return = json_encode(array('status'=>'empty'));
        }
    }else{
        $Qry->fields="name='".$param->businessunit."'";
        $rs=$Qry->exe_SELECT($con);
        //echo $Qry->fields;
        if(mysqli_num_rows($rs)>=1){
            while($row=mysqli_fetch_array($rs)){
                $data = array(
                    "idacct" 	=> $row['scheduler'],
                    "empaname"  => getEmployeeName($con,(int)$row['scheduler'])
                );
            }
            $return = json_encode($data);
        }else{
            $return = json_encode(array('status'=>'empty'));
        }
    }


print $return;
mysqli_close($con);

function getidhead( $con, $unitname ){
    $id = 0;
    $Qry 			= new Query();	
    $Qry->table     = "tblbunits";
    $Qry->selected  = "idhead";
    $Qry->fields    = "name = '".$unitname."'";
    $rs 			= $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $id = $row['idhead'];
        }
    }
    return $id;
}
?>