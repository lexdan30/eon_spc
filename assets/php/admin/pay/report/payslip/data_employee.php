<?php 
error_reporting(0);
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$search="pr.idacct =   '". $param->accountid ."' ";
$search2='';


if( !empty( $param->filter->id ) ){
    $search2=" b.`pay_date` =   '".  $param->filter->id ."'";
    $search="pr.idacct =   '". $param->accountid ."' AND ".$search2."";
}

$where = $search;
$counter = 0;

$Qry = new Query();	

if(getTypes($con, $param->accountid) == 'Helper'){
    $Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblpayperiod_helper AS b ON pr.idpayperiod = b.id";
}
else if(getTypes($con, $param->accountid) == 'Japanese'){
    $Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblpayperiod_japanese AS b ON pr.idpayperiod = b.id";
}
else if(getTypes($con, $param->accountid) == 'Japanese Conversion'){
    $Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblpayperiod_japaneseconversion AS b ON pr.idpayperiod = b.id";
}
else{
    $Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblpayperiod AS b ON pr.idpayperiod = b.id";
}

$Qry->selected  = "b.`period_start`, b.`period_end`, b.`pay_date`, pr.*,CONCAT(`a`.`lname`,IFNULL(CONCAT(' ',`a`.`suffix`),''),', ',`a`.`fname`,' ',SUBSTR(`a`.`mname`,1,1),'. ') AS `empname`, a.empid as empid";
$Qry->fields    = "b.stat = 1 AND " .$search . " ORDER BY idpayperiod DESC  LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($param->pagination->currentPage>1){
        $counter = ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
    }
    while($row=mysqli_fetch_array($rs)){
        $counter++;
        
        $data[] = array(
            'counter'   => $counter,
            'id'		=> $row['id'],
			'id_acct'	=> $row['idacct'],
			'empid'		=> $row['empid'],
            'emp'   	=> $row['empname'],
			'id_paydate'=> $row['idpayperiod'],
            'pay_date'  => $row['pay_date'],
            'pay_start'  => $row['period_start'],
            'pay_end'  => $row['period_end'],
			'amt'		=> number_format($row['net_amount'],2)
        );
    }

    $myData = array('status' => 'success', 'result' => $data, 'totalItems' => getTotal($con , $where, $param->accountid),'periodtype' => getTypes($con, $param->accountid ));
	$return = json_encode($myData);
}else{
	$return = json_encode(array());
	
}
print $return;
 mysqli_close($con);

function getTotal($con,$search, $account_id){
    $Qry = new Query();	
    $Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id";
    $Qry->selected  = "*";
    $Qry->fields    = "pr.idacct = '" .$account_id. "'";
    $rs = $Qry->exe_SELECT($con);


    $rows = mysqli_num_rows($rs);
    return $rows;
}


function getTypes($con, $account_id){
    $Qry = new Query();	
    $Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id";
    $Qry->selected  = "type";
    $Qry->fields    = "pr.idacct = '" .$account_id. "'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){					
            return $row['type'];
        }
    }
    return 0;
}

?>