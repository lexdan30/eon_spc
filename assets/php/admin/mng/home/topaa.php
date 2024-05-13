<?php
require_once('../../../logger.php');
require_once('../../../activation.php');
$conn = new connector();
$con  = $conn->connect();
require_once('../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));
$str 		=  $param->accountid;
$return 	= null;	
$data		= array(
				"name" => array(),
				"data" => array()
			);
$first_day_this_month = date('Y-01-01');
$last_day_this_month  = date('Y-m-t');

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



if( !empty( $param->accountid ) && !empty($str) ){
	
	$Qry2 			= new Query();	
	$Qry2->table     = "vw_attendance_application AS a";
	$Qry2->selected  = "a.idacct, a.empname, COUNT(a.idacct) AS ctr";
	$Qry2->fields    = "(a.date >= '".$first_day_this_month."' AND a.date <= '".$last_day_this_month."' ) AND a.idacct IN (".$str.") GROUP BY a.idacct ORDER BY COUNT(a.idacct) DESC LIMIT 5";
	$rs2 			= $Qry2->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
	if( mysqli_num_rows($rs2) >= 1 ){
		while($row2=mysqli_fetch_assoc($rs2)){
			
			array_push($data["name"],$row2['empname']);
			array_push($data["data"],$row2['ctr']);
		}
	}
	
	$return =  json_encode($data);
}else{
	//$return = json_encode(array('status'=>'error'));
	$return =  json_encode($data);
	
}

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
?>
