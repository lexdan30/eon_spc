<?php
require_once('../../../logger.php');
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param 		= json_decode(file_get_contents('php://input'));
$data		= array();
$return		= "";

if(!empty($param->accountid)){
	
	$Qry 			= new Query();	
	$Qry->table     = "(SELECT id, idacct, idleave, entitle, used, balance, carry_over, pending, entitled_year FROM vw_leavesummary) AS a 
						 LEFT JOIN (SELECT id, empname FROM vw_dataemployees) AS b ON (a.idacct = b.id)
						 LEFT JOIN (SELECT id, name, alias, imgicon, color FROM tblleaves) AS c ON (a.idleave = c.id)";
	$Qry->selected  = "a.id, a.idleave, b.id AS idacct, b.empname, c.name AS leave_name, c.alias, c.imgicon , a.entitle, a.used, a.balance, a.carry_over, a.pending, a.entitled_year, c.color";
	$Qry->fields    = "a.id > 0 AND b.id = '".$param->accountid."' ";
	$rs 			= $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_assoc($rs)){
			
			$entitle_hrs	= floatval( number_format($row['entitle'],2) );
			if( $entitle_hrs > (int)$entitle_hrs ){
				$entitle_hrs	= number_format($entitle_hrs,2);
			}
			
			$used_hrs	= floatval( number_format($row['used'],2) );
			if( $used_hrs > (int)$used_hrs ){
				$used_hrs	= number_format($used_hrs,2);
			}
			
			$balance_hrs	= floatval( number_format($row['balance'],2) );
			if( $balance_hrs > (int)$balance_hrs ){
				$balance_hrs = number_format($balance_hrs,2);
			}
			
			$pending_hrs	= floatval( number_format($row['pending'],2) );
			if( $pending_hrs > (int)$pending_hrs ){
				$pending_hrs = number_format($pending_hrs,2);
			}

			$carriedover_hrs	= floatval( number_format($row['carry_over'],2) );
			if( $carriedover_hrs > (int)$carriedover_hrs ){
				$carriedover_hrs = number_format($carriedover_hrs,2);
			}
			
			$entitle_day = floatval( $entitle_hrs ) / 8;
			if( $entitle_day > (int)$entitle_day ){
				$entitle_day	= number_format($entitle_day,2);
			}
			 
			$used_day = floatval( $used_hrs ) / 8;
			if( $used_day > (int)$used_day ){
				$used_day	= number_format($used_day,2);
			}
			
			$balance_day = floatval( $balance_hrs ) / 8;
			if( $balance_day > (int)$balance_day ){
				$balance_day	= number_format($balance_day,2);
			}
			
			$pending_day = floatval( $pending_hrs ) / 8;
			if( $pending_day > (int)$pending_day ){
				$pending_day	= number_format($pending_day,2);
			}

			$carriedover_day = floatval( $carriedover_hrs ) / 8;
			if( $carriedover_day > (int)$carriedover_day ){
				$carriedover_day	= number_format($carriedover_day,2);
			}
			
			
			$available_hrs	= floatval($balance_hrs - ( $pending_hrs));
			if( $available_hrs > (int)$available_hrs ){
				$available_hrs	= number_format($available_hrs,2);
			}
			
			$available_day	= floatval($balance_day - ( $pending_day));
			if( $available_day > (int)$available_day ){
				$available_day	= number_format($available_day,2);
            }

            if( $row['idleave'] == '2'){
                $backgroundColor = '#fe9901';
            }
            if( $row['idleave'] == '1'){
                $backgroundColor = '#00af50';
            }
            if( $row['idleave'] == '3'){
                $backgroundColor = '#525050';
            }
            if( $row['idleave'] == '4'){
                $backgroundColor = '#395723';
            }
            if($row['idleave'] == '5'){
                $backgroundColor = '#7e6000';
            }
            if($row['idleave'] == '6'){
                $backgroundColor = '#01b0f1';
            }
            if($row['idleave'] == '7'){
                $backgroundColor = '#01b0f1';
            }
            if($row['idleave'] == '8'){
                $backgroundColor = '#ff3300';
            }
            if($row['idleave'] == '9'){
                $backgroundColor = '#0071c0';
            }
            if($row['idleave'] == '10'){
                $backgroundColor = '#1f4e78';
            }
            if($row['idleave'] == '11'){
                $backgroundColor = '#58267f';
            }
            if($row['idleave'] == '12'){
                $backgroundColor = '#7e6000';
            }

            if($available_day == 0){
                $dl = '';
            }else{
                $dl = 'dl';
            }

			$key = array_keys($data);
			$lastkey = (int)end($key);
			$lastkey1 = $lastkey + 2;
            		
			$data[] = array(
				"id"			=>	$row['id'],
				"idacct"		=>	$row['idacct'],
				"empname"		=>	$row['empname'],
				"leave_name"	=>	$row['leave_name'],
				"leave_alias"	=>  $row['leave_name'],
				"imgicon"		=>	$row['imgicon'],
				"idleave"		=>	$row['idleave'],
				"entitle_hrs"	=>	$entitle_hrs,
				"used_hrs"		=>	$used_hrs,
				"balance_hrs"	=>	$balance_hrs,
				"pending_hrs"	=>	$pending_hrs,
				"available_hrs"	=>	$available_hrs,
				"carriedover_hrs"=>	$carriedover_hrs,
				"total_hrs" 	=>	$carriedover_hrs + $entitle_hrs,
				
				"entitle_day"	=>	$entitle_day,
				"used_day"		=>	$used_day,
				"balance_day"	=>	$balance_day,
				"pending_day"	=>	$pending_day,
				"available_day"	=>	$available_day,
				"carriedover_day"=>	$carriedover_day,
				"total_day"		=>	$carriedover_day + $entitle_day,
                "bg"            =>  $row['color'],
				"entitled_yr"	=>	$row['entitled_year'],
                "dl"            =>  $dl,
				"view"			=>  $lastkey1
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