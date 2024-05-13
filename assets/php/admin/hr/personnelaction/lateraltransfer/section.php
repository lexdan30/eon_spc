<?php
require_once('../../../../activation.php');
require_once('../../../../logger.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

    $param = json_decode(file_get_contents('php://input'));
    $data  = array();

    if(!empty($param->accountid)){
        $Qry=new Query();
        $Qry->table="vw_databusinessunits";
        $Qry->selected="*";
        $Qry->fields="idunder='".$param->idunder."' AND isactive = 1";
        $rs=$Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>0){
            while($row=mysqli_fetch_array($rs)){
                $data[] = array(
                    'id'			=>	$row['id'],
					'departmentname'=>	$row['name'],
					'alias'			=>	$row['alias'],
					'idhead'		=>	$row['idhead'],
					'idunder'		=>	$row['idunder'],
					'unittype'		=>	$row['unittype'],
					'stype'			=>	$row['stype'],
					'shead'			=>	$row['shead'],
                    'under'         =>  getAllUnderBUnits($con, $row['id'])
                );
            }
        }
		$return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'error'));
    }

print $return;
mysqli_close($con);


function getAllUnderBUnits($con, $id, &$arr = array()) {
    Log::v('id '.$id);
    $Qry=new Query();
    $Qry->table="tblbunits";
    $Qry->selected="id, `name`, idhead";
    $Qry->fields="idunder='".$id."'";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'findAllUnder');
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
            $arr[] = array('id' => $row['id'], 'departmentname' => $row['name'], 'idhead' => $row['idhead']);
        }
        return getAllUnderBUnits($con, $row['id'], $arr);
    } else {
        return $arr;
    }
}
?>