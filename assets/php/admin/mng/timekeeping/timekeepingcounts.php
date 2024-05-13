<?php
require_once('../../../activation.php');
$conn = new connector();
$con  = $conn->connect();
require_once('../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));
$Qry   = new Query();

$Qry->table    = "tbltimeshift";
$Qry->selected = "COUNT(*) as count";
$Qry->fields   = "stat = '3' AND cancelby is null AND approver1 = '" . $param->accountid. "'";
$rs            = $Qry->exe_SELECT($con);


if (mysqli_num_rows($rs) >= 1) {
    while ($row = mysqli_fetch_array($rs)) {
       $cs = $row['count'];
    }
} 

$Qry2   = new Query();
$Qry2->table    = "tbltimeadjustment";
$Qry2->selected = "COUNT(*) as count";
$Qry2->fields   = "stat = '3' AND cancelby is null AND approver1 = '" . $param->accountid. "'";
$rs2            = $Qry2->exe_SELECT($con);

if (mysqli_num_rows($rs2) >= 1) {
    while ($row2 = mysqli_fetch_array($rs2)) {
       $aa = $row2['count'];
    }
} 



$return = json_encode(array("cs"=>$cs, "aa"=>$aa));

print $return;
mysqli_close($con);

?>