<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "`tblloantype` as lt LEFT JOIN tblchartofaccount as coad ON  lt.debit = coad.code LEFT JOIN tblchartofaccount as coac ON  lt.credit = coac.code";
$Qry->selected  = "lt.*,coad.code as debitcode,coac.code as creditcode";
$Qry->fields = "lt.id>0";


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id" 	        => $row['id'],

            "code" 	        => getClasstype($con,$row['transid'],'alias'),
            "description" 	=> getClasstype($con,$row['transid'],'name'),

            "FH" 	        => $row['app_first'],
            "SH" 	        => $row['app_second'],
            "SP" 	        => $row['app_sp'],
            "FP" 	        => $row['app_fp'],
            "interest" 	        => $row['interest_percentage'],
            "debit" 	        => $row['debit'],
            "credit" 	        => $row['credit'],
            "loan_mode" 	    => $row['loan_mode'],
            "debitcode" 	        => $row['debitcode'],
            "creditcode" 	        => $row['creditcode']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);

function getClasstype($con,$id,$cols){
    $Qry = new Query();	
    $Qry->table         = "tblclasstrans";
    $Qry->selected      = $cols;
    $Qry->fields        = "id = '".$id."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row[0];
        }
    }
}


?>