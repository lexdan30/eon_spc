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

$data		= array();
$arr_ids 	= array();
$array_lbl 	= array();
$arr_data 	= array();
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
		array_push($arr_ids,$ids);
		array_push($arr_data,getEmpCtr($con,$ids,$param));
    }
}

$data = array(
	"lbl" 	=> $array_lbl,
	"data"	=> $arr_data,
	"depts" => $arr_ids,
	"total" => array_sum($arr_data)
);


$return = json_encode($data);

print $return;
mysqli_close($con);


function getEmpCtr($con,$ids,$param){
	$Qry 			= new Query();
	$Qry->table 	= "vw_dataemployees AS a";
	$Qry->selected 	= "COUNT(a.id) AS total";
	$Qry->fields    = "a.idunit in (".$ids.") AND a.etypeid='1' AND ( a.hdate >= '".$param->dfrom."' AND a.hdate <= '".$param->dto."')";

	$rs = $Qry->exe_SELECT($con);
	$data = array();
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			return (int)$row['total'];
		}
	}
	return 0;
}

?>