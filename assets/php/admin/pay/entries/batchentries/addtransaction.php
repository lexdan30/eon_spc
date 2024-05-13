<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tblbatchentries";
$Qry->selected = "docnum,transactionid,total,count,paydate,type";
$Qry->fields   = "'".$param->earnings->docnum."',
                    '".$param->earnings->transaction."',
                    '".$param->earnings->totalamount."',
                    '".$param->earnings->count."',
                    '".$param->earnings->paydate."',
                    '".$param->type."'
                ";   

                if( !empty($param->earnings->priority) ){
                    $Qry->selected 	= $Qry->selected . ", priority";
                    $Qry->fields 	= $Qry->fields 	 . ", '".$param->earnings->priority."'";
                }
                if( !empty($param->earnings->remarks) ){
                    $Qry->selected 	= $Qry->selected . ", remarks";
                    $Qry->fields 	= $Qry->fields 	 . ", '".$param->earnings->remarks."'";
                }
$Qry->exe_INSERT($con);
$batchentriesid = mysqli_insert_id($con);
echo mysqli_error($con);
if($batchentriesid){
    foreach($param->details as $key=>$value){
        $Qry2           = new Query();
        $Qry2->table    = "tblbatchentriesdetails";
        $Qry2->selected = "batchentriesid,empid,amount";
        $Qry2->fields   = "'".$batchentriesid."',
                            '".$value->employee."',
                            '".$value->amount."'
                        ";   

                        if( !empty($value->timebased) ){
                            $Qry2->selected 	= $Qry2->selected . ", hour";
                            $Qry2->fields 	= $Qry2->fields 	 . ", '".$value->timebased."'";
                        }
                        if( !empty($value->unit) ){
                            $Qry2->selected 	= $Qry2->selected . ", unit";
                            $Qry2->fields 	= $Qry2->fields 	 . ", '".$value->unit."'";
                        }
                        if( !empty($value->department) ){
                            $Qry2->selected 	= $Qry2->selected . ", departmentid";
                            $Qry2->fields 	= $Qry2->fields 	 . ", '".$value->department."'";
                        }
                        if( !empty($value->joblevel) ){
                            $Qry2->selected 	= $Qry2->selected . ", joblevelid";
                            $Qry2->fields 	= $Qry2->fields 	 . ", '".$value->joblevel."'";
                        }
                        if( !empty($value->accounts) ){
                            $Qry2->selected 	= $Qry2->selected . ", accountsid";
                            $Qry2->fields 	= $Qry2->fields 	 . ", '".$value->accounts."'";
                        }
    $checke = $Qry2->exe_INSERT($con);
	
    }
}

if($checke){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array("status"=>"error"));
}
                 
print $return;
mysqli_close($con);
?>