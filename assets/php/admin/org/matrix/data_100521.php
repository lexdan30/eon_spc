<?php
require_once('../../../activation.php');
$param = json_decode(file_get_contents('php://input'));
$conn = new connector();	

if( (int)$param->conn == 1 ){	
	$con = $conn->connect();
}else{
	$varcon = "connect".(int)$param->conn;
	$con = $conn->$varcon();

}

require_once('../../../classPhp.php');  


$return = null;	
$data = array();
$Qry 			= new Query();	
$Qry->table     = "tblbunits";
$Qry->selected  = "*";
$Qry->fields    = "unittype <> 6 ORDER BY unittype DESC";
$rs = $Qry->exe_SELECT($con);
$arr_id = array();

if(mysqli_num_rows($rs)>= 1){
	while($row=mysqli_fetch_array($rs)){
		
		if( !checkApproverMatrix( $con, $row['id'] ) ){
			$unittype = $row['unittype'];
			$approver = array();
			$idunder  = $row['idunder'];
			$approver[0] = array(
				'id' 			=> $row['id'],
				'unit'			=> $row['name'],
				'alias'			=> $row['alias'],
				'costcenter'	=> $row['costcenter'],
				'idhead'		=> $row['idhead'],
				'deputy1'		=> $row['deputy1'],
				'deputy2'		=> $row['deputy2'],
				'idunder'		=> $row['idunder'],
				'unittype'		=> $row['unittype']
			);
			$ndex	  = 1;
			$x = 1;
			if( empty($idunder) ){
				$x = 0;
			}
			if( !empty( $idunder ) ){
				// if($row['id'] == 94){
				// 	$idunder = 86;
				// }elseif($row['id'] == 113){
				// 	$idunder = 93;
				// }else{
					$idunder = 45; // Set to HR classification for approver 2
				//}
				
				//do{
					$approver[$ndex] = getapprovers( $con, $idunder );
					$idunder		 = $approver[ $ndex ]['idunder'];
					$unittype		 = $approver[ $ndex ]['unittype'];
					
					// if( $idunder <= '2'){
					// 	$x = 0;
					// }
					// $ndex++;
				//}while( $x == 1 );
				
			}
			
			$data[] = array(
				'id' 	=> $row['id'],
				'unit'	=> $row['name'],
				'appr'	=> $approver,
				'ctr'	=> count( $approver ),
				'f'		=> checkApproverMatrix( $con, $row['id'] ),
				'arr_id'=>$arr_id
			);
			if( checkApproverMatrix( $con, $row['id'] ) == false ){
			
				$a = insertDate( $con, array(
											'id' 	=> $row['id'],
											'unit'	=> $row['name'],
											'appr'	=> $approver,
											'ctr'	=> count( $approver )
										)
				);
			
			}
			
		}else{
			array_push($arr_id, checkApproverMatrix( $con, $row['id'] ));
		}
	}

	$return =  json_encode($data);
}else{
	$return =  json_encode(array(  ));
}

print $return;
mysqli_close($con);

function checkApproverMatrix( $con, $idunit ){
	$Qry3           = new Query();
	$Qry3->table    = "tblappmatrix";
	$Qry3->selected = "*";
	$Qry3->fields   = "idunit='".$idunit."'";
	$rs 			= $Qry3->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		return true;
	}
	return false;
}

function insertDate( $con, $data ){
	$Qry6 			= new Query();	
	$Qry6->table     = "tblappmatrix";
	$Qry6->selected  = "*";
	$Qry6->fields    = "idunit='".$data['id']."'";
	$rs6 = $Qry6->exe_SELECT($con);
	if(mysqli_num_rows($rs6)>= 1){
		return '';
	}else{
		$Qry3           = new Query();
		$Qry3->table    = "tblappmatrix";
		
		$cols = "idunit, ctr_approver";
		$rows = "'".$data['id']."', '".$data['ctr']."'";
		for( $x=1; $x<=$data['ctr']; $x++ ){
			$cols = $cols . ",approver_type_".$x."a, approver_unit_". $x ."a";
			$rows = $rows . ", '1', '".$data['appr'][$x-1]['id']."'";
		}
		//$trace = debug_backtrace();
		
		$Qry3->selected = $cols;
		$Qry3->fields   = $rows;     
		
		return $Qry3->exe_INSERT($con);
	}
}

?>