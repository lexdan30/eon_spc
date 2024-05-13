<?php
require_once('../../../../logger.php');
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
$Qry->table     = "
(SELECT 
    id,
    work_date 
  FROM
    vw_datacurrentworkdates3) AS a 
  LEFT JOIN 
    (SELECT 
      idacct,
      idshift,
      `date`,
      timein,
      date_out,
      timeout,
      date_in,
      timein2,
      date_in2,
      date_out2,
      timeout2 
    FROM
      tbltimesheet) AS b 
    ON (
      b.date = a.work_date 
      AND b.idacct = a.id
    ) 
  LEFT JOIN 
    (SELECT 
      idacct,
      munid,
      provcode,
      wshift 
    FROM
      tblaccountjob) AS i 
    ON (i.idacct = a.id) 
  LEFT JOIN 
    (SELECT 
      id,
      shiftsun,
      shiftmon,
      shifttue,
      shiftwed,
      shiftthu,
      shiftfri,
      shiftsat 
    FROM
      tblcalendar) AS e 
    ON (e.id = i.wshift) 
  LEFT JOIN 
    (SELECT 
      `date` AS `date`,
      `idacct` AS `idacct`,
      `idleave` AS `idleave`,
      `leave_names` AS `name`,
      `stat` AS `stat` 
    FROM
      `vw_leave_application2` 
    GROUP BY `idacct`,
      `date`) AS `lv` 
    ON (
      `a`.`work_date` = `lv`.`date` 
      AND `lv`.`idacct` = `a`.`id`
    ) 
  LEFT JOIN 
    (SELECT 
      `id` AS `id`,
      `idacct` AS `idacct`,
      `date` AS `date`,
      `stat` AS `stat`,
      `date_approve` AS `date_approve` 
    FROM
      `vw_attendance_application` 
    GROUP BY `idacct`,
      `date`) `adj` 
    ON `a`.`work_date` = `adj`.`date` 
    AND `adj`.`idacct` = `a`.`id` 
  LEFT JOIN 
    (SELECT 
      `id` AS `id`,
      `date` AS `date`,
      `idacct` AS `idacct`,
      `stat` AS `stat` 
    FROM
      `vw_shift_application2`) `tts` 
    ON (
      `tts`.`idacct` = `a`.`id` 
      AND `tts`.`date` = `a`.`work_date`
    ) 
  LEFT JOIN 
    (SELECT 
      `id` AS `id`,
      `idacct` AS `idacct`,
      `stat` AS `stat`,
      `date` AS `date` 
    FROM
      `vw_overtime_application_2` 
    GROUP BY `idacct`,
      `date`) `vl` 
    ON (
      `a`.`work_date` = `vl`.`date` 
      AND `vl`.`idacct` = `a`.`id`
    ) 
  LEFT JOIN 
    (SELECT 
      `tbltimeobtrip`.`id` AS `id`,
      `tbltimeobtrip`.`idacct` AS `idacct`,
      `tbltimeobtrip`.`date` AS `date`,
      `tbltimeobtrip`.`stat` AS `stat` 
    FROM
      `tbltimeobtrip` 
    WHERE ISNULL(`tbltimeobtrip`.`cancelby`)) `tto` 
    ON (
      `tto`.`idacct` = `a`.`id` 
      AND `tto`.`date` = `a`.`work_date`
    ) 
  LEFT JOIN 
    (SELECT 
      lname,
      fname,
      mname,
      suffix,
      id,
      empid 
    FROM
      tblaccount) AS c 
    ON c.id = a.id 
  LEFT JOIN 
    (SELECT 
      id,
      `name` 
    FROM
      tblshift) AS f 
    ON f.id = (
      CASE
        WHEN b.idshift IS NULL 
        AND DAYNAME(a.work_date) = 'Sunday' 
        THEN e.shiftsun 
        WHEN b.idshift IS NULL 
        AND DAYNAME(a.work_date) = 'Monday' 
        THEN e.shiftmon 
        WHEN b.idshift IS NULL 
        AND DAYNAME(a.work_date) = 'Tuesday' 
        THEN e.shifttue 
        WHEN b.idshift IS NULL 
        AND DAYNAME(a.work_date) = 'Wednesday' 
        THEN e.shiftwed 
        WHEN b.idshift IS NULL 
        AND DAYNAME(a.work_date) = 'Thursday' 
        THEN e.shiftthu 
        WHEN b.idshift IS NULL 
        AND DAYNAME(a.work_date) = 'Friday' 
        THEN e.shiftfri 
        WHEN b.idshift IS NULL 
        AND DAYNAME(a.work_date) = 'Saturday' 
        THEN e.shiftsat 
        ELSE b.idshift 
      END
    ) 
  LEFT JOIN 
    (SELECT 
      provcode,
      munid,
      `date`,
      idtype 
    FROM
      tblholidays) AS g 
    ON g.date = a.work_date 
  LEFT JOIN 
    (SELECT 
      id,
      `type` 
    FROM
      tblholidaytype) AS h 
    ON h.id = g.idtype";
$Qry->selected  = "
`a`.`work_date`,
`b`.`date_in` AS `date_in`,
`b`.`timein` AS `in`,
`b`.`date_out` AS `date_out`,
`b`.`timeout` AS `out`,
`b`.`date_in2` AS `date_in2`,
`b`.`timein2` AS `in2`,
`b`.`date_out2` AS `date_out2`,
`b`.`timeout2` AS `out2`,
`c`.`lname`,
`c`.`fname`,
`c`.`mname`,
`c`.`suffix`,
`c`.`id`,
`c`.`empid`,
`lv`.`idleave` AS `idleave`,
`lv`.`name` AS `leavename`,
`lv`.`stat` AS `leavestat`,
`adj`.`id` AS `aaid`,
`adj`.`stat` AS `adj_stat`,
`tts`.`id` AS `csid`,
`tts`.`stat` AS `changeshift_stat`,
`vl`.`id` AS `otid`,
`vl`.`stat` AS `overtime_status`,
`tto`.`id` AS `obid`,
`tto`.`stat` AS `ob_stat`,
`h`.`id` AS `holiday_id`,
`h`.`type` AS `holidaytype`,
(
  CASE
    WHEN b.idshift IS NULL 
    AND DAYNAME(a.work_date) = 'Sunday' 
    THEN e.shiftsun 
    WHEN b.idshift IS NULL 
    AND DAYNAME(a.work_date) = 'Monday' 
    THEN e.shiftmon 
    WHEN b.idshift IS NULL 
    AND DAYNAME(a.work_date) = 'Tuesday' 
    THEN e.shifttue 
    WHEN b.idshift IS NULL 
    AND DAYNAME(a.work_date) = 'Wednesday' 
    THEN e.shiftwed 
    WHEN b.idshift IS NULL 
    AND DAYNAME(a.work_date) = 'Thursday' 
    THEN e.shiftthu 
    WHEN b.idshift IS NULL 
    AND DAYNAME(a.work_date) = 'Friday' 
    THEN e.shiftfri 
    WHEN b.idshift IS NULL 
    AND DAYNAME(a.work_date) = 'Saturday' 
    THEN e.shiftsat 
    ELSE b.idshift 
  END
) AS idshift,
IF(
  h.type IS NOT NULL,
  CONCAT(h.type, ' Holiday'),
  f.name
) AS title,
f.name AS title2,
IF(
  f.name = 'Rest Day' 
  OR h.type IS NOT NULL,
  '#00b050',
  '#f39c12'
) AS bg,
DAYNAME(a.work_date) AS nameDay,
g.provcode AS holidayProvid,
g.munid AS holidayMunid,
i.munid AS employeeMunid,
i.provcode AS employeeProvid";
$Qry->fields    = "a.id = '".$param->accountid."' AND a.`work_date` BETWEEN '".date("Y-m-01", strtotime($param->date) )."' AND '".date('Y-m-t', strtotime($param->date) )."' GROUP BY a.`work_date` ORDER BY CONCAT(a.work_date,c.lname) ASC";

$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
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
            //         "title"             => 'Rest Day' ,
            //         "start"             =>  $row['work_date'],
            //         "end" 	            =>  $row['work_date'],
            //         "backgroundColor"   => '#00b050',
            //         "sort"              => 0
            //     );
            //     $data[] = array( 
            //         "title"             => $title ,
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
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getColors');
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['color'];
    }
    return null;
}



?>