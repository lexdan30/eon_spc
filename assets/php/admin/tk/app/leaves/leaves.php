<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$return = null;	

$Qry = new Query();	
$Qry->table     = "vw_leave_application";
$Qry->selected  = "
stat, idleave, cancel_reason, creator, leave_name,
remarks, file, hrs, docnumber, leave_status,
id,approver1,approver2,approver3,approver4, approver1_reason, approver2_reason, approver3_reason, approver4_reason,
approver1_stat, approver2_stat, approver3_stat, approver4_stat, `date`, approver2, stime, ftime";

if($param->date == ''){
    $Qry->fields    = "idacct = '".$param->accountid."' 
                    AND ( year( CURRENT_DATE()) = year(date)
                    and month( CURRENT_DATE()) = month(date) )";
}else{
    $date = $param->date;
    $Qry->fields    = "idacct = '".$param->accountid."' 
                    AND ( year('". $date ."') = year(date)
                    and month('". $date ."') = month(date) )";
}

$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        $start= $row['date'];
        $end = $row['date'];

        // $data["data"][] = array(
        //  'id'        		=> (int)$row['id'],
		// 	'docnumber'			=> $row['docnumber'],	
        //  'idleave'     		=> $row['idleave'],
        //  'leave_name'   		=> $row['leave_name'],
        //  'leave_type'    	=> $row['leave_type'],
		// 	'idtype'			=> $row['idtype'],
		// 	'idacct'			=> $row['idacct'],
		// 	'empid'				=> $row['empid'],
		// 	'empname'			=> $row['empname'],
		// 	'date'				=> $row['date'],
		// 	'time_in'			=> $row['stime'],
		// 	'time_out'			=> $row['ftime'],
		// 	'hrs'				=> $row['hrs'],
		// 	'remarks'			=> $row['remarks'],
		// 	'file'				=> $row['file'],
		// 	'leave_status'		=> $row['leave_status'],
		// 	'date_approve'		=> $row['date_approve']
        // );

        // if( $row['idleave'] == '2'){
        //     $backgroundColor = '#fe9901';
        // }
        // if( $row['idleave'] == '1'){
        //     $backgroundColor = '#00af50';
        // }
        // if( $row['idleave'] == '3'){
        //     $backgroundColor = '#525050';
        // }
        // if( $row['idleave'] == '4'){
        //     $backgroundColor = '#395723';
        // }
        // if($row['idleave'] == '5'){
        //     $backgroundColor = '#7e6000';
        // }
        // if($row['idleave'] == '6'){
        //     $backgroundColor = '#01b0f1';
        // }
        // if($row['idleave'] == '7'){
        //     $backgroundColor = '#01b0f1';
        // }
        // if($row['idleave'] == '8'){
        //     $backgroundColor = '#ff3300';
        // }
        // if($row['idleave'] == '9'){
        //     $backgroundColor = '#0071c0';
        // }
        // if($row['idleave'] == '10'){
        //     $backgroundColor = '#1f4e78';
        // }
        // if($row['idleave'] == '11'){
        //     $backgroundColor = '#58267f';
        // }
        // if($row['idleave'] == '12'){
        //     $backgroundColor = '#7e6000';
        // }

        $approverstatus = '';

        if($row['stat'] == 3){
            if (is_null($row['approver2'])){
                if(is_null($row['approver1_stat'])){
                    if($row['cancel_reason'] != null){
                        $approverstatus = 'Cancelled';
                    }else{
                       $approverstatus = 'Pending Department Head';
                    }
                }else{
                    if($row['approver1_stat'] == 1){
                    
                    } else if($row['approver1_stat'] == 2){
                    
                    }else if($row['approver1_stat'] == 3){
                    
                    }else if($row['cancel_reason'] != null){
                        $approverstatus = 'Cancelled';
                    }
                }
            }else{
                if(is_null($row['approver1_stat'])){
                    if($row['approver1_stat'] == 1){
                    
                    } else if($row['approver1_stat'] == 2){
                    
                    }else if($row['approver1_stat'] == 3){
                    
                    }else if($row['cancel_reason'] != null){
                        $approverstatus = 'Cancelled';
                    }else{
                        //$approverstatus = 'Pending 1st level approver';
                        if(strpos( $row['approver1'], ',')){
                            $approverstatus = 'Pending Approver 1: ' . getmultiNames( $con, $row['approver1'] );
                        }else{
                            $approverstatus = 'Pending Approver 1: ' . getAccountName( $con, $row['approver1'] );
                        }
                        
                    }
                }else if(is_null($row['approver2_stat'])){
                    if($row['approver2_stat'] == 1){
                    
                    } else if($row['approver2_stat'] == 2){
                    
                    }else if($row['approver2_stat'] == 3){
                    
                    }else if($row['cancel_reason'] != null){
                        $approverstatus = 'Cancelled';
                    }else{

                        if(strpos( $row['approver2'], ',')){
                            $approverstatus = 'Pending Approver 2: ' . getmultiNames( $con, $row['approver2'] );
                        }else{
                            $approverstatus = 'Pending Approver 2: ' . getAccountName( $con, $row['approver2'] );
                        }
                    }
                }else if(is_null($row['approver3_stat'])){
                    if($row['approver3_stat'] == 1){
                    
                    } else if($row['approver3_stat'] == 2){
                    
                    }else if($row['approver3_stat'] == 3){
                    
                    }else if($row['cancel_reason'] != null){
                        $approverstatus = 'Cancelled';
                    }else{
                        if(strpos( $row['approver3'], ',')){
                            $approverstatus = 'Pending Approver 3: ' . getmultiNames( $con, $row['approver3'] );
                        }else{
                            $approverstatus = 'Pending Approver 3: ' . getAccountName( $con, $row['approver3'] );
                        }
                    }
                }else if(is_null($row['approver4_stat'])){
                    if($row['approver4_stat'] == 1){
                    
                    } else if($row['approver4_stat'] == 2){
                    
                    }else if($row['approver4_stat'] == 3){
                    
                    }else if($row['cancel_reason'] != null){
                        $approverstatus = 'Cancelled';
                    }else{
                        if(strpos( $row['approver4'], ',')){
                            $approverstatus = 'Pending Approver 4: ' . getmultiNames( $con, $row['approver4'] );
                        }else{
                            $approverstatus = 'Pending Approver 4: ' . getAccountName( $con, $row['approver4'] );
                        }
                    }
                }
            }
        }
        if($row['stat'] == 2){
            $approverstatus = 'Disapproved';
            if($row['approver1_stat'] == 2){
                $row['cancel_reason']  = $row['approver1_reason'];
            }
             if($row['approver2_stat'] == 2){
                $row['cancel_reason']  = $row['approver2_reason'];
            }
             if($row['approver3_stat'] == 2){
                $row['cancel_reason']  = $row['approver3_reason'];
            }
             if($row['approver4_stat']== 2){
                $row['cancel_reason']  = $row['approver4_reason'];
            }
        }
        if($row['stat'] == 1){
            $approverstatus = 'Approved';
        }

        $data[] = array( 
            "id" 			    => $row['id'],
			"creator"			=> $row['creator'],
            "title" 			=> $row['leave_name'],
            "start" 			=> $start,
            "end" 		    	=> $end,
            "backgroundColor"   => getBackground($con,$row['idleave']),
            'leave_status'		=> $row['leave_status'],
            'remarks'		    => $row['remarks'],
            'cancelreason'		=> $row['cancel_reason'],
            'approverstatus'    => $approverstatus,
            'file'              => $row['file'],
            'sort'              => 10,
            'ticketno'          => $row['docnumber'],
            'idleave'           => $row['idleave'],
            'hrs'               => $row['hrs'],
            'start_time'        =>$row['stime'],
            'end_time'          =>$row['ftime'],
        );
    }

// $Qry5 = new Query();	
// $Qry5->table     = "tblappcancel";
// $Qry5->selected  = "*";

// if($param->date == ''){
//     $Qry5->fields    = "idacct = '".$param->accountid."' 
//                         AND ( year( CURRENT_DATE()) = year(date)
//                         and month( CURRENT_DATE()) = month(date) )";
// }else{
//     $date = $param->date;
//     $Qry5->fields    = "idacct = '".$param->accountid."' 
//                     AND ( year('". $date ."') = year(date)
//                     and month('". $date ."') = month(date) )";
// }
// $Qry5->fields    = $Qry5->fields    . " and status!='4'";
// $rs5 = $Qry5->exe_SELECT($con);

// if(mysqli_num_rows($rs5)>= 1){
//         while($row5=mysqli_fetch_array($rs5)){
//         $start= $row5['date'];
//         $end = $row5['date'];

//         if($row5['status'] == 3){
//             $row5['status'] = 'PENDING';
//         }
//         if($row5['status'] == 3){
//             $row5['status'] = 'DECLINED';
//         }
//         if($row5['status'] == 1){
//             $row5['status'] = 'APPROVED';
//         }

//         if($row5['type'] == 'obtrip'){
//             $row5['title'] = 'Official Business Trip - Cancellation';
//             //$row5['remarks'] = $row5['remarks'] . '<br>' .  date('h:i a', strtotime($row5['start_time'])) . ' - ' . date('h:i a', strtotime($row5['end_time']));

//         }
//         if($row5['type'] == 'attendance'){
//             $row5['title'] = 'Attendance Ajustment - Cancellation';
//         }
//         if($row5['type'] == 'overtime'){
//             $row5['title'] = 'Overtime - Cancellation';
//         }
//         if($row5['type'] == 'changeshift'){
//             $row5['title'] = 'Change Shift - Cancellation';
//         }


        

//         $data[] = array( 
//             "application"       => $row5['type'],
//             "title" 			=> $row5['title'],
//             "ids" 			    => $row5['id'],
// 			"creator"			=> $row5['idacct'],
//             "start" 			=> $start,
//             "end" 		    	=> $end,
//             //"remarks"           => $row5['remarks'],
//             "backgroundColor"   => '#63615e',
//             "status"            => $row5['status'],
//             'sort'              => 9,
//             'ticketno'          => $row5['docnumber'],
//             'origin'            => $row5['origin']
            
//         );
//     }
   
// }

    $return =  json_encode($data);
}else{
    $return = json_encode(array());
}





print $return;
mysqli_close($con);


function getBackground( $con,$id){
    $Qry 			= new Query();	
    $Qry->table     = "tblleaves";
    $Qry->selected  = "color";
    $Qry->fields    = "id = '".$id."'";
    $rs = $Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getBackground');
    if(mysqli_num_rows($rs)>= 1){
        return mysqli_fetch_assoc($rs)['color'];
    }
}


?>