<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php');

    $param = json_decode(file_get_contents('php://input'));

    $Qry=new Query();
    $Qry->table="tblpayperiod";
    $Qry->selected="*";
    $Qry->fields="id='".$param->cutoff."' AND stat=0";
    $rs=$Qry->exe_SELECT($con);
    //echo $Qry->fields;
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            $data = array(
                'status'    =>  'success',
                'start'     =>  $row['period_start'],
                'end'       =>  $row['period_end'],
            );
        }
        $return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'empty'));
    }
    
print $return;
mysqli_close($con);

?>