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
$Qry->table     = "tblloans AS tl 
                    LEFT JOIN tblloanpayment AS tlp ON tlp.loanid = tl.id 
                    LEFT JOIN tblloantype AS tlt ON tl.loanid = tlt.id
                    LEFT JOIN tblclasstrans AS tct ON tlt.transid = tct.id
                    LEFT JOIN tblaccount AS ta ON tl.empid = ta.id";
$Qry->selected  = "ta.idsss AS ssnumber,
                    CONCAT(ta.`lname`,IFNULL(CONCAT(' ',`ta`.`suffix`),''),', ',`ta`.`fname`,' ',SUBSTR(`ta`.`mname`,1,1),'. ') AS `empname`,
                    tl.docnumber,
                    tct.name as loantype,
                    tl.loandate,
                    tl.begginingbalance as bb,
                    (SELECT SUM(amount) FROM `tblloanpayment` WHERE loanid = tlp.loanid AND payment_date < (SELECT pay_date FROM `tblpayperiod` WHERE stat = 1 ORDER BY id DESC LIMIT 1)) AS totalpayment,
                    (tl.begginingbalance  - ((SELECT SUM(amount) FROM `tblloanpayment` WHERE loanid = tlp.loanid AND payment_date < (SELECT pay_date FROM `tblpayperiod` WHERE stat = 1 ORDER BY id DESC LIMIT 1)))) AS ob,
                    tlp.amount as payment,
                    tl.totalamount as ma";
$Qry->fields = "payment_date < (SELECT pay_date FROM `tblpayperiod` WHERE stat = 1 ORDER BY id DESC LIMIT 1) AND tlt.transid IN (51,142,161)
                AND YEAR(payment_date) = YEAR('" . $newformat . "') AND MONTH(payment_date) = MONTH('" . $newformat . "') GROUP BY tl.id ORDER BY empname";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array(
            "ssnumber" 	        => $row['ssnumber'],
            "empname" 	        => $row['empname'],
            "loantype" 	        => $row['loantype'],
            "docnumber" 	    => $row['docnumber'],
            "loandate" 	        => $row['loandate'],
            "bb" 	            => $row['bb'],
            "totalpayment" 	    => $row['totalpayment'],
            "ob" 	            => $row['ob'],
            "ma" 	            => $row['ma'],
        );
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename= SSS Loan Remittance'.$date.'.csv');
$output = fopen('php://output', 'w');

// fputcsv($output, array($param['company']));
fputcsv($output, array("New World Makati Hotel"));
fputcsv($output, array("Form Name: SSS Loan Remittance"));
fputcsv($output, array("Description: Employee's SSS Loan Remittance File "));
fputcsv($output, array("Employer SSS No. 03-9162184-2"));
fputcsv($output, array("Applicable Month: ".$param['month']));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('')); 

fputcsv($output, array('SSS Number',
                        'Employee Name',
                        'Loan Type',
                        'Document Number',
                        'Loan Date',
                        'Beginning Balance',
                        'Total Payment',
                        'Outstanding Balance',
                        'Monthly Amortization')); 

if (isset($data)) {
    foreach ($data as $row22) {
            fputcsv($output, $row22);
    }
}


mysqli_close($con);




?>