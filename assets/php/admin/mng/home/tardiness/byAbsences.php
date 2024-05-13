<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));


// $Qry = new Query();	
// $Qry->table     = "vw_databusinessunits";
// $Qry->selected  = "*";
// $Qry->fields    = "unittype = '3'";
// $rs = $Qry->exe_SELECT($con);

// $arr_ids 	= array();
// $array_lbl 	= array();
// $arr_data 	= array();
// $pie_colour = array();
// if(mysqli_num_rows($rs)>= 1){  
//     while($row=mysqli_fetch_array($rs)){
		
//         array_push($array_lbl,$row['name']);
        
// 		$dept = $row['id'];
//         $ids=0;
        
// 		if (!empty($dept)) {
// 			$arr_id = array();
// 			$arr    = getHierarchy($con, $dept);
// 			array_push($arr_id, $dept);
// 			if (!empty($arr["nodechild"])) {
// 				$a = getChildNode($arr_id, $arr["nodechild"]);
// 				if (!empty($a)) {
// 					foreach ($a as $v) {
// 						array_push($arr_id, $v);
// 					}
// 				}
// 			}
// 			if (count($arr_id) == 1) {
// 				$ids = $arr_id[0];
// 			} else {
// 				$ids = implode(",", $arr_id);
// 			}
//         }
        
// 		array_push($arr_ids,$ids);
//         array_push($arr_data,getDept($con,$ids));
//     }
// }

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

// $str =  $param->accountid;

// $Qry 			= new Query();	
// $Qry->table     = "vw_dataemployees";
// $Qry->selected  = "GROUP_CONCAT(id) as idacct";
// $Qry->fields    = "(idunit IN (".$ids.") OR idsuperior='".$param->accountid."') AND id != '".$param->accountid."'";
// $rs 			= $Qry->exe_SELECT($con);
// if(mysqli_num_rows($rs)>= 1){
//     while($row=mysqli_fetch_array($rs)){
//         $str = $row['idacct'];
//     }
// }


$pie_data	= getAbsences($con,$ids,$param->accountid);
$pie_colour = array('#b0beec');
$pie_labels	= getEmp($con, $ids,$param->accountid);

$data = array(

    "lbl" 	=> $pie_labels,
    "ctr" 	=> $pie_data,
    "colour"=> $pie_colour,



);

$return = json_encode($data);


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

// function getEmp($con, $ids,$idacct){
// 	$data=array();
// 	$Qry 			= new Query();	
// 	$Qry->table     = "vw_dataemployees";
// 	$Qry->selected  = "*";
// 	$Qry->fields    = "idunit IN (".$ids.")";
// 	$rs 			= $Qry->exe_SELECT($con);
// 	if( mysqli_num_rows($rs) >= 1 ){
// 		while($row=mysqli_fetch_array($rs)){

// 			array_push($data,$row['empname']);
			
// 		}
// 	}
// 	return $data;
// }
function getEmp($con, $ids,$idacct){
	$data=array();
	$Qry 			= new Query();	
	$Qry->table     = "vw_dataemployees";
	$Qry->selected  = "id";
    //$Qry->fields    = "idunit IN (".$ids.")";
    $Qry->fields    = "(idunit IN (".$ids.") OR idsuperior='".$idacct."') AND id != '".$idacct."'";
	$rs 			= $Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getEmp');
	if( mysqli_num_rows($rs) >= 1 ){
		while($row=mysqli_fetch_assoc($rs)){

            $Qry2 			= new Query();	
            $Qry2->table     = "vw_mng_timesheetfinal";
            $Qry2->selected  = "empname";
            $Qry2->fields    = " MONTH(work_date) = MONTH(CURRENT_DATE()) AND YEAR(work_date) = YEAR(CURRENT_DATE()) and absent IS NOT NULL AND absent <> '0' AND tid IN (".$row['id'].") group by empname";
            $rs2 			= $Qry2->exe_SELECT($con);
            Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getEmp(Qry2)');
            if( mysqli_num_rows($rs2) >= 1 ){
                while($row2=mysqli_fetch_assoc($rs2)){
                    array_push($data,$row2['empname']);
                }
            }
			
		}
	}
	return $data;
}

// function getAbsences($con, $ids){
// 	$data=array();
// 	$Qry 			= new Query();	
// 	$Qry->table     = "vw_dataemployees";
// 	$Qry->selected  = "*";
// 	$Qry->fields    = "idunit IN (".$ids.")";
// 	$rs 			= $Qry->exe_SELECT($con);
// 	if( mysqli_num_rows($rs) >= 1 ){
// 		while($row=mysqli_fetch_array($rs)){

//             $Qry2 			= new Query();	
//             $Qry2->table     = "vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID = de.id";
//             $Qry2->selected  = "COUNT(dt.absent) AS ctr";
//             $Qry2->fields    = "dt.empID =  '".$row['id']."' AND MONTH(dt.work_date) = MONTH(CURRENT_DATE()) AND YEAR(dt.work_date) = YEAR(CURRENT_DATE()) AND dt.absent IS NOT NULL AND dt.absent <> ' ' AND dt.absent > 0";
//             $rs2 			= $Qry2->exe_SELECT($con);
//             if( mysqli_num_rows($rs2) >= 1 ){
//                 while($row2=mysqli_fetch_array($rs2)){
                    
//                     array_push($data,$row2['ctr']);
//                 }
//             }
			
// 		}
// 	}
// 	return $data;
// }
function getAbsences($con, $ids,$idacct){
	$data=array();
	$Qry 			= new Query();	
	$Qry->table     = "vw_dataemployees";
	$Qry->selected  = "id";
    //$Qry->fields    = "idunit IN (".$ids.")";
    $Qry->fields    = "(idunit IN (".$ids.") OR idsuperior='".$idacct."') AND id != '".$idacct."'";
	$rs 			= $Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getAbsences');
	if( mysqli_num_rows($rs) >= 1 ){
		while($row=mysqli_fetch_assoc($rs)){

            $Qry2 			= new Query();	
            $Qry2->table     = "vw_mng_timesheetfinal";
            $Qry2->selected  = "COUNT(tid) AS ctr";
            $Qry2->fields    = " MONTH(work_date) = MONTH(CURRENT_DATE()) AND YEAR(work_date) = YEAR(CURRENT_DATE()) and absent IS NOT NULL AND absent <> '0' AND tid IN (".$row['id'].")";
            $rs2 			= $Qry2->exe_SELECT($con);
            Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getAbsences(Qry2)');
            if( mysqli_num_rows($rs2) >= 1 ){
                while($row2=mysqli_fetch_assoc($rs2)){
                    array_push($data,$row2['ctr']);
                }
            }
			
		}
	}
	return $data;
}
?>
