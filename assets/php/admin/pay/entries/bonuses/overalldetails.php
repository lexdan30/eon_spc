<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblbonusesdetails";
$Qry->selected  = "*";
$Qry->fields = "bonusid = '" . $param->bonusid . "'";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id" 	         => $row['id'],
            "idacct" 	     => $row['idacct'],
            "empname"       => getEmpname($con, $row['idacct']),
            "amount" 	     => $row['amount'],
            "taxable" 	     => $row['taxable'],
            "nontaxable" 	 => $row['nontaxable']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array("err"=>mysqli_error($con)));
	
}
print $return;
mysqli_close($con);

function getEmpname($con, $idacct){
    $Qry = new Query();	
    $Qry->table         = "vw_dataemployees";
    $Qry->selected      = "empname";
    $Qry->fields        = "id = '". $idacct ."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['empname'];
        }
    }
}
?>