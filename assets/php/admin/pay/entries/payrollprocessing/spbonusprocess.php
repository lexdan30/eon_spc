<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));


$Qry           = new Query();
$Qry->table    = "tblbonuses";
$Qry->selected = "status = 1";
$Qry->fields   = "id = '" . $param->bonusid . "' ";                 
$Qry->exe_UPDATE($con);


$Qry = new Query();	
$Qry->table     = "tblbonusesdetails as a LEFT JOIN tblbonuses as b ON a.bonusid = b.id";
$Qry->selected  = "*, a.id as bid";
$Qry->fields = "a.bonusid = '" . $param->bonusid . "' ";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        
        $data[] = array( 
            "id" 	         => $row['id'],
            "idacct" 	     => $row['idacct'],
            "transaction" 	 => $row['description'],
            "empname"        => getEmpname($con, $row['idacct']),
            "amount" 	     => $row['amount'],
            "taxable" 	     => $row['taxable'],
            "nontaxable" 	 => $row['nontaxable'],
            "totalamount" 	 => $row['taxable'] + $row['nontaxable'],
            "wtax"   	     => taxablebonus($con, $row)
        );

        ;
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array("err"=>mysqli_error($con)));
	
}
print $return;
mysqli_close($con);

function getEmpname($con, $idacct){
    $Qry = new Query();	
    $Qry->table         = "vw_dataemployees";
    $Qry->selected      = "empname";
    $Qry->fields        = "id = '". $idacct ."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['empname'];
        }
    }
}

function taxablebonus($con, $row){
   $amount = $row['taxable'];

    $Qry = new Query();	
    $Qry->table         = "tblcont_bir";
    $Qry->selected      = "*";

    if($amount > 333333.00){
        $Qry->fields        = "id = 6";
    }else{
        $Qry->fields        = "'".$amount."' BETWEEN `mini` AND `max`";
    }

    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row1=mysqli_fetch_array($rs)){
            $w_tax = ( ($amount - $row1['mini']) * $row1['multi']) + $row1['fix_amt'];
        

            $Qry1           = new Query();
            $Qry1->table    = "tblbonusesdetails";
            $Qry1->selected = "wtax = '" . $w_tax . "'";
            $Qry1->fields   = "id = '" . $row['bid'] . "'";          
            $Qry1->exe_UPDATE($con);

            return $w_tax;
        }
    }

}
?>