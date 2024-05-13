<?php
require_once('../../../logger.php');
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

	$param = json_decode(file_get_contents('php://input'));
	// $param = $_POST;
	$date_create=SysDate();
	$time_create=SysTime();
	$return = null;
	
	// print_r($param->newschd);

    if(!empty($param->idacct)){
		$ctr=1;
		foreach($param->dates as $key=>$value){
			if($param->newschd[$key] != ''){
				if(!checkIfHasPending($con, $param->idacct, $param->dates[$key])){
					$id_payperiod = getIdPayPeriod($con, $param->dates[$key]);

					$remarks = 'New schedule from '.$param->oldschd[$key].' to '.$param->newschd[$key];
	
					$time 	   = time();
					$docnumber = "SS".$param->idacct.strtotime( $date_create.$time ).$time.$ctr;
					$ctr++;
	
					// echo getIdShift($con, $param->newschd[$key]) . "\n";
	
					// echo $param->dates[$key]."\n";
	
					$Qry 			= new Query();
					$Qry->table 	= "tbltimeshift";
					$Qry->selected 	= "docnumber, creator, idacct, idshift, date, remarks, stat, id_payperiod, date_create";
					$Qry->fields 	= " '".$docnumber."',
										'".$param->accountid."',
										'".$param->idacct."',
										'".getIdShift($con, $param->newschd[$key])."',
										'".$param->dates[$key]."',
										'".$remarks."',
										'3',
										'".$id_payperiod."',
										'".$date_create."'";
					$checkentry 	= $Qry->exe_INSERT($con); 
				}
			}
		}
		$return = json_encode(array('status'=>'success'));
    }else{
        $return = json_encode(array('status'=>'error'));
    }

print $return;
mysqli_close($con);

function getIdPayPeriod($con, $date){
    $Qry=new Query();
    $Qry->table="tblpayperiod";
    $Qry->selected="id";
    $Qry->fields="period_start<='".$date."' AND period_end>='".$date."'";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getIdPayPeriod');
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['id'];
    }
    return '';
}

function getIdShift($con, $name){
    $Qry=new Query();
    $Qry->table="tblshift";
    $Qry->selected="id";
    $Qry->fields="name='".$name."'";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getIdShift');
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['id'];
    }
    return '';
}

function checkIfHasPending($con, $date, $idacct){
    $Qry=new Query();
    $Qry->table="tbltimeshift";
    $Qry->selected="id";
    $Qry->fields="idacct='".$idacct."' AND date='".$date."' AND stat=3 AND cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'checkIfHasPending');
    return mysqli_num_rows($rs) >= 1;
}
?>
