<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

    $param = json_decode(file_get_contents('php://input'));
    $data  = array();

    if(!empty($param->accountid)){
        $Qry=new Query();
        $Qry->table="tblposition";
        $Qry->selected="*";
        $Qry->fields="id>0";
        $rs=$Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>0){
            while($row=mysqli_fetch_assoc($rs)){
                $data[] = array(
                    'id'			=>	$row['id'],
					'alias'			=>	$row['alias'],
					'name'			=>	$row['name']
                );
            }
        }
		$return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'error'));
    }

print $return;
mysqli_close($con);
?>