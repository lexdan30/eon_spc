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

   
    $date = SysDate();
    $time = SysTime();
    $return = null;

    if(!empty($param->accountid)){
        //UPDATE TO DATABASE
        $Qry 			=   new Query();	
        $Qry->table 	=   "tblappmatrix";
		$cols = " idstatus = '".$param->sequence->status."' ";
		for( $x=1; $x <= $param->sequence->ctr_approver; $x++ ){
			$approver_type_a = "approver_type_".$x."a";
			$approver_unit_a = "approver_unit_".$x."a";
			$approver_a		 = "approver_".$x."a";
			
			$approver_type_b = "approver_type_".$x."b";
			$approver_unit_b = "approver_unit_".$x."b";
			$approver_b		 = "approver_".$x."b";
			
			$approver_type_c = "approver_type_".$x."c";
			$approver_unit_c = "approver_unit_".$x."c";
			$approver_c		 = "approver_".$x."c";
			
			$approver_from_b = "approver_from_".$x."b";
			$approver_to_b	 = "approver_to_".$x."b";
			$approver_from_c = "approver_from_".$x."c";
			$approver_to_c	 = "approver_to_".$x."c";
			
			$param->sequence->$approver_type_a = $param->sequence->$approver_type_a ? $param->sequence->$approver_type_a : '';
			$param->sequence->$approver_unit_a = $param->sequence->$approver_unit_a ? $param->sequence->$approver_unit_a : '';
			$param->sequence->$approver_a      = $param->sequence->$approver_a      ? $param->sequence->$approver_a      : '';
			
			$param->sequence->$approver_type_b = $param->sequence->$approver_type_b ? $param->sequence->$approver_type_b : '';
			$param->sequence->$approver_unit_b = $param->sequence->$approver_unit_b ? $param->sequence->$approver_unit_b : '';
			$param->sequence->$approver_b      = $param->sequence->$approver_b      ? $param->sequence->$approver_b      : '';
			
			$param->sequence->$approver_type_c = $param->sequence->$approver_type_c ? $param->sequence->$approver_type_c : '';
			$param->sequence->$approver_unit_c = $param->sequence->$approver_unit_c ? $param->sequence->$approver_unit_c : '';
			$param->sequence->$approver_c      = $param->sequence->$approver_c      ? $param->sequence->$approver_c      : '';
			
			$filter_error = 0;
			if( !empty( $param->sequence->$approver_type_b ) ){ 
				$param->sequence->$approver_from_b ? $param->sequence->$approver_from_b : '';
				$param->sequence->$approver_to_b ? $param->sequence->$approver_to_b : '';
			}
			if( !empty( $param->sequence->$approver_type_c ) ){ 
				$param->sequence->$approver_from_c ? $param->sequence->$approver_from_c : '';
				$param->sequence->$approver_to_c ? $param->sequence->$approver_to_c : '';
			}
			
			if( !empty( $param->sequence->$approver_from_b ) && !empty( $param->sequence->$approver_to_b ) ){
				if( strtotime( $param->sequence->$approver_to_b ) < strtotime( $param->sequence->$approver_from_b ) ){
					$filter_error = 1;
				}
			}
			if( !empty( $param->sequence->$approver_from_c ) && !empty( $param->sequence->$approver_to_c ) ){
				if( strtotime( $param->sequence->$approver_to_c ) < strtotime( $param->sequence->$approver_from_c ) ){
					$filter_error = 1;
				}
			}
			if( ( !empty( $param->sequence->$approver_from_b ) && empty( $param->sequence->$approver_to_b ) ) || ( empty( $param->sequence->$approver_from_b ) && !empty( $param->sequence->$approver_to_b ) ) ){
				$filter_error = 1;
			}
			if( ( !empty( $param->sequence->$approver_from_c ) && empty( $param->sequence->$approver_to_c ) ) || ( empty( $param->sequence->$approver_from_c ) && !empty( $param->sequence->$approver_to_c ) ) ){
				$filter_error = 1;
			}
			
			if( $filter_error == 1 ){
				$return = json_encode(array("status"=>"invdate"));
				print $return;
				mysqli_close($con);
				return;
			}
			
			$param->sequence->$approver_type_c = $param->sequence->$approver_type_c ? $param->sequence->$approver_type_c : '';
			$param->sequence->$approver_unit_c = $param->sequence->$approver_unit_c ? $param->sequence->$approver_unit_c : '';
			$param->sequence->$approver_c      = $param->sequence->$approver_c      ? $param->sequence->$approver_c      : '';
			
			if( $param->sequence->$approver_type_a != "1" ){
				$param->sequence->$approver_unit_a  = "";
				if( empty( $param->sequence->$approver_type_a ) ){
					$param->sequence->$approver_a 		= "";
				}
			}else{
				$param->sequence->$approver_a 		= "";
			}
			if( $param->sequence->$approver_type_b != "1" ){
				$param->sequence->$approver_unit_b = "";
				if( empty( $param->sequence->$approver_type_b ) ){
					$param->sequence->$approver_b 		= "";
				}
			}else{
				$param->sequence->$approver_b 		= "";
			}
			if( $param->sequence->$approver_type_c != "1" ){
				$param->sequence->$approver_unit_c = "";
				if( empty( $param->sequence->$approver_type_c ) ){
					$param->sequence->$approver_c 		= "";
				}
			}else{
				$param->sequence->$approver_c 		= "";
			}
			
			$cols = $cols . ",approver_type_".$x."a = '".$param->sequence->$approver_type_a."', 
							  approver_unit_".$x."a = '".$param->sequence->$approver_unit_a."',  
							  approver_".$x."a      = '".$param->sequence->$approver_a     ."',
							  approver_type_".$x."b = '".$param->sequence->$approver_type_b."', 
							  approver_unit_".$x."b = '".$param->sequence->$approver_unit_b."',  
							  approver_".$x."b      = '".$param->sequence->$approver_b     ."',
							  approver_type_".$x."c = '".$param->sequence->$approver_type_c."', 
							  approver_unit_".$x."c = '".$param->sequence->$approver_unit_c."',  
							  approver_".$x."c      = '".$param->sequence->$approver_c     ."',
							  approver_from_".$x."b = '".$param->sequence->$approver_from_b."',
							  approver_to_".$x."b 	= '".$param->sequence->$approver_to_b."',
							  approver_from_".$x."c = '".$param->sequence->$approver_from_c."',
							  approver_to_".$x."c 	= '".$param->sequence->$approver_to_c."'";
		}
		$Qry->selected 	=   $cols;
        $Qry->fields 	= "id='".$param->sequence->id."'";
        $checke 		= $Qry->exe_UPDATE($con);
        if($checke){  
            $return = json_encode(array("status"=>"success"));
        }else{
            $return = json_encode(array("status"=>"error", 'w'=>$Qry->selected, 'err'=> mysqli_error($con) ));
        }
    }else{
        $return = json_encode(array('status'=>'empty'));
    }

print $return;
mysqli_close($con);

?>