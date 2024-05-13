<?php 
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
if(!empty($param->accountid)){
$Qry = new Query();	
$Qry->table     = "tblpreference";
$Qry->selected  = "id,alias,prefname,remarks";
$Qry->fields = "id>0 AND alias IN('PEU','MPSC')";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id" 	  => $row['id'],
            "prefname" 	     => $row['prefname'],
            "empid" 	  => $row['id'],
            "name" 	     => $row['alias'],
            "remarks" 	  => $row['remarks']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
	
}
print $return;
 mysqli_close($con);
}else{
	mysqli_close($con);
	header("Location: https://peninsula.mydhris.com/mph/");  // unAuth back to login page
}
?>