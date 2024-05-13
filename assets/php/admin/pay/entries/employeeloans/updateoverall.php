<?php 
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$totalamount = (float)$param->info->loanamount + (float)$param->info->interest;



if($param->info->app_first_f->checked == 'true'){
        $param->info->app_first_f = 1;    
    }
    else if( $param->info->app_first_f->checked == 'false' ){
        $param->info->app_first_f = 0;
    }
    else {
        $param->info->app_first_f  = 0;
    }


        if( $param->info->app_second_f->checked  == 'true'){
            $param->info->app_second_f = 1;
        }
        else if( $param->info->app_second_f->checked == 'false' ){
            $param->info->app_second_f = 0;
        }
        else {
            $param->info->app_second_f  = 0;
        }


                if( $param->info->hold->checked  == 'true'){
                    $param->info->hold = 1;
                }
                else if( $param->info->hold->checked == 'false' ){
                    $param->info->hold = 0;
                }
                else {
                    $param->info->hold  = 0;
                }


                        if( $param->info->fullypaid->checked  == 'true'){
                            $param->info->fullypaid = 1;
                        }
                        else if( $param->info->fullypaid->checked == 'false' ){
                            $param->info->fullypaid = 0;
                        }
                        else {
                            $param->info->fullypaid  = 0;
                        }





$Qry           = new Query();
$Qry->table    = "tblloans";
$Qry->selected = "loandate     ='".$param->info->loandate."',
                    firstpaydate     ='".$param->info->firstpaydate."',
                    interest     ='".$param->info->interest."',
                    noa     ='".$param->info->noa."',
                    systemamortization     ='".$param->info->systemamortization."',
                    useramortization     ='".$param->info->useramortization."',
                    app_first_f     ='".$param->info->app_first_f."',
                    app_second_f     ='".$param->info->app_second_f."',
                    hold     ='".$param->info->hold."',
                    fullypaid     ='".$param->info->fullypaid."',
                    totalamount     ='". $totalamount."',
                    begginingbalance     ='". $totalamount."'
                    ";
$Qry->fields   = "id='".$param->info->id."'";                        
$checke =  $Qry->exe_UPDATE($con);

if($checke){
    $return = json_encode(array("status"=>"success","updates"=>$param->info));
}else{
    $return = json_encode(array('status'=>'error'));
}
print $return;
 mysqli_close($con);

?>