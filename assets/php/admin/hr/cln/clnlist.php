<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry 			= new Query();	
$Qry->table     = "tbldocutemp";
$Qry->selected  = "*";
$Qry->fields    = "id>0";
$rs 			= $Qry->exe_SELECT($con);


if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[]  = array( 
            "id"            => $row['id'],
            "status"        => $row['status'],
            "name"			=> $row['name'],
            "remarks"	    => $row['remarks'],
            "type"	        => $row['type'],
            "content"	    => $row['content']
        );
    }
    $return = json_encode($data);
}else{
    $return = json_encode(array('w'=>$Qry->fields));
}



print $return;
mysqli_close($con);
?>