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
                    idhealth,
                    SUM(ph_ee) as ph_ee,
                    SUM(ph_er) as ph_er,
                    SUM(ph_er + ph_ee) as totalcon";
                  
$Qry->fields = "YEAR(pp.pay_date) = YEAR('" . $newformat . "') AND MONTH(pp.pay_date) = MONTH('" . $newformat . "') GROUP BY pr.idacct ORDER BY lname";


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array(
            "idibig" 	        => $row['idhealth'],
            "last" 	            => $row['lname'],
            "first" 	        => $row['fname'],
            "mi" 	            => $row['mname'][0],
            "ph_ee" 	            => $row['ph_ee'],
            "ph_er" 	            => $row['ph_er'],
            "total" 	        => $row['totalcon']
        );
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename= Philhealth Contribution'.$date.'.csv');
$output = fopen('php://output', 'w');

// fputcsv($output, array("Employer Name:".$param['company']));
fputcsv($output, array("New World Makati Hotel"));
fputcsv($output, array("PhilHealth Number:	01-900000036-9"));
fputcsv($output, array("Employer Address:	12 Floor 6788 Oledan Square Ayala Avenue, San Lorenzo Makati City"));
fputcsv($output, array("Employer TIN:	216-343203-000"));
fputcsv($output, array("Employer Type:	Private"));

fputcsv($output, array('')); 

fputcsv($output, array('PhilHealth No.',
                        'Surname',
                        'Given Name',
                        'Middle Initial',
                        'ES',
                        'ER',
                        'Total')); 

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
                        $grandtot['ph_ee'],
                        $grandtot['ph_er'],
                        $grandtot['totalcon'])); 

mysqli_close($con);


function getGrandtotal($con,$newformat){
    $Qry = new Query();	
    $Qry->table     = "tblpayreg as pr 
                        LEFT JOIN tblpayperiod as pp ON pr.idpayperiod = pp.id
                        LEFT JOIN tblaccount as a ON a.id = pr.idacct";
    $Qry->selected  = "pr.id,
                        fname,
                        lname,
                        mname,
                        idhealth,
                        SUM(ph_ee) as ph_ee,
                        SUM(ph_er) as ph_er,
                        SUM(ph_er + ph_ee) as totalcon";
    $Qry->fields = "YEAR(pp.pay_date) = YEAR('" . $newformat . "') AND MONTH(pp.pay_date) = MONTH('" . $newformat . "')";

    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_assoc($rs)){
                $row['grandtotal'] = $row['ph_ee'] + $row['ph_er'];
                return $row;
			}
		}
		return 0;
}

?>