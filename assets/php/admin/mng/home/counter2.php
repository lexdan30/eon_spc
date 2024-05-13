<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data = array();

$Qry = new Query();	
$Qry->table     = "vw_data_timesheet AS a";
$Qry->selected  = "a.work_date, 
				   (  SELECT COUNT(w.work_date) FROM  vw_data_timesheet AS w LEFT JOIN vw_dataemployees AS ww ON ww.id=w.empID WHERE ww.idsuperior='".$param->accountid."' AND w.work_date = a.work_date AND w.absent IS NOT NULL AND w.absent <= 0 ) AS present,
				   (  SELECT GROUP_CONCAT(CONCAT(ww.empid,'-', ww.empname) SEPARATOR '|') FROM  vw_data_timesheet AS w LEFT JOIN vw_dataemployees AS ww ON ww.id=w.empID WHERE ww.idsuperior='".$param->accountid."' AND w.work_date = a.work_date AND w.absent IS NOT NULL AND w.absent <= 0 ) AS empids";
$Qry->fields    = "a.work_date BETWEEN '".$param->start_date."' AND '".$param->end_date."' GROUP BY a.work_date ";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
	while($row=mysqli_fetch_array($rs)){
        $arr2 = array();
		$arr3 = array();
		if( (int)$row['present'] > 0 ){
			$arr1 = explode('|',$row['empids']);
			foreach( $arr1 as $k=>$v ){
				$arr4 = explode("-",$v);
				array_push($arr2, $arr4[0]);
				array_push($arr3, $arr4[1]);
			}
		}
		$data[] = array( 
			"empids"			=> $arr2,
			"empname"			=> $arr3,
			"date"				=> $row['work_date'],
			"present"			=> $row['present'],
			"colour"			=> ''
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array('status'=>'error'));
	
}
print $return;
mysqli_close($con);
?>