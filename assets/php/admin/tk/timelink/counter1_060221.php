<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date=SysDate();
$time=SysTime();
$return = null;	
$data = array();
if( !empty($param->accountid) ){
	
	$Qry 			= new Query();	
	$Qry->table     = "tbltimelogs AS a LEFT JOIN vw_databusinessunits AS b ON a.idunit = b.id";
	$Qry->selected  = "b.name AS unit , COUNT(a.idunit) AS ctr";
	$Qry->fields    = "b.id>0 AND a.idconf = 1 AND (a.work_date BETWEEN '".$param->info->sdate."' AND '".$param->info->fdate."')  GROUP BY a.idunit ORDER BY COUNT(a.idunit) DESC";
	$rs				= $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){
		while($row=mysqli_fetch_array($rs)){
			$data[] = array(
				"name" => $row['unit'], 
				"ctr"  => $row['ctr'],
				"sum"  => getUploadedCtr($con,$param->info->sdate,$param->info->fdate),
			);
		}
	}
}
$return = json_encode($data);

print $return;
mysqli_close($con);

function getUploadedCtr($con,$sdate,$fdate){
	$Qry 			= new Query();	
	$Qry->table     = "tbltimelogs";
	$Qry->selected  = "*";
	$Qry->fields    = "id>0 AND idconf = 1 AND work_date BETWEEN '".$sdate."' AND '".$fdate."'";
	$rs				= $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}
?>