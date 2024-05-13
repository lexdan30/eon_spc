<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblsapcodes";
$Qry->selected  = "*";
$Qry->fields = "id>0 ORDER BY description LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize ."";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        $row['coaids'] = explode(",",$row['coaids']);

        $data[] = $row;
    }


    $myData = array('status' => 'success', 
                    'result' => $data

    );
    $return = json_encode($myData);
}else{
	$return = json_encode(array());
	
}



print $return;
mysqli_close($con);
?>