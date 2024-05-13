<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param 		= json_decode(file_get_contents('php://input'));
// $Qry->fields    = " `date` BETWEEN '".$param->dfrom."' AND '".$param->dto."'";
// print "$param->dfrom ";
// print "$param->dfrom";
$Qry 			= new Query();	
$Qry->table     = "vw_attendancebydept";
$Qry->selected  = "SUM(tot) as totsum";
$Qry->fields    = "id>0";
$rs 			= $Qry->exe_SELECT($con);
$totbydept      = getTotByDept($con,$param);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[]  = array( 
            "totsum"            => $row['totsum'],
            "totbydept"          => $totbydept
        );
    }
    $return = json_encode($data);
}else{
    $return = json_encode(array());
}

print $return;
mysqli_close($con);

function getTotByDept($con,$param){
    $data2 = array();
    $Qry 			= new Query();	
    $Qry->table     = "vw_data_attendancebydept";
    $Qry->selected  = "`name`, AVG(tot) AS `average`";
    $Qry->fields    = " id>0 AND (`date` BETWEEN '".$param->dfrom."' AND '".$param->dto."') GROUP BY `name`";
    $rs 			= $Qry->exe_SELECT($con);
    
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $data2[]  = array( 
                "name"         => $row['name'],
                "tot"          => $row['average']
            );
        }
    }
    return $data2;
}
?>