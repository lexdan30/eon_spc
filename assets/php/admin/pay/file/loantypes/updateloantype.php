<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
// $stats      = !empty($param->info->stats) ? 0 : 1;
$loan_mode  = !empty($param->info->loan_mode) ? $param->info->loan_mode : "Fixed Amount";
$app_first  = !empty($param->info->app_first) ? $param->info->app_first : 0;
$app_second = !empty($param->info->app_second) ? $param->info->app_second : 0;
$app_sp     = !empty($param->info->app_sp) ? $param->info->app_sp : 0;
$app_fp     = !empty($param->info->app_fp) ? $param->info->app_fp : 0;
$percentage = !empty($param->info->interest_percentage) ? $param->info->interest_percentage : 0;
//print_r($app_second);
// $Qry           = new Query();
// $Qry->table    = "tblloantype";
// $Qry->selected = "
//                   loan_mode             ='".$param->info->loan_mode."', 
//                   type                  ='".$param->info->type."',
//                   app_first	            ='".$app_first."',
//                   app_second	        ='".$app_second."',
//                   app_sp	            ='".$app_sp."',
//                   app_fp	            ='".$app_fp."',
//                   priority              ='".$param->info->priority."'
//                 ";
// $Qry->fields   = "id='".$param->info->id."'";     
$Qry           = new Query();
$Qry->table    = "tblloantype";
$Qry->selected = "
                  loan_mode             ='".$loan_mode ."', 
                  app_first	            ='".$app_first."',
                  app_second	        ='".$app_second."',
                  app_sp	            ='".$app_sp."',
                  app_fp	            ='".$app_fp."',
                  priority              ='".$param->info->priority."'
                ";
                // if( !empty($param->info->loan_mode) ){
                //     $Qry->selected 	= "loan_mode             ='".$param->info->loan_mode."'";
                // } 
                if( !empty($param->info->type) ){
                    $Qry->selected 	= $Qry->selected . ", type='".$param->info->type."'";
                }
                // if( !empty($app_first) ){
                //     $Qry->selected 	= "app_first             ='".$app_first."'";
                // }
                // if( !empty($app_second) ){
                //     $Qry->selected 	= $Qry->selected . ", app_second ='".$app_second."'";
                // }
                // if( !empty($app_sp) ){
                //     $Qry->selected 	= "app_sp             ='".$app_sp."'";
                // }
                // if( !empty($app_fp) ){
                //     $Qry->selected 	= "app_fp             ='".$app_fp."'";
                // }
                if( !empty($param->info->priority) ){
                    $Qry->selected 	= $Qry->selected . ",priority='".$param->info->priority."'";
                }



$Qry->fields   = "id='".$param->info->id."'";                     
$checke = $Qry->exe_UPDATE($con);

// $Qrytrans           = new Query();
// $Qrytrans->table    = "tblclasstrans";
// $Qrytrans->selected = "
//                   name             ='".$param->info->desc."', 
//                   rate             ='".$percentage."',
//                   debit	           ='".$param->info->debit."', 
//                   credit	       ='".$param->info->credit."',
//                   flags	           ='".$param->info->stats."'
//                 ";
// $Qrytrans->fields   = "id='".$param->info->transid."'";      
$Qrytrans           = new Query();
$Qrytrans->table    = "tblclasstrans";
$Qrytrans->selected = "
                  name             ='".$param->info->desc."', 
                  rate             ='".$percentage."',
                  debit	           ='".$param->info->debit."', 
                  credit	       ='".$param->info->credit."',
                  flags	           ='".$param->info->stats."'
                ";
                if( !empty($param->info->desc) ){
                    $Qry->selected 	= "name             ='".$param->info->desc."'";
                }
                if( !empty($percentage) ){
                    $Qry->selected 	= "rate             ='".$percentage."'";
                }
                if( !empty($param->info->debit) ){
                    $Qry->selected 	= "debit             ='".$param->info->debit."'";
                }
                if( !empty($param->info->credit) ){
                    $Qry->selected 	= "credit             ='".$param->info->credit."'";
                }
                if( !empty($param->info->stats) ){
                    $Qry->selected 	= "flags             ='".$param->info->stats."'";
                }



$Qrytrans->fields   = "id='".$param->info->transid."'";                     
$checketrans = $Qrytrans->exe_UPDATE($con);



if($checke){
    $return = json_encode(array("status"=>"success"));
}else if($checketrans){
    $return = json_encode(array("status"=>"success"));
}else {
    $return = json_encode(array('status'=>'error'));
}
print $return;
mysqli_close($con);
// classname ='".$param->info->classname."',
// `desc`                  ='".$param->info->desc."',
// stats                 ='".$param->info->stats."', 
// interest_percentage   ='".$percentage."',
// debit	                ='".$param->info->debit."', 
// credit	            ='".$param->info->credit."'
?>