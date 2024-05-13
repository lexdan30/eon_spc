<?php
require_once('../../../activation.php');
$conn = new connector();
$con  = $conn->connect();
require_once('../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));
$Qry   = new Query();

$Qry->table    = "tblleaves";
$Qry->selected = "*";
$Qry->fields   = "active = 'Y'";
$rs            = $Qry->exe_SELECT($con);


if (mysqli_num_rows($rs) >= 1) {
    while ($row = mysqli_fetch_array($rs)) {
        $count = leavecounts($con, $row['id'], $param->accountid );
        $data[] = array(
            "id" => $row['id'],
            "name" => $row['name'],
            "count" =>$count
        );
    }
    $return = json_encode($data);
} else {
    $return = json_encode(array(
        'w' => $Qry->fields
    ));
}


print $return;
mysqli_close($con);

function leavecounts($con, $id, $idacct)
{
    $Qry           = new Query();
    $Qry->table    = "vw_leave_application";
    $Qry->selected = " COUNT(*) as count";
    $Qry->fields   = "stat = 3 AND idleave = '" . $id . "' AND 
                        ((approver1_stat IS NULL OR approver1_stat != 1 AND approver1 = '" . $idacct . "') OR 
                        ((approver2_stat IS NULL OR approver2_stat != 1) AND approver2 = '" . $idacct . "') OR
                        ((approver3_stat IS NULL OR approver3_stat != 1) AND approver3 = '" . $idacct . "') OR
                        ((approver4_stat IS NULL OR approver4_stat != 1) AND approver4 = '" . $idacct . "'))";
    $rs            = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1) {
        if ($row = mysqli_fetch_array($rs)) {
            return $row['count'];
        }
    }
    return 0;
}
?>