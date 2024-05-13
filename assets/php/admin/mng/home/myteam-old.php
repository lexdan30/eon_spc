<?php
require_once('../../../activation.php');
$conn = new connector();
$con  = $conn->connect();
require_once('../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));

$dept = getIdUnit($con, $param->accountid);
$ids = 0;
//Search Department
if (!empty($dept)) {
    $arr_id = array();
    $arr    = getHierarchy($con, $dept);
    array_push($arr_id, 0);
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

$data          = array();
$Qry           = new Query();
$Qry->table    = "vw_dataemployees";
$Qry->selected = "id,empid, empname, post, pic, wshift_name";
if($param->search != ''){
    $Qry->fields   = "(idunit in (" . $ids . ") OR idsuperior='".$param->accountid."')  AND id != '" . $param->accountid . "' AND empname LIKE '%" . $param->search . "%' ";
}else{
    $Qry->fields   = "(idunit in (" . $ids . ") OR idsuperior='".$param->accountid."') AND id != '" . $param->accountid . "' ";
}


$rs = $Qry->exe_SELECT($con);

while ($row = mysqli_fetch_array($rs)) {
	$path = 'assets/images/undefined.webp?'.time();
	if( !empty( $row['pic'] ) ){
		$path = 'assets/php/admin/hr/employee/pix/'.$row['pic'].'?'.time();
	}
    $data[] = array(
        "staff_id" => $row['id'],
		"staff_lbl" => $row['empid']." ".$row['empname'],
        "id" => $row['empid'],
        "name" => $row['empname'],
        "post" => $row['post'],
		"pic"=>$path,
		"shift_name"=>$row['wshift_name']
    );
}

$return = json_encode($data);

print $return;
mysqli_close($con);

function getIdUnit($con, $idacct)
{
    $Qry           = new Query();
    $Qry->table    = "vw_dataemployees";
    $Qry->selected = "idunit";
    $Qry->fields   = "id='" . $idacct . "'";
    $rs            = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1) {
        while ($row = mysqli_fetch_array($rs)) {
            return $row['idunit'];
        }
    }
    return null;
}

?>