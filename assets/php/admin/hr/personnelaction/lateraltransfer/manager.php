<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

    $param = json_decode(file_get_contents('php://input'));
    $data  = array();

    if(!empty($param->accountid)){
        $Qry=new Query();
        $Qry->table="vw_databusinessunits";
        $Qry->selected="*";
        $Qry->fields="id='".$param->id."' AND idhead is not null";
        $rs=$Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>0){
            if($row=mysqli_fetch_array($rs)){
                $data[] = array(
                    'id'			=>	$row['idhead'],
					'shead'			=>	$row['shead']
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