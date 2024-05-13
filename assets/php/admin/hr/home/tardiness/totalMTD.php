<?php
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


            $arr_late[]  = getCountsLates($con,  $str, $dFrom, $dTo);
            $arr_absent[]  = getCountsAbsences($con,  $str, $dFrom, $dTo);
            $arr_ut[]  = getCountsUndertime($con,  $str, $dFrom, $dTo);

            
			
			$month++;
			
		}
	}
	
	
	$data	 	= array(
		"status"	    =>	"success",
        "late"	        =>	$arr_late,
        "absent"       =>	$arr_absent,
        "undertime"    =>	$arr_ut,
        "getTotalsMTD"  => getTotalsMTD($con,$param->accountid),
        "pp"            => $str,
	);
	
	$return =  json_encode($data);
}else{
	$return = json_encode(array('status'=>'error'));
}

$return =  json_encode($data);
print $return;
mysqli_close($con);


// function getCountsLates($con,  $idacct,$dFrom, $dTo){
// 	$Qry 			= new Query();	
// 	$Qry->table     = "vw_data_timesheet";
// 	$Qry->selected  = "SUM(late) AS ctr";
// 	$Qry->fields    = "(work_date >= '".$dFrom."' AND work_date < '".$dTo."') AND late IS NOT NULL and late <> ' '";
// 	$rs 			= $Qry->exe_SELECT($con);
// 	if( mysqli_num_rows($rs) >= 1 ){
// 		if($row=mysqli_fetch_array($rs)){
// 			return $row['ctr'];
// 		}
// 	}
// 	return 0;
// }
function getCountsLates($con,  $idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "vw_timesheetfinal";
	$Qry->selected  = "COUNT(tid) AS ctr";
	$Qry->fields    = "( work_date >= '".$dFrom."' AND work_date < '".$dTo."' ) AND late IS NOT NULL and late <> ' ' and late NOT LIKE '0%'";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
// function getCountsAbsences($con,$idacct,$dFrom, $dTo){
// 	$Qry 			= new Query();	
// 	$Qry->table     = "vw_data_timesheet";
// 	$Qry->selected  = "COUNT(id) AS ctr";
// 	$Qry->fields    = "(work_date >= '".$dFrom."' AND work_date < '".$dTo."') AND absent IS NOT NULL and absent <> ' '";
// 	$rs 			= $Qry->exe_SELECT($con);
// 	if( mysqli_num_rows($rs) >= 1 ){
// 		if($row=mysqli_fetch_array($rs)){
// 			return $row['ctr'];
// 		}
// 	}
// 	return 0;
// }
function getCountsAbsences($con,$idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "vw_timesheetfinal";
	$Qry->selected  = "COUNT(tid) AS ctr";
	$Qry->fields    = "(work_date >= '".$dFrom."' AND work_date < '".$dTo."') AND absent IS NOT NULL and absent <> ' '  and absent NOT LIKE '0%'";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}
// function getCountsUndertime($con,$idacct,$dFrom, $dTo){
// 	$Qry 			= new Query();	
// 	$Qry->table     = "vw_data_timesheet";
// 	$Qry->selected  = "COUNT(id) AS ctr";
// 	$Qry->fields    = "(work_date >= '".$dFrom."' AND work_date < '".$dTo."') AND ut IS NOT NULL and ut <> ' ' ";
// 	$rs 			= $Qry->exe_SELECT($con);
// 	if( mysqli_num_rows($rs) >= 1 ){
// 		if($row=mysqli_fetch_array($rs)){
// 			return $row['ctr'];
// 		}
// 	}
// 	return 0;
// }
function getCountsUndertime($con,$idacct,$dFrom, $dTo){
	$Qry 			= new Query();	
	$Qry->table     = "vw_timesheetfinal";
	$Qry->selected  = "COUNT(tid) AS ctr";
	$Qry->fields    = "(work_date >= '".$dFrom."' AND work_date < '".$dTo."') AND ut IS NOT NULL and ut <> ' ' and ut  NOT LIKE '0%'";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}

// function getTotalsMTD($con,$idacct){
//     $data = array();
// 	$Qry 			= new Query();	
// 	$Qry->table     = "vw_data_timesheet";
// 	$Qry->selected  = "SUM(late) AS tot_late,COUNT(absent) as tot_absent";
// 	$Qry->fields    = "MONTH(work_date) = MONTH(CURRENT_DATE()) AND YEAR(work_date) = YEAR(CURRENT_DATE()) and absent is not null and absent <> ' '";
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
function getTotalsMTD($con,$idacct){
    $data = array();
	$Qry 			= new Query();	
	$Qry->table     = "vw_timesheetfinal";
	$Qry->selected  = "SUM(late) AS tot_late,COUNT(absent) as tot_absent,SUM(ut) AS tot_ut";
	$Qry->fields    = "MONTH(work_date) = MONTH(CURRENT_DATE()) AND YEAR(work_date) = YEAR(CURRENT_DATE()) and absent is not null and absent <> ' ' AND absent NOT LIKE '0%'";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		while($row=mysqli_fetch_array($rs)){
            
            $data[]=array(

                "tot_late" => $row['tot_late'],
                "tot_absent" => $row['tot_absent'],
				"tot_ut" => $row['tot_ut']

            );
		}
	}
	return $data;
}


// $Qry = new Query();	
// $Qry->table     = "vw_data_timesheet";
// $Qry->selected  = "empID,MIN(work_date) AS min_date, MAX(work_date) AS max_date,absent, SUM(late) AS latess";
// $Qry->fields    = "absent IS NOT NULL AND absent <> '0.00' GROUP BY MONTH(work_date)";
// $rs = $Qry->exe_SELECT($con);
// if(mysqli_num_rows($rs)>= 1){
//     while($row=mysqli_fetch_array($rs)){

//         $earlier = new DateTime($row['min_date']);
//         $later = new DateTime($row['max_date']);
        
//         $absences = $later->diff($earlier)->format("%a")+1;

//         $data[] = array( 
//             "empID"        => $row['empID'],
//             "absences"     => $absences,
//             "late"         => $row['latess']
            

//         );
//     }
//     $return = json_encode($data);
// }else{
//     $return = json_encode(array('q'=>$Qry->fields));
// }
// print $return;
// mysqli_close($con);





?>