<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$search='';

if( !empty( $param->filter->empname ) ){ $search=" AND bnd.idacct ='". $param->filter->empname ."' "; }
if( !empty( $param->filter->bn ) ){ $search = $search . " AND aj.batchnum =   '". $param->filter->bn ."' "; }

$Qry = new Query();	
$Qry->table     = "tblbonuses AS bn 
                    LEFT JOIN tblbonusesdetails AS bnd ON bn.id = bnd.bonusid 
                    LEFT JOIN tblaccount AS a ON bnd.idacct = a.id
                    LEFT JOIN tblaccountjob aj ON aj.idacct = a.id";
$Qry->selected  = "bn.id,
                    bnd.idacct,
                    a.idpayroll,
                    a.fname,
                    a.lname,
                    a.mname,
                    ((bnd.taxable + bnd.nontaxable) - bnd.wtax) AS net_amount";

$Qry->fields = "bn.id = '" . $param->search->pp . "' ";

if($param->search->pg != 'all'){
    $Qry->fields = $Qry->fields . " AND aj.idpaygrp = '" . $param->search->pg . "'";
}

$Qry->fields = $Qry->fields . "" .$search." ORDER BY lname LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize ."";

$ta = 0;

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array(
            "id" 	            => $row['id'],
            "ban" 	            => $row['idpayroll'],
            "fname" 	        => $row['fname'],
            "lname" 	        => $row['lname'],
            "mname" 	        => $row['mname'],
            "amount" 	        => $row['net_amount'],
        );
        $ta = $ta +  $row['net_amount'];
    }

    $myData = array('status' => 'success', 
            'result' => $data, 
            'totalItems' => getTotal($con ,$param),
            'totalamount' => $ta,
            'uniqueDepartment' => getMainDepartment($con),
            'qry' => $Qry->fields
    );

	$return = json_encode($myData);
}else{
	$return = json_encode(array("err"=>mysqli_error($con)));
	
}
print $return;
mysqli_close($con);

function getTotal($con, $param){
    $search='';
    if( !empty( $param->filter->empname ) ){ $search=" AND pr.idacct ='". $param->filter->empname ."' "; }
    if( !empty( $param->filter->bn ) ){ $search = $search . " AND aj.batchnum =   '". $param->filter->bn ."' "; }

    $Qry = new Query();	
    $Qry->table     = "tblbonuses AS bn 
                        LEFT JOIN tblbonusesdetails AS bnd ON bn.id = bnd.bonusid 
                        LEFT JOIN tblaccount AS a ON bnd.idacct = a.id
                        LEFT JOIN tblaccountjob aj ON aj.idacct = a.id";
    $Qry->selected  = "bn.id,
                        a.idpayroll,
                        a.fname,
                        a.lname,
                        a.mname,
                        ((bnd.taxable + bnd.nontaxable) - bnd.wtax) AS net_amount";
    $Qry->fields = "bn.id = '" . $param->search->pp . "' ";

    if($param->search->pg != 'all'){
        $Qry->fields = $Qry->fields . " AND aj.idpaygrp = '" . $param->search->pg . "' " .$search . " ";
    }

    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $rowcount=mysqli_num_rows($rs);
            return $rowcount;
        }
    }
    return 0;
}


function getTotalamount($con, $param){
    $search='';
    if( !empty( $param->filter->empname ) ){ $search=" AND pr.idacct ='". $param->filter->empname ."' "; }
    if( !empty( $param->filter->bn ) ){ $search = $search . " AND aj.batchnum =   '". $param->filter->bn ."' "; }

    $Qry = new Query();	
    $Qry->table     = "tblbonuses AS bn 
                        LEFT JOIN tblbonusesdetails AS bnd ON bn.id = bnd.bonusid 
                        LEFT JOIN tblaccount AS a ON bnd.idacct = a.id
                        LEFT JOIN tblaccountjob aj ON aj.idacct = a.id";
    $Qry->selected  = "SUM((bnd.taxable + bnd.nontaxable) - bnd.wtax) AS net_amount as total";
    $Qry->fields = "bn.id = '" . $param->search->pp . "' ";

    if($param->search->pg != 'all'){
        $Qry->fields = $Qry->fields . " AND aj.idpaygrp = '" . $param->search->pg . "' " .$search . " ";
    }

    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['total'];
        }
    }
    return 0;
}


?>