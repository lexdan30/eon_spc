<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry           = new Query();
$Qry->table    = "tblrecurring";
$Qry->selected = "docnum,payitemid,total,sdate,edate";
$Qry->fields   = "'".$param->recurring->docnum."',
                    '".$param->recurring->transaction."',
                    '".$param->recurring->total."',
                    '".$param->recurring->sdate."',
                    '".$param->recurring->edate."'
                ";   

                if( !empty($param->recurring->priority) ){
                    $Qry->selected 	= $Qry->selected . ", priority";
                    $Qry->fields 	= $Qry->fields 	 . ", '".$param->recurring->priority."'";
                }


                if( !empty($param->recurring->fh) ){
                    $Qry->selected 	= $Qry->selected . ", fh";
                    $Qry->fields 	= $Qry->fields 	 . ", '".$param->recurring->fh."'";
                }

                if( !empty($param->recurring->sh) ){
                    $Qry->selected 	= $Qry->selected . ", sh";
                    $Qry->fields 	= $Qry->fields 	 . ", '".$param->recurring->sh."'";
                }

                 if( !empty($param->recurring->fp) ){
                    $Qry->selected 	= $Qry->selected . ", fp";
                    $Qry->fields 	= $Qry->fields 	 . ", '".$param->recurring->fp."'";
                }
        
                if( !empty($param->recurring->rule) ){
                    $Qry->selected 	= $Qry->selected . ", rule";
                    $Qry->fields 	= $Qry->fields 	 . ", '".$param->recurring->rule."'";
                }

                if( !empty($param->recurring->remarks) ){
                    $Qry->selected 	= $Qry->selected . ", remarks";
                    $Qry->fields 	= $Qry->fields 	 . ", '".$param->recurring->remarks."'";
                }
$Qry->exe_INSERT($con);
$lastid = mysqli_insert_id($con);

if($lastid){
    foreach($param->details as $key=>$value){
   
        $Qry2           = new Query();
        $Qry2->table    = "tblrecurringdetails";
        $Qry2->selected = "recurringid,empid,amount";
        $Qry2->fields   = "'".$lastid."',
                            '".$value->employee."',
                            '".$value->amount."'
                        "; 
       $checke = $Qry2->exe_INSERT($con);
    }
}else{
    $return = json_encode(array("status"=>"error", "mysqli_error" => mysqli_error($con))); 
}


if($checke){
    $return = json_encode(array("status"=>"success"));
}
                 
print $return;
mysqli_close($con);
?>