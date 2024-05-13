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
$Qry->table     = "tblpayreg as pr 
                    LEFT JOIN tblpayperiod as pp ON pr.idpayperiod = pp.id
                    LEFT JOIN tblaccount as a ON a.id = pr.idacct";
$Qry->selected  = "pr.id,
                    fname,
                    lname,
                    mname,
                    idsss,
                    SUM(er) as er,
                    SUM(ee) AS ee,
                    SUM(ec) AS ec,
                    SUM(m_ee) AS m_ee,
                    SUM(m_er) AS m_er,
                    SUM(er + ee + ec + m_er + m_ee) as totalcon";
$Qry->fields = "YEAR(pp.pay_date) = YEAR('" . $newformat . "') AND MONTH(pp.pay_date) = MONTH('" . $newformat . "') GROUP BY pr.idacct ORDER BY lname";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array(
            "last" 	            => $row['lname'],
            "first" 	        => $row['fname'],
            "mi" 	            => $row['mname'][0],
            "idsss" 	        => $row['idsss'],
            "ee" 	            => $row['ee'],
            "er" 	            => $row['er'],
            "ec" 	            => $row['ec'],
            "m_ee" 	            => $row['m_ee'],
            "m_er" 	            => $row['m_er'],
            "total" 	        => $row['totalcon'],
        );
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename= SSS Contribution'.$date.'.csv');
$output = fopen('php://output', 'w');

// fputcsv($output, array($param['company']));
fputcsv($output, array("New World Makati Hotel"));
fputcsv($output, array("Form Name: SSS Monthly Contribution"));
fputcsv($output, array("Description: Employee's SSS Monthly Contribution File "));
fputcsv($output, array("Employer SSS No. 03-9162184-2"));
fputcsv($output, array("Applicable Month: ".$param['month']));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('')); 

fputcsv($output, array('Last Name',
                        'First Name',
                        'Middle Name',
                        'SSS Number',
                        'EE',
                        'ER',
                        'EC',
                        'WISP-EE',
                        'WISP-ER',
                        'Total Contributions')); 

if (isset($data)) {
    foreach ($data as $row22) {
            fputcsv($output, $row22);
    }
}

$grandtot = getGrandtotal($con, $newformat);

fputcsv($output, array('')); 
fputcsv($output, array('',
                        '',
                        '',
                        'Grand Total :',
                        $grandtot['ee'],
                        $grandtot['er'],
                        $grandtot['ec'],
                        $grandtot['m_ee'],
                        $grandtot['m_er'],
                        $grandtot['totalcon'])); 

mysqli_close($con);


function getGrandtotal($con,$newformat){
    $Qry = new Query();	
    $Qry->table     = "tblpayreg as pr 
                        LEFT JOIN tblpayperiod as pp ON pr.idpayperiod = pp.id
                        LEFT JOIN tblaccount as a ON a.id = pr.idacct";
    $Qry->selected  = "SUM(er) as er,
                        SUM(ee) AS ee,
                        SUM(ec) AS ec,
                        SUM(m_ee) AS m_ee,
                        SUM(m_er) AS m_er,
                        SUM(er + ee + ec + m_er + m_ee) as totalcon";
    $Qry->fields = "YEAR(pp.pay_date) = YEAR('" . $newformat . "') AND MONTH(pp.pay_date) = MONTH('" . $newformat . "')";

    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_assoc($rs)){
                $row['grandtotal'] = $row['ee'] + $row['er'] + $row['ec'] + $row['m_ee'] + $row['m_er'];
                return $row;
			}
		}
		return 0;
}

?>