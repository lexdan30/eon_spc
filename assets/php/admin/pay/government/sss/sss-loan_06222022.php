<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$time = strtotime($param->month);
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
$Qry->fields = "payment_date < (SELECT pay_date FROM `tblpayperiod` WHERE stat = 1 ORDER BY id DESC LIMIT 1) AND tlt.transid IN (141,142)
                AND YEAR(payment_date) = YEAR('" . $newformat . "') AND MONTH(payment_date) = MONTH('" . $newformat . "') GROUP BY tl.id ORDER BY empname LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize ."";


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

  
    $myData = array('status' => 'success', 
                    'result' => $data,
                    'totalItems' => getTotal($con , $newformat),
                    'totals'  => getTotals($con , $newformat)
                );
	$return = json_encode($myData);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);

function getTotal($con,$newformat){
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
    $Qry->fields = "payment_date < (SELECT pay_date FROM `tblpayperiod` WHERE stat = 1 ORDER BY id DESC LIMIT 1)
                    AND YEAR(payment_date) = YEAR('" . $newformat . "') AND MONTH(payment_date) = MONTH('" . $newformat . "') ";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $rowcount=mysqli_num_rows($rs);
            return $rowcount;
        }
    }
    return 0;
}


function getTotals($con,$newformat){
    $Qry = new Query();	
    $Qry->table     = "tblloanpayment";
    $Qry->selected  = "SUM(amount) AS totalpayment";
    $Qry->fields = "payment_date < (SELECT pay_date FROM `tblpayperiod` WHERE stat = 1 ORDER BY id DESC LIMIT 1)
                    AND YEAR(payment_date) = YEAR('" . $newformat . "') AND MONTH(payment_date) = MONTH('" . $newformat . "') ";
    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_assoc($rs)){
                return $row;
			}
		}
		return 0;
}
?>