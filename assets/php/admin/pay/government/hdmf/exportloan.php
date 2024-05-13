<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_GET;
$date=SysDate();
$time = strtotime($param['month']);
$newformat = date('Y-m-d',$time);

$Qry = new Query();	
$Qry->table     = "tblpayreg AS pr 
                    LEFT JOIN tblpayperiod AS pp ON pr.idpayperiod = pp.id
                    LEFT JOIN tblaccount AS a ON a.id = pr.idacct
                    LEFT JOIN tblloanpayment AS lp ON pr.id = lp.payregid
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
                    SUM(l.totalamount) AS monthlyamortization,
                    ct.name,
                    lt.transid,
                    lp.loanid,
                    lp.payregid,
                    l.loandate";
                  
$Qry->fields = "ct.name = '".$param['loantype']."' AND YEAR(lp.payment_date) = YEAR('" . $newformat . "') AND MONTH(lp.payment_date) = MONTH('" . $newformat . "') GROUP BY pr.idacct ORDER BY lname";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array(
            "last" 	            => $row['lname'],
            "first" 	        => $row['fname'],
            "mi" 	            => $row['mname'],
            "bdate" 	        => $row['bdate'],
            "idibig" 	        => $row['idibig'],
            "loandate" 	        => $row['loandate'],
            "loanpayment" 	    => $row['loanpayment'],
            "monthlyamortization" 	    => $row['monthlyamortization']
           
        );
    }
}

header('Content-Type: text/csv; charset=utf-8');

if($param['loantype'] == 'HDMF Salary Loan'){
    header('Content-Disposition: attachment; filename= Pag-IBIG Salary Loan (SL)'.$date.'.csv');
}
if($param['loantype'] == 'HDMF Calamity Loan'){
    header('Content-Disposition: attachment; filename= Pag-IBIG Calamity Loan (CL)'.$date.'.csv');
}
if($param['loantype'] == 'HDMF Multi-purpose Loan'){
    header('Content-Disposition: attachment; filename= Pag-IBIG Multi-purpose'.$date.'.csv');
}


$output = fopen('php://output', 'w');
fputcsv($output, array($param['company']));

if($param['loantype'] == 'HDMF Salary Loan'){
    fputcsv($output, array("Pag-IBIG Salary Loan (SL)"));
    fputcsv($output, array("Description: Employee's Pag-IBIG Salary Loan File"));
}
if($param['loantype'] == 'HDMF Calamity Loan'){
    fputcsv($output, array("Pag-IBIG Calamity Loan (CL)"));
    fputcsv($output, array("Employee's Pag-IBIG Calamity Loan File"));
}
if($param['loantype'] == 'HDMF Multi-purpose Loan'){
    fputcsv($output, array("Pag-IBIG Multi-purpose Loan"));
    fputcsv($output, array("Employee's Pag-IBIG Multi-purpose Loan File"));
}


fputcsv($output, array("Applicable Month: ".$param['month']));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('')); 

fputcsv($output, array('Last Name',
                    'First Name',
                    'Middle Name',
                    'BirthDate',
                    'Pag-IBIG / SSS Number',
                    'Loan Date',
                    'Loan Payment',
                    'Montly Amortization')); 

if (isset($data)) {
    foreach ($data as $row22) {
            fputcsv($output, $row22);
    }
}

$grandtot = getGrandtotal($con,$newformat, $param);

fputcsv($output, array('')); 
fputcsv($output, array('',
                        '',
                        '',
                        '',
                        '',
                        'Grand Total :',
                        $grandtot['loanpayment'],
                        $grandtot['monthlyamortization']
                    )); 

mysqli_close($con);


function getGrandtotal($con,$newformat, $param){
    $Qry = new Query();	
    $Qry->table     = "tblpayreg AS pr 
                        LEFT JOIN tblpayperiod AS pp ON pr.idpayperiod = pp.id
                        LEFT JOIN tblaccount AS a ON a.id = pr.idacct
                        LEFT JOIN tblloanpayment AS lp ON pr.id = lp.payregid
                        LEFT JOIN tblloans AS l ON lp.loanid = l.id
                        LEFT JOIN tblloantype AS lt ON l.loanid = lt.id
                        LEFT JOIN tblclasstrans AS ct ON lt.transid = ct.id";
    $Qry->selected  = "IF(SUM(lp.amount) IS NOT NULL,SUM(lp.amount),0) AS loanpayment,
                        IF(SUM(l.totalamount) IS NOT NULL,SUM(l.totalamount),0) AS monthlyamortization";
    $Qry->fields = "ct.name = '".$param['loantype']."' AND YEAR(lp.payment_date) = YEAR('" . $newformat . "') AND MONTH(lp.payment_date) = MONTH('" . $newformat . "')";

    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row;
        }
    }
    return 0;
}

?>