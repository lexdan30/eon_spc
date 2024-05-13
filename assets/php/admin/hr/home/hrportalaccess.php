<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$data = array();

$Qry2 = new Query();	
$Qry2->table ="vw_dataemployees";	
$Qry2->selected ="id"; 
$Qry2->fields ="post='Administrative Manager'";
$rs2 = $Qry2->exe_SELECT($con);
if(mysqli_num_rows($rs2)>= 1){
    if($row2=mysqli_fetch_array($rs2)){
        $data = array( 
            "hr"				=> $row2['id'],
            "success"		=> 'fetch success'
        );
    }
    $return = json_encode($data);
}else{
    $return = json_encode(array( "error"		=> 'unable to fetch'));
}

// $Qry = new Query();	
// $Qry->table     = "tblpreference";
// $Qry->selected  = "value";
// $Qry->fields    = "alias='OBT'";
// $rs = $Qry->exe_SELECT($con);
// if(mysqli_num_rows($rs)>= 1){
//     if($row=mysqli_fetch_array($rs)){
//         $empaccess = $row['value']; 

//         $Qry2 = new Query();	
//         $Qry2->table ="vw_dataemployees";	
//         $Qry2->selected ="id"; 
//         $Qry2->fields ="post='".$empaccess."'";
//         $rs2 = $Qry2->exe_SELECT($con);
//         if(mysqli_num_rows($rs2)>= 1){
//             if($row2=mysqli_fetch_array($rs2)){
//                 $data = array( 
//                     "hr"				=> $row2['id'],
//                     "success"		=> 'fetch success'
//                 );
//             }
//         }
//         $return = json_encode($data);
//     }
// }else{
// 	$return = json_encode(array( "error"		=> 'unable to fetch'));
// }

print $return;
mysqli_close($con);
?>



