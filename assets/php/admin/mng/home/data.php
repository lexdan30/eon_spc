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
    $pending = 0;
    $declined = 0;
    $approved = 0;

    while($row=mysqli_fetch_array($rs)){
		
		if( !empty($row['holiday_id']) ){
			$row['shift_status'] = ucwords(strtolower($row['holiday_type'])).' Holiday';
		}
		
		if( empty($row['shift_status']) ){
			$shift_field			= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];
			$shift_info 			= getDateShiftData( $con, $row['empID'], $shift_field, $row['work_date'] );
			$row['shift_status']	= $shift_info[0];
		}
		// $application = '';		
		// if( !empty( $row['leavename'] ) ){
		// 	$application = $row['leavename'];		
		// }
		// if( !empty( $row['adj_hours'] ) ){
		// 	$application = $application." ATTENDANCE ADJUSTMENT"	;
        // }
        if($row['shift_status'] == 'Rest Day'){
            $row['shift_status'] = '<p class="csuccess fw9">RD</p>';
            $status = '<p class="csuccess fw9">N</p>';
        }else{
            $status = '<p class="fw9">W</p>';
        }

        if(strtotime($row['shiftin'])<strtotime($row['in'])) {
            $in = "<p class='danger'>" . ( $row['in'] ? date("h:i:s A",strtotime($row['in'])) : '') . "</p>";
        } else {
           $in = ( $row['in'] ? date("h:i:s A",strtotime($row['in'])) : '');
        }


        if(strtotime($row['shiftout'])>strtotime($row['out'])) {
            $out = "<p class='danger'>" . ( $row['out'] ? date("h:i:s A",strtotime($row['out'])) : '') . "</p>";
        
        } else {
            if( ((strtotime($row['out']) - strtotime($row['shiftout'])) / 60) >= 60) {
                $out = "<p class='danger'>" . ( $row['out'] ? date("h:i:s A",strtotime($row['out'])) : '') . "</p>";
            }else{
                $out = ( $row['out'] ? date("h:i:s A",strtotime($row['out'])) : '');
            }
        }

      

        if($row['adj_status'] != ''){
            if($row['adj_status'] == 'PENDING'){
                $pending++;
                if($row['in'] == ''){
                    $adaj = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>'. ( $row['shiftin'] ? date("h:i:s A",strtotime($row['shiftin'])) : '') . ' <a href=""><i class="fa fa-times danger" aria-hidden="true"></i></a> </span></div></div>';
                }
                if($row['out'] == ''){
                    $adaj = '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . ( $row['shiftout'] ? date("h:i:s A",strtotime($row['shiftout'])) : '') . '<a href=""><i class="fa fa-times danger" aria-hidden="true"></i> </a></span></div></div>';
                }
              
            }
             if($row['adj_status'] == 'DECLINED'){
                 $declined++;
                if($row['in'] == ''){
                    $adaj = '<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>'. ( $row['shiftin'] ? date("h:i:s A",strtotime($row['shiftin'])) : '') . '</span></div></div>';
                }
                if($row['out'] == ''){
                    $adaj = '<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . ( $row['shiftout'] ? date("h:i:s A",strtotime($row['shiftout'])) : '') . '</span></div></div>';
                }
            } 
            if($row['adj_status']  == 'APPROVED'){
                $approved++;
                if($row['in'] == ''){
                    $adaj = '<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>'. ( $row['shiftin'] ? date("h:i:s A",strtotime($row['shiftin'])) : '') . '</span></div></div>';
                }
                if($row['out'] == ''){
                    $adaj = '<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span>' . ( $row['shiftout'] ? date("h:i:s A",strtotime($row['shiftout'])) : '') . '</span></div></div>';
                }
            }
        }else{
            if($row['shift_status'] == '<p class="csuccess fw9">RD</p>'){
                $adaj = "<input type='checkbox' id='adaj" . $row['id'] . "' ng-click='adaj(\"".$row['work_date']."\"," . $row['id'] . ")'disabled>";
            }else{
                if($row['in'] == '' || $row['out'] == ''){
                    $adaj = "<input type='checkbox' id='adaj" . $row['id'] . "' ng-click='adaj(\"".$row['work_date']."\"," . $row['id'] . ")'>";
                }else{
                    $adaj = "<input type='checkbox' id='adaj" . $row['id'] . "' ng-click='adaj(\"".$row['work_date']."\"," . $row['id'] . ")'disabled>";
                }
                
            }
            
        }
        //
        if($row['overtime_status'] != ''){
            if($row['overtime_status'] == 1){
                $approved++;
            }
            if($row['overtime_status'] == 2){
                $declined++;
            }
            if($row['overtime_status'] == 3){
                $pending++;
               $ot =  '<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2"><span> OT <a href=""><i class="fa fa-bars" aria-hidden="true" ng-click="aot(' . $row['id'] .')"></i> </a></span></div></div>';
            }

        }else{
            $ot = "<input type='checkbox' id='ot" . $row['id'] . "' ng-click='ot(\"".$row['work_date']."\"," . $row['id'] . ")'>";
        }

        $wh = (strtotime($row['shiftout']) - strtotime($row['shiftin']))/3600; 

        $check = "<input type='checkbox' value=''>";
        $lv = "<input type='checkbox' id='leave" . $row['id'] . "' ng-click='leave(\"".$row['work_date']."\"," . $row['id'] . ")'>";

        $cs = "<input type='checkbox' id='" . $row['id'] . "' ng-click='changeshift(".'$event'."," . $row['id'] . ")'>";
        $ob = $check;
        $action = '<i ng-click="multi()" class="fa fa-plus-square cgreen" aria-hidden="true" style="cursor : pointer;"></i>';
        ///
		
        $data["data"][]  = array(
            'status'	 => $status,
			'shift'		 => $row['shift_status'],
            'date'    	 => date('D m/d/Y', strtotime($row['work_date'])),
            'day'  		 => date('l',strtotime($row['work_date'])),
            'in'		 => $in,
            'wh'	     => $wh,
			'out'		 => $out,
			'late'		 => sprintf('%0.2f', $row['late']),
			'ut'		 => sprintf('%0.2f', $row['ut']),
			'absent'	 => sprintf('%0.2f', $row['absent']),
            'lv'	     => $lv,
            'cs'	     => $cs,
            'adaj'	     => $adaj,
            'ot'	     => $ot,
            'ob'	     => $ob,
			'reghrs'	 => sprintf('%0.2f', $row['reghrs']),
			'acthrs'	 => sprintf('%0.2f', $row['acthrs']),
			'othrs'		 => sprintf('%0.2f', $row['ot']),
            'action'     => $action,
            //
            'pending'    => $pending,
            'declined'    => $declined,
            'approved'    => $approved,
            ///
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