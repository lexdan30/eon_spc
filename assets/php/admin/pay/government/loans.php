<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$time = strtotime($param->month);
$newformat = date('Y-m-d',$time);
$data = array();

// Loan Types
// HDMF Calamity Loan, HDMF Housing Loan,HDMF Multi-purpose Loan, HDMF Salary Loan, SSS Calamity Loan

$Qry = new Query();	
$Qry->table     = "tblpayreg AS pr 
                    LEFT JOIN tblpayperiod AS pp ON pr.idpayperiod = pp.id
                    LEFT JOIN tblaccount AS a ON a.id = pr.idacct
                    RIGHT JOIN tblloanpayment AS lp ON pr.id = lp.payregid
                    LEFT JOIN tblloans AS l ON lp.loanid = l.id
                    LEFT JOIN tblloantype AS lt ON l.loanid = lt.id
                    LEFT JOIN tblclasstrans AS ct ON lt.transid = ct.id";
$Qry->selected  = "pr.id,
                    fname,
                    lname,
                    mname,
                    idibig,
                    idtin,
                    bdate,
                    SUM(lp.amount) AS loanpayment,
                    ct.name,
                    lt.transid,
                    lp.loanid,
                    lp.payregid,
                    l.loandate,
                    l.systemamortization";
$Qry->fields = "ct.name = '".$param->loantype."' AND YEAR(lp.payment_date) = YEAR('" . $newformat . "') AND MONTH(lp.payment_date) = MONTH('" . $newformat . "') GROUP BY pr.idacct ORDER BY lname LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize ."";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array(
            "id" 	                => $row['id'],
            "last" 	                => $row['lname'],
            "first" 	            => $row['fname'],
            "mi" 	                => $row['mname'],
            "idibig" 	            => $row['idibig'],
            "idtin" 	            => $row['idtin'],
            "bdate" 	            => $row['bdate'],
            "loanpayment" 	        => $row['loanpayment'],
            "loandate" 	            => $row['loandate'],
            "systemamortization" 	=> $row['systemamortization']
        );
    }

    $myData = array('status' => 'success', 
                    'result' => $data,
                    'totalItems' => getTotal($con , $newformat, $param),
                    'totals'  => getTotals($con , $newformat, $param),
                    'appmonth'  => $param->month
                    );

	$return = json_encode($myData);
}else{
    $myData = array('status' => 'success',
                        'result' => $data,
                        'totalItems' => 0,
                        'totals'  => 0,
                        'appmonth'  => $param->month
                        );
	
 $return = json_encode($myData);
	
}
print $return;
mysqli_close($con);

function getTotal($con,$newformat, $param){
    $Qry = new Query();	
    $Qry->table     = "tblpayreg AS pr 
                        LEFT JOIN tblpayperiod AS pp ON pr.idpayperiod = pp.id
                        LEFT JOIN tblaccount AS a ON a.id = pr.idacct
                        RIGHT JOIN tblloanpayment AS lp ON pr.id = lp.payregid
                        LEFT JOIN tblloans AS l ON lp.loanid = l.id
                        LEFT JOIN tblloantype AS lt ON l.loanid = lt.id
                        LEFT JOIN tblclasstrans AS ct ON lt.transid = ct.id";
    $Qry->selected  = "pr.id,
                        fname,
                        lname,
                        mname,
                        idibig,
                        idtin,
                        bdate,
                        SUM(lp.amount) AS loanpayment,
                        ct.name,
                        lt.transid,
                        lp.loanid,
                        lp.payregid";
                      
    $Qry->fields = "ct.name = '".$param->loantype."' AND YEAR(lp.payment_date) = YEAR('" . $newformat . "') AND MONTH(lp.payment_date) = MONTH('" . $newformat . "') GROUP BY pr.idacct";

    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $rowcount=mysqli_num_rows($rs);
            return $rowcount;
        }
    }
    return 0;
}

function getTotals($con,$newformat, $param){
    $Qry = new Query();	
    $Qry->table     = "tblpayreg AS pr 
                        LEFT JOIN tblpayperiod AS pp ON pr.idpayperiod = pp.id
                        LEFT JOIN tblaccount AS a ON a.id = pr.idacct
                        RIGHT JOIN tblloanpayment AS lp ON pr.id = lp.payregid
                        LEFT JOIN tblloans AS l ON lp.loanid = l.id
                        LEFT JOIN tblloantype AS lt ON l.loanid = lt.id
                        LEFT JOIN tblclasstrans AS ct ON lt.transid = ct.id";
    $Qry->selected  = "IF(SUM(l.systemamortization) IS NOT NULL,SUM(l.systemamortization),0) AS loanpayment";
    $Qry->fields = "ct.name = '".$param->loantype."' AND YEAR(lp.payment_date) = YEAR('" . $newformat . "') AND MONTH(lp.payment_date) = MONTH('" . $newformat . "')";

    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row['loanpayment'];
        }
    }
    return 0;
}
?>