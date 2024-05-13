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

if( !empty( $param->filter->site ) ){
 
    $id_array = getLocationsbunits($con,$param->filter->site);
    $ids = implode(",",$id_array);
}


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

$Qry->fields = "pr.net_amount > 0 AND pr.idpayperiod = '" . $idpayperiod['period']['id'] . "' AND pr.type = '" . $idpayperiod['period']['type'] . "' AND pr.idpaygrp = '" .$param->data->paygroup. "'" ;

$Qry->fields = $Qry->fields . "" .$search . "  ORDER BY lname LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize ."";

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
            'filterName' => filter($con,$idpayperiod,$param),
            'qry' => $Qry->fields
    );

	$return = json_encode($myData);
}else{
	$return = json_encode(array("err"=>mysqli_error($con)));
	
}
print $return;
mysqli_close($con);

function getTotal($con,$param){
    $search='';
    if( !empty( $param->filter->empname ) ){ $search=" AND pr.idacct ='". $param->filter->empname ."' "; }
    if( !empty( $param->filter->bn ) ){ $search = $search . " AND aj.batchnum =   '". $param->filter->bn ."' "; }
    
        $idpayperiod = array(  
            "period"		=> getFPPeriod($con, $param),
        );

        if( !empty( $param->filter->site ) ){
 
            $id_array = getLocationsbunits($con,$param->filter->site);
            $ids = implode(",",$id_array);
        }

            $Qry = new Query();	
            $Qry->table     = "tblpayreg AS pr LEFT JOIN tblaccount AS a ON pr.idacct = a.id LEFT JOIN tblaccountjob as aj ON aj.idacct = a.id ";
            $Qry->selected  = "pr.id,
                                a.idpayroll,
                                a.fname,
                                a.lname,
                                a.mname,
                                pr.net_amount";

            $Qry->fields = "pr.net_amount > 0 AND pr.idpayperiod = '" . $idpayperiod['period']['id'] . "' AND pr.type = '" . $idpayperiod['period']['type'] . "'  AND pr.idpaygrp = '" .$param->data->paygroup. "'" .$search . " ";


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
    $search='';
    if( !empty( $param->filter->empname ) ){ $search=" AND pr.idacct ='". $param->filter->empname ."' "; }
    if( !empty( $param->filter->bn ) ){ $search = $search . " AND aj.batchnum =   '". $param->filter->bn ."' "; }

    $idpayperiod = array(  
        "period"		=> getFPPeriod($con, $param),
    );


    
    $Qry = new Query();	
    $Qry->table     = "tblpayreg AS pr LEFT JOIN tblaccount AS a ON pr.idacct = a.id LEFT JOIN tblaccountjob as aj ON aj.idacct = a.id";
    $Qry->selected  = "SUM(pr.net_amount) as total";

    $Qry->fields = "net_amount > 0 AND idpayperiod = '" . $idpayperiod['period']['id'] . "' AND type = '" . $idpayperiod['period']['type'] . "' AND pr.idpaygrp = '" .$param->data->paygroup. "'" .$search . " ";


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

function filter($con,$idpayperiod,$param){

    $id_paydate = $idpayperiod['period']['id'];
    $type = $idpayperiod['period']['type'];
    $paygroup = $param->data->paygroup;

    $data = array();	
    $Qry = new Query();	
    $Qry->table     = "tblpayreg as pr LEFT JOIN tblaccount as a ON pr.idacct = a.id LEFT JOIN tblaccountjob as aj ON aj.idacct = a.id ";
    $Qry->selected  = "pr.*,CONCAT(`a`.`lname`,IFNULL(CONCAT(' ',`a`.`suffix`),''),', ',`a`.`fname`,' ',SUBSTR(`a`.`mname`,1,1),'. ') AS `empname`";
    $Qry->fields    = "pr.idpayperiod = '" . $id_paydate  . "' AND pr.type = '" . $type  . "' AND pr.idpaygrp = '" . $paygroup  . "' ORDER BY empname";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $data[] = array( 
                "idacct"        => $row['idacct'],
                "empname" 		=> $row['empname']

            );
        }
    }
    return $data;
}

?>