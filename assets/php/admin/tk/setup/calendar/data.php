<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	

$search='';
/*

if( !empty( $param['alias'] ) ){ $search=" AND alias like   '%".$param['alias']."%' "; }
*/
if( !empty( $param['shiftname'] ) ){ $search=" AND id like   '%".$param['shiftname']."%' "; }
$where = $search;

if( $param['length'] !='' ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "vw_datacalendar";
$Qry->selected  = "*";
$Qry->fields    = "id>0 ".$search;
$rs = $Qry->exe_SELECT($con);
$recFiltered = getTotalRows($con,$where);
if(mysqli_num_rows($rs)>= 1){
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs),
        "recordsFiltered"=> $recFiltered,
        "qry"=>$Qry->fields,
        "data"=>array()
    );
    while($row=mysqli_fetch_array($rs)){
        $data["data"][] = array(
            'id'    	=> $row['id'],
            'name'  	=> $row['name'],
            'idsun'		=> $row['idsun'],
			'sun'		=> $row['sun'],
			'sun_in'	=> $row['sun_in'],
			'sun_out'	=> $row['sun_out'],
			'sun_brkin'	=> $row['sun_in'],
			'sun_brkout'=> $row['sun_out'],
			'idmon'		=> $row['idmon'],
			'mon'		=> $row['mon'],
			'mon_in'	=> $row['mon_in'],
			'mon_out'	=> $row['mon_out'],
			'mon_brkin'	=> $row['mon_in'],
			'mon_brkout'=> $row['mon_out'],
			'idtue'		=> $row['idtue'],
			'tue'		=> $row['tue'],
			'tue_in'	=> $row['tue_in'],
			'tue_out'	=> $row['tue_out'],
			'tue_brkin'	=> $row['tue_in'],
			'tue_brkout'=> $row['tue_out'],
			'idwed'		=> $row['idwed'],
			'wed'		=> $row['wed'],
			'wed_in'	=> $row['wed_in'],
			'wed_out'	=> $row['wed_out'],
			'wed_brkin'	=> $row['wed_in'],
			'wed_brkout'=> $row['wed_out'],
			'idthu'		=> $row['idthu'],
			'thu'		=> $row['thu'],
			'thu_in'	=> $row['thu_in'],
			'thu_out'	=> $row['thu_out'],
			'thu_brkin'	=> $row['thu_in'],
			'thu_brkout'=> $row['thu_out'],
			'idfri'		=> $row['idfri'],
			'fri'		=> $row['fri'],
			'fri_in'	=> $row['fri_in'],
			'fri_out'	=> $row['fri_out'],
			'fri_brkin'	=> $row['fri_in'],
			'fri_brkout'=> $row['fri_out'],
			'idsat'		=> $row['idsat'],
			'sat'		=> $row['sat'],
			'sat_in'	=> $row['sat_in'],
			'sat_out'	=> $row['sat_out'],
			'sat_brkin'	=> $row['sat_in'],
			'sat_brkout'=> $row['sat_out']
        );
    }
    $return =  json_encode($data);
}else{
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs),
        "recordsFiltered"=> mysqli_num_rows($rs),
        "data"=>array()
    );
    $return =  json_encode($data);
}

print $return;
mysqli_close($con);

function getTotalRows($con,$search){
	$Qry = new Query();	
	$Qry->table ="vw_datacalendar";
	$Qry->selected ="*";
	$Qry->fields ="id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>