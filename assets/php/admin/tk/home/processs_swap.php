<?php
error_reporting(0);
date_default_timezone_set('Asia/Manila');
require_once('../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));
$date   = SysDatePadLeft();
$pay_period = getPayPeriod($con);
$search ='';
$Qry = new Query();
if($param->typeemp == "Local Employee"){

    $Qry->table = "vw_sapdata AS de LEFT JOIN tblpayperiod AS dt ON de.idpayperiod=dt.period";
    $search = " AND vw_sapdata.type=". $param->typeemp;
  
}elseif($param->typeemp == "Japanese"){

    $Qry->table = "vw_sapdata_jap AS de LEFT JOIN tblpayperiod AS dt ON de.idpayperiod=dt.period";
    $search = " AND vw_sapdata.type=". $param->typeemp;
  
}elseif($param->typeemp == "Helper"){
  
    $Qry->table = "vw_sapdata_helper AS de LEFT JOIN tblpayperiod AS dt ON de.idpayperiod=dt.period";
    $search = " AND vw_sapdata.type=". $param->typeemp;
  
}elseif($param->typeemp == "Japanesecon"){
  
    $Qry->table = "vw_sapdata_japc AS de LEFT JOIN tblpayperiod AS dt ON de.idpayperiod=dt.period";
    $search = " AND vw_sapdata_japc.type=". $param->typeemp;
  
}else{

    $search = '';
    $Qry->table = "vw_sapdata_all AS de LEFT JOIN vw_payperiod_all AS dt ON de.idpayperiod=dt.id";
}



$Qry->selected = "AccountCode,Debit,Credit,ProjectCode,GL_Description,CostingCode";
$Qry->fields    = "de.pay_date LIKE '".date("Y-m", (int)$param->saptoday/1000)."%' ";
$rs = $Qry->exe_SELECT($con);
$data = array();
if(mysqli_num_rows($rs)>= 1){
    $sumdebit=0;
    $sumcredit=0;
    while($row=mysqli_fetch_assoc($rs)){

        $data[]= array( 
            'AccountCode'=>$row['AccountCode'],
            'Debit'=>$row['Debit'],
            'Credit'=>$row['Credit'],
            'ProjectCode'=>$row['ProjectCode'],
            'GL_Description'=>$row['GL_Description'],
            'CostingCode'=>$row['CostingCode'],
            'sumdebit'=>$sumdebit = $sumdebit+(int)$row['Debit'],
            'sumcredit'=>$sumcredit=$sumcredit+(int)$row['Credit'],
        );

    }
 
    $return = json_encode($data);
}else{
	$return = json_encode(array());
}


print $return;
mysqli_close($con);
?>