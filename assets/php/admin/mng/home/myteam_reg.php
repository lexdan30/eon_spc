<?php
require_once('../../../activation.php');
$conn = new connector();
$con  = $conn->connect();
$cone  = $conn->connect();
require_once('../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));
$date = date('Y-m-d');
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
    //$Qry->fields   = "(idunit in (" . $ids . ") OR idsuperior='".$param->accountid."')  AND id != '" . $param->accountid . "' AND etype = 'Active'  AND empstat = 5 AND empname LIKE '%" . $param->search . "%' ";
    $Qry->fields   = "(idunit in (" . $ids . ") OR idsuperior='".$param->accountid."')  AND id != '" . $param->accountid . "' AND etype = 'Active'  AND empstat IN (5,8,9) AND empname LIKE '%" . $param->search . "%' ";
}else{
    //$Qry->fields   = "(idunit in (" . $ids . ") OR idsuperior='".$param->accountid."') AND id != '" . $param->accountid . "'  AND etype = 'Active'  AND empstat = 5 ";
    $Qry->fields   = "(idunit in (" . $ids . ") OR idsuperior='".$param->accountid."') AND id != '" . $param->accountid . "'  AND etype = 'Active'  AND empstat IN (5,8,9) ";
}

$rs = $Qry->exe_SELECT($con);

while ($row = mysqli_fetch_array($rs)) 
{
    $emid = $row['id'];
    // $Qry2           = new Query();
    // $Qry2->table    = "tbltimesheet";
    // $Qry2->selected = "*";
    // $Qry2->fields   = " date_in = '2021-01-29' AND (timein IS NOT NULL AND timeout IS NULL)";
    // $rs2            = $Qry2->exe_SELECT($con);
     //$querytimein = "SELECT * FROM tbltimesheet WHERE idacct = '$emid' and (date_in = '$date' AND (timein IS NOT NULL AND timeout IS NULL))";
     $querytimein = "SELECT * FROM tbltimesheet WHERE idacct = '$emid' and (date_in = '$date' AND (timein IS NOT NULL AND timeout IS NULL OR timeout = ''))";
     $result2 = mysqli_query($cone, $querytimein);
    if (mysqli_num_rows($result2) >= 1) {
        $in = true;
    } else {
        $in = false;
    }

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
        "shift_name"=>$row['wshift_name'],
        "timein"=>$in
    );
}

$return = json_encode($data);

print $return;
mysqli_close($con);
mysqli_close($cone);

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