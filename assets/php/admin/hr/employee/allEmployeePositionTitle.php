<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

    $param = json_decode(file_get_contents('php://input'));

    $Qry=new Query();
    $Qry->table="vw_employee_data_report";
    $Qry->selected="PositionTitle";
    $Qry->fields="PositionTitle IS NOT NULL GROUP BY PositionTitle";
    $rs=$Qry->exe_SELECT($con);
    //echo $Qry->fields;
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
      
            $data[] = array(
                'status'=>'success',
                'postition_title'   =>$row['PositionTitle']
            );
        }
        $return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'empty'));
    }
    
print $return;
mysqli_close($con);
?>