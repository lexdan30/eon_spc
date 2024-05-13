<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$idpayperiod = array(  
    "period"		=> getFPPeriod($con, $param),
);

$search='';

//if( !empty( $param->filter->description ) ){ $search=" AND empname like   '%". $param->filter->description ."%' "; }
//if( !empty( $param->filter->empname ) ){ $search=$param->filter->empname; }
if( !empty( $param->filter->empname ) ){ $search=" AND pr.idacct =   '". $param->filter->empname ."' "; }
//if( !empty( $param->filter->paydate ) ){ $search=" AND pay_date like   '%". $param->filter->paydate ."%' "; }

//print_r($search);

$where = $search;
//$test = "58";

$Qry = new Query();	
$Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id";
$Qry->selected  = "pr.*,CONCAT(`a`.`lname`,IFNULL(CONCAT(' ',`a`.`suffix`),''),', ',`a`.`fname`,' ',SUBSTR(`a`.`mname`,1,1),'. ') AS `empname`, a.empid as empid";
$Qry->fields    = "pr.idpayperiod = '" .    $idpayperiod['period']['id'] . "' AND pr.type = '" .    $idpayperiod['period']['type'] . "'" .$search . " ORDER BY empname  LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        
        $data[] = array(
            'id'		=> $row['id'],
			'id_acct'	=> $row['idacct'],
			'empid'		=> $row['empid'],
            'emp'   	=> $row['empname'],
			'id_paydate'=> $row['idpayperiod'],
            'pay_date'  => $idpayperiod['period']['pay_date'],
            'type'      =>$row['type'],
			'amt'		=> number_format($row['net_amount'],2)
        );
    }

    $myData = array('status' => 'success', 'result' => $data, 'totalItems' => getTotal($con , $where, $param ));
	$return = json_encode($myData);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);

function getTotal($con,$search, $param){
    $idpayperiod = array(  
        "period"		=> getFPPeriod($con, $param),
    );
    $Qry = new Query();	
    $Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id";
    $Qry->selected  = "*";
    $Qry->fields    = "pr.idpayperiod = '" .    $idpayperiod['period']['id'] . "' AND pr.type = '" .    $idpayperiod['period']['type'] . "'";
    $rs = $Qry->exe_SELECT($con);


    $rows = mysqli_num_rows($rs);
    return $rows;
}
function getFPPeriod($con, $param){
    if($param->data->paytype == 'Local Employee'){
        $type = 'ho';
    }
    if($param->data->paytype == 'Helper'){
        $type = 'helper';
    }
    if($param->data->paytype== 'Japanese'){
        $type= 'hajap';
    }
    if($param->data->paytype == 'Japanese Conversion'){
        $type = 'hajapc';
    }


    $data = array();	
    $Qry = new Query();	
    $Qry->table     = "vw_payperiod_all";
    $Qry->selected  = "*";
    $Qry->fields   = "pay_date='".$param->data->paydate."' AND type='".$type."'";      
    
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            if($row['type'] == 'ho'){
                $row['type'] = 'Local Employee';
            }
            if($row['type'] == 'helper'){
                $row['type'] = 'Helper';
            }
            if($row['type'] == 'hajap'){
                $row['type'] = 'Japanese';
            }
            if($row['type'] == 'hajapc'){
                $row['type'] = 'Japanese Conversion';
            }

            $data = array( 
                "id"        	=> $row['id'],
                "pay_start"		=> $row['period_start'],
                "pay_end"		=> $row['period_end'],
                "pay_date"		=> $row['pay_date'],
                "hascontri" 	=> $row['hascontri'],
                "pay_stat"		=> $row['stat'],
                "tkstatus"		=> $row['tkstatus'],
                "period_type" 	=> $row['pay_period'],
                "type" 			=> $row['type'],
                "tkprocess" 	=> $row['tkprocess'],
                "payprocess" 	=> $row['payprocess'],
            );
        }
    }
    return $data;
}

?>