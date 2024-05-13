<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$date=SysDate();
$time=SysTime();
$return = null;	
// $pay_period = getPayPeriod($con);


if(payperiodstatus($con) == 1){
    $myData = array('status' => 'closed');
    $return = json_encode($myData);
    print $return;	
    return;
}

// //Validate parameters
// if( empty($param['info']['sdate']) ){
// 	$return = json_encode(array('status'=>'sdate'));
// 	print $return;	
// 	mysqli_close($con);
// 	return;
// }
// if( empty($param['info']['fdate']) ){
// 	$return = json_encode(array('status'=>'fdate'));
// 	print $return;	
// 	mysqli_close($con);
// 	return;
// }
// if( /*( strtotime( $param['info']['sdate'] ) > strtotime( $param['info']['fdate']  ) ) ||
// 	( strtotime( $param['info']['sdate'] ) < strtotime( $pay_period['pay_start'] ) ) ||
// 	( strtotime( $param['info']['fdate'] ) > strtotime( $pay_period['pay_end']   ) ) */
// 	( strtotime( $param['info']['sdate']) != strtotime( $pay_period['pay_start'] ) ) ||
// 	( strtotime( $param['info']['fdate']) != strtotime( $pay_period['pay_end'] ) )
// ){
// 	$return = json_encode(array('status'=>'invdates'));
// 	print $return;	
// 	mysqli_close($con);
// 	return;
// }

if(!empty($param['accountid'])){
	$idpayperiod = array(  
        "period"		=> getPayPeriod($con),
    );

	if( $idpayperiod['period']['id'] > 0 ){
		$Qry3           = new Query();

		if($idpayperiod['period']['type'] == 'Helper'){
			$Qry3->table    = "tblpayperiod_helper";
		}else if($idpayperiod['period']['type'] == 'Japanese'){
			$Qry3->table    = "tblpayperiod_japanese";
		}else if($idpayperiod['period']['type'] == 'Japanese Conversion'){
			$Qry3->table    = "tblpayperiod_japaneseconversion";
		}else{
			$Qry3->table    = "tblpayperiod";
		}

		$Qry3->selected = "tkstatus='1',dateclosed='".$date.' '.$time."',idby='".$param['accountid']."',headno='".getTSCtr($con,$idpayperiod['period']['id'])."'";
		$Qry3->fields   = "id = '".$idpayperiod['period']['id']."'";
		$checke 		= $Qry3->exe_UPDATE($con);	
		if( $checke ){
			$return = json_encode(array('status'=>'success','q'=>$Qry3->fields,'headno'=>getTSCtr($con,$idpayperiod['period']['id'])));

			drlock($con,$businessunit = '',$idpayperiod['period']['id']);

		}else{
			$return = json_encode(array('status'=>'error','q'=>mysqli_error($con),'headno'=>0));
		}
	}else{
		$return = json_encode(array('status'=>'error','q'=>'1','headno'=>0));
	}
}else{
	$return = json_encode(array('status'=>'error','q'=>'','headno'=>0));
}

print $return;
mysqli_close($con);

function getIDPayperiod($con,$param){
	$Qry 			= new Query();	
	$Qry->table     = "vw_payperiod";
	$Qry->selected  = "id";
	$Qry->fields    = "period_start='".$param['info']['sdate']."' AND period_end='".$param['info']['fdate']."' ORDER BY id ASC LIMIT 1";
	$rs				= $Qry->exe_SELECT($con);		
	if(mysqli_num_rows($rs)>=1){
		if($row=mysqli_fetch_array($rs)){
			return (int)$row['id'];
		}
	}
	return 0;
}

function getTSCtr($con, $id_payperiod){
	$Qry 			= new Query();	
	$Qry->table     = "tbltimesheet";
	$Qry->selected  = "count(id) as ctr";
	$Qry->fields    = "id_payperiod='".$id_payperiod."'";
	$rs				= $Qry->exe_SELECT($con);		
	if(mysqli_num_rows($rs)>=1){
		if($row=mysqli_fetch_array($rs)){
			return (int)$row['ctr'];
		}
	}
	return 0;
}

function payperiodstatus($con){
	$idpayperiod = array(  
        "period"		=> getPayPeriod($con),
    );


    $Qry = new Query();	
    $Qry->table         = "vw_payperiod_all";
    $Qry->selected      = "*";
    //$Qry->fields        = "type ='".$idpayperiod['period']['type']."' AND id = '".$idpayperiod['period']['id']."'";
    $Qry->fields        = "type ='ho' AND id = '".$idpayperiod['period']['id']."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['tkstatus'];
        }
    }
    
}

function drlock($con,$businessunit,$payid){
	$Qrybunit=new Query(); 
	$Qrybunit->table="vw_databusinessunits";
	$Qrybunit->selected="id";
	$Qrybunit->fields="isactive = 1 AND unittype = 3 ";
	$rsbunit=$Qrybunit->exe_SELECT($con);
	if(mysqli_num_rows($rsbunit)>=1){
		while($row=mysqli_fetch_array($rsbunit)){
			$Qry=new Query();
			$Qry->table="tbldutyrosterstat";
			$Qry->selected="*";
			$Qry->fields="id_department = '".$row['id']."'  AND id_payperiod = '".$payid."'";
			$rs=$Qry->exe_SELECT($con);
			if(mysqli_num_rows($rs)>=1){
				$Qry2           = new Query();
				$Qry2->table    = "tbldutyrosterstat";
				$Qry2->selected = "status='1',  date_close ='" . SysDate() . "', time_close='" . SysTime() . "'";
				$Qry2->fields   = "id_department = '".$row['id']."'  AND id_payperiod = '".$payid."'";                    
				$checke = $Qry2->exe_UPDATE($con);	
			}else{
				$Qry2           = new Query();
				$Qry2->table    = "tbldutyrosterstat";
				$Qry2->selected = "status,id_department,id_payperiod,date_close,time_close";
				$Qry2->fields   = "'1',
									'".$row['id']."',
									'".$payid."',
									'" . SysDate() . "',
									'" . SysTime() . "'";                   
				$checke = $Qry2->exe_INSERT($con);
			}
		}
	}

}

?>