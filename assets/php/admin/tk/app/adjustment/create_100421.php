<?php
error_reporting(0);
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 
session_start();


$param = $_POST;
$date_create=SysDate();
$time_create=SysTime();
$return = null;

if(!empty($param['accountid'])){
	if( $param['info']['acct'] ){
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
					
                    $idacct			= $param['info']['acct'];
					$remarks 		= ucwords(strtolower(str_replace("'","",$param['info']['remarks'])));					
					$reject			= array();
                    $ctr=1;
					$fileuploads = 0;
					$approver1 = getApprovers($con,  $idacct	, 'approver1',  $idacct	);
					foreach( $param['info']['leave_dates']  as $keys=>$value ){
                        $idshift		= $value['idshift'];

                        if($param['info']['timein']){
                            $timein			= $param['info']['timein'];
                        }else{
                            $timein			= $value['timein'];
                        }
                        
                        if($param['info']['timeout']){
                            $timeout		= $param['info']['timeout'];
                        }else{
                            $timeout		= $value['timeout'];
                        }
                      
                        
						if($value['doublepair'] == true){
                            if($param['info']['timein2']){
                                $timein2		=  $param['info']['timein2'];
                            }else{
                                $timein2		= $value['timein2'];
                            }


                            if($param['info']['timeout2']){
                                $timeout2		=  $param['info']['timeout2'];
                            }else{
                                $timeout2		= $value['timeout2'];
                            }
						
						}else{
							$timein2 = '';
							$timeout2= '';
                        }
                        

                        $idtimeleavetype= $value['val'];
						$date			= $value['date'];
						$doubleshift	= $value['doublepair'];
						
						
						if( adjustmentExists($con, $date, $idacct, $idtimeleavetype) ){
							$reject[]	= array(
								"date"	=> $date,
								"msg"	=> "Already has adjustment application for this date."
							);
						}elseif( has2Adjustments($con, $pay_period, $idacct, $idtimeleavetype) ){
							$reject[]	= array(
								"date"	=> $date,
								"msg"	=> "You can file 2 Attendance Adjustment only per cut-off"
							);
						}elseif( hasTimelogs($con, $date, $idacct, $doubleshift) ){
							$reject[]	= array(
								"date"	=> $date,
								"msg"	=> "Time logs exists."
							);
						}elseif( hasLeave($con, $date, $idacct, $idtimeleavetype) ){
							$reject[]	= array(
								"date"	=> $date,
								"msg"	=> "Applied Leave."
							);
						}elseif( (int)$value['hrs'] > 0 ){
											
							$hrs	   = (int)$value['hrs'];														
							//$shifttime = getShiftTime( $con, $idshift, $idtimeleavetype );
							//$stime	   = $shifttime['stime'];
							//$ftime	   = $shifttime['ftime'];
							$time 	   = time();
							$docnumber = "AA".$param['info']['acct'].strtotime( $date_create.$time ).$time.$ctr;
							$ctr++;
							$Qry 			= new Query();	
							$Qry->table 	= "tbltimeadjustment";
							//$Qry->selected 	= "creator, docnumber, idacct, idshift, idtimetype, date, stime, ftime, hrs, date_create, id_payperiod";
							$Qry->selected 	= "creator, docnumber, idacct, idshift, date, hrs, date_create, approver1, id_payperiod";
							$id_period		= getTimesheetPayPeriods($con, $date);
							if( (int)$id_period['id'] == 0 ){
								$id_period		= array();
								$id_period		= getLatePayPeriod($con,$date);
							}
							$Qry->fields 	= "'".$param['accountid']."', '".$docnumber."', '".$idacct."', '".$idshift."', '".$date."', '".$hrs."', '".$date_create."','". $approver1 ."', '".$id_period['id']."'";
							if( !empty( $remarks ) ){
								$Qry->selected 	= $Qry->selected . ", remarks";
								$Qry->fields 	= $Qry->fields 	 . ", '".$remarks."'";
							}
							if( !empty($timein) ){
								$Qry->selected 	= $Qry->selected . ", `stime`";
								$Qry->fields 	= $Qry->fields 	 . ", '".$timein."'";
							} 
							if( !empty($timein2) ){
								$Qry->selected 	= $Qry->selected . ", `sstime`";
								$Qry->fields 	= $Qry->fields 	 . ", '".$timein2."'";
							}
							if( !empty($timeout) ){
								$Qry->selected 	= $Qry->selected . ", `ftime`";
								$Qry->fields 	= $Qry->fields 	 . ", '".$timeout."'";
							} 
							if( !empty($timeout2) ){
								$Qry->selected 	= $Qry->selected . ", `sftime`";
								$Qry->fields 	= $Qry->fields 	 . ", '".$timeout2."'";
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
							}else{
								$reject[]	= array(
									"date"	=> $date,
									"msg"	=> mysqli_error($con)
								);
							}							
                        }
                        $fileuploads++;
                    }
                  
					
					// AUTO EMAIL ??
					
					$return = json_encode( array('status'=>'success','reject'=>$reject) );
					
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
		$return = json_encode(array('status'=>'acct'));
	}
}else{
	$return = json_encode(array('status'=>'notloggedin'));
}


print $return;
mysqli_close($con);

function hasTimelogs($con, $date, $idacct, $doubleshift){
	if($doubleshift){
		$Qry = new Query();	
		$Qry->table ="vw_data_timesheet AS a";
		$Qry->selected ="id";
		$Qry->fields =" a.idacct='".$idacct."' AND a.work_date = '".$date."' AND ( a.in IS NOT NULL AND a.out IS NOT NULL AND a.in<>'' AND a.out<>'' ) AND ( a.in2 IS NOT NULL AND a.out2 IS NOT NULL AND a.in2<>'' AND a.out2<>'' )";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>=1){
			return true;
		}
	}else{
		$Qry = new Query();	
		$Qry->table ="vw_data_timesheet AS a";
		$Qry->selected ="id";
		$Qry->fields =" a.idacct='".$idacct."' AND a.work_date = '".$date."' AND ( a.in IS NOT NULL AND a.out IS NOT NULL AND a.in<>'' AND a.out<>'' )";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>=1){
			return true;
		}
	}
	return false;
}

function adjustmentExists($con, $date, $idacct, $idtimeleavetype){
	$Qry = new Query();	
	$Qry->table ="tbltimeadjustment";
	$Qry->selected ="*";
	$Qry->fields =" idacct='".$idacct."' AND date='".$date."'  AND stat in ('1','3') AND cancelby IS null";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){		
		// while($row=mysqli_fetch_array($rs)){
		// 	if( $row['idtimetype'] == $idtimeleavetype ){
		// 		return true;
		// 	}elseif( (int)$row['idtimetype'] == 1 && ((int)$idtimeleavetype==2 || (int)$idtimeleavetype==3) ){
		// 		return true;
		// 	}elseif( (int)$idtimeleavetype == 1 && ((int)$row['idtimetype']==2 || (int)$row['idtimetype']==3) ){
		// 		return true;
		// 	}
		// }
		return true;
	}
	return false;
}

function has2Adjustments($con, $pay_period, $idacct, $idtimeleavetype){

	$pp= $pay_period['id'];
	$Qry = new Query();	
	$Qry->table ="tbltimeadjustment";
	$Qry->selected ="COUNT(*) as count";
	$Qry->fields =" idacct='".$idacct."' AND id_payperiod='".$pp."' AND stat in ('1','3') AND cancelby IS null";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){	
		if($row=mysqli_fetch_array($rs)){
			//print_r($row['count']);
            if($row['count'] >= 2){
				return true;			
			}
        }		
	}
	return false;
}

function hasLeave($con, $date, $idacct, $idtimeleavetype){
	$Qry = new Query();	
	$Qry->table ="tbltimeleaves";
	$Qry->selected ="*";
	$Qry->fields =" idacct='".$idacct."' AND date='".$date."' AND stat in ('1','3') ";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){
		// while($row=mysqli_fetch_array($rs)){
		// 	if( (int)$row['idtimeleavetype'] == 1 ){
		// 		return true;
		// 	}elseif( (int)$row['idtimeleavetype'] <= $idtimeleavetype ){
		// 		return true;
		// 	}elseif( (int)$row['idtimeleavetype'] == 3 && ( (int)$idtimeleavetype == 1 || (int)$idtimeleavetype == 3 ) ){
		// 		return true;
		// 	}
		// }
		return true;
	}
	return false;
}

function updateData($con,$folder_name,$lastID){	
	$Qry = new Query();	
	$Qry->table ="tbltimeadjustment";	
	$Qry->selected ="file='".$folder_name."'";
	$Qry->fields ="id='".$lastID."'";
	return $Qry->exe_UPDATE($con);
}

function getLastID($con, $ticket ){
	$return='';
	$Qry = new Query();	
	$Qry->table ="tbltimeadjustment";
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
                "id"        => (int)$row['id_payperiod']        
            );
        }
        return $data;
    }
    return array("id"=>0);
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
           $idunit =  $row['idunit']; //set unit/department
            
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
?>