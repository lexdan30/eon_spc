<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 
session_start();

$param = $_POST;
$date_create=SysDate();
$time_create=SysTime();
$return = null;

$findhr = array('1');

if (in_array($param['info']['idleave'], $findhr)){
    $approver1 = getHrid($con, $param['info']['idleave']);
    $approver2 = getApprovers($con, $param['info']['acct'], 'approver1', $param['accountid']);
    $approver3 = getApprovers($con, $param['info']['acct'], 'approver2', $param['accountid']);
    $approver4 = getApprovers($con, $param['info']['acct'], 'approver3', $param['accountid']);
}else{
    $approver1 = getApprovers($con, $param['info']['acct'], 'approver1', $param['accountid']);
    $approver2 = getApprovers($con, $param['info']['acct'], 'approver2', $param['accountid']);
    $approver3 = getApprovers($con, $param['info']['acct'], 'approver3', $param['accountid']);
    $approver4 = '';
}

if($approver1){
    if(!empty($param['accountid'])){
        if( $param['info']['acct'] ){
            if( $param['info']['idleave'] ){
                if( $param['info']['datefrom'] ){
                    if( $param['info']['dateto'] ){
                        $date1	= strtotime( $param['info']['datefrom'] );
                        $date2	= strtotime( $param['info']['dateto'] );
                        if( $date1 <= $date2 ){

                            if( array_key_exists('file',$_FILES) ){
                                $valid_formats = array("jpg", "png", "jpeg", "pdf");	
                                foreach ($_FILES['file']['name'] as $f => $name) { 
                                    if ($_FILES['file']['error'][$f] == 4) {
                                        $return = json_encode(array('status'=>'error','on'=>'img_check'));
                                        print $return;	
                                        mysql_close($con);
                                        return;
                                    }
                                    if ($_FILES['file']['error'][$f] == 0) {
                                        if(!in_array(pathinfo(strtolower($name), PATHINFO_EXTENSION), $valid_formats) ){
                                            $return = json_encode(array('status'=>'error-upload-type'));
                                            print $return;	
                                            mysql_close($con);
                                            return;
                                        }
                                    }
                                }
                            }
                            
                            $idleave		= $param['info']['idleave'];
                            $idacct			= $param['info']['acct'];
                            $remarks 		= ucwords(strtolower(str_replace("'","",$param['info']['remarks'])));
                            $total_units	= 0;
                            
                            $reject			= array();
                            $ctr=1;
                            $fileuploads = 0;
                            foreach( $param['info']['leave_dates']  as $keys=>$value ){
                                $idshift		= $value['idshift'];
                                $idtimeleavetype= $value['val'];	
                                $date			= $value['date'];
                                
                                
                                if( leaveExists($con, $date, $idacct, $idtimeleavetype) ){
                                    $reject[]	= array(
                                        "date"	=> $date,
                                        "msg"	=> "Already has leave application for this date."
                                    );
                                }
                                // elseif( hasTimelogs($con, $date, $idacct) ){
                                //     $reject[]	= array(
                                //         "date"	=> $date,
                                //         "msg"	=> "Time logs exists."
                                //     );
                                // }
                                elseif( hasAdjustment($con, $date, $idacct, $idtimeleavetype) ){
                                    $reject[]	= array(
                                        "date"	=> $date,
                                        "msg"	=> "Applied Attendance Adjustment."
                                    );
                                }elseif( (int)$value['hrs'] > 0 ){
                                    $leave_balance  = getLeaveBalance($con, $param['info']['acct'], $param['info']['idleave']);
                                    $pending_bal	= getPendingBalance($con, $idleave , $idacct);								
                                    $hrs			= (int)$value['hrs'];
                                    if( $hrs <= $leave_balance ){
                                        $total_units 	= $total_units + (int)$value['hrs'];
                                        $shifttime = getShiftTime( $con, $idshift, $idtimeleavetype );
                                        $stime	   = $shifttime['stime'];
                                        $ftime	   = $shifttime['ftime'];
                                        $time 	   = time();
                                        $docnumber = "LV".$param['info']['acct'].strtotime( $date_create.$time ).$time.$ctr;
                                        $ctr++;
                                        $Qry 			= new Query();	
                                        $Qry->table 	= "tbltimeleaves";
                                        $Qry->selected 	= "creator, docnumber, idleave, idacct, idshift, idtimeleavetype, date, stime, ftime, hrs, date_create, approver1";
                                        
                                        $id_period		= getTimesheetPayPeriods($con, $date);
                                        if( (int)$id_period	== 0 ){
                                            $id_period		= getLatePayPeriod($con,$date);
                                        }
                                    
                                        
                                        $Qry->fields 	= "'".$param['accountid']."', '".$docnumber."','".$idleave."', '".$idacct."', '".$idshift."', '".$idtimeleavetype."', '".$date."', '".$stime."', '".$ftime."', '".$hrs."', '".$date_create."', '".$approver1."'";
                                        if( !empty( $remarks ) ){
                                            $Qry->selected 	= $Qry->selected . ", remarks";
                                            $Qry->fields 	= $Qry->fields 	 . ", '".$remarks."'";
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
                                        $checke 			= $Qry->exe_INSERT($con);

                                        if($checke){										
                                            $lastID = getLastID($con,$docnumber);
                                            if( array_key_exists('file',$_FILES) ){
                                                if($fileuploads != 0){
                                                    $folder = $_SESSION['foldername'];
                                                    updateData($con,$folder,$lastID);
                                                }else{
                                                    $folder_path = $param['targetPath'].'req-'.$lastID;
                                                    $folder_name = 'req-'.$lastID;
                                                    if( !is_dir($folder_path) ){
                                                        mkdir($folder_path);	
                                                    }
                                                    $_SESSION['foldername'] = $folder_name;	
                                                    $check = updateData($con,$folder_name,$lastID);
                                                    if( $check ){										
                                                        $fileCtr=1;
                                                        foreach ($_FILES['file']['name'] as $f => $name) {
                                                            $t=strtotime($date_create).time();	
                                                            $extMove = pathinfo($name, PATHINFO_EXTENSION);
                                                            $save_name	= $fileCtr.'-'.$t.'.'.$extMove;	
                                                            move_uploaded_file($_FILES["file"]["tmp_name"][$f], $folder_path.'/'.$save_name);

                                                            $fileCtr++;
                                                        }																	
                                                    }	
                                                }									
                                            }
                                            $pending_bal = $pending_bal + $hrs;
                                            $return = updatePendingBal($con, $idleave , $idacct, $pending_bal);
                                        }
                                    }else{
                                        $reject[]	= array(
                                            "date"	=> $date,
                                            "msg"	=> "No more leave balance. LB=>'".$leave_balance."' PB=>'".$pending_bal."' CH=>'".$hrs."' TOTAL=>'".$total_units."'"
                                        );
                                    }
                                }
                                $fileuploads++;
                            }
                            
                            // AUTO EMAIL ??
                            
                            $return = json_encode( array('status'=>'success','reject'=>$reject, 'error'=>mysqli_error($con)) );
                            
                        }else{
                            $return = json_encode(array('status'=>'invdate'));
                        }
                    }else{
                        $return = json_encode(array('status'=>'dateto'));
                    }
                }else{
                    $return = json_encode(array('status'=>'datefrom'));
                }
            }else{
                $return = json_encode(array('status'=>'idleave'));
            }
        }else{
            $return = json_encode(array('status'=>'acct'));
        }
    }else{
        $return = json_encode(array('status'=>'notloggedin'));
    }
}else{
    $return = json_encode(array('status'=>'approver'));
}

print $return;
mysqli_close($con);

function hasTimelogs($con, $date, $idacct){
	$Qry = new Query();	
	$Qry->table ="vw_data_timesheet AS a";
	$Qry->selected ="id";
	$Qry->fields =" a.idacct='".$idacct."' AND a.work_date = '".$date."' AND ( a.in IS NOT NULL AND a.out IS NOT NULL AND a.in<>'' AND a.out<>'' )";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){
		return true;
	}
	return false;
}

function leaveExists($con, $date, $idacct, $idtimeleavetype){
	$Qry = new Query();	
	$Qry->table ="tbltimeleaves";
	$Qry->selected ="*";
	$Qry->fields =" idacct='".$idacct."' AND date='".$date."'  AND stat in ('1','3') AND cancelby is NULL";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){		
		while($row=mysqli_fetch_array($rs)){
			if( $row['idtimeleavetype'] == $idtimeleavetype ){
				return true;
			}elseif( (int)$row['idtimeleavetype'] == 1 && ((int)$idtimeleavetype==2 || (int)$idtimeleavetype==3) ){
				return true;
			}elseif( (int)$idtimeleavetype == 1 && ((int)$row['idtimeleavetype']==2 || (int)$row['idtimeleavetype']==3) ){
				return true;
			}
		}
	}
	return false;
}

function hasAdjustment($con, $date, $idacct, $idtimeleavetype){
	$Qry = new Query();	
	$Qry->table ="tbltimeadjustment";
	$Qry->selected ="*";
	$Qry->fields =" idacct='".$idacct."' AND date='".$date."' AND stat in ('1','3') AND cancelby IS null";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){
		while($row=mysqli_fetch_array($rs)){
			if( (int)$row['idtimetype'] == 1 ){
				return true;
			}elseif( (int)$row['idtimetype'] <= $idtimeleavetype ){
				return true;
			}elseif( (int)$row['idtimetype'] == 3 && ( (int)$idtimeleavetype == 1 || (int)$idtimeleavetype == 3 ) ){
				return true;
			}
		}
	}
	return false;
}

function updatePendingBal($con, $idleave , $idacct, $pending_bal){
	$Qry 			= new Query();	
	$Qry->table 	= "tblaccountleaves";	
	$Qry->selected 	= "pending_bal='".$pending_bal."'";
	$Qry->fields   	= "idacct='".$idacct."' AND idleave='".$idleave."'";
	$ch = $Qry->exe_UPDATE($con);
	if( $ch ){
		return json_encode(array('status'=>'success'));
	}else{
		return json_encode(array('status'=>'error','on'=>'pendingBal update'));
	}
}

function getPendingBalance($con, $idleave , $idacct){
	$Qry = new Query();	
	$Qry->table     = "tblaccountleaves";
	$Qry->selected  = "pending_bal";
	$Qry->fields    = "idacct='".$idacct."' AND idleave='".$idleave."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			return $row['pending_bal'];
		}
	}
	return 0;
}

function updateData($con,$folder_name,$lastID){	
	$Qry = new Query();	
	$Qry->table ="tbltimeleaves";	
	$Qry->selected ="file='".$folder_name."'";
	$Qry->fields ="id='".$lastID."'";
	return $Qry->exe_UPDATE($con);
}

function getLastID($con, $ticket ){
	$return='';
	$Qry = new Query();	
	$Qry->table ="tbltimeleaves";
	$Qry->selected ="id";
	$Qry->fields ="docnumber='".$ticket."' ORDER BY id DESC LIMIT 1";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){
		if($row=mysqli_fetch_array($rs)){
			$return = $row['id'];
		}
	}else{
		$return = '';
	}
	return $return;
}

function getSuperiorid( $con,$idacct ){
    $Qry 			= new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "idsuperior";
    $Qry->fields    = "id = '".$idacct."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['idsuperior'];
        }
    }
    return '';
}

function getApprovers($con, $idacct, $approver, $creator){	
    $idapplied = $creator;
	$Qry = new Query();	
	$Qry->table ="tblaccountjob";	
	$Qry->selected ="idunit";
	$Qry->fields ="idacct='".$idacct."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
           $idunit =  $row['idunit'];
            
            $Qry2 = new Query();	
            $Qry2->table ="tblappmatrix";	
            $Qry2->selected ="*";
            $Qry2->fields ="idunit='".$idunit."'";
            $rs2 = $Qry2->exe_SELECT($con);
            if(mysqli_num_rows($rs2)>= 1){
                if($row2=mysqli_fetch_array($rs2)){
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
        if($row=mysqli_fetch_array($rs)){
            return $row['idhead'];
        }
    }
}

function getHrid($con, $idleave){
    $Qry = new Query();	
	$Qry->table ="tblleaves";	
	$Qry->selected ="name";
	$Qry->fields ="id='".$idleave."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $name =  $row['name'];

            $Qry2 = new Query();	
            $Qry2->table ="tblpreference";	
            $Qry2->selected ="value";
            $Qry2->fields ="alias='".$name."'";
            $rs2 = $Qry2->exe_SELECT($con);
            if(mysqli_num_rows($rs2)>= 1){
                if($row2=mysqli_fetch_array($rs2)){
                    $approver = $row2['value'];

                    $Qry3 = new Query();	
                    $Qry3->table ="vw_dataemployees";	
                    $Qry3->selected ="id";
                    $Qry3->fields ="post='".$approver."' LIMIT 1";
                    $rs3 = $Qry3->exe_SELECT($con);
                    if(mysqli_num_rows($rs3)>= 1){
                        if($row3=mysqli_fetch_array($rs3)){
                            return $row3['id'];         
                        }
                    }

                }
            }

        }
    }


}

function getTimesheetPayPeriods( $con, $date ){
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
?>