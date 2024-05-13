<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param 		= json_decode(file_get_contents('php://input'));
$data		= array();

$datelv = new DateTime();
$datelv->modify('-1 day');
$lvallowed = $datelv->format('Y-m-d');

$datelvvl = new DateTime();
$datelvvl->modify('+7 day');
$lvvlallowed = $datelvvl->format('Y-m-d');

$date  = $param->date;

$today = date("Y-m-d");

if(!empty($param->accountid)){
	
	$Qry 			= new Query();	
	$Qry->table     = "tblaccountleaves AS a 
						 LEFT JOIN vw_dataemployees AS b ON a.idacct = b.id
						 LEFT JOIN tblleaves AS c ON a.idleave = c.id";
    $Qry->selected  = "a.id,a.idleave, b.id AS idacct, b.empname, c.name AS leave_name, c.alias, c.imgicon , a.entitle, a.used, a.balance, a.pending_bal";
    
    if ($date >= $lvallowed) {
        $Qry->fields    = "a.id > 0 AND b.id = '".$param->accountid."' AND a.idleave = '1'";
    }
    if ($date == $today) {
        $Qry->fields    = "a.id > 0 AND b.id = '".$param->accountid."' AND a.idleave != '2'";
    }
    if ($date > $today) {
        $Qry->fields    = "a.id > 0 AND b.id = '".$param->accountid."' AND a.idleave != '2' AND a.idleave != '1'";
    }
    if ($date >= $lvvlallowed) {
        $Qry->fields    = "a.id > 0 AND b.id = '".$param->accountid."' AND a.idleave != '1'";
    }

	$rs 			= $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_array($rs)){
			
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
			
			$pending_hrs	= floatval( number_format($row['pending_bal'],2) );
			if( $pending_hrs > (int)$pending_hrs ){
				$pending_hrs = number_format($pending_hrs,2);
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
			
			
			$available_hrs	= floatval($balance_hrs - ($used_hrs + $pending_hrs));
			if( $available_hrs > (int)$available_hrs ){
				$available_hrs	= number_format($available_hrs,2);
			}
			
			$available_day	= floatval($balance_day - ($used_day + $pending_day));
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



            		
			$data[] = array(
				"id"			=>	$row['id'],
				"idacct"		=>	$row['idacct'],
				"empname"		=>	$row['empname'],
				"leave_name"	=>	$row['leave_name'],
				"leave_alias"	=>  $row['alias'],
				"imgicon"		=>	$row['imgicon'],
				"idleave"		=>	$row['idleave'],
				"entitle_hrs"	=>	$entitle_hrs,
				"used_hrs"		=>	$used_hrs,
				"balance_hrs"	=>	$balance_hrs,
				"pending_hrs"	=>	$pending_hrs,
				"available_hrs"	=>	$available_hrs,
				
				"entitle_day"	=>	$entitle_day,
				"used_day"		=>	$used_day,
				"balance_day"	=>	$balance_day,
				"pending_day"	=>	$pending_day,
                "available_day"	=>	$available_day,
                "bg"            =>  $backgroundColor,
                "dl"            =>  $dl
			);
		}
		$return = json_encode($data);
	}
	
}else{
	$return = json_encode(array('status'=>'error'));
}

print $return;
mysqli_close($con);

function getCurrentIn($con, $work_date, $idacct){
	$Qry 			= new Query();	
	$Qry->table     = "vw_data_timesheet as a";
	$Qry->selected  = "a.in";
	$Qry->fields    = "work_date = '".$work_date."' AND idacct = '".$idacct."'  ";
	$rs 			= $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			if( !empty( $row['in'] ) ){
				return date_format(date_create($row['in']), "H:i A");
			}else{
				return "00:00 AM";
			}
		}
	}
	return "00:00 AM";
}

function getCurrentLate($con, $work_date, $idacct){
	$Qry 			= new Query();	
	$Qry->table     = "vw_data_timesheet";
	$Qry->selected  = "late";
	$Qry->fields    = "work_date = '".$work_date."' AND idacct = '".$idacct."'  ";
	$rs 			= $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			if( !empty( $row['late'] ) ){
				$num = floatval( number_format($row['late'],2) ) * 60;
				if( $num > (int)$num ){
					return number_format($num,2);
				}else{
					return $num;
				}
			}else{
				return "0";
			}
		}
	}
	return "0";
}

?>