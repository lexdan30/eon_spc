<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 
session_start();

$param = json_decode(file_get_contents('php://input'));

$paydate = $param->paydate;

$Qry = new Query();	
$Qry->table     = "tblbonuses as bn 
                    LEFT JOIN tblbonusesdetails AS bdn ON bn.id = bdn.bonusid 
                    LEFT JOIN tblaccount as a ON bdn.idacct = a.id
                    LEFT JOIN tblaccountjob as aj ON a.id=aj.idacct";
$Qry->selected  = "bn.releasedate, 
                    bdn.*,CONCAT(`a`.`lname`,IFNULL(CONCAT(' ',`a`.`suffix`),''),', ',`a`.`fname`,' ',SUBSTR(`a`.`mname`,1,1),'. ') AS `empname`,
                    a.empid as empid,
                    aj.idunit as idbunit,
                    aj.idpaygrp";
$Qry->fields = "releasedate = '" . $paydate . "' ORDER BY empname LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize ."";


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        $data[] = array( 
            "paygroup"              => getPaygroup($con,$row['idpaygrp']),
            "empid"        	        => $row['empid'],
            "empname"        	    => $row['empname'],
            "idunit"        	    => $row['idbunit'],
            "department"        	=> getDepartments($con,$row['idbunit']),
            "section"        	    => getSection($con,$row['idbunit']),
            "taxable"        	    => $row['taxable'],
            "nontaxable"        	=> $row['nontaxable'],
            "tg"        	        => ($row['taxable'] + $row['nontaxable']),
            "wtax"        	        => $row['wtax'],
            "netpay"        	    => ($row['taxable'] + $row['nontaxable']) - $row['wtax']
        );

    }

    $myData = array('status' => 'success', 
                    'totalItems' => getTotal($con , $paydate),
                    'grandTotal'  => grandTotal($con , $paydate),
                    'result' => $data

    );
    $return = json_encode($myData);
}else{
	$return = json_encode(array('status' => 'error'));
	
}
print $return;
mysqli_close($con);

function grandTotal($con,$paydate){
    $Qry = new Query();	
    $Qry->table         = "tblbonuses as bn 
                            LEFT JOIN tblbonusesdetails AS bdn ON bn.id = bdn.bonusid ";
    $Qry->selected      = "SUM(taxable) as totaltaxable,
                            SUM(nontaxable) AS totalnontaxable,
                            SUM(nontaxable + taxable) AS totalgross,
                            SUM(wtax) AS totalwtax,
                            SUM((nontaxable + taxable) - wtax) AS totalnetpay";
    $Qry->fields        = "releasedate = '" . $paydate . "'";
    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_assoc($rs)){
            
				return $row;
			}
		}
		return 0;
}

function getTotal($con,$paydate){
    $Qry = new Query();	
    $Qry->table         = "tblbonuses as bn 
                            LEFT JOIN tblbonusesdetails AS bdn ON bn.id = bdn.bonusid 
                            LEFT JOIN tblaccount as a ON bdn.idacct = a.id
                            LEFT JOIN tblaccountjob as aj ON a.id=aj.idacct";
    $Qry->selected      = "bn.releasedate, 
                            bdn.*,CONCAT(`a`.`lname`,IFNULL(CONCAT(' ',`a`.`suffix`),''),', ',`a`.`fname`,' ',SUBSTR(`a`.`mname`,1,1),'. ') AS `empname`,
                            a.empid as empid,
                            aj.idunit as idbunit,
                            aj.idpaygrp";
    $Qry->fields        = "releasedate = '" . $paydate . "'";
    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
                $rowcount=mysqli_num_rows($rs);
				return $rowcount;
			}
		}
		return 0;
}

function getPaygroup($con,$id){
    $Qry = new Query();	
    $Qry->table     = "tblpaygrp";
    $Qry->selected  = "`group`";
    $Qry->fields = "id='".$id."'";
    $rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
           return $row['group'];
        }
    }
}
function getDepartments($con,$idunit){
    $Qry = new Query();	
    $Qry->table     = "vw_databusinessunits";
    $Qry->selected  = "idunder,name,stype";
    $Qry->fields = "id='".$idunit."'";
    $rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            if($row['stype'] == 'Department'){
                return $row['name'];
            }else if($row['stype'] != 'Division'){
                return getDepartments($con, $row['idunder']);
            }else if($row['stype'] == 'Division'){
                return '';
            }
        }
    }
}

function getSection($con,$idunit){
    $Qry = new Query();	
    $Qry->table     = "vw_databusinessunits";
    $Qry->selected  = "idunder,name,stype";
    $Qry->fields = "id='".$idunit."'";
    $rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            if($row['stype'] == 'Section'){
                return $row['name'];
            }else if($row['stype'] != 'Department'){
                return getDepartments($con, $row['idunder']);
            }else if($row['stype'] == 'Department'){
                return '';
            }
        }
    }
 
}


session_destroy();
?>