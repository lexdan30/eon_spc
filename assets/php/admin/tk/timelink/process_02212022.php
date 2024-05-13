<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$date=SysDate();
$time=SysTime();
$return = null;	
$timelogs = array();
$counter = 0;


if(!empty($param["info"]["sdate"])){
	if(payperiodstatus($con,$param["info"]["sdate"],$param["info"]["fdate"]) == 1){
		$myData = array('status' => 'closed');
		$return = json_encode($myData);
		print $return;	
		return;
	}
}else{
	$idpayperiod = array(  
		"period"		=> getPayPeriod($con),
	);
	
	$date = $idpayperiod['period']['pay_start'];
	$date1 = $idpayperiod['period']['pay_end'];
	if(payperiodstatus($con,$date,$date1) == 1){
		$myData = array('status' => 'closed');
		$return = json_encode($myData);
		print $return;	
		return;
	}
}

$pay_period = getPayPeriod($con);

$shift_cols = array("monday"	=>"idmon,mon,mon_in,mon_out,mon_brk", 
					"tuesday"	=>"idtue,tue,tue_in,tue_out,tue_brk",
					"wednesday"	=>"idwed,wed,wed_in,wed_out,wed_brk",
					"thursday"	=>"idthu,thu,thu_in,thu_out,thu_brk",
					"friday"	=>"idfri,fri,fri_in,fri_out,fri_brk",
					"saturday"	=>"idsat,sat,sat_in,sat_out,sat_brk", 
					"sunday"	=>"idsun,sun,sun_in,sun_out,sun_brk");
					

//Validate parameters
if( empty($param["info"]["sdate"]) ){
	$return = json_encode(array('status'=>'sdate'));
	print $return;	
	mysqli_close($con);
	return;
}
if( empty($param["info"]["fdate"]) ){
	$return = json_encode(array('status'=>'fdate'));
	print $return;	
	mysqli_close($con);
	return;
}
if( empty($param["info"]["batchid"]) ){
	$return = json_encode(array('status'=>'batchid'));
	print $return;	
	mysqli_close($con);
	return;
}
if( empty($param["info"]["machid"]) ){
	$return = json_encode(array('status'=>'machid'));
	print $return;	
	mysqli_close($con);
	return;
}
if( ( strtotime( $param["info"]["sdate"] ) > strtotime( $param["info"]["fdate"]  ) ) ||
	( strtotime( $param["info"]["sdate"] ) < strtotime( $pay_period['pay_start'] ) ) ||
	( strtotime( $param["info"]["fdate"] ) > strtotime( $pay_period['pay_end']   ) )
){
	$return = json_encode(array('status'=>'invdates'));
	print $return;	
	mysqli_close($con);
	return;
}
if( checkbatchid($con,$param["info"]["batchid"],$param["info"]["machid"]) ){
	$return = json_encode(array('status'=>'dupbatchid'));
	print $return;	
	mysqli_close($con);
	return;
}

$ids=0;
if( !empty( $param["info"]["classi"] ) ){
	$dept = $param["info"]["classi"];
	if (!empty($dept)) {
		$arr_id = array();
		$arr    = getHierarchy($con, $dept);
		array_push($arr_id, $dept);
		if (!empty($arr["nodechild"])) {
			$a = getChildNode($arr_id, $arr["nodechild"]);
			if (!empty($a)) {
				foreach ($a as $v) {
					array_push($arr_id, $v);
				}
			}
		}
		if (count($arr_id) == 1) {
			$ids = $arr_id[0];
		} else {
			$ids = implode(",", $arr_id);
		}
	}
}

//upload file
if( array_key_exists('file',$_FILES) ){
	$valid_formats = array("txt");	
	$name = $_FILES['file']['name'];
	if ($_FILES['file']['error'] == 4) {
		$return = json_encode(array('status'=>'error','on'=>'img_check'));
		print $return;	
		mysqli_close($con);
		return;
	}
	if ($_FILES['file']['error'] == 0) {
		if(!in_array(pathinfo(strtolower($name), PATHINFO_EXTENSION), $valid_formats) ){
			$return = json_encode(array('status'=>'error-upload-type'));
			print $return;	
			mysqli_close($con);
			return;
		}
	}
	$folder_path 	= $param['targetPath'];	
	$t				= strtotime($date).time();	
	$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
	$save_name		= $pay_period['pay_date'].'.'.$extMove;	
	move_uploaded_file($_FILES["file"]["tmp_name"], $folder_path.$save_name);	
}else{
	$return = json_encode(array('status'=>'nofile','on'=>'img_check'));
	print $return;	
	mysqli_close($con);
	return;
}

/* Machine Info*/
$machine_data	= getTimeLogsConf($con,$param["info"]["machid"]);
$emp			= explode(",",$machine_data[0]['emp']);
$wdate			= explode(",",$machine_data[0]['wdate']);
$wtime			= explode(",",$machine_data[0]['wtime']);
$atype			= explode(",",$machine_data[0]['atype']);
$ain			= $machine_data[0]['ain'];
$aout			= $machine_data[0]['aout'];
$empexplode		= explode(",",$ids);

//Getting the total number of lines on textfile
$linecount = 0;
$handle = fopen($folder_path.$save_name,"r");
while(!feof($handle)){
	if (fgets($handle) !== false) {
			$linecount++;
			$str1 = fgets($handle);
			$dateline = date('Y-m-d',strtotime(substr($str1,$wdate[0],$wdate[1])));
			//print_r($pay_period['pay_start']." - ".$dateline);

			$plusdayperiod = new DateTime($pay_period['pay_end']);
			$plusdayperiod->modify('+15 day');
			$plusday = date_format($plusdayperiod,"Y-m-d");
			
			if(!empty($dateline) && $dateline != '1970-01-01'){
				
				if($dateline < $pay_period['pay_start'] ||  $dateline > $plusday){
					$return = json_encode(array('status'=>'invfiledates'));
					print $return;	
					mysqli_close($con);
					return;
				}
			}



	}
}
fclose($handle);

/* REPROCESS ALL */
//$truncData = truncateSheet($con);
$file = fopen($folder_path.$save_name,"r");
$empids = array();
while(! feof($file)){
	$arr_data = array();
	$str = fgets($file);
	//$arr_data	= explode(" ",$txt_file); 
	
	array_push($arr_data, substr($str,$emp[0],$emp[1])); //Employee ID
	array_push($arr_data,date('Y-m-d',strtotime(substr($str,$wdate[0],$wdate[1])))); //Date
	array_push($arr_data,substr($str,$wtime[0],$wtime[1])); //Time
	array_push($arr_data,substr($str,$atype[0],$atype[1])); //In/Out
	array_push($empids, substr($str,$emp[0],$emp[1])); //Employee id's to be uploaded for translogs

	if( !empty( $arr_data ) ){
		if( count($arr_data) == 4 ){
			//created by alex if incase classi filter will be insisted
			// $ret = insertToTimeLogs($con, $arr_data, $param["info"]["batchid"], $param["info"]["sdate"], $param["info"]["fdate"]);
			$txt_acct = trim($arr_data[0]);
			$empunit = getEmpAcctDept($con, $txt_acct);
			//var_dump($ids);
			if($ids==0){
				$ret = insertToTimeLogs($con, $arr_data, $param["info"]["batchid"], $param["info"]["sdate"], $param["info"]["fdate"]);
			}else{
				foreach($empexplode	as $res) {
					if($empunit == $res){
						$counter++;
						$ret = insertToTimeLogs($con, $arr_data, $param["info"]["batchid"], $param["info"]["sdate"], $param["info"]["fdate"]);
					}
				}
			}
		}else{
			fclose($file);
			//$truncData = truncateSheet($con);
			$return = json_encode(array('status'=>'err_format'));
			print $return;	
			mysqli_close($con);
			return;
		}
	}
}
fclose($file);
if (!empty($empids)) {
	$insertlog = insertTransLogs($con,$empids, $param["accountid"], $param["info"]["batchid"], $param["info"]["sdate"], $param["info"]["fdate"]);
}

//$mydata = array('totallastbatch' => getlastBatchid($con), 'totallogs' => $linecount, 'filtered' => $counter, 'batchno' => getlastBatch($con));
$mydata = array('totallastbatch' => getlastBatchid($con), 'filtered' => $counter, 'batchno' => getlastBatch($con));
$return = json_encode($mydata);

print $return; 
mysqli_close($con);

function insertTransLogs($con,$empids,$uploader,$batchid,$sdate,$fdate){
	$transaction = 'Manual Upload';
	$description = implode (",", $empids);
	$Qry3           = new Query();
	$Qry3->table    = "tbltransaction_logs";
	$Qry3->selected = "transaction,description,batchid,date,time,createdby";
	$Qry3->fields   = "'".$transaction."','".$description."','".$batchid."','".SysDate()."','".SysTime()."','".$uploader."'";
	$checke = $Qry3->exe_INSERT($con);
	return $checke;
}

function getlastBatch($con){
	$Qry = new Query();	
	$Qry->table     = "tbltimelogs";
	$Qry->selected  = "*";
	$Qry->fields    = "id>0 ORDER BY id DESC LIMIT 1";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			return $row['batchid'];
		}
	}
	return '';
}

function insertToTimeLogs($con, $arr_data, $idbatch, $sdate, $fdate){	
//function insertToTimeLogs($con, $arr_data, $idbatch){	
	$empid	  = '';
	$txt_acct = trim($arr_data[0]);
    $txt_date = trim($arr_data[1]);
    $txt_time = trim($arr_data[2]);
	$txt_type = trim($arr_data[3]);
	$manual_cebu = 1;
	if( !empty( $txt_acct ) ){

		// DELETE TBLLOGSPAIR  AND TBLTIMELOGS AND CLEAR TBLTIMESHEET
		$Qry4 = new Query();	
		$Qry4->table     = "tbltimelogs";
		$Qry4->fields    = "acct_id='".$txt_acct."' AND work_date = '".$txt_date."' AND batchid <> '".$idbatch."' AND (work_date BETWEEN '".$sdate."' AND '".$fdate."')";	
		$rs4 = $Qry4->exe_DELETE($con);
		if($rs4){
			$empid = getEmpAcctID($con, $txt_acct);
			$Qry5 = new Query();	
			$Qry5->table     = "tbltimelogpair";
			$Qry5->fields    = "idacct='".$empid."' AND work_date = '".$txt_date."' AND batchid <> '".$idbatch."'";
			$rs5 = $Qry5->exe_DELETE($con);
			if($rs5){
				$clear = '';
				$Qry6 = new Query();	
				$Qry6->table     = "tbltimesheet"; 
				$Qry6->selected = "
								date_in		= '".$clear."',
								timein		= '".$clear."',
								date_out	= '".$clear."',
								timeout		= '".$clear."',
								date_in2	= '".$clear."',
								timein2		= '".$clear."',
								date_out2	= '".$clear."',
								timeout2	= '".$clear."'";
				$Qry6->fields    = "idacct='".$empid."' AND date = '".$txt_date."'";
				$rs6 = $Qry6->exe_UPDATE($con);
			}
		}
		// DELETE TBLLOGSPAIR  AND TBLTIMELOGS AND CLEAR TBLTIMESHEET
		//$timestamp = date('Y-m-d H:i:s');
		$stamp = SysDate().' '.SysTime();
		$Qry3           = new Query();
		$Qry3->table    = "tbltimelogs";
		$Qry3->selected = "idconf,acct_id,idunit,work_time,batchid,time_type,date_pair,uploaded_timestamp";
		$Qry3->fields   = "'".$manual_cebu."',
						   '".$txt_acct."',
						   '".getEmpAcctDept($con, $txt_acct)."',
						   '".$txt_time."',
						   '".$idbatch."',
						   '".$txt_type."',
						   '".Sysdate()."',
						   '".$stamp."'";
						   
		if( !empty( $txt_date ) ){
			$arr = explode("-",$txt_date);
			//$txt_date = $arr[2]."-".$arr[0]."-".$arr[1];
			$Qry3->selected = $Qry3->selected." ,work_date";
			$Qry3->fields   = $Qry3->fields  .",'".$txt_date."'";
		}				   
						   
		$checke = $Qry3->exe_INSERT($con);

		if($checke){
			return  json_encode(array("status"=>"success"));
		}else{
			return  json_encode(array('status'=>'error',"err"=>mysqli_error($con)));
		}
	}else{
		return  json_encode(array('status'=>'error'));
	}
}

function insertTimelogPairRec( $con, $row, $ain, $aout ){
	$Qry3           = new Query();
	$Qry3->table    = "tbltimelogpair";
	$Qry3->selected = "batchid,machid,work_date,idshift,idacct,idunit,sdate,fdate,create_stamp";
	$Qry3->fields   = "'".$row['batchid']."','".$row['machid']."','".$row['work_date']."','".$row['idshift']."', '".$row['idacct']."', '".$row['idunit']."', '".$row['sdate']."', '".$row['fdate']."', '".SysDatePadLeft()." ".SysTime()."'";
	if( $row['type'] == $ain ){
		$Qry3->selected = $Qry3->selected . ", date_in1, time_in1";
		$Qry3->fields   = $Qry3->fields   . ",'".$row['work_date']."','".$row['work_time']."'";
	}elseif( $row['type'] == $aout ){
		$Qry3->selected = $Qry3->selected . ", date_out1, time_out1";
		$Qry3->fields   = $Qry3->fields   . ",'".$row['work_date']."','".$row['work_time']."'";
	}
	$checke = $Qry3->exe_INSERT($con);
	return $checke;
}

function updateTimelogPairRec( $con, $fields, $id ){
	$Qry3           = new Query();
	$Qry3->table    = "tbltimelogpair";
	$Qry3->selected = $fields;
	$Qry3->fields   = "id='".$id."'";
	$checke = $Qry3->exe_UPDATE($con);
	return $checke;
}

function truncateSheet($con){
	$QryTrunc      		= new Query();
	$QryTrunc->table	= "tbltimelogs";
	$checkTrucn			= $QryTrunc->exe_TRUNCATE($con);
	return $checkTrucn;
}


function payperiodstatus($con,$date,$date1){
    $Qry = new Query();	
    $Qry->table         = "tblpayperiod";
    $Qry->selected      = "tkstatus";
    $Qry->fields        = "period_start = '".$date."' AND period_end = '".$date1."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['tkstatus'];
        }
    }
    
}
?>