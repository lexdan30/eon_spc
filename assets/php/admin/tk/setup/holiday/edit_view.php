<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();
$loc = 0;

$Qry 			= new Query();	
$Qry->table     = "tblholidays";
$Qry->selected  = "*";
$Qry->fields    = "id='".$param->id."'";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_array($rs)){
        if($row['regcode'] != '' && $row['regcode'] != NULL){
            $loc = 2;
        }
        $data = array( 
            "id"        => $row['id'],
			"name"		=> $row['name'],
			"idtype"	=> $row['idtype'],
            "date"		=> $row['date'],
            "loc"		=> $loc,
            // "region"     => getReg($con, $row['provid']),
            // "province"   => getReg($con, $row['provid']),
            "regions"        => $row['regcode'],
            "provinces"      => $row['provcode'],
            "municipality"	 => $row['munid']
        );
    }
}
$return = json_encode($data);
print $return;

// function getReg($con, $provid){
//     //return $provid;
//     if($provid != null){
//         $data = 0;	
//         $Qry = new Query();	
//         $Qry->table     = "tblprovince";
//         $Qry->selected  = "regCode";
//         $Qry->fields    = "id = 40";
//         $rs = $Qry->exe_SELECT($con);
//         if(mysqli_num_rows($rs)>= 1){
//             if($row=mysqli_fetch_array($rs)){
//                 $data = $row['regCode'];
//             }
//         }
//         return $data;
//     }
// }

mysqli_close($con);
?>