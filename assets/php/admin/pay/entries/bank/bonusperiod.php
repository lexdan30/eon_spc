<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblbonuses";
$Qry->selected  = "*";
$Qry->fields = "status = 1 ORDER BY releasedate DESC";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array(
            "id" 	            =>  $row['id'],
            "paydate" 	        => $row['releasedate'],
            "period_start" 	        => $row['start'],
            "period_end" 	        => $row['end'],
        );
    }
}

$return = json_encode($data);
print $return;
mysqli_close($con);
?>