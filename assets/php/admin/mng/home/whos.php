<?php
require_once('../../../activation.php');
$conn = new connector();
$con  = $conn->connect();
require_once('../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));

$dept = getIdUnit($con, $param->accountid);

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


$in          = array();
$Qry           = new Query();
$Qry->table    = "`vw_dataemployees` vde LEFT JOIN vw_data_timesheet vdt ON vdt.empID = vde.id";
$Qry->selected = "vde.empid, vde.empname, vdt.shift_status, vdt.in, vdt.shiftin, vde.pic";
if($param->search != ''){
    $Qry->fields   = "(vde.idunit in (" . $ids . ") OR vde.idsuperior='".$param->accountid."') AND vde.id != '" . $param->accountid . "' AND empname LIKE '%" . $param->search . "%' AND vdt.work_date = CURDATE() AND (vdt.in != '' OR vdt.in IS NOT NULL OR vdt.aaid IS NOT NULL) GROUP BY vde.id";
}else{
    $Qry->fields   = "(vde.idunit in (" . $ids . ") OR vde.idsuperior='".$param->accountid."') AND vde.id != '" . $param->accountid . "' AND vdt.work_date = CURDATE() AND (vdt.in != '' OR vdt.in IS NOT NULL OR vdt.aaid IS NOT NULL) GROUP BY vde.id";
}


$rs = $Qry->exe_SELECT($con);

while ($row = mysqli_fetch_array($rs)) {
    if($row['in'] == ''){
        $timein = date('h:i a', strtotime($row['shiftin']));
    }else{
        $timein = date('h:i a', strtotime($row['in']));
    }
   
    $path = 'assets/images/undefined.webp?'.time();
	if( !empty( $row['pic'] ) ){
		$path = 'assets/php/admin/hr/employee/pix/'.$row['pic'].'?'.time();
	}

    $in[] = array(
        "id" => $row['empid'],
        "name" => $row['empname'],
        "shift_status" => $row['shift_status'],
        "in" => $timein,
		"pic"=>$path
    );
}


$out           = array();
$Qry2           = new Query();
$Qry2->table    = "`vw_dataemployees` vde LEFT JOIN vw_data_timesheet vdt ON vdt.empID = vde.id";
$Qry2->selected = "vde.empid, vde.empname, vdt.shift_status, vdt.in, vdt.leavename,vdt.leave_status,vdt.changeshift_stat, vdt.lvid, vdt.csid, vde.pic";
if($param->search != ''){
    $Qry2->fields   = "(vde.idunit in (" . $ids . ") OR vde.idsuperior='".$param->accountid."') AND vde.id != '" . $param->accountid . "' AND empname LIKE '%" . $param->search . "%' AND vdt.work_date = CURDATE() AND ( vdt.in = '' OR vdt.in IS NULL) AND vdt.aaid IS NULL GROUP BY vde.id";
}else{
    $Qry2->fields   = "(vde.idunit in (" . $ids . ") OR vde.idsuperior='".$param->accountid."') AND vde.id != '" . $param->accountid . "' AND vdt.work_date = CURDATE() AND ( vdt.in = '' OR vdt.in IS NULL) AND vdt.aaid IS NULL GROUP BY vde.id";
}


$rs2 = $Qry2->exe_SELECT($con);

while ($row2 = mysqli_fetch_array($rs2)) {
    if($row2['lvid']){
        $application = $row2['leavename'];
        $applicationstat = $row2['leave_status'];
    }else if($row2['csid']){
        $application = $row2['leavename'];
        if($row2['changeshift_stat'] == 1){
            $applicationstat = 'APPROVED';
        }
        if($row2['changeshift_stat'] == 2){
            $applicationstat = 'DECLINED';
        }
        if($row2['changeshift_stat'] == 3){
            $applicationstat = 'PENDING';
        }
      
    }else{
        $application = '';
        $applicationstat = ''; 
    }
	
	$path = 'assets/images/undefined.webp?'.time();
	if( !empty( $row2['pic'] ) ){
		$path = 'assets/php/admin/hr/employee/pix/'.$row2['pic'].'?'.time();
	}
	
    $out[] = array(
        "id" => $row2['empid'],
        "name" => $row2['empname'],
        "shift_status" => $row2['shift_status'],
        'application' => $application,
        'applicationstat' => $applicationstat,
		'pic'=>$path
    );
}



$return = json_encode(array('in'=>$in,'out'=>$out));

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