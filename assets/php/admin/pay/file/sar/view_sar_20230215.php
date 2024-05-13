<?php 
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));
// print_r('hello');
$data  = array();
$data_shifthrs  = array();
$data_whrs  = array();
$mgmt_data_whrs  = array();
$nonmgmt_data_whrs  = array();
if(!empty($param->accountid)){ 
    if(empty($param->filter->period)){
        $pay_period = getPayPeriodts($con);
    }else{ 
        $pay_period = getPayPeriodts($con,$param->filter->period);
    }

    $date  = $pay_period['pay_start'];//$param->dfrom;
    $date1 = $pay_period['pay_end'];//$param->dto; 
    // $date  = '2023-01-01';//$param->dfrom;
    // $date1 = '2023-01-15';//$param->dto; 
    $search='';
    if( !empty( $param->filter->department ) ){ 
        if($param->filter->department == 1){
            $param->filter->department = '';
        }
    }

    if( !empty( $param->filter->department ) ){  

        $arr_id = array();
        $arr 	= getHierarchy($con,$param->filter->department);
        if( !empty( $arr["nodechild"] ) ){     
            $ids = join(',', flatten($arr['nodechild']));
        } else {
            $ids = '';
        }
 
        if( !empty( $ids ) ){  
            $search = " AND (idunit IN(".$ids.") OR idunit=".$param->filter->department.")";
        }else{
            $search = " AND idunit=".$param->filter->department;
        }
  
     }

    //print_r($search);
    //print_r($search);
    
    $where = $search;
    
    $Qry 			= new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "*";
    //$Qry->fields    = "id!=1 AND (sdate > '2023-01-15' OR sdate IS NULL) AND etypeid = 1  AND idunit=2 ORDER BY empname LIMIT 10 OFFSET 0";
    //$Qry->fields    = "empid in (2214,2226,0414,2513,0102,2593) ORDER BY empname LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
    $offset = ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
    $Qry->fields    = "id!=1 AND (sdate > '".$date1."' OR sdate IS NULL) AND etypeid = 1 ". $search . " ORDER BY empname LIMIT " .$param->pagination->pageSize. " OFFSET " . $offset;
    $rs 			= $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
    
        while($row=mysqli_fetch_array($rs)){

            $np_reg = 0;
            $np_sh = 0;
            $np_lh = 0;
            $np_rd = 0;
            $br_reg = 0;
            $br_sh = 0;
            $br_lh = 0;
            $br_rd = 0;

            if( empty($row['pic']) ){
                $row['pic'] = "undefined.webp";
            }

            if($row['pay_grp'] == 'Management'){
                $row['pay_grp'] = 'MGNT';
            }elseif($row['pay_grp'] == 'Non-Management'){
                $row['pay_grp'] = 'NON-MGNT';
            }

            $scheobj = getVwTimesheet($con, $row['id'], $date, $date1);
            for($i = 0; $i < (sizeof($scheobj)); $i++){
                //==================NP
                if(!isset($scheobj[$i]['np_reg'])){
                    $scheobj[$i]['np_reg'] = 0;
                }
                $np_reg += $scheobj[$i]['np_reg'];

                if(!isset($scheobj[$i]['np_sh'])){
                    $scheobj[$i]['np_sh'] = 0;
                }
                $np_sh += $scheobj[$i]['np_sh'];

                if(!isset($scheobj[$i]['np_lh'])){
                    $scheobj[$i]['np_lh'] = 0;
                }
                $np_lh += $scheobj[$i]['np_lh'];

                if(!isset($scheobj[$i]['np_rd'])){
                    $scheobj[$i]['np_rd'] = 0;
                }
                $np_rd += $scheobj[$i]['np_rd'];
                //==================NP
                //==================BROKEN
                if(!isset($scheobj[$i]['br_reg'])){
                    $scheobj[$i]['br_reg'] = 0;
                }
                $br_reg += $scheobj[$i]['br_reg'];

                if(!isset($scheobj[$i]['br_sh'])){
                    $scheobj[$i]['br_sh'] = 0;
                }
                $br_sh += $scheobj[$i]['br_sh'];

                if(!isset($scheobj[$i]['br_lh'])){
                    $scheobj[$i]['br_lh'] = 0;
                }
                $br_lh += $scheobj[$i]['br_lh'];

                if(!isset($scheobj[$i]['br_rd'])){
                    $scheobj[$i]['br_rd'] = 0;
                }
                $br_rd += $scheobj[$i]['br_rd'];
                //==================BROKEN 

            } 
            $data[] = array( 
                "empID"			        => $row['id'],
                "empIDn"			    => $row['empid'],
                "empname"			    => $row['empname'], 
                "location"			    => $row['location'],
                "idlvl"			        => $row['idlvl'],
                "jobalias"			    => getJobalias($con,$row['idlvl']),
                "joblvl"			    => $row['joblvl'],
                "wshifttype"			=> $row['wshifttype'],
                "pic"			        => $row['pic'],
                "wshift_name"		    => $row['wshift_name'],
                "post"			        => $row['business_unit'],
                "paygrp"			    => $row['pay_grp'],
                "getVwTimesheet"        => $scheobj,
                "getTotalApproved"      => '',
                "getTotalDeclined"      => '',
                "getTotalPending"       => '',
                "twh"                   => '',
                "totalwh"               => '',
                "totalewh"              => '',
                "totalexcess"           => '',
                "totallate"             => '',
                "totalut"               => '',
                "totalabsent"           => '',
                "totalnp"               => '',
                "totalot"               => '',
                'np_reg'          => sprintf('%0.2f',$np_reg), 
                'np_sh'           => sprintf('%0.2f',$np_sh), 
                'np_lh'           => sprintf('%0.2f',$np_lh), 
                'np_rd'           => sprintf('%0.2f',$np_rd), 
                'br_reg'          => 0, 
                'br_sh'           => 0, 
                'br_lh'           => 0, 
                'br_rd'           => 0
            ); 
        }

        // $mgmt_data_whrs  = array();
        // $nonmgmt_data_whrs  = array();
 
        foreach ($data as $key => $val) {  
            for($i = 0; $i < (sizeof($val['getVwTimesheet'])); $i++){
                //===============SHIFTHRS
                if(!isset($data_shifthrs[$i])){
                    $data_shifthrs[$i] = 0;
                }

                if(!isset($val['getVwTimesheet'][$i]['work_hours'])){
                    $val['getVwTimesheet'][$i]['work_hours'] = 0;
                }
                $data_shifthrs[$i] = $data_shifthrs[$i]+$val['getVwTimesheet'][$i]['work_hours'];
                //===============SHIFTHRS
                //===============WHRS
                if(!isset($data_whrs[$i])){
                    $data_whrs[$i] = 0;
                }

                if(!isset($val['getVwTimesheet'][$i]['whrs'])){
                    $val['getVwTimesheet'][$i]['whrs'] = 0;
                }
                $data_whrs[$i] = $data_whrs[$i]+$val['getVwTimesheet'][$i]['whrs'];
                //===============WHRS
                //segregating management and non-management
                if($val['paygrp'] == 'MGNT'){
                    if(!isset($mgmt_data_whrs[$i])){
                        $mgmt_data_whrs[$i] = 0;
                    }
                    $mgmt_data_whrs[$i] = $mgmt_data_whrs[$i]+$val['getVwTimesheet'][$i]['whrs'];
                }else{ //segregating management and non-management
                    if(!isset($nonmgmt_data_whrs[$i])){
                        $nonmgmt_data_whrs[$i] = 0;
                    }
                    $nonmgmt_data_whrs[$i] = $nonmgmt_data_whrs[$i]+$val['getVwTimesheet'][$i]['whrs'];
                } 
            }  
        }  

        $myData = array('status' => 'success',
        'result' => $data, 'totalItems' => getTotal($con , $where, $date1),
        'sdate' => $date, 'edate' => $date1,
        'dept'=> getSARDept( $con, $param->filter->department ),
        'totshift' => $data_shifthrs,
        'totwhrs' => $data_whrs,
        'totwhrs_mgmt' => $mgmt_data_whrs,
        'totwhrs_nonmgmt' => $nonmgmt_data_whrs,
        'sumtotshift' => array_sum($data_shifthrs),
        'sumtotwhrs' => array_sum($data_whrs),
        'sumtotwhrs_mgmt' => array_sum($mgmt_data_whrs),
        'sumtotwhrs_nonmgmt' => array_sum($nonmgmt_data_whrs),
        'Qry->fields ' => $Qry->fields ); 
        $return = json_encode($myData);
    }else{ 
        $myData = array('status' => 'success',
        'result' => $data, 'totalItems' => 0,
        'sdate' => $date, 'edate' => $date1,'dept'=>'',
        'totshift' => $data_shifthrs,
        'totwhrs' => $data_whrs,
        'totwhrs_mgmt' => $mgmt_data_whrs,
        'totwhrs_nonmgmt' => $nonmgmt_data_whrs,
        'sumtotshift' => 0,
        'sumtotwhrs' => 0,
        'sumtotwhrs_mgmt' => 0,
        'sumtotwhrs_nonmgmt' => 0);
        $return = json_encode($myData);
    }

    print $return;
    mysqli_close($con);
}else{
	mysqli_close($con);
	header("Location: https://peninsula.mydhris.com/mph/");   
}

function getVwTimesheet($con, $idacct, $date, $date1){
    error_reporting(0);
    $shift_cols = array( 
        "monday" => "mon", 
        "tuesday" => "tue",
        "wednesday" => "wed",
        "thursday" => "thu",
        "friday" => "fri",
        "saturday" => "sat",
        "sunday" => "sun"
    );

    $data=array();
    $Qry=new Query();
    $Qry->table="vw_timesheetfinal";
    $Qry->selected="*";
    $Qry->fields=" tid='".$idacct."' AND work_date BETWEEN '".$date."' AND '".$date1."' ORDER BY work_date ASC";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){

        $cutoff_work_hours = 0;
        $cutoff_leaves = '';
        $leavearray = array();
        $cutoff_tardiness = 0;
        $cutoff_whrs = 0;

        while($row=mysqli_fetch_array($rs)){
            $row['name']   = $row['defaultsched'];

            if (!empty($row['holiday'])) {
                if($row['csstatus'] == 1){
                    $row['name'] = "<p style='color:red; font-weight: 900;'>". ucwords(strtolower($row['alias'])) . "H <strike>" . $row["defaultsched"] . "</strike> </p>";
                }else{
                    $row['name'] = "<p style='color:red; font-weight: 900;'> ". ucwords(strtolower($row['alias'])) . "H " . $row["defaultsched"] . "</p>";
                }
            }
          
            if ($row['name'] == 'Rest Day') {
                $row['name'] = '<p class="csuccess fw9">RD</p>';
                $status              = '<p class="csuccess fw9">N</p>';
            } else if($row['absent'] > 0.00) {
                    $status = '<p class="absent fw9">A</p>';
            } else {
                    $status = '<p class="fw9">W</p>';
            }
 
            if(($row['absent'] + $row['acthrs'] > $row['shifthrs']) && ($row['absent']>0) ){
                if($row['acthrs'] > 0){
                    $row['excess'] = ($row['absent']+$row['acthrs']) - $row['shifthrs'];
                }
            }
     
            if($row['holiday'] ){
                $status              = '<p class="csuccess fw9">N</p>';
 
                $row['late'] = 0;
                $row['ut'] = 0;
            }

            if($row['leaveappstatus'] == 1 && $row['leaveidtype'] != 3){
                $status              = '<p class="csuccess fw9">P</p>';
            }

            if($row['lateref'] + $row['utref']  > 1){
               
                $row['late'] = $row['late'];
                $row['ut'] = $row['ut'];
            }else{
                if($row['lateref'] + $row['utref'] != 0){
 
                 
                } 
            }   
            
            
            if(($row['lateref'] + $row['utref']) > 1 ){
                $whrs  = $row['acthrs'] - $row['excess']; 
            }
            else{
                $whrs  = $row['acthrs'] - ($row['excess']); // + $row['lateref'] + $row['utref']); 
            }

            
            $npot = $row['npot'];


            if($row['acthrs'] < $whrs){
                $whrs = $row['acthrs'];
            }
 

            if (($row['obtripstatus'] == 1  || $row['aastatus'] == 1) && $row['leaveappstatus'] == 1) {
                $whrs = $row['leave'];
                $row['acthrs'] = $whrs;
            }

            $whrs = $whrs ;  

            $csapprove = strtotime(date($row['csapprove']));
            $period_start = strtotime(date($row['period_start']));
            $period_endref =  strtotime(date($row['period_endref']));

            if($row['csstatus'] == 1 ){
                if($row['name'] == '<p class="csuccess fw9">RD</p>'){
                    $row['name'] = '<p class="csuccess fw9"><strike> RD</strike></p>';
                }else{
                    if( (($csapprove >= $period_start) && ($csapprove >= $period_endref)) ){
                        $row['name'] = $row['name'];
                    }else{
                        $row['name'] = "<strike> " . $row['name'] . "</strike>";
                    }
                    
                }
            }

            if($row['aastatus'] == 1){
                $row['timein'] = $row['timein']? "<strike> " . date('h:i a', strtotime($row['timein'])) . "</strike>" :'';
                $row['timeout'] = $row['timeout']? "<strike> " . date('h:i a', strtotime($row['timeout'])) . "</strike>" :'';
                $row['timein2'] = $row['timein2']? "<strike> " . date('h:i a', strtotime($row['timein2'])) . "</strike>":'';
                $row['timeout2'] = $row['timeout2']? "<strike> " . date('h:i a', strtotime($row['timeout2'])) . "</strike>":'';
            }else{
                $row['timein'] = $row['timein']?  date('h:i a', strtotime($row['timein'])) :'';
                $row['timeout'] = $row['timeout']?  date('h:i a', strtotime($row['timeout'])):'';
                $row['timein2'] = $row['timein2']?  date('h:i a', strtotime($row['timein2'])) :'';
                $row['timeout2'] = $row['timeout2']?  date('h:i a', strtotime($row['timeout2'])) :'';
            }

            $checkdate = false;
            foreach ($data as $key => $val) {
                if ($val['date'] == $row['work_date']) { 
                    $checkdate = true;
                    //$data[$key]['getTimesheetLeaves'] = array_merge($val['getTimesheetLeaves'],  getTimesheetLeaves($row, $con ,$date , $row['work_date'], $row['tid'], $row['leaveappstatus'], $row['leavename'], $row['leave'], $row['idtimeleavetype'], $row['leaveidtype']));
                    $finalleavehrs =0;
                    foreach ($data[$key]['getTimesheetLeaves'] as $keys => $vals) {
                        if($vals['leavetype'] != '3' && $vals['leavetype'] != '34'){
                            $finalleavehrs += $vals['hrs'];
                            if($keys == 1){
                                $data[$key]['absent'] =  sprintf('%0.2f',  sprintf('%0.2f',  $data[$key]['absent']) - $vals['hrs'] );
                            }
                        } 
                    }
                    $data[$key]['whrs'] = sprintf('%0.2f',$finalleavehrs);

                    if(!$row['lvapprove']){
                        //$data[$key]['getOvertime'] = array_merge($val['getOvertime'],  getOvertime($row, $con ,$date , $row['work_date'],$row['tid'],$row['otstatus'], $row['othrs']));
                        $data[$key]['ot'] = $data[$key]['ot'] + $row['othrs'];
                    }
                    break;
                }
            }

            if($row['leaveappstatus'] == 1 &&  ($row['idtimeleavetype'] == 2 || $row['idtimeleavetype'] == 3)){
                    $whrs =  $whrs + $row['leave'];
            }

            if($row['shifthrs'] < $whrs){
                $whrs = $row['shifthrs'];
            }

            //need to add condition in processing - start
            if(($row['shifthrs']/2) ==  $row['ut'] && $row['absent'] <= 0){
                $row['absent'] = $row['ut'];
                $row['ut'] = 0;
            }

            if(($row['shifthrs']/2) ==  $row['late'] && $row['absent'] <= 0){
                $row['absent'] = $row['late'];
                $row['late'] = 0;
            }

            if($row['shifthrs'] < $row['absent']){
                $row['absent'] = $row['shifthrs'];
            }
            
            //if whrs and late
            if(sprintf('%0.2f', $row['shifthrs']) == 9.00 && (sprintf('%0.2f', $whrs) + sprintf('%0.2f', $row['late'])) == 10.00){
                if(sprintf('%0.2f', $row['late']) > sprintf('%0.2f', $whrs) && sprintf('%0.2f', $row['late']) > (sprintf('%0.2f', $row['shifthrs'])/2)){
                    $row['late'] =  sprintf('%0.2f', $row['late'] - 1);
                    $row['absent'] = sprintf('%0.2f', $row['shifthrs']) - sprintf('%0.2f', $row['late']);
                    $row['late'] = 0;
                }
            }

             
            if((sprintf('%0.2f', $row['late']) + sprintf('%0.2f', $row['ut']) + sprintf('%0.2f', $whrs)) >= 8 && $row['shifttype'] == 'Broken Schedule' && $row['leaveappstatus'] != 1){
                $whrs = 8 - (sprintf('%0.2f', $row['late']) + sprintf('%0.2f', $row['ut']));
            }
             
            if($row['obtripstatus'] == 1 && $whrs == 0 && $row['shifttype'] == 'Broken Schedule'){
                $whrs = sprintf('%0.2f', $row['obhrs']);
                if($whrs >= 8){
                    $row['absent'] = 0;
                    $whrs = 8;
                }
            } 
            if($row['shifttype'] == 'Broken Schedule' && $row['leaveappstatus'] == 1){
                if($whrs == 0){
                    $whrs = $row['leave'];
                }
            }elseif($row['leaveappstatus'] == 1){
                //$whrs = $whrs + $row['leave'];
                if($row['idtimeleavetype'] != 1){
                    if($row['shifthrs']/2 < $whrs && $row['acthrs'] <= 0){
                        $whrs = $row['shifthrs']/2;
                        $row['absent'] = $row['shifthrs']/2;
                    }elseif($row['acthrs'] > 0 && $row['shifthrs'] < $whrs ){
                        $whrs = $row['shifthrs'];
                    }
                }else{
                    if($row['shifthrs'] < $whrs && $row['acthrs'] <= 0){
                        $whrs = $row['shifthrs'];
                    }
                }
            }

            //================ SAR CONDITION ONLY =====================//
            //need to add condition in processing - end
            if($row['shifthrs'] > 0){
                $status = 'P';
            }else{
                $status = 'D';
            }

            if($row['alias'] == 'L'){
                $status = 'PH';
            }elseif($row['alias'] == 'S'){
                $status = 'SH';
            }

             
            if($row['leave'] && $row['leaveappstatus'] == 1){
                $status = $row['leavealias'];
            }

            //lex refilling array not starting the startdate
            $daystart = date("j",strtotime($date));
            $daystartemp = date("j",strtotime($row['work_date']));
            if($daystart != $daystartemp && count($data) == 0){
                $x = $daystart;
                while($x < $daystartemp) {
                    $data[] = array();
                    $x++;
                }
            }

            //lex collecting leves
            $checkleaves = false;
            foreach ($leavearray as $key => $val) {
                if ($val['leavealias'] == $row['leavealias'] && $row['leaveappstatus'] == 1){
                    $checkleaves = true;
                    $leavearray[$key]['value'] = $leavearray[$key]['value'] + $row['leave'];
                    break;
                }
            }

            if(!$checkleaves){
                if($row['leavealias'] && $row['leaveappstatus'] == 1){
                    $leavearray[] = array(
                        'leavealias' =>$row['leavealias'],
                        'value' =>$row['leave']
                    );
                }
            }
            //================ SAR CONDITION ONLY =====================//
            $np_reg = 0;
            $np_sh = 0;
            $np_lh = 0;
            $np_rd = 0;
            if($row['defaultschedid '] != 4 && empty($row['holidaytype'])){
                $np_reg = $row['np'];
            }
            if($row['defaultschedid '] != 4 && $row['holidaytype'] == 'SPECIAL'){
                $np_sh = $row['np'];
            }
            if($row['defaultschedid '] != 4 && $row['holidaytype'] == 'LEGAL'){
                $np_lh = $row['np'];
            }
            if(($row['defaultschedid'] == 4 || $row['idshift'] == 4) && empty($row['holidaytype'])){
                $np_rd = $row['np'];
            }


            if(!$checkdate){ 
                //================ SAR CONDITION ONLY =====================//
                $cutoff_work_hours = $cutoff_work_hours + $row['shifthrs'];
                $cutoff_leaves = $leavearray;
                $cutoff_tardiness = $cutoff_tardiness + ($row['late']+$row['ut']);
                $cutoff_whrs = $cutoff_whrs + $whrs;
                //================ SAR CONDITION ONLY =====================//

                $data[] = array(
                    "status" 	            => $status,
                    "date"                  => $row['work_date'],
                    "work_date" 	        => date('D m/d/Y', strtotime($row['work_date'])),
                    'shift'                 => $row['name'],
                    'holiday_idtype'        => $row['holidaytype'],
                    "in" 	                => $row['timein'],
                    "out" 	                => $row['timeout'],
                    "in2" 	                => $row['timein2'],
                    "out2" 	                => $row['timeout2'],
                    "aaref" 	            => $row['aaref'],
    
                    "shiftin" 	                => $row['stime']? $row['stime'] :'',
                    "shiftout" 	                => $row['ftime']? $row['ftime'] :'',
                    "shiftin2" 	                => $row['sstime']? $row['sstime'] :'',
                    "shiftout2" 	            => $row['sftime']? $row['sftime'] :'',
    
                    
                    "wdate" 	                => $row['work_date']? date('m/d/Y', strtotime($row['work_date'])) : '',
                    "datein" 	                => $row['date_in']? date('m/d/Y', strtotime($row['date_in'])) : '',
                    "dateout" 	                => $row['date_out']? date('m/d/Y', strtotime($row['date_out'])) :'',
                    "datein2" 	                => $row['date_in2']? date('m/d/Y', strtotime($row['date_in2'])) :'',
                    "dateout2" 	                => $row['date_out2']? date('m/d/Y', strtotime($row['date_out2'])) :'',
                    "tardiness" 	            => sprintf('%0.2f', $row['late']+$row['ut']),
                    "late" 	                => sprintf('%0.2f', $row['late']),
                    'undertime'             => sprintf('%0.2f', $row['ut']),
                    'absent'                => sprintf('%0.2f', $row['absent']),
                    'np'                    => sprintf('%0.2f', $row['np']),
                    'work_hours'            => sprintf('%0.2f', $row['shifthrs']),
                    'total_work_hours'      => sprintf('%0.2f',$row['acthrs']),
                    'excess_hours'          => sprintf('%0.2f',$row['excess']),
                    'whrs'                  => sprintf('%0.2f',$whrs),
                    'ot'                    => sprintf('%0.2f',$row['othrs']),
                    'otstat'                => $row['otstatus'],
                    
                    'npot'                  => sprintf('%0.2f',$npot),
                    'cutoff_work_hours'     => sprintf('%0.2f',$cutoff_work_hours), 
                    'cutoff_leaves'         => $cutoff_leaves,
                    'cutoff_tardiness'      => sprintf('%0.2f',$cutoff_tardiness), 
                    'cutoff_whrs'           => sprintf('%0.2f',$cutoff_whrs), 
                    'np_reg'          => sprintf('%0.2f',$np_reg), 
                    'np_sh'           => sprintf('%0.2f',$np_sh), 
                    'np_lh'           => sprintf('%0.2f',$np_lh), 
                    'np_rd'           => sprintf('%0.2f',$np_rd), 
                    'br_reg'          => 0, 
                    'br_sh'           => 0, 
                    'br_lh'           => 0, 
                    'br_rd'           => 0);
            } 
        }
        //================ SAR CONDITION ONLY =====================//
        //lex refilling array to end day display
        $dayend = date("j",strtotime($date1));

        if($dayend == 15 ){
            $data[] = array(
                'cutoff_work_hours'     => sprintf('%0.2f',$cutoff_work_hours), 
                'cutoff_leaves'         => $cutoff_leaves,
                'cutoff_tardiness'      => sprintf('%0.2f',$cutoff_tardiness), 
                'cutoff_whrs'           => sprintf('%0.2f',$cutoff_whrs));
        }elseif($dayend == 28){
            $data[] = array(
                'cutoff_work_hours'     => sprintf('%0.2f',$cutoff_work_hours), 
                'cutoff_leaves'         => $cutoff_leaves,
                'cutoff_tardiness'      => sprintf('%0.2f',$cutoff_tardiness), 
                'cutoff_whrs'           => sprintf('%0.2f',$cutoff_whrs)); 
            $data[] = array(
                'cutoff_work_hours'     => sprintf('%0.2f',$cutoff_work_hours), 
                'cutoff_leaves'         => $cutoff_leaves,
                'cutoff_tardiness'      => sprintf('%0.2f',$cutoff_tardiness), 
                'cutoff_whrs'           => sprintf('%0.2f',$cutoff_whrs)); 
            $data[] = array(
                'cutoff_work_hours'     => sprintf('%0.2f',$cutoff_work_hours), 
                'cutoff_leaves'         =>$cutoff_leaves,
                'cutoff_tardiness'      => sprintf('%0.2f',$cutoff_tardiness), 
                'cutoff_whrs'           => sprintf('%0.2f',$cutoff_whrs)); 
        }elseif($dayend == 29){ 
            $data[] = array(
                'cutoff_work_hours'     => sprintf('%0.2f',$cutoff_work_hours), 
                'cutoff_leaves'         => $cutoff_leaves,
                'cutoff_tardiness'      => sprintf('%0.2f',$cutoff_tardiness), 
                'cutoff_whrs'           => sprintf('%0.2f',$cutoff_whrs));  
            $data[] = array(
                'cutoff_work_hours'     => sprintf('%0.2f',$cutoff_work_hours), 
                'cutoff_leaves'         => $cutoff_leaves,
                'cutoff_tardiness'      => sprintf('%0.2f',$cutoff_tardiness), 
                'cutoff_whrs'           => sprintf('%0.2f',$cutoff_whrs)); 
        }elseif($dayend == 30){
            $data[] = array(
                'cutoff_work_hours'     => sprintf('%0.2f',$cutoff_work_hours), 
                'cutoff_leaves'         => $cutoff_leaves,
                'cutoff_tardiness'      => sprintf('%0.2f',$cutoff_tardiness), 
                'cutoff_whrs'           => sprintf('%0.2f',$cutoff_whrs)); 
        }
        //================ SAR CONDITION ONLY =====================//


    }else{
        return $data[] = array();
    }
    return $data;
}


function getTotal($con,$search,$date1){
	$Qry = new Query();	
	$Qry->table ="vw_dataemployees";
	$Qry->selected ="*";
	$Qry->fields = "id!=1 AND (sdate > '".$date1."' OR sdate IS NULL) AND etypeid = 1 ". $search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

function getJobalias($con, $id){
    $data = 0;
    $Qry=new Query();
    $Qry->table="tbljoblvl";
    $Qry->selected="*";
    $Qry->fields=" id='".$id."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        if($row=mysqli_fetch_assoc($rs)){
            $data = $row['alias'];
        }
    }else{
        return $data = 0;
    }

    return $data;
}

function getSARDept( $con, $idunit ){
		
    $data = array();
    $Qry 			= new Query();	
    $Qry->table     = "tblbunits";
    $Qry->selected  = "*";
    $Qry->fields    = "id='".$idunit."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
                return $row['name'];
        }
    }
    return '';
}
?>