<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$counter = 0;
$search='';
$ids = '';

$idpayperiod = array(  
    "period"		=> getFPPeriod($con, $param),
);


if( !empty( $param->filter->empname ) ){ $search=" AND pr.idacct ='". $param->filter->empname ."' "; }
if( !empty( $param->filter->site ) ){ $search = $search . " AND pr.idbunit IN  (". $ids .") "; }
if( !empty( $param->filter->bn ) ){ $search = $search . " AND aj.batchnum =   '". $param->filter->bn ."' "; }


$Qry = new Query();	
$Qry->table     = "tblpayreg AS pr LEFT JOIN tblaccount AS a ON pr.idacct = a.id LEFT JOIN tblaccountjob as aj ON aj.idacct = a.id";
$Qry->selected  = "pr.id,
                pr.idacct, 
                pr.idbunit,  
                a.idpayroll,
                a.fname,
                a.lname,
                a.mname,
                pr.net_amount";

$Qry->fields = "pr.idpayperiod = '" . $idpayperiod['period']['id'] . "' AND pr.type = '" . $idpayperiod['period']['type'] . "' ";

if($param->search->pg != 'all'){
    $Qry->fields = $Qry->fields . " AND pr.idpaygrp = '" . $param->search->pg . "'";
}

$Qry->fields = $Qry->fields . "ORDER BY lname";

$ta = 0;

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($param->pagination->currentPage>1){
        $counter = ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
    }


    while($row=mysqli_fetch_array($rs)){
        $counter++;
        $data[] = array(
            'counter'           => $counter,
            "id" 	            => $row['id'],
            "idacct" 	        => $row['idacct'],
            "idunit"        	    => $row['idbunit'],
            "ban" 	            => $row['idpayroll'],
            "fname" 	        => $row['fname'],
            "lname" 	        => $row['lname'],
            "mname" 	        => $row['mname'],
            "amount" 	        => $row['net_amount'],
        );
        
    }

    $myData = array('status' => 'success', 
            'result' => $data, 
            'totalItems' => getTotal($con , $param),
            'totalamount' => getTotalamount($con , $param),
            'uniqueDepartment' => getMainDepartment($con),
            'qry' => $Qry->fields
    );

	$return = json_encode($myData);
}else{
	$return = json_encode(array("err"=>mysqli_error($con)));
	
}
print $return;
mysqli_close($con);

function getTotal($con,$param){
    $idpayperiod = array(  
        "period"		=> getFPPeriod($con, $param),
    );
    
    $Qry = new Query();	
    $Qry->table     = "tblpayreg AS pr LEFT JOIN tblaccount AS a ON pr.idacct = a.id";
    $Qry->selected  = "pr.id,
                        a.idpayroll,
                        a.fname,
                        a.lname,
                        a.mname,
                        pr.net_amount";
    $Qry->fields = "pr.net_amount > 0 AND pr.idpayperiod = '" . $idpayperiod['period']['id'] . "' AND pr.type = '" . $idpayperiod['period']['type'] . "'";

 

    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
                $rowcount=mysqli_num_rows($rs);
				return $rowcount;
			}
		}
		return 0;
}
function getTotalamount($con,$param){
    $idpayperiod = array(  
        "period"		=> getFPPeriod($con, $param),
    );
    $Qry = new Query();	
    $Qry->table     = "tblpayreg";
    $Qry->selected  = "SUM(net_amount) as total";

    $Qry->fields = "net_amount > 0 AND idpayperiod = '" . $idpayperiod['period']['id'] . "' AND type = '" . $idpayperiod['period']['type'] . "'";


    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['total'];
        }
    }
    return 0;
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