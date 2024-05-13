<?php
date_default_timezone_set('Asia/Manila');
require_once('../../../activation.php');
$conn = new connector();
$con = $conn->connect();
require_once('../../../classPhp.php');

$param 		= json_decode(file_get_contents('php://input'));
$info 		= getdate();
$date 		= $info['mday'];
$month 		= $info['mon'];
$year 		= $info['year'];
$date_s 	= $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-01";
$date_e 	= date("Y-m-t", strtotime($date_s));

$Qry = new Query();
$Qry->table = "(SELECT idcreator, id, event_title, event_desc, efrom, eto, canview, atype, filename, isactive FROM `tblcompanyact`) as tblc LEFT JOIN (SELECT id, fname, lname FROM tblaccount) as tbla ON (tblc.idcreator = tbla.id)";
$Qry->selected = "tblc.id, fname, lname, event_title, event_desc, efrom, eto, canview, atype, filename";
$Qry->fields    = "DATE_FORMAT(efrom, '%c-%d') BETWEEN DATE_FORMAT('".$date_s."', '%c-%d')  AND  DATE_FORMAT('".$date_e."', '%c-%d') AND isactive = '1'  ORDER BY DATE_FORMAT(efrom, '%c-%d') asc";


$rs = $Qry->exe_SELECT($con);
$data = array();
if(mysqli_num_rows($rs)>= 1){
while($row=mysqli_fetch_assoc($rs)){
	$name = $row['fname'] . " " . $row['lname'];
	$title = $row['event_title'];
	$start= $row['efrom'];
	$end = $row['eto'];
	
	$f=false;
	$filesize = "0 bytes";
	if( !empty( $row['filename'] ) ){
		$file = filesize("../../org/activity/file/" . $row['filename']);
		$filesize = formatSizeUnits($file);
		$f=true;
	}

	if($row['canview'] == 'all'){
		$pushd = array(
			"id"=> $row['id'],
			"name" => $name,
			"title" => $title,
			"start" => $start,
			"end" => $end,
			"description" => strip_tags($row['event_desc']),
			"desc"=>$row['event_desc'],
			"type" => $row['atype'],
			"filesize" => $filesize,
			"hasfile"	=> $f
		);
		array_push($data, $pushd);
	}else{
		$ids = (explode(",",$row['canview']));
		if (in_array($param->accountid, $ids)){
			$pushd = array(
				"id"=> $row['id'],
				"name" => $name,
				"title" => $title,
				"start" => $start,
				"end" => $end,
				"description" => strip_tags($row['event_desc']),
				"desc"=>$row['event_desc'],
				"type" => $row['atype'],
				"filesize" => $filesize,
				"hasfile"	=> $f
			);
			array_push($data, $pushd);
		}
	}
}
$return = json_encode($data);
}else{
	$return = json_encode(array());
}

function formatSizeUnits($bytes) {
	if ($bytes >= 1073741824){
		$bytes = number_format($bytes / 1073741824, 2) . ' GB';
	}elseif ($bytes >= 1048576){
		$bytes = number_format($bytes / 1048576, 2) . ' MB';
	}elseif ($bytes >= 1024){
		$bytes = number_format($bytes / 1024, 2) . ' KB';
	}elseif ($bytes > 1){
		$bytes = $bytes . ' bytes';
	}elseif ($bytes == 1){
		$bytes = $bytes . ' byte';
	}else{
		$bytes = '0 bytes';
	}
	return $bytes;
}

print $return;
mysqli_close($con);
?>