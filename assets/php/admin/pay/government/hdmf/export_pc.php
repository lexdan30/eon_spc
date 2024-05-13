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
                    idibig,
                    idtin,
                    bdate,
                    SUM(p_ee) as p_ee,
                    SUM(p_er) as p_er,
                    SUM(p_ee + p_er) as totalcon";
                  
$Qry->fields    = "YEAR(pp.pay_date) = YEAR('" . $newformat . "') AND MONTH(pp.pay_date) = MONTH('" . $newformat . "') GROUP BY pr.idacct ORDER BY lname";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array(
            "last" 	            => $row['lname'],
            "first" 	        => $row['fname'],
            "mi" 	            => $row['mname'],
            "idibig" 	        => $row['idibig'],
            "p_ee" 	            => $row['p_ee'],
            "p_er" 	            => $row['p_er'],
            "bdate" 	        => $row['bdate']
           
        );
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename= Pag-IBIG Monthly Contribution (MC)'.$date.'.csv');
$output = fopen('php://output', 'w');

// fputcsv($output, array($param['company']));
fputcsv($output, array("New World Makati Hotel"));
fputcsv($output, array("Pag-IBIG Monthly Contribution (MC)"));
fputcsv($output, array("Description: Employee's Pag-IBIG Monthly Contribution File"));

fputcsv($output, array("Applicable Mont: ".$param['month']));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('')); 

fputcsv($output, array('Last Name',
                    'First Name',
                    'Middle Name',
                    'Pag-IBIG Number',
                    'Employee Premium',
                    'Employer Premium',
                    'BirthDate')); 

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
$grandtot['p_ee'],
$grandtot['p_er'],)); 

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
                        idibig,
                        idtin,
                        bdate,
                        SUM(p_ee) as p_ee,
                        SUM(p_er) as p_er,
                        SUM(p_ee + p_er) as totalcon";
    $Qry->fields = "YEAR(pp.pay_date) = YEAR('" . $newformat . "') AND MONTH(pp.pay_date) = MONTH('" . $newformat . "')";

    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            $row['grandtotal'] = $row['p_ee'] + $row['p_er'];
            return $row;
        }
    }
    return 0;
}

?>