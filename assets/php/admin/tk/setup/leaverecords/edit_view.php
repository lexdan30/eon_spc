<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php');


$param = json_decode(file_get_contents('php://input'));
$data  = array();



$Qry 			= new Query();	
$Qry->table="tblaccountleaves AS al LEFT JOIN vw_dataemployees AS de ON al.idacct = de.id LEFT JOIN tblleaves AS tl ON al.idleave = tl.id";
// $Qry->selected="al.id,al.idacct,tl.name,al.entitle,de.empname,de.business_unit,de.idunit";
$Qry->selected  = "al.id,al.idacct,de.empid,de.empname,de.business_unit,de.idunit, tl.name,al.entitle, al.used,al.balance,al.pending_bal,tl.id AS tl_id";
$Qry->fields="al.id='".$param->idss."'";
// $Qry->table     = "tblaccountleaves";
// $Qry->selected  = "*";
// $Qry->fields    = "id='".$param->idss."'";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_array($rs)){
        $data = array( 
            'id'  	    => $row['id'],
            'idacct'  	=> $row['idacct'],
            'empname'  	=> $row['empname'],
            'depart'	=> $row['business_unit'],
            'idunit'	=> $row['idunit'],
            'name'	=> $row['name'],
            'tl_id'	=> $row['tl_id'],
            'entitle'	=> $row['entitle'],
        );
    }
}

$return = json_encode($data);

print $return;
mysqli_close($con);

?>