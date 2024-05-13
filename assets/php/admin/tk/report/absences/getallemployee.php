<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

$search ='';



$Qry 			= new Query();	
$Qry->table     = "tblaccount";
$Qry->selected  = "empid,CONCAT(fname, ' ', mname, ' ', lname ) AS empname";
$Qry->fields    = "id!=1 ORDER BY fname ASC";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>0){

    while($row=mysqli_fetch_assoc($rs)){

        $data[] = array( 
           
            "empid"			 => $row['empid'],
            "empname" 		 => $row['empname']
         
        );
    }

    $return = json_encode($data);

}
else {
    $return = json_encode(array());
}

print $return;
mysqli_close($con);
?>