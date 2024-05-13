<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$return = null;	

$shift_cols = array("monday"	=>"mon", 
					"tuesday"	=>"tue",
					"wednesday"	=>"wed",
					"thursday"	=>"thu",
					"friday"	=>"fri",
					"saturday"	=>"sat", 
					"sunday"	=>"sun");

$search='';

if( !empty( $param['acct'] ) ){ $search= $search." AND empID = '".$param['acct']."' ";}
if( !empty( $param['dfrom'] ) && empty($param['dto']) ){ $search= $search." AND work_date =DATE('".$param['dfrom']."') "; }
if( !empty( $param['dfrom'] ) && !empty($param['dto']) ){ $search=$search." AND work_date BETWEEN DATE('".$param['dfrom']."') AND DATE('".$param['dto']."') "; }


$where = $search;

if( empty( $param['acct'] ) || empty( $param['dfrom'] ) ){
	 $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> 0,
        "recordsFiltered"=> 0,
        "data"=>array(),
		"qry"=>''
    );
	$return =  json_encode($data);
    print $return;
	mysqli_close($con);
	return;
}


$search=$search." ORDER BY work_date ASC ";

if( $param['length'] !='' ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "vw_data_timesheet";
$Qry->selected  = "*";
$Qry->fields    = "work_date is not null ".$search."";
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
		
		if( !empty($row['holiday_id']) ){
			$row['shift_status'] = ucwords(strtolower($row['holiday_type'])).' Holiday';
		}
		
		if( empty($row['shift_status']) ){
			$shift_field			= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];
			$shift_info 			= getDateShiftData( $con, $row['empID'], $shift_field, $row['work_date'] );
			$row['shift_status']	= $shift_info[0];
		}
		$application = '';		
		if( !empty( $row['leavename'] ) ){
			$application = $row['leavename'];		
		}
		if( !empty( $row['adj_hours'] ) ){
			$application = $application." ATTENDANCE ADJUSTMENT"	;
		}
		
        $data["data"][]  = array(
			'shift'		 => $row['shift_status'],
            'date'    	 => date('D m/d/y', strtotime($row['work_date'])),
            'day'  		 => date('l',strtotime($row['work_date'])),
            'in'		 => ( $row['in'] ? date("h:i:s a",strtotime($row['in'])) : ''),
			'out'		 => ( $row['out'] ? date("h:i:s a",strtotime($row['out'])) : ''),
			'late'		 => sprintf('%0.2f', $row['late']),
			'ut'		 => sprintf('%0.2f', $row['ut']),
			'absent'	 => sprintf('%0.2f', $row['absent']),
			'leavehrs'	 => sprintf('%0.2f', $row['leavehrs']),
			'reghrs'	 => sprintf('%0.2f', $row['reghrs']),
			'acthrs'	 => sprintf('%0.2f', $row['acthrs']),
			'othrs'		 => sprintf('%0.2f', $row['ot']),
			'idacct'	 => $row['empID'],
			'work_date'	 => $row['work_date'],
			'date_in'	 => $row['date_in'],
			'date_out'	 => $row['date_out'],
			'time_in'	 => $row['in'],
			'time_out'	 => $row['out'],
			'application'=> $application,
			'total' 	 => getSumRows($con,$where)
        );
    }
    $return =  json_encode($data);
}else{
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs),
        "recordsFiltered"=> mysqli_num_rows($rs),
        "data"=>array(),
		"qry"=>$Qry->fields
    );
    $return =  json_encode($data);
}

print $return;
mysqli_close($con);

function getTotalRows($con,$search){
	$Qry = new Query();	
	$Qry->table ="vw_data_timesheet";
	$Qry->selected ="*";
	$Qry->fields ="work_date is not null ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>