<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$date_create=SysDate();
$time_create=SysTime();
$return = null;

if(!empty($param['accountid'])){
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
				
				$arr_acct		= explode(",",$param['info']['emp']);
				$remarks 		= ucwords(strtolower(str_replace("'","",$param['info']['remarks'])));					
				$reject			= array();
				$ctr=1;
				foreach( $arr_acct  as $kk=>$vv ){
					$idacct = $vv;
					foreach( $param['info']['shift_dates']  as $keys=>$value ){

						$oldshift		= $value['oldshiftid'];
						$idshift		= $value['newshiftid'];
						$date			= $value['date'];
						$time 	   = time();
						$docnumber = "CS".$idacct.strtotime( $date_create.$time ).$time.$ctr;
						$param['info']['date'] 	 = $value['date'];
						$param['info']['idacct'] = $idacct;
						$id_period		= getTimesheetPayPeriods($con, $date);
						if( (int)$id_period	== 0 ){
							$id_period		= getLatePayPeriod($con,$date);
						}
				

						if($idshift != ''){
							$Qry           = new Query();
                            $Qry->table    = "tbltimeshift";
                            $Qry->selected = "docnumber, 
                                                creator, 
                                                idacct,
												oldshift,
                                                idshift,
                                                date, 
                                                date_create, 
                                                id_payperiod, approver1, approver1_date, approver1_time, approver1_stat, stat";
                            $Qry->fields   = "'".$docnumber."',
                                                '".$param['accountid']."',
                                                '".$idacct."',
												'".$oldshift."',
                                                '".$idshift."',
                                                '".$date."',
                                                '".$date_create."',
                                                '".$id_period['id']."','".$param['accountid']."', '".SysDatePadLeft()."', '".SysTime()."', '1', '1'"; 
												
							if( !empty( $remarks ) ){
								$Qry->selected 	= $Qry->selected . ", remarks";
								$Qry->fields 	= $Qry->fields 	 . ", '".$remarks."'";
							}
							
							if( leaveExists($con, $date, $idacct) ){
								$reject[]	= array(
									"date"	=> $date,
									"idacct"=> $idacct,
									"msg"	=> "Already has leave application for this date."
								);
							}else{
								$rs = $Qry->exe_INSERT($con);
								if( $rs ){
									$timesheet_data = getTimeSheetData($con, $idacct, $date);
									if( !empty( $timesheet_data ) ){
										if( addToBackuptimesheet($con,$timesheet_data) ){
											$return = updateTimesheetLate($con, json_decode (json_encode ($param), FALSE), $idshift);
										}
									}
								}
							}
							
						}else{
							$reject[]	= array(
								"date"	=> $date,
								"idacct"=> $idacct,
								"msg"	=> "Change Shift not available in this date"
							);
						}					
					}
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
	$return = json_encode(array('status'=>'notloggedin'));
}


print $return;
mysqli_close($con);

function leaveExists($con, $date, $idacct){
	$Qry = new Query();	
	$Qry->table ="tbltimeleaves";
	$Qry->selected ="*";
	$Qry->fields =" idacct='".$idacct."' AND date='".$date."'  AND stat in ('1','3') AND cancelby is NULL";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){		
		return true;
	}
	return false;
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
                "id"        => $row['id_payperiod']
            );

            return $data;
        }
    }
    return 0;
}

?>