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
$Qry->table     = "vw_timekeepingapps";
$Qry->selected  = "id, idshift, holiday_id, title2, work_date, title, holidayProvid, holidayMunid, employeeProvid, `in`, in2, `out`, out2, idleave, leavename, leavestat, leavehrs";
$Qry->fields    = "CAST(aid AS INT) = '".$param->accountid."' AND DATE(work_date) BETWEEN '".date("Y-m-01", strtotime($param->date) )."' AND '".date('Y-m-t', strtotime($param->date) )."' GROUP BY work_date ORDER BY CONCAT(work_date, lname) ASC";

$rs = $Qry->exe_SELECT($con);

if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){

        // if( !empty($row['holiday_id']) ){
		// 	$row['shift_status'] = ucwords(strtolower($row['holiday_type'])).' Holiday';
		// }
		
		// if( empty($row['shift_status']) ){
		// 	$shift_field			= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];
		// 	$shift_info 			= getDateShiftData( $con, $row['empID'], $shift_field, $row['work_date'] );
		// 	$row['shift_status']	= $shift_info[0];
		// }

        if($row['idshift']  != 4){
            $title = $row['title'];

            if($title == 'Rest Day'){
				$backgroundColor = '#00b050';
			}else if($row['holiday_id']){ 
                $backgroundColor = '#f00404';
                $data[] = array( 
                    "title"             => $row['title2'], // we can put SH + title2 but we must follow calendar standard to remain its integrity as calendar bullets
                    "start"             =>  $row['work_date'],
                    "end" 	            =>  $row['work_date'],
                    "backgroundColor"   => '#f39c12',
                    "sort"              => 0
                );
			}else{
				$backgroundColor = '#f39c12';
            }
            
            // if($row['nameDay'] == 'Saturday' || $row['nameDay'] == 'Sunday'){ 
            //     $data[] = array( 
            //         "title"             => 'Rest Day' , // we can put RD + title but we must follow calendar standard to remain its integrity as calendar bullets
            //         "start"             =>  $row['work_date'],
            //         "end" 	            =>  $row['work_date'],
            //         "backgroundColor"   => '#00b050',
            //         "sort"              => 0
            //     );
               
            //     $data[] = array( 
            //         "title"             => $title,
            //         "start"             =>  $row['work_date'],
            //         "end" 	            =>  $row['work_date'],
            //         "backgroundColor"   => $backgroundColor,
            //         "sort"              => 1
            //     );
            
            // }else{
                if($row['holiday_id'] && !empty($row['holidayProvid'])){
                    //state here the municipality capital of all provinces as array in static declaration
                    //SELECT * FROM tblmunicipality WHERE citymunDesc LIKE '%(Capital)%' to check names for capital
                    //sample static declation fro capital of its provinces(batangas=batangas city(capital)->353,cavite=trece marteres city(capital)->404,
                    //laguna=santa cruz(capital)->431,makati=city of makati->1374,
                    //$corporateCapitals = array('353', '404', '431', '1374'); 
                    $viewCapitalholiday = false;
                    if(empty($row['holidayMunid']) && $row['employeeProvid'] == $row['holidayProvid'])
                    {
                            $viewCapitalholiday = true;
                    }
                    if(($row['holidayMunid'] == $row['employeeMunid']) || ($viewCapitalholiday)){
                        $data[] = array( 
                            "title"             => $title,
                            "start"             =>  $row['work_date'],
                            "end" 	            =>  $row['work_date'],
                            "backgroundColor"   => $backgroundColor,
                            "sort"              => 1
                        );
                    }
                }else{
                    $data[] = array( 
                        "title"             => $title,
                        "start"             =>  $row['work_date'],
                        "end" 	            =>  $row['work_date'],
                        "backgroundColor"   => $backgroundColor,
                        "sort"              => 1
                    );
                }
            //}

           if(!empty($row['in'])){
                $title =  $row['in'] ? 'Time-In '. date('h:i a', strtotime($row['in'])) : $row['in'] ;
                $data[] = array( 
                    "id" 	=> $row['id'],
                    "title" => $title ,
                    "start" =>  $row['work_date'],
                    "end" 	=>  $row['work_date'],
                    "backgroundColor"   => '#3c8dbc',
                    "sort"  =>  2
                );

            }

            if(!empty($row['in2'])){
                $title =  $row['in2'] ? 'Time-In2 '. date('h:i a', strtotime($row['in2'])) : $row['in2'] ;
                $data[] = array( 
                    "id" 	=> $row['id'],
                    "title" => $title ,
                    "start" =>  $row['work_date'],
                    "end" 	=>  $row['work_date'],
                    "backgroundColor"   => '#3c8dbc',
                    "sort"  =>  4
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
                    "sort"  => 3
                );
            }

            if(!empty($row['out2'])){
                $title =   $row['out2'] ? 'Time-Out2 '. date('h:i a', strtotime($row['out2'])) : $row['out2'] ;
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

        if($row['idshift']  == 4){
            $title = $row['title'];

            if($title == 'Rest Day'){
				$backgroundColor = '#00b050';
			}else if($row['holiday_id']){
				$backgroundColor = '#f00404';
			}else{
				$backgroundColor = '#f39c12';
			}

           
        if($row['holiday_id'] && !empty($row['holidayProvid'])){
                //state here the municipality capital of all provinces as array in static declaration
                //SELECT * FROM tblmunicipality WHERE citymunDesc LIKE '%(Capital)%' to check names for capital
                //sample static declation fro capital of its provinces(batangas=batangas city(capital)->353,cavite=trece marteres city(capital)->404,
                //laguna=santa cruz(capital)->431,makati=city of makati->1374,
                //$corporateCapitals = array('353', '404', '431', '1374'); 
                $viewCapitalholiday = false;
                if(empty($row['holidayMunid']) && $row['employeeProvid'] == $row['holidayProvid'])
                {
                        $viewCapitalholiday = true;
                }
                if(($row['holidayMunid'] == $row['employeeMunid']) || ($viewCapitalholiday)){
                    $data[] = array( 
                        "id" 	=> $row['id'],
                        "title" => $title,
                        "start" =>  $row['work_date'],
                        "end" 	=>  $row['work_date'],
                        "backgroundColor"   => $backgroundColor,
                        "sort"  => 4
                    );
                }
        }else{
            $data[] = array( 
                "id" 	=> $row['id'],
                "title" => $title,
                "start" =>  $row['work_date'],
                "end" 	=>  $row['work_date'],
                "backgroundColor"   => $backgroundColor,
                "sort"  => 4
            );
        }
            

           
			
			   
           if(!empty($row['in'])){
                $title =  $row['in'] ? 'Time-In '. date('h:i a', strtotime($row['in'])) : $row['in'] ;
                $data[] = array( 
                    "id" 	=> $row['id'],
                    "title" => $title ,
                    "start" =>  $row['work_date'],
                    "end" 	=>  $row['work_date'],
                    "backgroundColor"   => '#3c8dbc',
                    "sort"  =>  5
                );

                }

                if(!empty($row['in2'])){
                    $title =  $row['in2'] ? 'Time-In2 '. date('h:i a', strtotime($row['in2'])) : $row['in2'] ;
                    $data[] = array( 
                        "id" 	=> $row['id'],
                        "title" => $title ,
                        "start" =>  $row['work_date'],
                        "end" 	=>  $row['work_date'],
                        "backgroundColor"   => '#3c8dbc',
                        "sort"  =>  7
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

                if(!empty($row['out2'])){
                    $title =   $row['out2'] ? 'Time-Out2 '. date('h:i a', strtotime($row['out2'])) : $row['out2'] ;
                    $backgroundColor = '#7cacff';
                    $data[] = array( 
                        "id" 	=> $row['id'],
                        "title" => $title ,
                        "start" =>  $row['work_date'],
                        "end" 	=>  $row['work_date'],
                        "backgroundColor"   => $backgroundColor,
                        "sort"  => 8
                    );
                }

        }



        if($row['idleave']){

            $titles = $row['idleave'];
            $title1 =$row['leavename'];
            $backgroundColor_pending='';
            $backgroundColor_declined='';
            $backgroundColor_approved='';
            $trap = "leaves";

             if ($row['leavestat'] == 3) {
                $status = 'PENDING';
                $backgroundColor_pending ='<div id=""><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title1 . '</span></div></div>';
            }
             if ($row['leavestat'] == 2) {
                 $status = 'DECLINED';
                $backgroundColor_declined ='<div id=""><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title1 . '</span></div></div>';
            }
            if ($row['leavestat'] == 1) {
                $status = 'APPROVED';
                $backgroundColor_approved ='<div id=""><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title1 . '</span></div></div>';
            }

            // $backgroundColor = '#f32c12';

              $data[] = array( 
                // "id" 	=> $row['id'],
                "application"       => 'leave',
                "title" => $row['leavename'],
                "remarks"    =>  $row['leavehrs'] . ' hrs',
                // "title"  => $row['leavename'],
                "start" =>  $row['work_date'],
                "end" 	=>  $row['work_date'],
                'status'		=> $status,
                "backgroundColor"   => getColors($con,$row['idleave']),
                // "backgroundColor_pending" => $backgroundColor_pending,
                // "backgroundColor_declined" => $backgroundColor_declined,
                // "backgroundColor_approved" => $backgroundColor_approved,
                "sort" => 4,
                "trap" => $trap
            );


        }


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
        while($row=mysqli_fetch_assoc($rs)){
            $data = $row['color'];
        }
        return $data;
    }
    return null;
}



?>