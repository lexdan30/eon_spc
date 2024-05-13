<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

    $param = json_decode(file_get_contents('php://input'));
    $data  = array();

    if(!empty($param->accountid)){
		if( !empty( $param->idunder ) ){
			$idunits = $param->idunder;
			//$idunits = getUnits( $con, $param->idunder );
			
			$Qry=new Query();
			$Qry->table="vw_dataemployees";
			$Qry->selected="*";
			$Qry->fields="idunit in (".$idunits.") ";
			$rs=$Qry->exe_SELECT($con);
			if(mysqli_num_rows($rs)>0){
				while($row=mysqli_fetch_array($rs)){
					$data[] = array(
						'id'			=>	$row['id'],
						'empid'			=>	$row['empid'],
						'empname'		=>	$row['empname']
					);
				}
			}
			$return = json_encode($data);
		}else{
			$return = json_encode($data);
		}
    }else{
        $return = json_encode(array('status'=>'error'));
    }

print $return;
mysqli_close($con);

function getUnits( $con, $idunit ){
	$Qry=new Query();
	$Qry->table="vw_databusinessunits";
	$Qry->selected="GROUP_CONCAT(id) AS id";
	$Qry->fields="idunder in (".$idunit.") ";
	$rs=$Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>0){
		if($row=mysqli_fetch_array($rs)){
			return $row['id'];
		}
	}
	return $idunit;
}

?>