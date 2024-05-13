<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));
$date   = SysDatePadLeft();
$pay_period = getPayPeriod($con);


$Qry = new Query();
$Qry->table = "vw_sapdata";
$Qry->selected = "AccountCode,Debit,Credit,ProjectCode,GL_Description,CostingCode";
$Qry->fields    = "id>0 AND pay_date LIKE '".substr($param->saptoday,0,7)."%'";
$rs = $Qry->exe_SELECT($con);
$data = array();
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){

        $data[]= array( 
            'AccountCode'=>$row['AccountCode'],
            'Debit'=>$row['Debit'],
            'Credit'=>$row['Credit'],
            'ProjectCode'=>$row['ProjectCode'],
            'GL_Description'=>$row['GL_Description'],
            'CostingCode'=>$row['CostingCode']
        );

    }
 
    $return = json_encode($data);
}else{
	$return = json_encode(array());
}


print $return;
mysqli_close($con);
?>