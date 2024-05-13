<?php 
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
if(!empty($param->accountid)){
$monthparam = strtotime($param->period);
$monthparam = date('Y-m-d',$monthparam);

$Qry           = new Query();
$Qry->table    = "tblservicecharge";
$Qry->selected = "`docnumber`, payitemid, headcount, totalhours, hourlyrate, total, appliedto, monthparam, releasedate, remarks"; //payitemid
$Qry->fields   = "'".$param->docnumber."',
                    '1',
                    '".$param->headcount."',
                    '".$param->info->totalwhrs."',
                    '".round($param->info->scrate,2)."',
                    '".$param->info->total."',
                    'All with SC status', 
                    '".$monthparam."',
                    '".$monthparam."',
                    'TPM SC'"; 

$checke1 = $Qry->exe_INSERT($con);

$servicechargeid = mysqli_insert_id($con);
echo mysqli_error($con);
if($checke1){
    $return = json_encode(array("status"=>"success"));
    foreach($param->info->result as $key=>$value){
        $Qry1           = new Query();
        $Qry1->table    = "tblservicechargedetails";
        $Qry1->selected = "servicechargeid,empid,hours,amount";
        $Qry1->fields   = "'".$servicechargeid."',
                            '".$value->id."',
                            '".$value->totwhrs."',
                            '".$value->empsc."'
                        ";   
        $checke = $Qry1->exe_INSERT($con);
        echo mysqli_error($con);
    }
    if($checke){
        //PEU
        $Qry2           = new Query();
        $Qry2->table    = "tblservicechargedetails";
        $Qry2->selected = "servicechargeid,empid,hours,amount";
        $Qry2->fields   = "'".$servicechargeid."',
                            '".getUnionPref($con,$unionpref = 'PEU')."', 
                            '".$param->info->peu_whr."',
                            '".$param->info->peu_sc."'
                        ";   // getUnionPref get PEUid in preferences
        $checke2 = $Qry2->exe_INSERT($con);
        echo mysqli_error($con);

        //MPSC
        $Qry3           = new Query();
        $Qry3->table    = "tblservicechargedetails";
        $Qry3->selected = "servicechargeid,empid,hours,amount";
        $Qry3->fields   = "'".$servicechargeid."',
                            '".getUnionPref($con,$mpscpref = 'MPSC')."',
                            '".$param->info->mpsc_whr."',
                            '".$param->info->mpsc_sc."'
                        ";    // getUnionPref get MPSCid in preferences
        $checke3 = $Qry3->exe_INSERT($con);
        echo mysqli_error($con);

        $return = json_encode(array("status"=>"success"));
    }else{
        $return = json_encode(array("status"=>"error"));
    }
}else{
    $return = json_encode(array("status"=>"error"));
}



print $return;
 mysqli_close($con);
}else{
	mysqli_close($con);
	header("Location: https://peninsula.mydhris.com/mph/");  // unAuth back to login page
}
function getUnionPref($con,$alias){
    $share = 0;
	$Qry = new Query();	
	$Qry->table ="tblpreference";
	$Qry->selected ="`id`";
	$Qry->fields = "alias = '".$alias."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $share = $row['id'];	
        }
    }
    return $share;
}
?>