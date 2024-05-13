<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param       = json_decode(file_get_contents('php://input'));
$date_create=SysDate();
$time_create=SysTime();

$begin = new DateTime($param->from);
$end = new DateTime($param->to);
$preference = '';
$idshift		= '';
$apphrs         = '';
$idtimeleavetype= ''; 
$reject = array();

foreach( $param->info->leave_dates as $keys=>$value ){ 
    $idshift		= $value->idshift;
    $apphrs         = $value->hrs;
    $idtimeleavetype= $value->val;
}

$approver1 = getApproverss($con, $param->acct, 'approver1', $param->acct);
$approver2 = getApproverss($con, $param->acct, 'approver2', $param->acct);
$approver3 = getApproverss($con, $param->acct, 'approver3', $param->acct);
$approver4 = getApproverss($con, $param->acct, 'approver4', $param->acct);

$ctr=1;
$x = 0;
for($i = $begin; $i <= $end; $i->modify('+1 day')){
    $date =   $i->format("Y-m-d");
    $time 	   = time();

    $docnumber = "OB".$param->acct.strtotime( $date_create.$time ).$time.$ctr;
    $ctr++;
    $ctr2=1;

    foreach( $param->info->leave_dates as $keys=>$value ){
        $idtimeleavetype= $value->val;
        $apphrs         = $value->hrs;
        $ctr2++;

        
    if( empty($approver1) ){
        $reject[]	= array(
            "date"	=> $date,
            "msg"	=> "Please setup classification in 201."
        );
    }elseif( obExists($con, $date, $param->acct) ){
        
        if($ctr  == $ctr2){
            $reject[]	= array(
                "date"	=> $date,
                "msg"	=> "Already has official business trip application for this date."
            );
        }
    }else if($x == $keys){
        if ($value->shift == 'Rest Day') {
            continue;
        }

        if($x == 0 && $value->sched->stime == 0 && $value->sched->ftime == 0){
            $sltime = $param->stime_sindex;
            $fltime = $param->ftime_findex;
        }else{
            $sltime = $value->sched->stime;
            $fltime = $value->sched->ftime;
        }
    $Qry 			= new Query();	
    $Qry->table 	= "tbltimeobtrip";
    $Qry->selected 	= "creator,
                        docnumber, 
                        idacct,
                        start_time,
                        end_time,
                        idshift,
                        date,
                        remarks, 
                        approver1, 
                        date_create";
  
    $Qry->fields 	= "'".$param->acct."', 
                        '".$docnumber."', 
                        '".$param->acct."',
                        '".$sltime."', 
                        '".$fltime."',  

                        '". $idshift ."',

                        '". $date ."',
                        '". $param->remarks ."',
                        '". $approver1 ."',
                        '".$date_create."'";
                        
    if( !empty( $remarks ) ){
        $Qry->selected 	= $Qry->selected . ", remarks";
        $Qry->fields 	= $Qry->fields 	 . ", '".$remarks."'";
    }
    if( !empty( $param->remarks2 ) ){
        $Qry->selected 	= $Qry->selected . ", remarks_2";
        $Qry->fields 	= $Qry->fields 	 . ", '".str_replace("'", "\'", $param->remarks2)."'";
    }
    if( !empty( $approver2 ) ){
        $Qry->selected 	= $Qry->selected . ", approver2";
        $Qry->fields 	= $Qry->fields 	 . ", '".$approver2."'";
    }
    if( !empty( $approver3 ) ){
        $Qry->selected 	= $Qry->selected . ", approver3";
        $Qry->fields 	= $Qry->fields 	 . ", '".$approver3."'";
    }

    if( !empty( $approver4 ) ){
        $Qry->selected 	= $Qry->selected . ", approver4";
        $Qry->fields 	= $Qry->fields 	 . ", '".$approver4."'";
    }

    if( !empty( $apphrs ) ){
        $Qry->selected 	= $Qry->selected . ", hrs";
        $Qry->fields 	= $Qry->fields 	 . ", '".$apphrs."'";
    }

    if( !empty( $idtimeleavetype ) ){
        $Qry->selected 	= $Qry->selected . ", idtimetype";
        $Qry->fields 	= $Qry->fields 	 . ", '".$value->val."'";
    }
    
    if( $param->isJapanese == 'true'){
        $Qry->selected 	= $Qry->selected . ", `stat`";
        $Qry->fields 	= $Qry->fields 	 . ", '1'";
    } 

    if(!empty($param->random)){
        $Qry->selected 	= $Qry->selected . ", `file`";
        $Qry->fields 	= $Qry->fields 	 . ", '".$param->random."'";
    } 

    $checke 			= $Qry->exe_INSERT($con);
 
    }

    }       

    $x++;
}
 
$return = json_encode( array('status'=>'success','reject'=>$reject, 'err_code' => mysqli_error($con)) );

print $return;
mysqli_close($con);

function getTimesheetPayPeriods( $con, $date ){ // unused function
    $data = array();	
    $Qry = new Query();	
    $Qry->table     = "tbltimesheet";
    $Qry->selected  = "id_payperiod";
    $Qry->fields    = "date='".$date."' ORDER BY id ASC limit 1";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $data = array( 
                "id"        => $row['id_payperiod'],
        
            );
        }
        return $data;
    }
    return 0;
}

function getSuperiorid( $con,$idacct ){
    $Qry 			= new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "idsuperior";
    $Qry->fields    = "id = '".$idacct."'";
    $rs = $Qry->exe_SELECT($con);
    return mysqli_num_rows($rs) >= 1 ? mysqli_fetch_array($rs)['idsuperior'] : '';
}

//naigo

function getApproverss($con, $idacct, $approver, $creator){	
    $idapplied = $creator;
	$Qry = new Query();	
	$Qry->table ="tblaccountjob";	
	$Qry->selected ="idunit";
	$Qry->fields ="idacct='".$idacct."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
           $idunit =  $row['idunit']; //set unit/department
            
            $Qry2 = new Query();	
            $Qry2->table ="tblappmatrix";	
            $Qry2->selected ="
            approver_1a, approver_1b, approver_1c,
            approver_type_1a, approver_type_1b, approver_type_1c,
	        approver_unit_1a, approver_unit_1b, approver_unit_1c,
            approver_2a, approver_2b, approver_2c,
            approver_type_2a, approver_type_2b, approver_type_2c,
            approver_unit_2a, approver_unit_2b, approver_unit_2c,
            approver_3a, approver_3b, approver_3c,
            approver_type_3a, approver_type_3b, approver_type_3c,
            approver_unit_3a, approver_unit_3b, approver_unit_3c,
            approver_4a, approver_4b, approver_4c,
            approver_type_4a, approver_type_4b, approver_type_4c,
            approver_unit_4a, approver_unit_4b, approver_unit_4c";
            $Qry2->fields ="idunit='".$idunit."'";
            $rs2 = $Qry2->exe_SELECT($con);
            if(mysqli_num_rows($rs2)>= 1){
                if($row2=mysqli_fetch_assoc($rs2)){
                    if($approver == 'approver1'){
                        if($row2['approver_type_1a']){
                            if($row2['approver_type_1b'] ){
                                if($row2['approver_type_1c']){
                                    if($row2['approver_type_1a'] == 1){
                                        $insertapprover1a =  geidhead($con, $row2['approver_unit_1a']);

                                        if($insertapprover1a == $idapplied){
                                            $insertapprover1a = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover1a = $row2['approver_1a'];

                                        if($insertapprover1a == $idapplied){
                                            $insertapprover1a = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    if($row2['approver_type_1b'] == 1){
                                        $insertapprover1b =  geidhead($con, $row2['approver_unit_1b']);

                                        if($insertapprover1b == $idapplied){
                                            $insertapprover1b = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover1b = $row2['approver_1b'];

                                        if($insertapprover1b == $idapplied){
                                            $insertapprover1b = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    if($row2['approver_type_1c'] == 1){
                                        $insertapprover1c =  geidhead($con, $row2['approver_unit_1c']);

                                        if($insertapprover1c == $idapplied){
                                            $insertapprover1c = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover1c = $row2['approver_1c'];

                                        if($insertapprover1c == $idapplied){
                                            $insertapprover1c = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    return $insertapprover1a . ',' . $insertapprover1b  . ',' . $insertapprover1c;
                                }else{
                                    if($row2['approver_type_1a'] == 1){
                                        $insertapprover1a =  geidhead($con, $row2['approver_unit_1a']);

                                        if($insertapprover1a == $idapplied){
                                            $insertapprover1a = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover1a = $row2['approver_1a'];

                                        if($insertapprover1a == $idapplied){
                                            $insertapprover1a = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    if($row2['approver_type_1b'] == 1){
                                        $insertapprover1b =  geidhead($con, $row2['approver_unit_1b']);

                                        if($insertapprover1b == $idapplied){
                                            $insertapprover1b = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover1b = $row2['approver_1b'];

                                        if($insertapprover1b == $idapplied){
                                            $insertapprover1b = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    return $insertapprover1a . ',' . $insertapprover1b;
                                }
                            }else{
                                if($row2['approver_type_1a'] == 1){
                                    $insertapprover1a =  geidhead($con, $row2['approver_unit_1a']);

                                    if($insertapprover1a == $idapplied){
                                        $insertapprover1a = getSuperiorid( $con, $idapplied);
                                    }

                                    return $insertapprover1a;
                                }else{
                                    $insertapprover1a = $row2['approver_1a'];

                                    if($insertapprover1a == $idapplied){
                                        $insertapprover1a = getSuperiorid( $con, $idapplied);
                                    }

                                    return $insertapprover1a;
                                }
                             
                            }
                        }
                    }else if($approver == 'approver2'){
                        if($row2['approver_type_2a']){
                            if($row2['approver_type_2b'] ){
                                if($row2['approver_type_2c']){
                                    if($row2['approver_type_2a'] == 1){
                                        $insertapprover2a =  geidhead($con, $row2['approver_unit_2a']);

                                        if($insertapprover2a == $idapplied){
                                            $insertapprover2a = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover2a = $row2['approver_2a'];

                                        if($insertapprover2a == $idapplied){
                                            $insertapprover2a = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    if($row2['approver_type_2b'] == 1){
                                        $insertapprover2b =  geidhead($con, $row2['approver_unit_2b']);

                                        if($insertapprover2b == $idapplied){
                                            $insertapprover2b = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover2b = $row2['approver_2b'];

                                        if($insertapprover2b == $idapplied){
                                            $insertapprover2b = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    if($row2['approver_type_2c'] == 1){
                                        $insertapprover2c =  geidhead($con, $row2['approver_unit_2c']);

                                        if($insertapprover2c == $idapplied){
                                            $insertapprover2c = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover2c = $row2['approver_2c'];

                                        if($insertapprover2c == $idapplied){
                                            $insertapprover2c = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    return $insertapprover2a . ',' . $insertapprover2b  . ',' . $insertapprover2c;
                                }else{
                                    if($row2['approver_type_2a'] == 1){
                                        $insertapprover2a =  geidhead($con, $row2['approver_unit_2a']);

                                        if($insertapprover2a == $idapplied){
                                            $insertapprover2a = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover2a = $row2['approver_2a'];

                                        if($insertapprover2a == $idapplied){
                                            $insertapprover2a = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    if($row2['approver_type_2b'] == 1){
                                        $insertapprover2b =  geidhead($con, $row2['approver_unit_2b']);

                                        if($insertapprover2b == $idapplied){
                                            $insertapprover2b = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover2b = $row2['approver_2b'];

                                        if($insertapprover2b == $idapplied){
                                            $insertapprover2b = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    return $insertapprover2a . ',' . $insertapprover2b;
                                }
                            }else{
                                if($row2['approver_type_2a'] == 1){
                                    $insertapprover2a =  geidhead($con, $row2['approver_unit_2a']);

                                    if($insertapprover2a == $idapplied){
                                        $insertapprover2a = getSuperiorid( $con, $idapplied);
                                    }

                                    return $insertapprover2a;
                                }else{
                                    $insertapprover2a = $row2['approver_2a'];

                                    if($insertapprover2a == $idapplied){
                                        $insertapprover2a = getSuperiorid( $con, $idapplied);
                                    }

                                    return $insertapprover2a;
                                }
                             
                            }
                        }
                    }else if($approver == 'approver3'){
                        if($row2['approver_type_3a']){
                            if($row2['approver_type_3b'] ){
                                if($row2['approver_type_3c']){
                                    if($row2['approver_type_3a'] == 1){
                                        $insertapprover3a =  geidhead($con, $row2['approver_unit_3a']);

                                        if($insertapprover3a == $idapplied){
                                            $insertapprover3a = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover3a = $row2['approver_3a'];

                                        if($insertapprover3a == $idapplied){
                                            $insertapprover3a = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    if($row2['approver_type_3b'] == 1){
                                        $insertapprover3b =  geidhead($con, $row2['approver_unit_3b']);

                                        if($insertapprover3b == $idapplied){
                                            $insertapprover3b = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover3b = $row2['approver_3b'];

                                        if($insertapprover3b == $idapplied){
                                            $insertapprover3b = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    if($row2['approver_type_3c'] == 1){
                                        $insertapprover3c =  geidhead($con, $row2['approver_unit_3c']);

                                        if($insertapprover3c == $idapplied){
                                            $insertapprover3c = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover3c = $row2['approver_3c'];

                                        if($insertapprover3c == $idapplied){
                                            $insertapprover3c = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    return $insertapprover3a . ',' . $insertapprover3b  . ',' . $insertapprover3c;
                                }else{
                                    if($row2['approver_type_3a'] == 1){
                                        $insertapprover3a =  geidhead($con, $row2['approver_unit_3a']);

                                        if($insertapprover3a == $idapplied){
                                            $insertapprover3a = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover3a = $row2['approver_3a'];

                                        if($insertapprover3a == $idapplied){
                                            $insertapprover3a = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    if($row2['approver_type_3b'] == 1){
                                        $insertapprover3b =  geidhead($con, $row2['approver_unit_3b']);

                                        if($insertapprover3b == $idapplied){
                                            $insertapprover3b = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover3b = $row2['approver_3b'];

                                        if($insertapprover3b == $idapplied){
                                            $insertapprover3b = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    return $insertapprover3a . ',' . $insertapprover3b;
                                }
                            }else{
                                if($row2['approver_type_3a'] == 1){
                                    $insertapprover3a =  geidhead($con, $row2['approver_unit_3a']);

                                    if($insertapprover3a == $idapplied){
                                        $insertapprover3a = getSuperiorid( $con, $idapplied);
                                    }

                                    return $insertapprover3a;
                                }else{
                                    $insertapprover3a = $row2['approver_3a'];

                                    if($insertapprover3a == $idapplied){
                                        $insertapprover3a = getSuperiorid( $con, $idapplied);
                                    }

                                    return $insertapprover3a;
                                }
                             
                            }
                        }
                    }else if($approver == 'approver4'){
                        if($row2['approver_type_4a']){
                            if($row2['approver_type_4b'] ){
                                if($row2['approver_type_4c']){
                                    if($row2['approver_type_4a'] == 1){
                                        $insertapprover4a =  geidhead($con, $row2['approver_unit_4a']);

                                        if($insertapprover4a == $idapplied){
                                            $insertapprover4a = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover4a = $row2['approver_4a'];

                                        if($insertapprover4a == $idapplied){
                                            $insertapprover4a = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    if($row2['approver_type_4b'] == 1){
                                        $insertapprover4b =  geidhead($con, $row2['approver_unit_4b']);

                                        if($insertapprover4b == $idapplied){
                                            $insertapprover4b = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover4b = $row2['approver_4b'];

                                        if($insertapprover4b == $idapplied){
                                            $insertapprover4b = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    if($row2['approver_type_4c'] == 1){
                                        $insertapprover4c =  geidhead($con, $row2['approver_unit_4c']);

                                        if($insertapprover4c == $idapplied){
                                            $insertapprover4c = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover4c = $row2['approver_4c'];

                                        if($insertapprover4c == $idapplied){
                                            $insertapprover4c = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    return $insertapprover4a . ',' . $insertapprover4b  . ',' . $insertapprover4c;
                                }else{
                                    if($row2['approver_type_4a'] == 1){
                                        $insertapprover4a =  geidhead($con, $row2['approver_unit_4a']);

                                        if($insertapprover4a == $idapplied){
                                            $insertapprover4a = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover4a = $row2['approver_4a'];

                                        if($insertapprover4a == $idapplied){
                                            $insertapprover4a = getSuperiorid( $con, $idapplied);
                                        }
                                    }

                                    if($row2['approver_type_4b'] == 1){
                                        $insertapprover4b =  geidhead($con, $row2['approver_unit_4b']);

                                        if($insertapprover4b == $idapplied){
                                            $insertapprover4b = getSuperiorid( $con, $idapplied);
                                        }
                                    }else{
                                        $insertapprover4b = $row2['approver_4b'];

                                        if($insertapprover4b == $idapplied){
                                            $insertapprover4b = getSuperiorid( $con, $idapplied);
                                        }
                                    }
                                    
                                    return $insertapprover4a . ',' . $insertapprover4b;
                                }
                            }else{
                                if($row2['approver_type_4a'] == 1){
                                    $insertapprover4a =  geidhead($con, $row2['approver_unit_4a']);

                                    if($insertapprover4a == $idapplied){
                                        $insertapprover4a = getSuperiorid( $con, $idapplied);
                                    }

                                    return $insertapprover4a;
                                }else{
                                    $insertapprover4a = $row2['approver_4a'];

                                    if($insertapprover4a == $idapplied){
                                        $insertapprover4a = getSuperiorid( $con, $idapplied);
                                    }

                                    return $insertapprover4a;
                                }
                            
                            }
                        }
                    }else{
                        return '';
                    }
                }
            }
		}
	}
}

function geidhead($con,$id){
    $Qry = new Query();	
	$Qry->table ="tblbunits";	
	$Qry->selected ="idhead";
	$Qry->fields ="id='".$id."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){ 
        return mysqli_fetch_assoc($rs)['idhead'];
    }
}
function obExists($con, $date, $idacct){
	$Qry = new Query();	
	$Qry->table ="tbltimeobtrip";
	$Qry->selected ="id";
	$Qry->fields =" idacct='".$idacct."' AND date='".$date."' AND stat NOT IN (4,2) AND id NOT IN (SELECT idapp FROM tblappcancel WHERE `type` = 'obtrip' AND STATUS = 1) ";
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs) >= 1;
}
?>