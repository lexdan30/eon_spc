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
$Qry->table     = "vw_datacurrentworkdates2 AS a
LEFT JOIN tbltimesheet AS b ON b.date = a.work_date AND b.idacct = a.id
LEFT JOIN tblaccountjob AS i ON i.idacct = a.id
LEFT JOIN tblcalendar AS e ON e.id = i.wshift
LEFT JOIN (SELECT `id` AS `id`,
                  `date` AS `date`,
                  `idacct` AS `idacct`,
                  `idleave` AS `idleave`,
                  `leave_names` AS `NAME`,
                  `leave_type` AS `type`,
                   SUM(`hrs`) AS `hrs`,
                   `stat` AS `stat` 
                  FROM `vw_leave_application2` 
                  GROUP BY `idacct`, `date`) AS `lv` ON `a`.`work_date` = `lv`.`date` AND `lv`.`idacct` = `a`.`id`
LEFT JOIN (SELECT `id` AS `id`,
		  `idacct` AS `idacct`,
                  `date` AS `date`,
                   SUM(`hrs`) AS `hrs`,
		  `stat` AS `stat`,
                  `date_approve` AS `date_approve` 
              FROM `vw_attendance_application` 
              GROUP BY `idacct`, `date`) `adj` 
              ON `a`.`work_date` = `adj`.`date` AND `adj`.`idacct` = `a`.`id`
LEFT JOIN (SELECT `id` AS `id`,
              `docnumber` AS `docnumber`,
              `creator` AS `creator`,
              `idacct` AS `idacct`,
              `empid` AS `empid`,
              `empname` AS `empname`,
              `idsuperior` AS `idsuperior`,
              `idunit` AS `idunit`,
              `date` AS `date`,
              `remarks` AS `remarks`,
              `file` AS `FILE`,
              `stat` AS `stat`,
              `oldidshift` AS `oldidshift`,
              `idshift` AS `idshift`,
              `newshift` AS `newshift`,
              `shiftin` AS `shiftin`,
              `shiftout` AS `shiftout`,
              `break` AS `break`,
              `oldshift` AS `oldshift`,
              `shift_status` AS `shift_status`,
              `date_create` AS `date_create`,
              `approver1` AS `approver1`,
              `approver1_name` AS `approver1_name`,
              `approver1_reason` AS `approver1_reason`,
              `date_approve` AS `date_approve`,
              `time_approve` AS `time_approve`,
              `id_payperiod` AS `id_payperiod`,
              `period_start` AS `period_start`,
              `period_end` AS `period_end`,
              `grace_hour` AS `grace_hour`,
              `datetimeapprove` AS `datetimeapprove` 
            FROM `vw_shift_application2`) `tts` 
            ON `tts`.`idacct` = `a`.`id` AND `tts`.`date` = `a`.`work_date`
LEFT JOIN (SELECT `id` AS `id`,
                  `docnumber` AS `docnumber`,
                  `creator` AS `creator`,
                  `idacct` AS `idacct`,
                  `shift_id` AS `shift_id`,
                  `name` AS `NAME`,
                  `shift_stime` AS `shift_stime`,
                  `shift_ftime` AS `shift_ftime`,
                  `break` AS `break`,
                  `date` AS `date`,
                  `sdate` AS `sdate`,
                  `fdate` AS `fdate`,
                  `overtime_stime` AS `overtime_stime`,
                  `overtime_ftime` AS `overtime_ftime`,
                  `planhrs` AS `planhrs`,
                  `hrs` AS `hrs`,
                  `remarks` AS `remarks`,
                  `file` AS `FILE`,
                  `stat` AS `stat`,
                  `ot_stat` AS `ot_stat`,
                  `date_create` AS `date_create`,
                  `date_approve` AS `date_approve`,
                  `holiday_date` AS `holiday_date`,
                  `ot_type` AS `ot_type`,
                  `reg_ot_rate` AS `reg_ot_rate`,
                  `rd_ot_rate` AS `rd_ot_rate`,
                  `spcl_hol_rate` AS `spcl_hol_rate`,
                  `legal_hol_rate` AS `legal_hol_rate`,
                  `spcl_rd_rate` AS `spcl_rd_rate`,
                  `legal_rd_rate` AS `legal_rd_rate`,
                  `reg_ot8_rate` AS `reg_ot8_rate`,
                  `rd_ot8_rate` AS `rd_ot8_rate`,
                  `spcl_hol8_rate` AS `spcl_hol8_rate`,
                  `legal_hol8_rate` AS `legal_hol8_rate`,
                  `spcl_rd8_rate` AS `spcl_rd8_rate`,
                  `legal_rd8_rate` AS `legal_rd8_rate`,
                  `reg_np_rate` AS `reg_np_rate`,
                  `legal_np_rate` AS `legal_np_rate`,
                  `rd_np_rate` AS `rd_np_rate`,
                  `spcl_rd_np_rate` AS `spcl_rd_np_rate`,
                  `legal_rd_np_rate` AS `legal_rd_np_rate`,
                  `spcl_np_rate` AS `spcl_np_rate`,
                  `reg_ot` AS `reg_ot`,
                  `rd_ot` AS `rd_ot`,
                  `spcl_hol` AS `spcl_hol`,
                  `legal_hol` AS `legal_hol`,
                  `spcl_rd` AS `spcl_rd`,
                  `legal_rd` AS `legal_rd`,
                  `reg_ot8` AS `reg_ot8`,
                  `rd_ot8` AS `rd_ot8`,
                  `spcl_hol8` AS `spcl_hol8`,
                  `legal_hol8` AS `legal_hol8`,
                  `spcl_rd8` AS `spcl_rd8`,
                  `legal_rd8` AS `legal_rd8`,
                  `reg_np` AS `reg_np`,
                  `legal_np` AS `legal_np`,
                  `spcl_np` AS `spcl_np`,
                  `rd_np` AS `rd_np`,
                  `spcl_rd_np` AS `spcl_rd_np`,
                  `legal_rd_np` AS `legal_rd_np`,
                  `sl_rd_rate` AS `sl_rd_rate`,
                  `sl_hol_rate` AS `sl_hol_rate`,
                  `sl_rd8_rate` AS `sl_rd8_rate`,
                  `sl_hol8_rate` AS `sl_hol8_rate`,
                  `sl_rd_np_rate` AS `sl_rd_np_rate`,
                  `sl_np_rate` AS `sl_np_rate`,
                  `sl_hol` AS `sl_hol`,
                  `sl_rd` AS `sl_rd`,
                  `sl_hol8` AS `sl_hol8`,
                  `sl_rd8` AS `sl_rd8`,
                  `sl_np` AS `sl_np`,
                  `sl_rd_np` AS `sl_rd_np` 
                FROM `vw_overtime_application_2` GROUP BY `idacct`, `date`) `vl` 
                ON `a`.`work_date` = `vl`.`date` AND `vl`.`idacct` = `a`.`id`
LEFT JOIN (SELECT `tbltimeobtrip`.`id` AS `id`,
             `tbltimeobtrip`.`idacct` AS `idacct`,
             `tbltimeobtrip`.`remarks` AS `remarks`,
             `tbltimeobtrip`.`date` AS `date`,
             `tbltimeobtrip`.`stat` AS `stat` 
          FROM `tbltimeobtrip` 
          WHERE ISNULL(`tbltimeobtrip`.`cancelby`)) `tto` 
          ON `tto`.`idacct` = `a`.`id` AND `tto`.`date` = `a`.`work_date`
LEFT JOIN tblaccount AS c ON c.id = a.id
LEFT JOIN tblshift AS d ON d.id = b.idshift 
LEFT JOIN (SELECT * FROM `tbltimesched` WHERE id IN (SELECT MAX(id) FROM tbltimesched GROUP BY idacct)) AS e ON e.idacct = a.id
LEFT JOIN tblshift AS f ON f.id = (CASE 
					WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Sunday' THEN e.idsun
					WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Monday' THEN e.idmon
					WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Tuesday' THEN e.idtue
					WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Wednesday' THEN e.idwed
					WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Thursday' THEN e.idthu
					WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Friday' THEN e.idfri
					WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Saturday' THEN e.idsat
					ELSE b.idshift
				END)
LEFT JOIN tblholidays AS g ON g.date = a.work_date 
LEFT JOIN tblholidaytype AS h ON h.id = g.idtype";
$Qry->selected  = "a.work_date,
                    `b`.`date_in` AS `date_in`,
                    `b`.`timein` AS `in`,
                    `b`.`date_out` AS `date_out`,
                    `b`.`timeout` AS `out`,
                    `b`.`date_in2` AS `date_in2`,
                    `b`.`timein2` AS `in2`,
                    `b`.`date_out2` AS `date_out2`,
                    `b`.`timeout2` AS `out2`,
                    c.lname,
                    c.fname,
                    c.mname,
                    c.suffix,
                    c.id,
                    c.empid,
                    `lv`.`idleave` AS `idleave`,
                    `lv`.`NAME` AS `leavename`,
                    `lv`.`stat` AS `leavestat`,
                    `lv`.`hrs` as leavehrs,
                    `adj`.`id` AS `aaid`,
                    `adj`.`stat` AS `adj_stat`,
                    `tts`.`id` AS `csid`,
                    `tts`.`stat` AS `changeshift_stat`,
                    `vl`.`id` AS `otid`,
                    `vl`.`stat` AS `overtime_status`,
                    `tto`.`id` AS `obid`,
                    `tto`.`stat` AS `ob_stat`,
                    h.id as holiday_id,
                    h.type as holidaytype,
                    (CASE 
                        WHEN b.idshift is null and dayname(a.work_date) = 'Sunday' THEN e.idsun
                        WHEN b.idshift is null and dayname(a.work_date) = 'Monday' THEN e.idmon
                        WHEN b.idshift is null and dayname(a.work_date) = 'Tuesday' THEN e.idtue
                        WHEN b.idshift is null and dayname(a.work_date) = 'Wednesday' THEN e.idwed
                        WHEN b.idshift is null and dayname(a.work_date) = 'Thursday' THEN e.idthu
                        WHEN b.idshift is null and dayname(a.work_date) = 'Friday' THEN e.idfri
                        WHEN b.idshift is null and dayname(a.work_date) = 'Saturday' THEN e.idsat
                        ELSE b.idshift
                    END) as idshift,
                    IF(h.type is not null
                        ,CONCAT(h.type, ' Holiday')
                        ,f.name
                    ) as title,
                    if(f.name = 'Rest Day' OR h.type IS not null
                        ,'#00b050'
                        ,'#f39c12'
                        ) as bg,
                        IF(h.type is null
                        ,CONCAT(h.type, ' Holiday')
                        ,f.name
                    ) as title2, DAYNAME(a.work_date) as nameDay,
                    g.munid AS holidayProvid,
                    g.munid AS holidayMunid,
                    i.munid AS employeeMunid,
                    i.provcode As employeeProvid";
$Qry->fields    = "a.id = '".$param->accountid."' AND a.`work_date` BETWEEN '".date("Y-m-01", strtotime($param->date) )."' AND '".date('Y-m-t', strtotime($param->date) )."' ORDER BY CONCAT(a.work_date,c.lname) ASC";
$rs = $Qry->exe_SELECT($con);

if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        if($row['idshift']  != 4){
            $title = $row['title'];
            $title2 = $row['title2'];
            if($title == 'Rest Day'){
				$backgroundColor = '#00b050';
			}else if($row['holiday_id']){
                $backgroundColor = '#f00404';
                $data[] = array( 
                    "title"             => $title2 ,
                    "start"             =>  $row['work_date'],
                    "end" 	            =>  $row['work_date'],
                    "backgroundColor"   => '#f39c12',
                    "sort"              => 1
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
        if($row['idleave'] && empty($param->leaveapp)){

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