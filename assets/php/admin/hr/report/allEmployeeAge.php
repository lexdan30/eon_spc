<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$SysDate = SysDate();
$time  = SysTime();

    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="TIMESTAMPDIFF(YEAR, bdate, CURDATE()) AS age";
    $Qry->fields="id > 0 GROUP BY age";
    $rs=$Qry->exe_SELECT($con);
    //echo $Qry->fields;
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){

            $data[] = array(
                'status'=>'success',
                'age'   => $row['age'],
            );
        }
        $return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'empty'));
    }
    
print $return;
mysqli_close($con);
?>