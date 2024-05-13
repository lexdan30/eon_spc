<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

    $param = json_decode(file_get_contents('php://input'));

    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="idunit,business_unit";
    $Qry->fields="business_unit IS NOT NULL GROUP BY business_unit";
    $rs=$Qry->exe_SELECT($con);
    //echo $Qry->fields;
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
      
            $data[] = array(
                'status'=>'success',
                'department'   =>$row['business_unit'],
				'idunit'	=> $row['idunit']
            );
        }
        $return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'empty'));
    }
    
print $return;
mysqli_close($con);
?>