<?php
require_once('../../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

//$date = date('Y-m-d');
$date 	= SysDate();
$time 	= SysTime();

$empname = !empty($param->info->fullname) ? $param->info->fullname : 'NULL';

if(empty($param->info->idacct)){
    $return = json_encode(array('status'=>'noid'));
}elseif(empty($param->info->effectivedate)){
    $return = json_encode(array('status'=>'nodate'));
}elseif(empty($param->info->asignatory)){
    $return = json_encode(array('status'=>'noasig'));
}else{



    $Qry           = new Query();
    $Qry->table    = "tblwage_increase";
    $Qry->selected = "idacct,
                        refcode,
                        empid,
                        empname,
                        effectivedate,
                        asignatory,
                        date_created,
                        time_created";
    $Qry->fields   = "'".$param->info->idacct."',
                        '".getrefcode($con)."',
                        '".$param->info->empid."',
                        '".$empname."',
                        '".$param->info->effectivedate."',
                        '".$param->info->asignatory."',
                        '".$date ."',
                        '".$time ."'";
    if( !empty($param->info->fromempname) ){
        $Qry->selected 	= $Qry->selected . ", `fromempname`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->fromempname."'";
    }
    if( !empty($param->info->subject) ){
        $Qry->selected 	= $Qry->selected . ", `subject`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->subject."'";
    }
    if( !empty($param->info->remarks) ){
        $Qry->selected 	= $Qry->selected . ", `remarks`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->remarks."'";
    }
    if( !empty($param->info->totalallowance) ){
        $Qry->selected 	= $Qry->selected . ", `from_allowance`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->totalallowance."'";
    }
    if( !empty($param->info->currentbasepay) ){
        $Qry->selected 	= $Qry->selected . ", `from_salary`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->currentbasepay."'";
    }
    if( !empty($param->info->from_contract) ){
        $Qry->selected 	= $Qry->selected . ", `from_contract`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->from_contract."'";
    }
    if( !empty($param->info->currentempstatus) ){
        $Qry->selected 	= $Qry->selected . ", `from_status`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->currentempstatus."'";
    }
    if( !empty($param->info->currentpositiontitle) ){
        $Qry->selected 	= $Qry->selected . ", `from_project`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->currentpositiontitle."'";
    }
    if( !empty($param->info->currentimmediatesupervisor) ){
        $Qry->selected 	= $Qry->selected . ", `from_supervisor`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->currentimmediatesupervisor."'";
    }
    if( !empty($param->info->from_corporate) ){
        $Qry->selected 	= $Qry->selected . ", `from_corporate`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->from_corporate."'";
    }
    if( !empty($param->info->from_iso) ){
        $Qry->selected 	= $Qry->selected . ", `from_iso`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->from_iso."'";
    }

    if( !empty($param->info->to_allowance) ){
        $Qry->selected 	= $Qry->selected . ", `to_allowance`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->to_allowance."'";
    }
    if( !empty($param->info->to_salary) ){
        $Qry->selected 	= $Qry->selected . ", `to_salary`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->to_salary."'";
    }
    if( !empty($param->info->to_contract) ){
        $Qry->selected 	= $Qry->selected . ", `to_contract`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->to_contract."'";
    }
    if( !empty($param->info->to_status) ){
        $Qry->selected 	= $Qry->selected . ", `to_status`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->to_status."'";
    }
    if( !empty($param->info->to_project) ){
        $Qry->selected 	= $Qry->selected . ", `to_project`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->to_project."'";
    }
    if( !empty($param->info->to_supervisor) ){
        $Qry->selected 	= $Qry->selected . ", `to_supervisor`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->to_supervisor."'";
    }
    if( !empty($param->info->to_corporate) ){
        $Qry->selected 	= $Qry->selected . ", `to_corporate`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->to_corporate."'";
    }
    if( !empty($param->info->to_iso) ){
        $Qry->selected 	= $Qry->selected . ", `to_iso`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->info->to_iso."'";
    }

    $checke = $Qry->exe_INSERT($con);
    if($checke){
        $return = json_encode(array("status"=>"success"));
    }else{
        $return = json_encode(array('status'=>'error'));
    }
}

print $return;
mysqli_close($con);

function getrefcode($con){
    $ref = '';
    $refnum = '';
	$Qry=new Query();
	$Qry->table="tblwage_increase";
	$Qry->selected="count(*) as total";
	$Qry->fields="id>0";
	$rs=$Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>0){
		if($row=mysqli_fetch_array($rs)){
            $num = strlen($row['total']);
            $num = 5-(int)$num;
            for ($i = 0; $i < $num; $i++) {
                $refnum .= '0';
            }
            $ref =  'KPI'.$refnum . ((int)$row['total']+1);
            return $ref;
		}
	}
	return 0;
}
?>