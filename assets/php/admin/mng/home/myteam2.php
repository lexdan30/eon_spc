<?php
require_once('../../../activation.php');
$conn = new connector();
$con  = $conn->connect();
require_once('../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));

$dept = getIdUnit($con, $param->accountid);
$ids=0;
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
$Qry->selected = "GROUP_CONCAT(id) AS idaccts,wshift_name, wshift";
if($param->search != ''){
    $Qry->fields   = "(idunit in (" . $ids . ") OR idsuperior='".$param->accountid."')  AND id != '" . $param->accountid . "' AND empname LIKE '%" . $param->search . "%' ";
}else{
    $Qry->fields   = "(idunit in (" . $ids . ") OR idsuperior='".$param->accountid."') AND id != '" . $param->accountid . "' ";
}


$rs = $Qry->exe_SELECT($con);

while ($row = mysqli_fetch_array($rs)) {
    $data[] = array(
        "staff_id" => $row['idaccts'],
		"shift_name"=>$row['wshift_name'],
		"shift_id"=>$row['wshift']
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