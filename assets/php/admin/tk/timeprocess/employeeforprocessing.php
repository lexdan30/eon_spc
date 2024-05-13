<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date=SysDate();
$time=SysTime();
$return = null;	

$ids='';

if( !empty( $param->info->classi ) ){
	$dept = $param->info->classi;
	if (!empty($dept)) {
		$arr_id = array();
		$arr    = getHierarchy($con, $dept);
		array_push($arr_id, $dept);
		if (!empty($arr["nodechild"])) {
			$a = getChildNode($arr_id, $arr["nodechild"]);
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
}


$Qry = new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "empname,id";
if($ids == ''){
    $Qry->fields    = "id>0";
}else{
    $Qry->fields    = "idunit in (".$ids.")";
}


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "empname" 	               => $row['empname'],
            "empid" 	           => $row['id']
        );
    }
    $return =  json_encode($data);
}else{
	$return = json_encode('');
}
print $return;
mysqli_close($con);




?>