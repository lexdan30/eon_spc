<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$str =  $param->accountid;


$year = date("Y");
$month = 1;
$months = array(
	'January',
	'February',
	'March',
	'April',
	'May',
	'June',
	'July ',
	'August',
	'September',
	'October',
	'November',
	'December',
);

$dept = getIdUnit($con,$param->accountid);

$ids=0;
//Get Managers Under person
if( !empty( $dept ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$dept);
    if( !empty( $arr["nodechild"] ) ){     
        $ids = join(',', flatten($arr['nodechild']));
    } else {
        $ids = '0';
    }
}

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "GROUP_CONCAT(id) as idacct";
$Qry->fields    = "(idunit IN (".$ids.") OR idsuperior='".$param->accountid."') AND id != '".$param->accountid."'";
$rs 			= $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    $str = mysqli_fetch_assoc($rs)['idacct'];
}



if( !empty( $param->accountid ) ){
	
    $arr_late  = array();
    $arr_absent  = array();
    $arr_ut  = array();

    if( strlen($str) > 0 ){
		while($month<=12){
			if( $month != 12 ){
				$dFrom	= $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-01";
				$dTo	= $year."-".str_pad(((int)$month+1),2,"0",STR_PAD_LEFT)."-01";
			}else{
				$dFrom	= $year."-12-01";
				$dTo	= ((int)$year+1)."-01-01";
			}


            $arr_late[]    = getCountsLates($con, $str, $dFrom, $dTo,$ids);
            $arr_absent[]  = getCountsAbsences($con, $str, $dFrom, $dTo,$ids);
            $arr_ut[]      = getCountsUndertime($con, $str, $dFrom, $dTo,$ids);

			$month++;
			
		}
	}
	
	
	$data	 	= array(
		"status"	    =>	"success",
        "late"	        =>	$arr_late,
        "absent"        =>	$arr_absent,
        "undertime"     =>	$arr_ut,
        "getTotalsMTD"  => getTotalsMTD($con,$str,$ids),
        "pp"            => $str,
	); 
	
	$return =  json_encode($data);
}else{
	$return = json_encode(array('status'=>'error'));
}

$return =  json_encode($data);
print $return;
mysqli_close($con);

function getIdUnit($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getIdUnit');
    if(mysqli_num_rows($rs)>=1){
		return mysqli_fetch_assoc($rs)['idunit'];
    }
    return null;
}

// function getCountsLates($con, $idacct,$dFrom, $dTo, $ids){
// 	$Qry 			= new Query();	
// 	$Qry->table     = "vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID = de.id";
// 	$Qry->selected  = "SUM(dt.late) AS ctr";
// 	$Qry->fields    = "(dt.work_date >= '".$dFrom."' AND dt.work_date < '".$dTo."') AND dt.late IS NOT NULL and dt.late <> ' ' AND de.idunit IN (".$ids.")";
// 	$rs 			= $Qry->exe_SELECT($con);
// 	if( mysqli_num_rows($rs) >= 1 ){
// 		if($row=mysqli_fetch_array($rs)){
// 			return $row['ctr'];
// 		}
// 	}
// 	return 0;
// }

function getCountsLates($con, $idacct,$dFrom, $dTo, $ids){
	//print_r($ids);
	$Qry 			= new Query();	
	$Qry->table     = "vw_mng_timesheetfinal";
	$Qry->selected  = "COUNT(tid) AS ctr";
	$Qry->fields    = "work_date BETWEEN '".$dFrom."' AND '".$dTo."' AND late <> '0.00' AND late <> '0' AND tid IN (".$idacct.")";
	$rs 			= $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getCountsLates');
	if( mysqli_num_rows($rs) >= 1 ){
        return mysqli_fetch_assoc($rs)['ctr'];
	}
	return 0;
}

// function getCountsAbsences($con,$idacct,$dFrom, $dTo, $ids){
// 	$Qry 			= new Query();	
// 	$Qry->table     = "vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID = de.id";
// 	$Qry->selected  = "COUNT(dt.id) AS ctr";
// 	$Qry->fields    = "(dt.work_date >= '".$dFrom."' AND dt.work_date < '".$dTo."') AND dt.absent IS NOT NULL and dt.absent <> ' ' AND de.idunit IN (".$ids.")";
// 	$rs 			= $Qry->exe_SELECT($con);
// 	if( mysqli_num_rows($rs) >= 1 ){
// 		if($row=mysqli_fetch_array($rs)){
// 			return $row['ctr'];
// 		}
// 	}
// 	return 0;
// }

function getCountsAbsences($con,$idacct,$dFrom, $dTo, $ids){
	$Qry 			= new Query();	
	$Qry->table     = "vw_mng_timesheetfinal";
	$Qry->selected  = "COUNT(tid) AS ctr";
	$Qry->fields    = "work_date BETWEEN '".$dFrom."' AND '".$dTo."') AND absent IS NOT NULL AND absent <> '0' AND tid IN (".$idacct.")";
	$rs 			= $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getCountsAbsences');
	if( mysqli_num_rows($rs) >= 1 ){
        return mysqli_fetch_assoc($rs)['ctr'];
	}
	return 0; 
}

// function getCountsUndertime($con,$idacct,$dFrom, $dTo, $ids){
// 	$Qry 			= new Query();	
// 	$Qry->table     = "vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID = de.id";
// 	$Qry->selected  = "COUNT(dt.id) AS ctr";
// 	$Qry->fields    = "(dt.work_date >= '".$dFrom."' AND dt.work_date < '".$dTo."') AND dt.ut IS NOT NULL and dt.ut <> ' ' AND de.idunit IN (".$ids.")";
// 	$rs 			= $Qry->exe_SELECT($con);
// 	if( mysqli_num_rows($rs) >= 1 ){
// 		if($row=mysqli_fetch_array($rs)){
// 			return $row['ctr'];
// 		}
// 	}
// 	return 0;
// }

function getCountsUndertime($con,$idacct,$dFrom, $dTo, $ids){
	$Qry 			= new Query();	
	$Qry->table     = "vw_mng_timesheetfinal";
	$Qry->selected  = "COUNT(tid) AS ctr";
	$Qry->fields    = "work_date BETWEEN '".$dFrom."' AND '".$dTo."') AND ut <> '0.00' AND ut <> '0' AND tid IN (".$idacct.")";
	$rs 			= $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getCountsUndertime');
	if( mysqli_num_rows($rs) >= 1 ){
        return mysqli_fetch_assoc($rs)['ctr'];
	}
	return 0;
}

// function getTotalsMTD($con,$idacct,$ids){
//     $data = array();
// 	$Qry 			= new Query();	
// 	$Qry->table     = "vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID = de.id";
// 	$Qry->selected  = "SUM(dt.late) AS tot_late,COUNT(dt.absent) as tot_absent";
// 	$Qry->fields    = "MONTH(dt.work_date) = MONTH(CURRENT_DATE()) AND YEAR(dt.work_date) = YEAR(CURRENT_DATE()) and dt.absent is not null and dt.absent <> ' ' AND de.idunit IN (".$ids.")";
// 	$rs 			= $Qry->exe_SELECT($con);
// 	if( mysqli_num_rows($rs) >= 1 ){
// 		while($row=mysqli_fetch_array($rs)){
            
//             $data[]=array(
//                 "tot_late" => $row['tot_late'],
//                 "tot_absent" => $row['tot_absent']

//             );
// 		}
// 	}
// 	return $data;
// }

function getTotalsMTD($con,$idacct,$ids){
    $data = array();
	$Qry 			= new Query();	
	$Qry->table     = "vw_mng_timesheetfinal";
	$Qry->selected  = "SUM(late) AS tot_late,COUNT(absent) as tot_absent,SUM(ut) AS tot_ut";
	$Qry->fields    = "MONTH(work_date) = MONTH(CURRENT_DATE()) AND YEAR(work_date) = YEAR(CURRENT_DATE()) and absent is not null and absent <> ' ' AND tid IN (".$idacct.")";
	$rs 			= $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTotalsMTD');
	if( mysqli_num_rows($rs) >= 1 ){
		while($row=mysqli_fetch_assoc($rs)){
            
            $data[]=array(
                "tot_late" => $row['tot_late'],
				"tot_absent" => $row['tot_absent'],
				"tot_ut" => $row['tot_ut']

            );
		}
	}
	return $data;
}

?>