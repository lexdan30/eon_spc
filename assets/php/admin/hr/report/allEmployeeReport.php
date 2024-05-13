<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

    $param = json_decode(file_get_contents('php://input'));

    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="*";
    $Qry->fields="id>0";
    $rs=$Qry->exe_SELECT($con);
    //echo $Qry->fields;
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
      
            $data[] = array(
                'status'=>'success',
                'id'                =>$row['id'],
                'empid'             =>$row['empid'],
                'fullname'          =>trim($row['empname']),
                // 'department'        =>$row['BusinessUnit'],
                // 'postition_title'   =>$row['PositionTitle'],
                // 'pay_group'         =>$row['PayGroup'],
                // 'pay_status'        =>$row['PayStatus'],
                // 'employment_type'   =>$row['EmploymentType'],
                // 'employment_status' =>$row['EmploymentStatus'],
            );
        }
        $return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'empty'));
    }
    
print $return;
mysqli_close($con);
?>