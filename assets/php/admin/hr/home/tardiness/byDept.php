<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));


$Qry = new Query();	
$Qry->table     = "vw_databusinessunits";
$Qry->selected  = "*";
$Qry->fields    = "unittype = '3'";
$rs = $Qry->exe_SELECT($con);

$arr_ids 	= array();
$array_lbl 	= array();
$arr_data 	= array();
$pie_colour = array();
if(mysqli_num_rows($rs)>= 1){    
    while($row=mysqli_fetch_array($rs)){
		
        array_push($array_lbl,$row['name']);
        
		$dept = $row['id'];
        $ids=0;
        
		if (!empty($dept)) {
			$arr_id = array();
			$arr    = getHierarchy($con, $dept);
			array_push($arr_id, $dept);
			if (!empty($arr["nodechild"])) {
				$a = getChildNodes($arr_id, $arr["nodechild"]);
				if (!empty($a)) {
					foreach ($a as $v) {
						array_push($arr_id, $v);
					}
				}
			}
			if (count($arr_id) == 1) {
				$ids = $arr_id[0];
			} else {
				$ids = implode(",", $arr_id);
			}
        }
        
		array_push($arr_ids,$ids);
        array_push($arr_data,getDept($con,$ids));
    }
}


//$pie_data	= array(getAbsencesbyDept($con, $param->accountid,$ids));
$pie_colour = array('#b0beec');
$pie_labels	= $array_lbl;



$data = array(

    "lbl" 	=> $pie_labels,
    "ctr" 	=> $arr_data,
    "colour"=> $pie_colour,
    "sum"	=> (int)array_sum($arr_data)


);

$return = json_encode($data);


print $return;
mysqli_close($con);

// function getDept($con,$ids){
//     $Qry 			= new Query();
// 	$Qry->table 	= "vw_data_timesheet as a LEFT JOIN vw_dataemployees AS b on a.empID=b.id";
// 	$Qry->selected 	= "COUNT(a.absent) AS ctr";
// 	$Qry->fields    = "b.idunit in (".$ids.") AND a.absent IS NOT NULL AND a.absent <> ' ' AND MONTH(a.work_date) = MONTH(CURRENT_DATE()) AND YEAR(a.work_date) = YEAR(CURRENT_DATE())";

// 	$rs = $Qry->exe_SELECT($con);
// 	$data = array();
// 	if(mysqli_num_rows($rs)>= 1){
// 		if($row=mysqli_fetch_array($rs)){
// 			return (int)$row['ctr'];
// 		} 
// 	}
// 	return 0;
// }
function getDept($con,$ids){
    $Qry 			= new Query();
	$Qry->table 	= "vw_timesheetfinal";
	$Qry->selected 	= "COUNT(tid) AS ctr";
	$Qry->fields    = "idunit in (".$ids.") AND absent IS NOT NULL AND absent <> ' ' AND absent NOT LIKE '0%' AND MONTH(work_date) = MONTH(CURRENT_DATE()) AND YEAR(work_date) = YEAR(CURRENT_DATE())";

	$rs = $Qry->exe_SELECT($con);
	$data = array();
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			return (int)$row['ctr'];
		}
	}
	return 0;
}


// function getAbsencesbyDept($con,$acct,$ids){
//     $data = array();
// 	$Qry 			= new Query();	
// 	$Qry->table     = "vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID = de.id";
// 	$Qry->selected  = "dt.empID,de.empname, de.idunit, COUNT(dt.absent) AS absent";
// 	$Qry->fields    = "MONTH(dt.work_date) = MONTH(CURRENT_DATE()) AND YEAR(dt.work_date) = YEAR(CURRENT_DATE()) AND dt.absent IS NOT NULL AND dt.absent <> ' ' AND dt.absent IS NOT NULL";
// 	$rs 			= $Qry->exe_SELECT($con);
// 	if( mysqli_num_rows($rs) >= 1 ){
// 		while($row=mysqli_fetch_array($rs)){
            
//             $data[]=array(
//                 "idunit" => $row['idunit'],
//                 "empname" => $row['empname'],
//                 "absent" => $row['absent'],
                

//             );
// 		}
// 	}
// 	return $data;
// }
function getAbsencesbyDept($con,$acct,$ids){
    $data = array();
	$Qry 			= new Query();	
	$Qry->table     = "vw_timesheetfinal";
	$Qry->selected  = "empid,empname, idunit, COUNT(absent) AS absent";
	$Qry->fields    = "MONTH(work_date) = MONTH(CURRENT_DATE()) AND YEAR(work_date) = YEAR(CURRENT_DATE()) AND absent IS NOT NULL AND absent <> ' ' AND absent IS NOT NULL";
	$rs 			= $Qry->exe_SELECT($con);
	if( mysqli_num_rows($rs) >= 1 ){
		while($row=mysqli_fetch_array($rs)){
            
            $data[]=array(
                "idunit" => $row['idunit'],
                "empname" => $row['empname'],
                "absent" => $row['absent'],
            
            );
		}
	}
	return $data;
}



?>