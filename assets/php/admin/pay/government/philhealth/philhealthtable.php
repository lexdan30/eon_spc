<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblcont_health";
$Qry->selected  = "*";
$Qry->fields = "id>0 ORDER by id";


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $premium=($row['prem_rate']*100);
        // $premium=number_format($premium1, 2); 
        $data[] = array(
            "id" 	        => $row['id'],
            "description" 	=> $row['description'],
            "salfrom" 	    => $row['salfrom'],
            "salto" 	    => $row['salto'],
            "ee" 	        => $row['ee'],
            "er" 	        => $row['er'],
            "fix_amt" 	    => $row['fix_amt'],
            "yr_use" 	    => $row['yr_use'],
            "prem_rate" 	=> $premium,
            "monthly_prem" 	=> $row['monthly_prem']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);
?>