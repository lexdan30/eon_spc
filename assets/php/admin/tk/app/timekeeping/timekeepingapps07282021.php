<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$return = null;	

$shift_cols = array("monday"	=>"mon", 
					"tuesday"	=>"tue",
					"wednesday"	=>"wed",
					"thursday"	=>"thu",
					"friday"	=>"fri",
					"saturday"	=>"sat", 
                    "sunday"	=>"sun");
               
if($param->date == ''){
    $param->date =date('Y-m-01');
}

$Qry = new Query();	
$Qry->table     = "vw_timesheet";
$Qry->selected  = "*";
$Qry->fields    = "id = '".$param->accountid."' AND `work_date` BETWEEN '".date("Y-m-01", strtotime($param->date) )."' AND '".date('Y-m-t', strtotime($param->date) )."' ORDER BY work_date";

$rs = $Qry->exe_SELECT($con);

if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        // if( !empty($row['holiday_id']) ){
		// 	$row['shift_status'] = ucwords(strtolower($row['holiday_type'])).' Holiday';
		// }
		
		// if( empty($row['shift_status']) ){
		// 	$shift_field			= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];
		// 	$shift_info 			= getDateShiftData( $con, $row['empID'], $shift_field, $row['work_date'] );
		// 	$row['shift_status']	= $shift_info[0];
		// }

        if($row['FPidshift']  != 4){
            if($row['holiday']){
                $backgroundColor = '#f00404';
                
                $data[] = array( 
                    "title"             => $row['holiday'] . ' - ' . strtoupper($row['holidaytype']) . ' HOLIDAY',
                    "start"             =>  $row['work_date'],
                    "end" 	            =>  $row['work_date'],
                    "backgroundColor"   => $backgroundColor,
                    "sort"              => 1
                );
			}

            $title = $row['name'];

            if($title == 'Rest Day'){
                $backgroundColor = '#00b050';
                $data[] = array( 
                    "title"             => $title ,
                    "start"             =>  $row['work_date'],
                    "end" 	            =>  $row['work_date'],
                    "backgroundColor"   => $backgroundColor,
                    "sort"              => 1
                );
			}else{
                $backgroundColor = '#f39c12';
                $data[] = array( 
                    "title"             => $title ,
                    "start"             =>  $row['work_date'],
                    "end" 	            =>  $row['work_date'],
                    "backgroundColor"   => $backgroundColor,
                    "sort"              => 1
                );
			}
        

           if(!empty($row['timein'])){
                $title =  $row['timein'] ? 'Time-In '. date('h:i a', strtotime($row['timein'])) : $row['timein'] ;
                $data[] = array( 
                    "id" 	=> $row['id'],
                    "title" => $title ,
                    "start" =>  $row['work_date'],
                    "end" 	=>  $row['work_date'],
                    
                    "sort"  =>  2
                );

            }

           if(!empty($row['timeout'])){
                $title =   $row['timeout'] ? 'Time-Out '. date('h:i a', strtotime($row['timeout'])) : $row['timeout'] ;
                $backgroundColor = '#7cacff';
                $data[] = array( 
                    "id" 	=> $row['id'],
                    "title" => $title ,
                    "start" =>  $row['work_date'],
                    "end" 	=>  $row['work_date'],
                    "backgroundColor"   => $backgroundColor,
                    "sort"  => 3
                );
            }
            
            if(!empty($row['timein2'])){
                $title =  $row['timein2'] ? 'Time-In2 '. date('h:i a', strtotime($row['timein2'])) : $row['timein2'] ;
                $data[] = array( 
                    "id" 	=> $row['id'],
                    "title" => $title ,
                    "start" =>  $row['work_date'],
                    "end" 	=>  $row['work_date'],
                    
                    "sort"  =>  4
                );

            }

           if(!empty($row['timeout2'])){
                $title =   $row['timeout2'] ? 'Time-Out2 '. date('h:i a', strtotime($row['timeout2'])) : $row['timeout2'] ;
                $backgroundColor = '#7cacff';
                $data[] = array( 
                    "id" 	=> $row['id'],
                    "title" => $title ,
                    "start" =>  $row['work_date'],
                    "end" 	=>  $row['work_date'],
                    "backgroundColor"   => $backgroundColor,
                    "sort"  => 5
                );
            }
        }

        if($row['FPidshift']  == 4){
            if($row['holiday']){
                $backgroundColor = '#f00404';
                
                $data[] = array( 
                    "title"             => $row['holiday'] ,
                    "start"             =>  $row['work_date'],
                    "end" 	            =>  $row['work_date'],
                    "backgroundColor"   => $backgroundColor,
                    "sort"              => 1
                );
			}

            $title = $row['name'];

            if($title == 'Rest Day'){
				$backgroundColor = '#00b050';
			}else{
				$backgroundColor = '#f39c12';
			}

            $data[] = array( 
                "id" 	=> $row['id'],
                "title" => $title,
                "start" =>  $row['work_date'],
                "end" 	=>  $row['work_date'],
                "backgroundColor"   => $backgroundColor,
                "sort"  => 4
            );
			
			   
           if(!empty($row['in'])){
                $title =  $row['in'] ? 'Time-In '. date('h:i a', strtotime($row['in'])) : $row['in'] ;
                $data[] = array( 
                    "id" 	=> $row['id'],
                    "title" => $title ,
                    "start" =>  $row['work_date'],
                    "end" 	=>  $row['work_date'],
                    
                    "sort"  =>  5
                );

                }

            if(!empty($row['out'])){
                    $title =   $row['out'] ? 'Time-Out '. date('h:i a', strtotime($row['out'])) : $row['out'] ;
                    $backgroundColor = '#7cacff';
                    $data[] = array( 
                        "id" 	=> $row['id'],
                        "title" => $title ,
                        "start" =>  $row['work_date'],
                        "end" 	=>  $row['work_date'],
                        "backgroundColor"   => $backgroundColor,
                        "sort"  => 6
                    );
                }

        }



        // if($row['idleave']){

        //     $titles = $row['idleave'];
        //     $title1 =$row['leavename'];
        //     $backgroundColor_pending='';
        //     $backgroundColor_declined='';
        //     $backgroundColor_approved='';

        //      if ($row['leavestat'] == 3) {
        //         $status = 'PENDING';
        //         $backgroundColor_pending ='<div id=""><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
        //         <span>' . $title1 . '</span></div></div>';
        //     }
        //      if ($row['leavestat'] == 2) {
        //          $status = 'DECLINED';
        //         $backgroundColor_declined ='<div id=""><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
        //         <span>' . $title1 . '</span></div></div>';
        //     }
        //     if ($row['leavestat'] == 1) {
        //         $status = 'APPROVED';
        //         $backgroundColor_approved ='<div id=""><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
        //         <span>' . $title1 . '</span></div></div>';
        //     }
        //     // $backgroundColor = '#f32c12';
        //     $data[] = array( 
        //         // "id" 	=> $row['id'],
        //      "application"       => 'leave',
        //         "title" => $row['leavename'],
        //         "remarks"    =>  $row['leavehrs'] . ' hrs',
        //         // "title"  => $row['leavename'],
        //         "start" =>  $row['work_date'],
        //         "end" 	=>  $row['work_date'],
        //         'status'		=> $status,
        //         "backgroundColor"   => getColors($con,$row['idleave']),
        //         // "backgroundColor_pending" => $backgroundColor_pending,
        //         // "backgroundColor_declined" => $backgroundColor_declined,
        //         // "backgroundColor_approved" => $backgroundColor_approved,
        //         "sort" => 4
        //     );


        // }


        // if($row['aaid']){
        //      $backgroundColor = '#da3e28';
        //     $data[] = array( 
        //         // "id" 	=> $row['id'],
        //         "title"  => 'Attendance Adjustment',
        //         "start" =>  $row['work_date'],
        //         "end" 	=>  $row['work_date'],
        //         "backgroundColor"   => $backgroundColor,
        //         "sort" => 4
        //     );

        // }


         
    //     if($row['csid']){
    //         $backgroundColor = '#079b49';
    //        $data[] = array( 
    //            // "id" 	=> $row['id'],
    //            "title"  => 'Change Shift',
    //            "start" =>  $row['work_date'],
    //            "end" 	=>  $row['work_date'],
    //            "backgroundColor"   => $backgroundColor,
    //            "sort" => 4
    //        );

    //    }


        // if($row['otid']){    
        //     $backgroundColor = '#c47f12';

        //     $data[] = array( 
        //         "title" => 'Overtime',
        //         // "start" =>  $ot['planned_date'],
        //         // "end"   =>  $ot['planned_date'],
        //         "start" =>  $row['work_date'],
        //         "end" 	=>  $row['work_date'],
        //         "backgroundColor"   => $backgroundColor,
        //         "sort"  => 5
        //     );

        // }

        // if($row['obid']){    
        //     $backgroundColor = '#00bbf0';

        //     $data[] = array( 
        //         "title" => 'Official Business Trip',
        //         // "start" =>  $ot['planned_date'],
        //         // "end"   =>  $ot['planned_date'],
        //         "start" =>  $row['work_date'],
        //         "end" 	=>  $row['work_date'],
        //         "backgroundColor"   => $backgroundColor,
        //         "sort"  => 6
        //     );
        // }
    }
    $return =  json_encode($data);
}else{
    $return = json_encode(array("sort2"  => $Qry->fields));
}

print $return;
mysqli_close($con);


function getColors($con, $idleave){
    $Qry=new Query();
    $Qry->table="tblleaves";
    $Qry->selected="color";
    $Qry->fields="id='".$idleave."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            $data = $row['color'];
        }
        return $data;
    }
    return null;
}



?>