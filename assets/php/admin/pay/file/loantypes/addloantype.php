<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));
$stats      = !empty($param->info->stats) ? 0 : 1;
$loan_mode  = !empty($param->info->loan_mode) ? $param->info->loan_mode : "Fixed Amount";
$app_first  = !empty($param->info->app_first) ? $param->info->app_first : 0;
$app_second = !empty($param->info->app_second) ? $param->info->app_second : 0;
$app_sp     = !empty($param->info->app_sp) ? $param->info->app_sp : 0;
$app_fp     = !empty($param->info->app_fp) ? $param->info->app_fp : 0;
$percentage = !empty($param->info->interest_percentage) ? $param->info->interest_percentage : 0;
$credit = !empty($param->info->credit) ? $param->info->credit : NULL;
$debit = !empty($param->info->debit) ? $param->info->debit : NULL;
$isdef      = 1;
$classno    = 65;


// Add to transaction types
$Qrytrans           = new Query();
$Qrytrans->table    = "tblclasstrans";
$Qrytrans->selected = "`idclass`,
                    `name`,
                    `alias`,
                    `isdef`,
					`entrytype`";
$Qrytrans->fields   = "
                    '".$classno."',
                    '".$param->info->desc."', 
                    '".$param->info->code."',
                    '".$isdef."',
					''
                    "; 

//                     $Qrytrans->selected = "`idclass`,
//                     `name`,
//                     `alias`,
//                     `credit`, 
//                     `debit`,
//                     `rate`,
//                     `isdef`,
//                     `flags`";
//                      $Qrytrans->fields   = "
//                     '".$classno."',
//                     '".$param->info->desc."', 
//                     '".$param->info->code."',
//                     '".$credit."',
//                     '".$debit."',
//                     '".$percentage."',
//                     '".$isdef."',
//                     '".$stats."'
//                     "; 
                    if( !empty($percentage) ){
                        $Qrytrans->selected 	= $Qrytrans->selected . ", `rate`";
                        $Qrytrans->fields 	= $Qrytrans->fields 	 . ", '".$percentage."'";
                    }
                    if( !empty($credit) ){
                        $Qrytrans->selected 	= $Qrytrans->selected . ", `credit`";
                        $Qrytrans->fields 	= $Qrytrans->fields 	 . ", '".$credit."'";
                    }
                    if( !empty($debit) ){
                        $Qrytrans->selected 	= $Qrytrans->selected . ", `debit`";
                        $Qrytrans->fields 	= $Qrytrans->fields 	 . ", '".$debit."'";
                    } 
                    if( !empty($stats) ){
                        $Qrytrans->selected 	= $Qrytrans->selected . ", `flags`";
                        $Qrytrans->fields 	= $Qrytrans->fields 	 . ", '".$stats."'";
                    } 


$rstrans = $Qrytrans->exe_INSERT($con);
echo mysqli_error($con);
$transid = mysqli_insert_id($con);

//Add to loan types table
$Qry           = new Query();
$Qry->table    = "tblloantype";
$Qry->selected = "`stats`,`app_first`,`app_second`,`app_sp`,`app_fp`";
$Qry->fields   = "
                '".$stats."',
                '".$app_first."',
                '".$app_second."',
                '".$app_sp."',
                '".$app_fp."'
                ";       
                // $Qry->selected = "`transid`,`stats`,`type`,`loan_mode`,`app_first`,`app_second`,`app_sp`,`app_fp`,`priority`";
                // $Qry->fields   = "'".$transid."',
                //                 '".$stats."',
                //                 '".$param->info->type."',
                //                 '".$loan_mode."',
                //                 '".$app_first."',
                //                 '".$app_second."',
                //                 '".$app_sp."',
                //                 '".$app_fp."',
                //                 '".$param->info->priority."'
                //                 ";              
                if( !empty($transid) ){
                    $Qry->selected 	= $Qry->selected . ", `transid`";
                    $Qry->fields 	= $Qry->fields 	 . ", '".$transid."'";
                }
                if( !empty($param->info->type) ){
                    $Qry->selected 	= $Qry->selected . ", `type`";
                    $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->type."'";
                }
                if( !empty($loan_mode) ){
                    $Qry->selected 	= $Qry->selected . ", `loan_mode`";
                    $Qry->fields 	= $Qry->fields 	 . ", '".$loan_mode."'";
                }
                if( !empty($param->info->priority) ){
                    $Qry->selected 	= $Qry->selected . ", `priority`";
                    $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->priority."'";
                } 
$rs = $Qry->exe_INSERT($con);

if($rstrans){
    $return = json_encode(array("status"=>"success"));
}else if($rs){
    $return = json_encode(array("status"=>"success"));
}else{
    $return = json_encode(array('status'=>'error', 'status error' => mysqli_error($con)));
}
print $return;
mysqli_close($con);

?>