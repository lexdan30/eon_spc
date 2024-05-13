<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php');

    $param = json_decode(file_get_contents('php://input'));

    if(checkIfHasPending($con, $param->idacct, $param->date)){
        $Qry = new Query();	
        $Qry->table     = "tbldutyroster";
        $Qry->fields    = "idacct='".$param->idacct."' AND date='".$param->date."' AND type_creator=2";
        $rs = $Qry->exe_DELETE($con);
        if($rs){
            $return = json_encode(array('savestatus'=>'success'));
        }else{
            $return = json_encode(array('savestatus'=>'oops'));
        }
    }else{
        $return = json_encode(array('savestatus'=>'nope'));
    }

    
print $return;
mysqli_close($con);

function checkIfHasPending($con, $idacct, $date){
    $Qry=new Query();
    $Qry->table="tbldutyroster";
    $Qry->selected="*";
    $Qry->fields="idacct='".$idacct."' AND date='".$date."' AND type_creator=2";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return true;
        }
    }
    return false;
}


?>