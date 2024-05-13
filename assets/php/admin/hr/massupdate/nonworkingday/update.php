<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php');
require_once('../../../../email/emailFunction.php');

    $param 	= $_POST;
    $date 	= SysDate();
    $time 	= SysTime();
    $return = null;

    $shift = '';

    $shift = getShiftIDData($con, $param['entry']['shift']);

    if($shift[2]==null||$shift[2]==''){
        $shift[2]='00:00:00';
    }

    if($shift[3]==null||$shift[3]==''){
        $shift[3]='00:00:00';
    }
    
    // //Update to 201 tbltimesheet column idrevenue
    // $Qryb 			= new Query();	
    // $Qryb->table 	= "tbltimesheet";
    // $Qryb->selected = "idshift	= '".$param['entry']['idshift']."', shiftin = '".$shift[2]."', shiftout = '".$shift[3]."' ";
    // $Qryb->fields 	= "idacct = '".$param['employees'][$key]['idacct']."'";
    // $checkentryb 	= $Qryb->exe_UPDATE($con); 
	
    if(!empty($param['accountid'])){
		
		$param['entry']['note'] = str_replace("'","",$param['entry']['note']);
				
        $Qry 			= new Query();	
        $Qry->table 	= "tblsdand";
        $Qry->selected 	= "idupdatedby, dateupdated, timeupdated, idunit, newidshift, newshift, typeshift, note, sdate, edate";
        $Qry->fields 	= " '".$param['accountid']."',
							'".SysDate()."',
                            '".SysTime()."',
                            '".$param['entry']['classif']."',
                            '".$param['entry']['shift']."',
							'".getStrShift($con, $param['entry']['shift'])."',
                            '".$param['entry']['typeshift']."',
                            '".$param['entry']['note']."',
                            '".$param['entry']['sdate']."',
                            '".$param['entry']['edate']."'";
        $checkentry 	= $Qry->exe_INSERT($con); 
		if( $checkentry ){
			$lastid = mysqli_insert_id($con);
			$arr_err = array();
			foreach($param['employees'] as $key=>$value){
				$Qrya 			= new Query();	
				$Qrya->table 	= "tblsdandlogs";
				$Qrya->selected = "idtblsdand, empid, idacct, lname, fname, mi, idunit, business_unit, oldidshift, oldshift, newidshift, newshift, sdate, edate";
				$Qrya->fields 	= " '".$lastid."',
                                    '".$param['employees'][$key]['id']."',
                                    '".$param['employees'][$key]['id']."',
									'".$param['employees'][$key]['lname']."',
									'".$param['employees'][$key]['fname']."',
									'".$param['employees'][$key]['mname']."',
									'".$param['employees'][$key]['id_unit']."',
									'".$param['employees'][$key]['business_unit']."',
									'".$param['employees'][$key]['idshift']."',
									'".$param['employees'][$key]['shiftstatus']."',
									'".$param['entry']['shift']."',
									'".getStrShift($con, $param['entry']['shift'])."',
									'".$param['employees'][$key]['startdate']."',
									'".$param['employees'][$key]['enddate']."'";
				
				$checkentrya 	= $Qrya->exe_INSERT($con); 
				if($checkentrya){
                    if(checkTimesheet($con, $param['employees'][$key]['id'], $param['employees'][$key]['startdate'])==true){
                        //Update to 201 tbltimesheet column idrevenue
                        $Qryb 			= new Query();	
                        $Qryb->table 	= "tbltimesheet";
                        $Qryb->selected = "idshift = '".$param['entry']['shift']."', shiftin = '".$shift[2]."', shiftout = '".$shift[3]."', absent = '0.00' ";
                        $Qryb->fields 	= "idacct = '".$param['employees'][$key]['id']."' AND date = '".$param['employees'][$key]['startdate']."'";
                        $checkentryb 	= $Qryb->exe_UPDATE($con); 
                    }else if(checkTimesheet($con, $param['employees'][$key]['id'], $param['employees'][$key]['startdate'])==false){
                        $day		    = date("l", strtotime($param['employees'][$key]['startdate']));
                        $Qryc 			= new Query();	
                        $Qryc->table 	= "tbltimesheet";
                        $Qryc->selected = "idacct,day,date,idshift,shiftin,shiftout,timein,timeout,late,ut,absent,reghrs,acthrs,excess,ot,np,id_payperiod,date_in, date_out";
                        $Qryc->fields 	= " '".$param['employees'][$key]['id']."',
                                            '".$day."',
                                            '".$param['employees'][$key]['startdate']."',
                                            '".$param['entry']['shift']."',
                                            '".$shift[2]."',
                                            '".$shift[3]."',
                                            '',
                                            '',
                                            '0.00',
                                            '0.00',
                                            '0.00',
                                            '0.00',
                                            '0.00',
                                            '0.00',
                                            '0.00',
                                            '0.00',
                                            '".getIdPayPeriod($con, $param['employees'][$key]['startdate'])."',
                                            '".$param['employees'][$key]['startdate']."',
                                            '".$param['employees'][$key]['enddate']."'";
                        
                        $checkentryc 	= $Qryc->exe_INSERT($con); 
                    }
				}else{
					$arr_err[] = array(
						"idacct" => $param['employees'][$key]['id'],
						"err"	 => mysqli_error($con)
					);
				}
			}

			$return = json_encode(array('status'=>'success','w'=>$arr_err));
			
		}else{
			$return = json_encode(array('status'=>'error','w'=>mysqli_error($con)));
		}
    }else{
        $return = json_encode(array('status'=>'notloggedin'));
    }

print $return;
mysqli_close($con);



// function getReqCtr($con){
//     $Qry=new Query();
//     $Qry->table="tblforms01";
//     $Qry->selected="count(id) as ctr";
//     $Qry->fields="id>0";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
//             return $row['ctr'];
//         }
//     }
//     return null;
// }

function getStrShift($con, $id){
    $Qry=new Query();
    $Qry->table="tblshift";
    $Qry->selected="name";
    $Qry->fields="id='".$id."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['name'];
        }
    }
    return null;
}

function getIdPayPeriod($con, $date){
    $Qry=new Query();
    $Qry->table="tblpayperiod";
    $Qry->selected="*";
    $Qry->fields="period_start<='".$date."' AND period_end>='".$date."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['id'];
        }
    }
    return null;
}

function checkTimesheet($con, $idacct, $date){
    $Qry=new Query();
    $Qry->table="tbltimesheet";
    $Qry->selected="*";
    $Qry->fields="idacct='".$idacct."' AND date='".$date."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return true;
        }
    }
    return false;
}


?>