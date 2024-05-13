<?php 
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
if(!empty($param->accountid)){
$date = strtotime($param->month);
$date = date('Y-m-d',$date);


$Qry = new Query();	
$Qry->table     = "vw_timesheet";
$Qry->selected  = "id,empname,empid,idunit";
$Qry->fields = "MONTH(work_date) = MONTH('" . $date  . "')
                AND YEAR(work_date) = YEAR('" . $date  . "')
                GROUP BY id  HAVING SUM(acthrs) > 0";


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id" 	     => $row['id'],
            "empid" 	  => $row['empid'],
            "name" 	     => $row['empname'],
            "idunit" 	  => $row['idunit']
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