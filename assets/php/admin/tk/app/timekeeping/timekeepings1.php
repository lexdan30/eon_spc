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
$Qry->table     = "vw_data_timesheet";
$Qry->selected  = "*";
$Qry->fields    = "empID = '".$param->accountid."' AND ( work_date >= '".date("Y-m-01", strtotime($param->date) )."' AND work_date <= '".date('Y-m-t', strtotime($param->date) )."' ) ORDER BY work_date ASC";

$rs = $Qry->exe_SELECT($con);

if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        if( !empty($row['holiday_id']) ){
			$row['shift_status'] = ucwords(strtolower($row['holiday_type'])).' Holiday';
		}
		
		if( empty($row['shift_status']) ){
			$shift_field			= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];
			$shift_info 			= getDateShiftData( $con, $row['empID'], $shift_field, $row['work_date'] );
			$row['shift_status']	= $shift_info[0];
		}

        if($row['idshift']  != 4){
            $title = $row['shift_status'];

            if($title == 'Rest Day'){
				$backgroundColor = '#00b050';
			}else if($row['holiday_id']){
				$backgroundColor = '#f00404';
			}else{
				$backgroundColor = '#f39c12';
			}
          
            $data[] = array( 
                "title"             => $title ,
                "start"             =>  $row['work_date'],
                "end" 	            =>  $row['work_date'],
                "backgroundColor"   => $backgroundColor,
                "sort"              => 1
            );

           if(!empty($row['in'])){
                $title =  $row['in'] ? 'Time-In '. date('h:i a', strtotime($row['in'])) : $row['in'] ;
                $data[] = array( 
                    "id" 	=> $row['id'],
                    "title" => $title ,
                    "start" =>  $row['work_date'],
                    "end" 	=>  $row['work_date'],
                    
                    "sort"  =>  2
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
            
        }

        if($row['idshift']  == 4){
            $title = $row['shift_status'];
            $backgroundColor = '#00AF50';
            
            // $backgroundColorRD = '#ff1a1a';

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



        if($row['idleave']){

            $titles = $row['idleave'];
            $title1 =$row['leavename'];
            $backgroundColor_pending='';
            $backgroundColor_declined='';
            $backgroundColor_approved='';

             if ($row['leavestat'] == 3) {
                $backgroundColor_pending ='<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title1 . '</span></div></div>';
            }
             if ($row['leavestat'] == 2) {
                $backgroundColor_declined ='<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title1 . '</span></div></div>';
            }
            if ($row['leavestat'] == 1) {
                $backgroundColor_approved ='<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title1 . '</span></div></div>';
            }
            // $backgroundColor = '#f32c12';
            $data[] = array( 
                // "id" 	=> $row['id'],
                "titles" => $titles,
                // "title"  => $row['leavename'],
                "start" =>  $row['work_date'],
                "end" 	=>  $row['work_date'],
                "backgroundColor"   => getColors($con,$row['idleave']),
                "backgroundColor_pending" => $backgroundColor_pending,
                "backgroundColor_declined" => $backgroundColor_declined,
                "backgroundColor_approved" => $backgroundColor_approved,
                "sort" => 4
            );

        }


        if($row['aaid']){
            $backgroundColor = '#da3e28';
            $title = 'Attendance Adjustment';
            $backgroundColor_pending='';
            $backgroundColor_declined='';
            $backgroundColor_approved='';

             if ($row['adj_stat'] == 3) {
                $backgroundColor_pending ='<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title . '</span></div></div>';
            }
             if ($row['adj_stat'] == 2) {
                $backgroundColor_declined ='<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title . '</span></div></div>';
            }
            if ($row['adj_stat'] == 1) {
                $backgroundColor_approved ='<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title . '</span></div></div>';
            }

            $data[] = array(                 
                // "title"  => 'Attendance Adjustment',
                "start" =>  $row['work_date'],
                "end" 	=>  $row['work_date'],
                "backgroundColor"   => $backgroundColor,
                "backgroundColor_pending"   => $backgroundColor_pending,
                "backgroundColor_declined"   => $backgroundColor_declined,
                "backgroundColor_approved"   => $backgroundColor_approved,
                "sort" => 4
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


        if($row['csid']){
            $backgroundColor = '#079b49';
            $title  = 'Change Shift';
            $backgroundColor_pending='';
            $backgroundColor_declined='';
            $backgroundColor_approved='';

             if ($row['changeshift_stat'] == 3) {
                $backgroundColor_pending ='<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title . '</span></div></div>';
            }
             if ($row['changeshift_stat'] == 2) {
                $backgroundColor_declined ='<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title . '</span></div></div>';
            }
            if ($row['changeshift_stat'] == 1) {
                $backgroundColor_approved ='<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title . '</span></div></div>';
            }
           $data[] = array( 
               // "id" 	=> $row['id'],
            //    "title"  => 'Change Shift',
               "start" =>  $row['work_date'],
               "end" 	=>  $row['work_date'],
               "backgroundColor"   => $backgroundColor,
               "backgroundColor_pending"   => $backgroundColor_pending,
               "backgroundColor_declined"   => $backgroundColor_declined,
               "backgroundColor_approved"   => $backgroundColor_approved,
               "sort" => 4
           );
        }

         
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

        if($row['otid']){    
            $backgroundColor = '#c47f12';
            $title  = 'Overtime';
            $backgroundColor_pending='';
            $backgroundColor_declined='';
            $backgroundColor_approved='';

             if ($row['overtime_status'] == 3) {
                $backgroundColor_pending ='<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title . '</span></div></div>';
            }
             if ($row['overtime_status'] == 2) {
                $backgroundColor_declined ='<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title . '</span></div></div>';
            }
            if ($row['overtime_status'] == 1) {
                $backgroundColor_approved ='<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title . '</span></div></div>';
            }

            $data[] = array( 
                // "title" => 'Overtime',
                // "start" =>  $ot['planned_date'],
                // "end"   =>  $ot['planned_date'],
                "start" =>  $row['work_date'],
                "end" 	=>  $row['work_date'],
                "backgroundColor"   => $backgroundColor,
                "backgroundColor_pending"   => $backgroundColor_pending,
                "backgroundColor_declined"   => $backgroundColor_declined,
                "backgroundColor_approved"   => $backgroundColor_approved,
                "sort"  => 5
            );
        }

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

        if($row['obid']){    
            $backgroundColor = '#00bbf0';
            $title = 'Official Business Trip';
            $backgroundColor_pending='';
            $backgroundColor_declined='';
            $backgroundColor_approved='';

             if ($row['ob_stat'] == 3) {
                $backgroundColor_pending ='<div id="block_container"><div id="bloc1" class="dot3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title . '</span></div></div>';
            }
             if ($row['ob_stat'] == 2) {
                $backgroundColor_declined ='<div id="block_container"><div id="bloc1" class="dot2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title . '</span></div></div>';
            }
            if ($row['ob_stat'] == 1) {
                $backgroundColor_approved ='<div id="block_container"><div id="bloc1" class="dot">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div><div id="bloc2">
                <span>' . $title . '</span></div></div>';
            }
            $data[] = array( 
                // "title" => 'Official Business Trip',
                // "start" =>  $ot['planned_date'],
                // "end"   =>  $ot['planned_date'],
                "start" =>  $row['work_date'],
                "end" 	=>  $row['work_date'],
                "backgroundColor"   => $backgroundColor,
                "backgroundColor_pending"   => $backgroundColor_pending,
                "backgroundColor_declined"   => $backgroundColor_declined,
                "backgroundColor_approved"   => $backgroundColor_approved,
                "sort"  => 6
            );
        }

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