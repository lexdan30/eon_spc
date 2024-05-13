<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblcont_sss";
$Qry->selected  = "*";
$Qry->fields = "id>0 ORDER by id";


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        if($row['msalary_ec'] == 0){
            $msalary_ec = '-';
        }else{
            $msalary_ec = $row['msalary_ec'];
        }
        
        if($row['mpfund'] == 0){
            $fund = '-';
        }else{
            $fund = $row['mpfund'];
        }

        if($row['ecc_er'] == 0){
            $ecc_er = '-';
        }else{
            $ecc_er = $row['ecc_er'];
        }
        if($row['ecc_ee'] == 0){
            $ecc_ee = '-';
        }else{
            $ecc_ee = $row['ecc_ee'];
        }

        if($row['emprcont'] == 0){
            $emprcont = '-';
        }else{
            $emprcont = $row['emprcont'];
        }
        if($row['empcont'] == 0){
            $empcont = '-';
        }else{
            $empcont = $row['empcont'];
        }
        
        if($row['mandatory_er'] == 0){
            $mandatory_er = '-';
        }else{
            $mandatory_er = $row['mandatory_er'];
        }
        if($row['mandatory_ee'] == 0){
            $mandatory_ee = '-';
        }else{
            $mandatory_ee = $row['mandatory_ee'];
        }
       
        $data[] = array(
            "id" 	            => $row['id'],
            "description" 	    => $row['description'],
            "sal_creditfrom" 	=> $row['sal_creditfrom'] ,
            "sal_creditto" 	    => $row['sal_creditto'] ,
            "msalary_ec" 	    => $msalary_ec ,
            "mpfund" 	        => $fund,
            "empcont" 	        => $empcont,
            "emprcont" 	        => $emprcont,
            "ecc_er" 	        => $ecc_er,
            "ecc_ee" 	        => $ecc_ee,
            "mandatory_er" 	    => $mandatory_er,
            "mandatory_ee" 	    => $mandatory_ee,
            "yr_use" 	        => $row['yr_use'] 
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);
?>