<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input')); 


$Qry 			= new Query();	
$Qry->table     = "tblacctsalary ";
$Qry->selected  = "*";
$Qry->fields    = "idacct='".$param->id."' 
                    AND id in (SELECT MAX(id) FROM tblacctsalary WHERE idacct='".$param->id."'  GROUP BY effectivity_date ASC)
                    ORDER BY effectivity_date ASC";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        $data[] = $row;
    }
}
else{
    $data[] = '';
}
$myData = array('status' => 'success', 'result' => $data);

$return = json_encode($myData);
print $return;
mysqli_close($con);
?>