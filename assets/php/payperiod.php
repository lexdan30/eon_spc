<?php
require_once('logger.php');
require_once('activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('classPhp.php');

    $param = json_decode(file_get_contents('php://input'));
	$data = array();	
	
	
    $Qry=new Query();
    $Qry->table="tblpayperiod";
    $Qry->selected="id, period_start, period_end, pay_date, grace_hour, hascontri, tkstatus,stat";
	
    if( (int)$param->f == 1 ){ //prev
		$Qry->fields="id < '".(int)$param->idperiod."' ORDER BY id DESC LIMIT 1 ";
	}elseif( (int)$param->f == 2 ){ //nxt
		$Qry->fields="id > '".(int)$param->idperiod."' ORDER BY id ASC LIMIT 1 ";
	}
	
	
	$rs=$Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
    //echo $Qry->fields;
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
            $data = array( 
				"id"        => $row['id'],
				"pay_start"	=> $row['period_start'],
				"pay_end"	=> $row['period_end'],
				"pay_date"	=> $row['pay_date'],
				"grace_hour"=> $row['grace_hour'],
				"hascontri" => $row['hascontri'],
				"pay_stat"	=> $row['stat'],
				"tk_stat"	=> $row['tkstatus']
			);
        }
    }
	
$return = json_encode($data);    
print $return;
mysqli_close($con);
?>