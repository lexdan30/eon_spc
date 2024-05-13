<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

  
$Qry = new Query();	
$Qry->table         = "tblbonuses";
$Qry->selected      = " `docnum`, 
                        `description`, 
                        `period`, 
                        `start`, 
                        `end`, 
                        `releasedate`, 
                        `totalmonthcovered`, 
                        `applyto`, 
                        `accountid`, 
                        `rule`, 
                        `unitamount`, 
                        `mode`, 
                        `exemption`, 
                        `remarks`";
$Qry->fields        = " '".$param->bonus->docnum."',
                        '".$param->bonus->description."',
                        '".$param->bonus->period."',
                        '".$param->bonus->startdate."',
                        '".$param->bonus->endate."',
                        '".$param->bonus->releasedate."',
                        '".$param->bonus->tmc."',
                        '".$param->bonus->applyto."',
                        '".$param->bonus->account."',
                        '".$param->bonus->rule."',
                        '".$param->bonus->amount."',
                        '".$param->bonus->mode."',
                        '".$param->bonus->er."',
                        '".$param->bonus->remarks."'";  
                        



$Qry->exe_INSERT($con);
$bonusid = mysqli_insert_id($con);
echo mysqli_error($con);
if($bonusid){
    foreach($param->bonusdetails as $key=>$value){
        $Qry2           = new Query();
        $Qry2->table    = "tblbonusesdetails";
        $Qry2->selected = "bonusid,idacct,amount,taxable,nontaxable";
        $Qry2->fields   = "'".$bonusid."',
                            '".$value->id."',
                            '".$value->amount."',
                            '".$value->taxable."',
                            '".$value->nontaxable."'
                        "; 
    $checke = $Qry2->exe_INSERT($con);
	
    }
}


if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array("status"=>"error"));
}

?>