<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

    $param = json_decode(file_get_contents('php://input'));
    $return = null;

    if(!empty($param->accountid)){
        $Qry=new Query();
        $Qry->table="vw_dataemployees";
        $Qry->selected="*";
        $Qry->fields="empid='".$param->empid."'";
        $rs=$Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>0){
            if($row=mysqli_fetch_array($rs)){
                // if($row['suffix']!=null){
                //     $fullname = $row['lname']. ' ' .$row['suffix']. ', ' .$row['fname']. ' ' .$row['mname'];
                // }else{
                //     $fullname = $row['lname']. ', ' .$row['fname']. ' ' .$row['mname'];
                // }

                $data = array(
                    // 'fullname'=>$row['lname']. ' ' .$row['suffix']. ', ' .$row['fname']. ' ' .$row['mname'],
                    'fullname'=>trim($row['empname']),
                    'departmentname' => $row['business_unit'],
                    'immediatesupervisor' => $row['superior'],
                    'empstatus' => $row['emp_status'],
                    'positiontitle' => $row['post'],
                    'paygroup' => $row['pay_grp'],
                    'labortype' => $row['labor_type'],
                );
            }
            $return = json_encode($data);
        }
    }else{
        $return = json_encode(array('status'=>'error'));
    }

print $return;
mysqli_close($con);
?>