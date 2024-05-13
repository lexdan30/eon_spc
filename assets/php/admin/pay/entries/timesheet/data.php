<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$search='';

//if( !empty( $param->filter->description ) ){ $search=" AND empname like   '%". $param->filter->description ."%' "; }
//if( !empty( $param->filter->empname ) ){ $search=$param->filter->empname; }
if( !empty( $param->filter->empname ) ){ $search=" AND pr.idacct =   '". $param->filter->empname ."' "; }
if( !empty( $param->filter->id ) ){ $param->id_paydate= $param->filter->id ; }
//if( !empty( $param->filter->paydate ) ){ $search=" AND pay_date like   '%". $param->filter->paydate ."%' "; }

//print_r($search);

$where = $search;
//$test = "58";

$Qry = new Query();	
$Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id";
$Qry->selected  = "pr.*,CONCAT(`a`.`lname`,IFNULL(CONCAT(' ',`a`.`suffix`),''),', ',`a`.`fname`,' ',SUBSTR(`a`.`mname`,1,1),'. ') AS `empname`, a.empid as empid";
$Qry->fields    = "pr.idpayperiod = '" .   $param->id_paydate . "'" .$search . " ORDER BY empname  LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        
        $data[] = array(
            'id'		=> $row['id'],
			'id_acct'	=> $row['idacct'],
			'empid'		=> $row['empid'],
            'emp'   	=> $row['empname'],
			'id_paydate'=> $row['idpayperiod'],
            'pay_date'  => $param->pay_date,
			'amt'		=> number_format($row['net_amount'],2)
        );
    }

    $myData = array('status' => 'success', 'result' => $data, 'totalItems' => getTotal($con , $where, $param->id_paydate ));
	$return = json_encode($myData);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);

function getTotal($con,$search, $id_paydate){
    $Qry = new Query();	
    $Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id";
    $Qry->selected  = "*";
    $Qry->fields    = "pr.idpayperiod = '" .   $id_paydate . "'";
    $rs = $Qry->exe_SELECT($con);


    $rows = mysqli_num_rows($rs);
    return $rows;
}
?>