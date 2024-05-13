<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$data  = array();
$date  = SysDateDan();
$time  = SysTime();


$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "*";
$Qry->fields    = "id > 0 AND (skill_name IS NOT NULL OR skill_type IS NOT NULL) ORDER BY empname";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        // $getSkillsInventory = getSkillsInventory($con, $row['id']);

        $data[] = array( 
            "id"        	        => $row['id'],
            "empid"			        => $row['empid'],
            "empname" 		        => $row['empname'],
            "post" 		            => ucwords($row['post']),
            'skill_name'            =>$row['skill_name'],
            'skill_type'            =>$row['skill_type'],
            // "getSkillsInventory"    => $getSkillsInventory,
            "date"                  => $date,
            "time"                  => date ("H:i:s A",strtotime($time)),


			
        );
        $return = json_encode($data);
    }
}else{
    $return = json_encode(array('status'=>'error'));
}

// function getSkillsInventory($con, $idacct){
//     $Qry=new Query();
//     $Qry->table="tblaccountskiinv";
//     $Qry->selected="*";
//     $Qry->fields="id>0 AND idacct='".$idacct."'";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){

//             $data[] = array(

//                 'skill_name'    =>$row['skill_name'],
//                 'skill_type'    =>$row['skill_type'],
//             );
//         }
//         return $data;
//     }
//     return null;
// }


print $return;
mysqli_close($con);
?>