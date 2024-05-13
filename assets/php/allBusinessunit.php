<?php
require_once('activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('classPhp.php');

    $param = json_decode(file_get_contents('php://input'));

    $Qry=new Query();
    $Qry->table="vw_databusinessunits";
    $Qry->selected="*";
    $Qry->fields="isactive = 1";
    $rs=$Qry->exe_SELECT($con);
    //echo $Qry->fields;
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            $data[] = array(
                'status'		=>	'success',
                'id'			=>	(int)$row['id'],
				'departmentname'=>	$row['name'],
				'alias'			=>	$row['alias'],
				'idhead'		=>	$row['idhead'],
				'idunder'		=>	$row['idunder'],
				'unittype'		=>	$row['unittype'],
				'stype'			=>	$row['stype'],
				'shead'			=>	$row['shead']
            );
        }
        $return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'empty'));
    }
    
print $return;
mysqli_close($con);
?>