<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblaccountjob";
$Qry->selected  = "*";
if($param->bonus->applyto == 'all'){
    $Qry->fields = "sdate > '". $param->bonus->startdate . "' OR empstat NOT IN (6,7) ";
}else if($param->bonus->applyto == 'individual'){
    $Qry->fields = "(sdate > '". $param->bonus->startdate . "' OR empstat NOT IN (6,7)) AND idacct= '". $param->bonus->employee . "'";
}else{
    $Qry->fields = "sdate > '". $param->bonus->startdate . "' OR empstat NOT IN (6,7) AND idpaygrp = '". $param->bonus->applyto . "'";
}


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $tamount = getbonuses($con, $row['idacct'], $param->bonus->startdate, $param->bonus->endate);
        $amount = $tamount *  ($param->bonus->amount / 100);


        if($amount > 90000){
            $nontaxable = 90000;
            $taxable = round($amount - 90000,2);
        }else{
            $nontaxable = round($amount,2);
            $taxable =0;
        }
        
        $data[] = array( 
            "id" 	        => $row['idacct'],
            "empname"       => getEmpname($con, $row['idacct']),
            "taxable" 	    => $taxable,
            "nontaxable" 	=> $nontaxable,
            "amount" 	    => $tamount
        );

    }
	$return = json_encode($data);
}else{
	$return = json_encode(array());
}
print $return;
mysqli_close($con);
function getEmpname($con, $idacct){
    $Qry = new Query();	
    $Qry->table         = "vw_dataemployees";
    $Qry->selected      = "empname";
    $Qry->fields        = "id = '". $idacct ."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['empname'];
        }
    }
}
function getbonuses($con, $idacct, $startdate, $endate ){
    $bonus = 0;
    $Qry = new Query();	
    $Qry->table         = "vw_datacurrentworkdates2 AS a1 
                            LEFT JOIN tbltimesheet AS a ON a1.id = a.idacct AND a1.work_date = a.date
                            LEFT JOIN tblpayperiod AS j ON `a1`.`work_date` BETWEEN `j`.`period_start` AND `j`.`period_end`
                            LEFT JOIN tblaccountjob AS e ON e.idacct = a1.id
                            LEFT JOIN tblholidays AS c ON 
                            (c.date = a1.work_date AND (c.regcode IS NULL OR c.regcode = '') AND (c.provcode IS NULL OR c.provcode ='') AND (c.munid IS NULL OR c.munid ='')) OR 
                            (c.date = a1.work_date AND IF( (c.regcode IS NOT NULL OR c.regcode != '') AND (c.munid IS NULL OR c.munid ='')
                                                ,c.regcode = e.regcode AND c.provcode = e.provcode AND (c.munid IS NULL OR c.munid ='')
                                                ,c.regcode = e.regcode AND c.provcode = e.provcode AND c.munid = e.munid)
                                
                            )
                            LEFT JOIN tblholidaytype AS d ON d.id = c.idtype 
                            LEFT JOIN tbltimeadjustment AS k ON k.idacct = a1.id AND k.date =  a1.work_date AND k.stat != 4
                            LEFT JOIN tbltimeleaves AS l ON l.idacct = a1.id AND l.date =  a1.work_date AND l.stat != 4
                            LEFT JOIN tblleaves AS r ON r.id = l.idleave
                            LEFT JOIN tbltimeovertime AS m ON m.idacct = a1.id AND m.date =  a1.work_date AND m.stat != 4
                            LEFT JOIN tbltimeobtrip AS p ON p.idacct = a1.id AND p.date =  a1.work_date AND p.stat != 4 
                            LEFT JOIN tbltimeshift AS q ON q.idacct = a1.id AND q.date =  a1.work_date AND q.stat != 4 
                            LEFT JOIN tblshift AS v ON v.id = q.idshift
                            LEFT JOIN tblaccount AS o ON o.id = a1.id 	
                            LEFT JOIN (SELECT * FROM `tbltimesched` WHERE id IN (SELECT MAX(id) FROM tbltimesched GROUP BY idacct)) AS t ON t.idacct = a1.id
                            LEFT JOIN tblshift AS s ON s.id = (CASE 
                                            WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Sunday' THEN t.idsun
                                            WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Monday' THEN t.idmon
                                            WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Tuesday' THEN t.idtue
                                            WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Wednesday' THEN t.idwed
                                            WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Thursday' THEN t.idthu
                                            WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Friday' THEN t.idfri
                                            WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Saturday' THEN t.idsat
                                            ELSE a.idshift
                                        END)
                            LEFT JOIN tblshift AS fpshift ON fpshift.id = (CASE 
                                        WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Sunday' THEN IF(q.stat = 1, q.idshift,t.idsun)
                                            WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Monday' THEN IF(q.stat = 1, q.idshift,t.idmon)
                                            WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Tuesday' THEN IF(q.stat = 1, q.idshift,t.idtue)
                                            WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Wednesday' THEN IF(q.stat = 1, q.idshift,t.idwed)
                                            WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Thursday' THEN IF(q.stat = 1, q.idshift,t.idthu)
                                            WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Friday' THEN IF(q.stat = 1, q.idshift,t.idfri)
                                            WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Saturday' THEN IF(q.stat = 1, q.idshift,t.idsat)
                                            ELSE IF(q.stat = 1, q.idshift,a.idshift)
                                        END)
                            LEFT JOIN tblshift AS u ON u.id = IF(q.stat = 1 
                                                ,q.idshift
                                                ,(CASE 
                                                    WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Sunday' THEN t.idsun
                                                    WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Monday' THEN t.idmon
                                                    WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Tuesday' THEN t.idtue
                                                    WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Wednesday' THEN t.idwed
                                                    WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Thursday' THEN t.idthu
                                                    WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Friday' THEN t.idfri
                                                    WHEN a.idshift IS NULL AND DAYNAME(a1.work_date) = 'Saturday' THEN t.idsat
                                                    ELSE a.idshift
                                                END)
                                                )";
    $Qry->selected      = "a1.id,
                            a1.work_date,
                            e.idunit,
                            CONCAT(`o`.`lname`,IFNULL(CONCAT(' ',`o`.`suffix`),''),', ',`o`.`fname`,' ',SUBSTR(`o`.`mname`,1,1),'. ') AS `empname`,
                            SUM(ROUND((CASE
                                    WHEN (CASE  WHEN l.stat = 1 AND l.idtimeleavetype = 1 THEN 0
                                            WHEN l.stat = 1 AND l.idtimeleavetype != 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                    )
                                                                                ,0
                                                                            )
                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                    )
                                                                                ,0
                                                                            )
                                                                        )
                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                        )
                                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                            )
                                                                )  - l.hrs
                                            WHEN DATE(NOW()) <= DATE(a1.work_date) THEN 0
                                            WHEN ((CASE WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ) AS TIME)
                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ), fpshift.stime)) / 3600,2)
                                                                                ,'0.00'
                                                                                )
                                                                            ,0
                                                                        ) 
                                                            ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                        ,'0.00'
                                                                        )
                                                                        - IF(CAST(a.timein AS TIME) > CAST(`fpshift`.`breakout` AS TIME) 
                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                )
                                                                                ,0
                                                                        )
                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                        ,'0.00'
                                                                        ) 
                                                                    + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                        ,'0.00'
                                                                        )
                                                                        
                                                                        - IF(CAST(a.timein AS TIME) > CAST(`fpshift`.`breakout` AS TIME) 
                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                    )
                                                                                ,0
                                                                        )
                                                                ) 
                                                        END)  + (CASE WHEN ((CASE WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ) AS TIME)
                                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ), fpshift.stime)) / 3600,2)
                                                                                                ,'0.00'
                                                                                                )
                                                                                            ,0
                                                                                        ) 
                                                                                        ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                                    
                                                                                                    
                                                                                                    ,'0.00'
                                                                                                    )
                                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                                                    ,'0.00'
                                                                                                    )
                                                                                                + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                                                    ,'0.00'
                                                                                                    )
                                                                                            ) 
                                                                                    END) + (CASE 
                                                                                        WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                    ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                                    ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                                    + IF(CAST(k.sftime AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, k.sftime)) / 3600,2),'0.00') 
                                                                                                ) 
                                                                                        WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                        ,IF(CAST(
                                                                                                        IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time )
                                                                                                        AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time ))) / 3600,2),'0.00') 
                                                                                                        ,0
                                                                                                    ) 
                                                                                        ELSE  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                ,IF(CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(
                                                                                                        IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                            ,a1.work_date
                                                                                                            ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                                                        , ' ', fpshift.ftime) AS DATETIME)
                                                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                                                    ,'0.00')
                                                                                                ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                                                + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                                            ) 
                                                                                    END) >  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                            )
                                                                                                        ,0
                                                                                                    )
                                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                        )
                                                                                                        ,0
                                                                                                    )
                                                                                                )
                                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                                + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                                                    )
                                                                                        ) ) 
                                                                                    THEN 0
                                                                    WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                + IF(CAST(k.sftime AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, k.sftime)) / 3600,2),'0.00') 
                                                                            ) 
                                                                    WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                    ,IF(CAST(
                                                                                    IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time )
                                                                                    AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time ))) / 3600,2),'0.00') 
                                                                                    ,0
                                                                                ) 
                                                                    ELSE  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                            ,IF(CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(
                                                                                    IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                        ,a1.work_date
                                                                                        ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                                    , ' ', fpshift.ftime) AS DATETIME)
                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                                ,'0.00')
                                                                            ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                            + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                        ) 
                                                                END)) > IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                                - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                        )
                                                                                    ,0
                                                                                )
                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                        )
                                                                                    ,0
                                                                                )
                                                                            )
                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                            )
                                                                            + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                                )
                                                                    )
                                                    THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                        )
                                                                        ,0
                                                                    )
                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                            )
                                                                        ,0
                                                                    )
                                                                )
                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                )
                                                                + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                    )
                                                        )
                                            WHEN k.stat = 1 THEN 0
                                            WHEN p.stat = 1 THEN 0
                                            WHEN m.stat = 1 THEN 0
                                            WHEN d.type = 'LEGAL' OR d.type = 'LEGAL SPECIAL' THEN 0
                                            ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                    ,IF(a.timein IS NULL OR a.timeout IS NULL OR a.timein = '' OR a.timeout = ''
                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL			
                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                    )
                                                                    ,0
                                                                )
                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL			
                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                        )
                                                                    ,0
                                                                )
                                                            )
                                                        ,0
                                                    )
                                                    ,IF(a.timein IS NULL OR a.timeout IS NULL OR a.timein = '' OR a.timeout = ''
                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                            )
                                                        ,0
                                                    ) + IF(a.timein2 IS NULL OR a.timeout2 IS NULL OR a.timein2 = '' OR a.timeout2 = ''
                                                        ,IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2) 
                                                            )
                                                        ,0
                                                    )
                                                    
                                                )
                                        END) 
                                        =  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                    )
                                                    ,0
                                                )
                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                    )
                                                    ,0
                                                )
                                            )
                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                            )
                                            + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                )
                                    ) 
                                    THEN 0
                                    WHEN ((CASE 
                                                    WHEN l.stat = 1 AND l.idtimeleavetype = 1 THEN 0
                                                    WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                + IF(CAST(k.sftime AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, k.sftime)) / 3600,2),'0.00') 
                                                            ) 
                                                    WHEN p.stat = 1 AND p.idtimetype = 1 THEN 0
                                                    WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                    ,IF(CAST(
                                                                    IF(a.timeout IS NOT NULL OR a.timeout != '',
                                                                    IF(CAST(a.timeout AS TIME) < CAST(p.end_time AS TIME)
                                                                                                ,p.end_time
                                                                                                ,a.timeout 
                                                                                            ) ,p.end_time )
                                                                    AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time ))) / 3600,2),'0.00') 
                                                                    ,0
                                                                ) 
                                                    WHEN l.stat = 1 AND l.idtimeleavetype = 3 THEN IF( IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                    ,IF(CAST(CONCAT(a.date_out, ' ', IF(CAST(a.timeout AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                                        ,fpshift.breakout
                                                                                                        ,a.timeout
                                                                                                    )) AS DATETIME) < CAST(CONCAT(
                                                                                            IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                ,a1.work_date
                                                                                                ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                                            , ' ', fpshift.ftime) AS DATETIME)
                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                                        ,'0.00')  - IF(CAST(a.timeout AS TIME) < CAST(`fpshift`.`breakout` AS TIME)
                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                                ,0
                                                                                                ) 
                                                                                        
                                                                                    ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                                    + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                                
                                                                                )  - l.hrs > 0 
                                                                            ,IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                    ,IF(CAST(CONCAT(a.date_out, ' ', IF(CAST(a.timeout AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                                        ,fpshift.breakout
                                                                                                        ,a.timeout
                                                                                                    )) AS DATETIME) < CAST(CONCAT(
                                                                                            IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                ,a1.work_date
                                                                                                ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                                            , ' ', fpshift.ftime) AS DATETIME)
                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                                        ,'0.00')  - IF(CAST(a.timeout AS TIME) < CAST(`fpshift`.`breakout` AS TIME)
                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                                ,0
                                                                                                ) 
                                                                                        
                                                                                    ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                                    + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                                
                                                                                )  - l.hrs 
                                                                            ,0
                                                                            )
                                                    ELSE  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                            ,IF(CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(
                                                                    IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                        ,a1.work_date
                                                                        ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                    , ' ', fpshift.ftime) AS DATETIME)
                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(CAST(a.timeout AS TIME) BETWEEN  CAST(`fpshift`.`breakin` AS TIME)  AND CAST(`fpshift`.`breakout` AS TIME)
                                                                            ,`fpshift`.`breakout`
                                                                            ,a.timeout
                                                                        ))) / 3600,2)
                                                                
                                                                        
                                                                        
                                                                    - IF(CAST(IF(CAST(a.timeout AS TIME) BETWEEN  CAST(`fpshift`.`breakin` AS TIME)  AND CAST(`fpshift`.`breakout` AS TIME)
                                                                            ,`fpshift`.`breakout`
                                                                            ,a.timeout
                                                                        ) AS TIME) < CAST(`fpshift`.`breakout` AS TIME)
                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                        )
                                                                        ,0
                                                                        ) 
                                                                ,'0.00') 
                                                                
                                                            ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                            + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                        
                                                        ) 
                                                END) + (CASE
                                                        WHEN ((CASE  WHEN l.stat = 1 AND l.idtimeleavetype = 1 THEN 0
                                                                WHEN l.stat = 1 AND l.idtimeleavetype != 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                                                - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                    )
                                                                                                    ,0
                                                                                                )
                                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                                - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                    )
                                                                                                    ,0
                                                                                                )
                                                                                            )
                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                                            )
                                                                                            + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                    )  - l.hrs
                                                                WHEN DATE(NOW()) <= DATE(a1.work_date) THEN 0
                                                                WHEN ((CASE WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ) AS TIME)
                                                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ), fpshift.stime)) / 3600,2)
                                                                                                    ,'0.00'
                                                                                                    )
                                                                                                ,0
                                                                                            ) 
                                                                                ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                            ,'0.00'
                                                                                            )
                                                                                            - IF(CAST(a.timein AS TIME) > CAST(`fpshift`.`breakout` AS TIME) 
                                                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                    )
                                                                                                    ,0
                                                                                            )
                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                                            ,'0.00'
                                                                                            ) 
                                                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                                            ,'0.00'
                                                                                            )
                                                                                            
                                                                                            - IF(CAST(a.timein AS TIME) > CAST(`fpshift`.`breakout` AS TIME) 
                                                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                        )
                                                                                                    ,0
                                                                                            )
                                                                                    ) 
                                                                            END)  + (CASE WHEN ((CASE WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ) AS TIME)
                                                                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ), fpshift.stime)) / 3600,2)
                                                                                                                    ,'0.00'
                                                                                                                    )
                                                                                                                ,0
                                                                                                            ) 
                                                                                                            ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                                                        
                                                                                                                        
                                                                                                                        ,'0.00'
                                                                                                                        )
                                                                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                                                                        ,'0.00'
                                                                                                                        )
                                                                                                                    + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                                                                        ,'0.00'
                                                                                                                        )
                                                                                                                ) 
                                                                                                        END) + (CASE 
                                                                                                            WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                        ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                                                        ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                                                        + IF(CAST(k.sftime AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, k.sftime)) / 3600,2),'0.00') 
                                                                                                                    ) 
                                                                                                            WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                            ,IF(CAST(
                                                                                                                            IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time )
                                                                                                                            AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time ))) / 3600,2),'0.00') 
                                                                                                                            ,0
                                                                                                                        ) 
                                                                                                            ELSE  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                    ,IF(CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(
                                                                                                                            IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                                                ,a1.work_date
                                                                                                                                ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                                                                            , ' ', fpshift.ftime) AS DATETIME)
                                                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                                                                        ,'0.00')
                                                                                                                    ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                                                                    + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                                                                ) 
                                                                                                        END) >  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                                                )
                                                                                                                            ,0
                                                                                                                        )
                                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                                                )
                                                                                                                            ,0
                                                                                                                        )
                                                                                                                    )
                                                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                                                                    )
                                                                                                                    + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                                                                        )
                                                                                                            ) ) 
                                                                                                        THEN 0
                                                                                        WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                    ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                                    ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                                    + IF(CAST(k.sftime AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, k.sftime)) / 3600,2),'0.00') 
                                                                                                ) 
                                                                                        WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                        ,IF(CAST(
                                                                                                        IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time )
                                                                                                        AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time ))) / 3600,2),'0.00') 
                                                                                                        ,0
                                                                                                    ) 
                                                                                        ELSE  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                ,IF(CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(
                                                                                                        IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                            ,a1.work_date
                                                                                                            ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                                                        , ' ', fpshift.ftime) AS DATETIME)
                                                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                                                    ,'0.00')
                                                                                                ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                                                + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                                            ) 
                                                                                    END)) > IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                            )
                                                                                                        ,0
                                                                                                    )
                                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                        )
                                                                                                        ,0
                                                                                                    )
                                                                                                )
                                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                                + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                                                    )
                                                                                        )
                                                                        THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                            )
                                                                                            ,0
                                                                                        )
                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                            )
                                                                                            ,0
                                                                                        )
                                                                                    )
                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                                    )
                                                                                    + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                                        )
                                                                            )
                                                                WHEN k.stat = 1 THEN 0
                                                                WHEN p.stat = 1 THEN 0
                                                                WHEN m.stat = 1 THEN 0
                                                                WHEN d.type = 'LEGAL' OR d.type = 'LEGAL SPECIAL' THEN 0
                                                                ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                        ,IF(a.timein IS NULL OR a.timeout IS NULL OR a.timein = '' OR a.timeout = ''
                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL			
                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                        )
                                                                                        ,0
                                                                                    )
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL			
                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                            )
                                                                                        ,0
                                                                                    )
                                                                                )
                                                                            ,0
                                                                        )
                                                                        ,IF(a.timein IS NULL OR a.timeout IS NULL OR a.timein = '' OR a.timeout = ''
                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                )
                                                                            ,0
                                                                        ) + IF(a.timein2 IS NULL OR a.timeout2 IS NULL OR a.timein2 = '' OR a.timeout2 = ''
                                                                            ,IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2) 
                                                                                )
                                                                            ,0
                                                                        )
                                                                        
                                                                    )
                                                            END)  
                                                            =  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                            )
                                                                            ,0
                                                                        )
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                            )
                                                                            ,0
                                                                        )
                                                                    )
                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                    )
                                                                    + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                        )
                                                                ) 
                                                            )
                                                        THEN 0
                                                        WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(k.stime AS TIME) AND k.ftime != ''
                                                                            ,IF(CAST(k.stime AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a1.work_date, ' ', fpshift.breakin) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a1.work_date, ' ', k.stime) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                            )	
                                                                            ,'0.00'
                                                                            )
                                                                            - IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                            ,IF(fpshift.breakin = '00:00'
                                                                                                                ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                                , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                )
                                                                                                            ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                        )
                            
                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                )
                                                                                ,0
                                                                            )
                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                            ,'0.00'
                                                                            ) 
                                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                            ,'0.00'
                                                                            )
                                                                            
                                                                            -  IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                            ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                            ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                        )
                            
                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                )
                                                                                ,0
                                                                            )
                                                                    ) 
                                                        WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ) AS TIME)
                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ), fpshift.stime)) / 3600,2)
                                                                            ,'0.00'
                                                                            )
                                                                        ,0
                                                                    ) 
                                                        WHEN l.stat = 1 AND l.idtimeleavetype = 2 THEN IF(  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                            ,IF(CAST(a.timein AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', fpshift.breakin) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                            )	
                                                                                            ,'0.00'
                                                                                            )
                                                                                            - IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                            ,IF(fpshift.breakin = '00:00'
                                                                                                                                ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                                                , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                                )
                                                                                                                            ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                        )
                            
                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                                ,0
                                                                                            )
                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                                            ,'0.00'
                                                                                            ) 
                                                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                                            ,'0.00'
                                                                                            )
                                                                                            
                                                                                            -  IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                            ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                                            ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                        )
                            
                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                                ,0
                                                                                            )
                                                                                    ) - l.hrs > 0
                                                                                    ,IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                            ,IF(CAST(a.timein AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', fpshift.breakin) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                            )	
                                                                                            ,'0.00'
                                                                                            )
                                                                                            - IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                            ,IF(fpshift.breakin = '00:00'
                                                                                                                                ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                                                , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                                )
                                                                                                                            ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                        )
                            
                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                                ,0
                                                                                            )
                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                                            ,'0.00'
                                                                                            ) 
                                                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                                            ,'0.00'
                                                                                            )
                                                                                            
                                                                                            -  IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                            ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                                            ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                        )
                            
                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                                ,0
                                                                                            )
                                                                                    ) - l.hrs
                                                                                    ,0
                                                                                    )
                                                        
                                                        ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                    ,IF(CAST(a.timein AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', fpshift.breakin) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                    )	
                                                                    ,'0.00'
                                                                    )
                                                                    - IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    ,IF(fpshift.breakin = '00:00'
                                                                                                        ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                        , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                        )
                                                                                                    ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                )
                            
                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                        )
                                                                        ,0
                                                                    )
                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                    ,'0.00'
                                                                    ) 
                                                                + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                    ,'0.00'
                                                                    )
                                                                    
                                                                    -  IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                    ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                )
                            
                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                        )
                                                                        ,0
                                                                    )
                                                            ) 
                                                    END) > IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                            )
                                                                        ,0
                                                                    )
                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                        )
                                                                        ,0
                                                                    )
                                                                )
                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                )
                                                                + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                    )
                                                            ))
                                    THEN 0
                                    WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(k.stime AS TIME) AND k.ftime != ''
                                                        ,IF(CAST(k.stime AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a1.work_date, ' ', fpshift.breakin) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a1.work_date, ' ', k.stime) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                        )	
                                                        ,'0.00'
                                                        )
                                                        - IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                        ,IF(fpshift.breakin = '00:00'
                                                                                            ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                            , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                            )
                                                                                        ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                    )
                            
                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                            )
                                                            ,0
                                                        )
                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                        ,'0.00'
                                                        ) 
                                                    + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                        ,'0.00'
                                                        )
                                                        
                                                        -  IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                        ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                        ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                    )
                            
                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                            )
                                                            ,0
                                                        )
                                                ) 
                                    WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ) AS TIME)
                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ), fpshift.stime)) / 3600,2)
                                                        ,'0.00'
                                                        )
                                                    ,0
                                                ) 
                                    WHEN l.stat = 1 AND l.idtimeleavetype = 2 THEN IF(  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                        ,IF(CAST(a.timein AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', fpshift.breakin) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                        )	
                                                                        ,'0.00'
                                                                        )
                                                                        - IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                        ,IF(fpshift.breakin = '00:00'
                                                                                                            ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                            , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                            )
                                                                                                        ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                    )
                            
                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                            )
                                                                            ,0
                                                                        )
                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                        ,'0.00'
                                                                        ) 
                                                                    + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                        ,'0.00'
                                                                        )
                                                                        
                                                                        -  IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                        ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                        ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                    )
                            
                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                            )
                                                                            ,0
                                                                        )
                                                                ) - l.hrs > 0
                                                                ,IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                        ,IF(CAST(a.timein AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', fpshift.breakin) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                        )	
                                                                        ,'0.00'
                                                                        )
                                                                        - IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                        ,IF(fpshift.breakin = '00:00'
                                                                                                            ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                            , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                            )
                                                                                                        ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                    )
                            
                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                            )
                                                                            ,0
                                                                        )
                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                        ,'0.00'
                                                                        ) 
                                                                    + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                        ,'0.00'
                                                                        )
                                                                        
                                                                        -  IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                        ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                        ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                    )
                            
                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                            )
                                                                            ,0
                                                                        )
                                                                ) - l.hrs
                                                                ,0
                                                                )	
                                    ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                ,IF(CAST(a.timein AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', fpshift.breakin) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                )	
                                                ,'0.00'
                                                )
                                                - IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                ,IF(fpshift.breakin = '00:00'
                                                                                    ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                    , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                    )
                                                                                ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                            )
                            
                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                    )
                                                    ,0
                                                )
                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                ,'0.00'
                                                ) 
                                            + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                ,'0.00'
                                                )
                                                
                                                -  IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                            )
                            
                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                    )
                                                    ,0
                                                )
                                        ) 
                                END) 
                                * (IFNULL((SELECT currentbasepay FROM (select id ,requestor ,currentbasepay ,effectivedate,idstatus from tblforms02 union select id ,requestor ,currentbasepay ,effectivedate,idstatus from tblforms03) a WHERE effectivedate > a1.work_date AND idstatus = 1 AND requestor = a1.id LIMIT 1),e.salary) / 26.0833) / 8,2
                                )) 
                            AS late,
                            SUM(ROUND((CASE 
                                    WHEN ((CASE 
                                                    WHEN l.stat = 1 AND l.idtimeleavetype = 1 THEN 0
                                                    WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                + IF(CAST(k.sftime AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, k.sftime)) / 3600,2),'0.00') 
                                                            ) 
                                                    WHEN p.stat = 1 AND p.idtimetype = 1 THEN 0
                                                    WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                    ,IF(CAST(
                                                                    IF(a.timeout IS NOT NULL OR a.timeout != '',
                                                                    IF(CAST(a.timeout AS TIME) < CAST(p.end_time AS TIME)
                                                                                                ,p.end_time
                                                                                                ,a.timeout 
                                                                                            ) ,p.end_time )
                                                                    AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time ))) / 3600,2),'0.00') 
                                                                    ,0
                                                                ) 
                                                    WHEN l.stat = 1 AND l.idtimeleavetype = 3 THEN IF( IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                    ,IF(CAST(CONCAT(a.date_out, ' ', IF(CAST(a.timeout AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                                        ,fpshift.breakout
                                                                                                        ,a.timeout
                                                                                                    )) AS DATETIME) < CAST(CONCAT(
                                                                                            IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                ,a1.work_date
                                                                                                ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                                            , ' ', fpshift.ftime) AS DATETIME)
                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                                        ,'0.00')  - IF(CAST(a.timeout AS TIME) < CAST(`fpshift`.`breakout` AS TIME)
                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                                ,0
                                                                                                ) 
                                                                                        
                                                                                    ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                                    + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                                
                                                                                )  - l.hrs > 0 
                                                                            ,IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                    ,IF(CAST(CONCAT(a.date_out, ' ', IF(CAST(a.timeout AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                                        ,fpshift.breakout
                                                                                                        ,a.timeout
                                                                                                    )) AS DATETIME) < CAST(CONCAT(
                                                                                            IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                ,a1.work_date
                                                                                                ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                                            , ' ', fpshift.ftime) AS DATETIME)
                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                                        ,'0.00')  - IF(CAST(a.timeout AS TIME) < CAST(`fpshift`.`breakout` AS TIME)
                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                                ,0
                                                                                                ) 
                                                                                        
                                                                                    ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                                    + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                                
                                                                                )  - l.hrs 
                                                                            ,0
                                                                            )
                                                    ELSE  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                            ,IF(CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(
                                                                    IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                        ,a1.work_date
                                                                        ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                    , ' ', fpshift.ftime) AS DATETIME)
                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(CAST(a.timeout AS TIME) BETWEEN  CAST(`fpshift`.`breakin` AS TIME)  AND CAST(`fpshift`.`breakout` AS TIME)
                                                                            ,`fpshift`.`breakout`
                                                                            ,a.timeout
                                                                        ))) / 3600,2)
                                                                
                                                                        
                                                                        
                                                                    - IF(CAST(IF(CAST(a.timeout AS TIME) BETWEEN  CAST(`fpshift`.`breakin` AS TIME)  AND CAST(`fpshift`.`breakout` AS TIME)
                                                                            ,`fpshift`.`breakout`
                                                                            ,a.timeout
                                                                        ) AS TIME) < CAST(`fpshift`.`breakout` AS TIME)
                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                        )
                                                                        ,0
                                                                        ) 
                                                                ,'0.00') 
                                                                
                                                            ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                            + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                        
                                                        ) 
                                                END) + (CASE
                                                        WHEN ((CASE  WHEN l.stat = 1 AND l.idtimeleavetype = 1 THEN 0
                                                                WHEN l.stat = 1 AND l.idtimeleavetype != 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                                                - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                    )
                                                                                                    ,0
                                                                                                )
                                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                                - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                    )
                                                                                                    ,0
                                                                                                )
                                                                                            )
                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                                            )
                                                                                            + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                    )  - l.hrs
                                                                WHEN DATE(NOW()) <= DATE(a1.work_date) THEN 0
                                                                WHEN ((CASE WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ) AS TIME)
                                                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ), fpshift.stime)) / 3600,2)
                                                                                                    ,'0.00'
                                                                                                    )
                                                                                                ,0
                                                                                            ) 
                                                                                ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                            ,'0.00'
                                                                                            )
                                                                                            - IF(CAST(a.timein AS TIME) > CAST(`fpshift`.`breakout` AS TIME) 
                                                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                    )
                                                                                                    ,0
                                                                                            )
                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                                            ,'0.00'
                                                                                            ) 
                                                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                                            ,'0.00'
                                                                                            )
                                                                                            
                                                                                            - IF(CAST(a.timein AS TIME) > CAST(`fpshift`.`breakout` AS TIME) 
                                                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                    )
                                                                                                    ,0
                                                                                            )
                                                                                    ) 
                                                                            END)  + (CASE WHEN ((CASE WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ) AS TIME)
                                                                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ), fpshift.stime)) / 3600,2)
                                                                                                                    ,'0.00'
                                                                                                                    )
                                                                                                                ,0
                                                                                                            ) 
                                                                                                            ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                                                        
                                                                                                                        
                                                                                                                        ,'0.00'
                                                                                                                        )
                                                                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                                                                        ,'0.00'
                                                                                                                        )
                                                                                                                    + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                                                                        ,'0.00'
                                                                                                                        )
                                                                                                                ) 
                                                                                                        END) + (CASE 
                                                                                                            WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                        ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                                                        ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                                                        + IF(CAST(k.sftime AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, k.sftime)) / 3600,2),'0.00') 
                                                                                                                    ) 
                                                                                                            WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                            ,IF(CAST(
                                                                                                                            IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time )
                                                                                                                            AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time ))) / 3600,2),'0.00') 
                                                                                                                            ,0
                                                                                                                        ) 
                                                                                                            ELSE  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                    ,IF(CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(
                                                                                                                            IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                                                ,a1.work_date
                                                                                                                                ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                                                                            , ' ', fpshift.ftime) AS DATETIME)
                                                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                                                                        ,'0.00')
                                                                                                                    ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                                                                    + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                                                                ) 
                                                                                                        END) >  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                    )
                                                                                                                            ,0
                                                                                                                        )
                                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                    )
                                                                                                                            ,0
                                                                                                                        )
                                                                                                                    )
                                                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                                                                    )
                                                                                                                    + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                                                                        )
                                                                                                            ) ) 
                                                                                                        THEN 0
                                                                                        WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                    ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                                    ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                                    + IF(CAST(k.sftime AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, k.sftime)) / 3600,2),'0.00') 
                                                                                                ) 
                                                                                        WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                        ,IF(CAST(
                                                                                                        IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time )
                                                                                                        AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time ))) / 3600,2),'0.00') 
                                                                                                        ,0
                                                                                                    ) 
                                                                                        ELSE  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                ,IF(CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(
                                                                                                        IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                            ,a1.work_date
                                                                                                            ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                                                        , ' ', fpshift.ftime) AS DATETIME)
                                                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                                                    ,'0.00')
                                                                                                ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                                                + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                                            ) 
                                                                                    END)) > IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                    )
                                                                                                        ,0
                                                                                                    )
                                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                    )
                                                                                                        ,0
                                                                                                    )
                                                                                                )
                                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                                + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                                                    )
                                                                                        )
                                                                        THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                            )
                                                                                            ,0
                                                                                        )
                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                            )
                                                                                            ,0
                                                                                        )
                                                                                    )
                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                                    )
                                                                                    + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                                        )
                                                                            )
                                                                WHEN k.stat = 1 THEN 0
                                                                WHEN p.stat = 1 THEN 0
                                                                WHEN m.stat = 1 THEN 0
                                                                WHEN d.type = 'LEGAL' OR d.type = 'LEGAL SPECIAL' THEN 0
                                                                ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                        ,IF(a.timein IS NULL OR a.timeout IS NULL OR a.timein = '' OR a.timeout = ''
                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL			
                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                        )
                                                                                        ,0
                                                                                    )
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL			
                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                            )
                                                                                        ,0
                                                                                    )
                                                                                )
                                                                            ,0
                                                                        )
                                                                        ,IF(a.timein IS NULL OR a.timeout IS NULL OR a.timein = '' OR a.timeout = ''
                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                )
                                                                            ,0
                                                                        ) + IF(a.timein2 IS NULL OR a.timeout2 IS NULL OR a.timein2 = '' OR a.timeout2 = ''
                                                                            ,IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2) 
                                                                                )
                                                                            ,0
                                                                        )
                                                                        
                                                                    )
                                                            END)  
                                                            =  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                            )
                                                                            ,0
                                                                        )
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                            )
                                                                            ,0
                                                                        )
                                                                    )
                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                    )
                                                                    + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                        )
                                                                ) 
                                                            )
                                                        THEN 0
                                                        WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(k.stime AS TIME) AND k.ftime != ''
                                                                            ,IF(CAST(k.stime AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a1.work_date, ' ', fpshift.breakin) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a1.work_date, ' ', k.stime) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                            )	
                                                                            ,'0.00'
                                                                            )
                                                                            - IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                            ,IF(fpshift.breakin = '00:00'
                                                                                                                ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                                , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                )
                                                                                                            ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                        )
                            
                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                )
                                                                                ,0
                                                                            )
                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                            ,'0.00'
                                                                            ) 
                                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                            ,'0.00'
                                                                            )
                                                                            
                                                                            -  IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                            ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                            ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                        )
                            
                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                )
                                                                                ,0
                                                                            )
                                                                    ) 
                                                        WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ) AS TIME)
                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ), fpshift.stime)) / 3600,2)
                                                                            ,'0.00'
                                                                            )
                                                                        ,0
                                                                    ) 
                                                        WHEN l.stat = 1 AND l.idtimeleavetype = 2 THEN IF(  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                            ,IF(CAST(a.timein AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', fpshift.breakin) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                            )	
                                                                                            ,'0.00'
                                                                                            )
                                                                                            - IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                            ,IF(fpshift.breakin = '00:00'
                                                                                                                                ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                                                , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                                )
                                                                                                                            ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                        )
                            
                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                                ,0
                                                                                            )
                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                                            ,'0.00'
                                                                                            ) 
                                                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                                            ,'0.00'
                                                                                            )
                                                                                            
                                                                                            -  IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                            ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                                            ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                        )
                            
                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                                ,0
                                                                                            )
                                                                                    ) - l.hrs > 0
                                                                                    ,IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                            ,IF(CAST(a.timein AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', fpshift.breakin) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                            )	
                                                                                            ,'0.00'
                                                                                            )
                                                                                            - IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                            ,IF(fpshift.breakin = '00:00'
                                                                                                                                ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                                                , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                                )
                                                                                                                            ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                        )
                            
                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                                ,0
                                                                                            )
                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                                            ,'0.00'
                                                                                            ) 
                                                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                                            ,'0.00'
                                                                                            )
                                                                                            
                                                                                            -  IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                            ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                                            ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                        )
                            
                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                                ,0
                                                                                            )
                                                                                    ) - l.hrs
                                                                                    ,0
                                                                                    )
                                                        
                                                        ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                    ,IF(CAST(a.timein AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', fpshift.breakin) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                    )	
                                                                    ,'0.00'
                                                                    )
                                                                    - IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    ,IF(fpshift.breakin = '00:00'
                                                                                                        ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                        , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                        )
                                                                                                    ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                )
                            
                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                        )
                                                                        ,0
                                                                    )
                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                    ,'0.00'
                                                                    ) 
                                                                + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                    ,'0.00'
                                                                    )
                                                                    
                                                                    -  IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                    ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                )
                            
                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                        )
                                                                        ,0
                                                                    )
                                                            ) 
                                                    END) > IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                            )
                                                                        ,0
                                                                    )
                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                        )
                                                                        ,0
                                                                    )
                                                                )
                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                )
                                                                + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                    )
                                                            ))
                                    THEN 0
                                    WHEN l.stat = 1 AND l.idtimeleavetype = 1 THEN 0
                                    WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime
                                                                    ,IF(CAST(k.ftime AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND CAST(fpshift.breakout AS TIME)
                                                                        ,fpshift.breakout
                                                                        ,k.ftime
                                                                    ))
                                                                ) / 3600,2)
                                                    ,'0.00') 
                                                    
                                                ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                + IF(CAST(k.sftime AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, k.sftime)) / 3600,2),'0.00') 
                                            ) 
                                    WHEN p.stat = 1 AND p.idtimetype = 1 THEN 0
                                    WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                    ,IF(CAST(
                                                    IF(a.timeout IS NOT NULL OR a.timeout != '',
                                                    IF(CAST(a.timeout AS TIME) < CAST(p.end_time AS TIME)
                                                                                ,p.end_time
                                                                                ,a.timeout 
                                                                            ) ,p.end_time )
                                                    AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time ))) / 3600,2),'0.00') 
                                                    ,0
                                                ) 
                                    WHEN l.stat = 1 AND l.idtimeleavetype = 3 THEN IF( IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                    ,IF(CAST(CONCAT(a.date_out, ' ', IF(CAST(a.timeout AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                        ,fpshift.breakout
                                                                                        ,a.timeout
                                                                                    )) AS DATETIME) < CAST(CONCAT(
                                                                            IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                ,a1.work_date
                                                                                ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                            , ' ', fpshift.ftime) AS DATETIME)
                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                        ,'0.00')  - IF(CAST(a.timeout AS TIME) < CAST(`fpshift`.`breakout` AS TIME)
                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                )
                                                                                ,0
                                                                                ) 
                                                                        
                                                                    ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                    + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                
                                                                )  - l.hrs > 0 
                                                            ,IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                    ,IF(CAST(CONCAT(a.date_out, ' ', IF(CAST(a.timeout AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                        ,fpshift.breakout
                                                                                        ,a.timeout
                                                                                    )) AS DATETIME) < CAST(CONCAT(
                                                                            IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                ,a1.work_date
                                                                                ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                            , ' ', fpshift.ftime) AS DATETIME)
                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                        ,'0.00')  - IF(CAST(a.timeout AS TIME) < CAST(`fpshift`.`breakout` AS TIME)
                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                )
                                                                                ,0
                                                                                ) 
                                                                        
                                                                    ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                    + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                
                                                                )  - l.hrs 
                                                            ,0
                                                            )
                                    ELSE  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                            ,IF(CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(
                                                    IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                        ,a1.work_date
                                                        ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                    , ' ', fpshift.ftime) AS DATETIME)
                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(CAST(a.timeout AS TIME) BETWEEN  CAST(`fpshift`.`breakin` AS TIME)  AND CAST(`fpshift`.`breakout` AS TIME)
                                                            ,`fpshift`.`breakout`
                                                            ,a.timeout
                                                        ))) / 3600,2)
                                                
                                                        
                                                        
                                                    - IF(CAST(IF(CAST(a.timeout AS TIME) BETWEEN  CAST(`fpshift`.`breakin` AS TIME)  AND CAST(`fpshift`.`breakout` AS TIME)
                                                            ,`fpshift`.`breakout`
                                                            ,a.timeout
                                                        ) AS TIME) < CAST(`fpshift`.`breakout` AS TIME)
                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                        )
                                                        ,0
                                                        ) 
                                                ,'0.00') 
                                                
                                            ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                            + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                        
                                        ) 
                                END) 
                                * 
                                (IFNULL((SELECT currentbasepay FROM (select id ,requestor ,currentbasepay ,effectivedate,idstatus from tblforms02 union select id ,requestor ,currentbasepay ,effectivedate,idstatus from tblforms03) a WHERE effectivedate > a1.work_date AND idstatus = 1 AND requestor = a1.id LIMIT 1),e.salary) / 26.0833) / 8,2
                                ))
                            AS ut,
                            SUM(ROUND((CASE 
                                    WHEN l.stat = 1 AND l.idtimeleavetype = 1 THEN 0
                                    WHEN l.stat = 1 AND l.idtimeleavetype != 1 THEN IF((CASE WHEN fpshift.id  != 4 
                                                                        THEN (CASE 
                                                                            WHEN DATE(NOW()) <= DATE(a1.work_date) THEN 0
                                                                            WHEN ((CASE WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ) AS TIME)
                                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ), fpshift.stime)) / 3600,2)
                                                                                                        ,'0.00'
                                                                                                        )
                                                                                                    ,0
                                                                                                ) 
                                                                                    ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                                ,'0.00'
                                                                                                )
                                                                                                - IF(CAST(a.timein AS TIME) > CAST(`fpshift`.`breakout` AS TIME) 
                                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                                                        ,0
                                                                                                )
                                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                                                ,'0.00'
                                                                                                ) 
                                                                                            + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                                                ,'0.00'
                                                                                                )
                                                                                                
                                                                                                - IF(CAST(a.timein AS TIME) > CAST(`fpshift`.`breakout` AS TIME) 
                                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                                                        ,0
                                                                                                )
                                                                                        ) 
                                                                                END)  + (CASE WHEN ((CASE WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ) AS TIME)
                                                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ), fpshift.stime)) / 3600,2)
                                                                                                                        ,'0.00'
                                                                                                                        )
                                                                                                                    ,0
                                                                                                                ) 
                                                                                                                ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                                                            
                                                                                                                            
                                                                                                                            ,'0.00'
                                                                                                                            )
                                                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                                                                            ,'0.00'
                                                                                                                            )
                                                                                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                                                                            ,'0.00'
                                                                                                                            )
                                                                                                                    ) 
                                                                                                            END) + (CASE 
                                                                                                                WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                            ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                                                            ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                                                            + IF(CAST(k.sftime AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, k.sftime)) / 3600,2),'0.00') 
                                                                                                                        ) 
                                                                                                                WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                                ,IF(CAST(
                                                                                                                                IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time )
                                                                                                                                AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time ))) / 3600,2),'0.00') 
                                                                                                                                ,0
                                                                                                                            ) 
                                                                                                                ELSE  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                        ,IF(CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(
                                                                                                                                IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                                                    ,a1.work_date
                                                                                                                                    ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                                                                                , ' ', fpshift.ftime) AS DATETIME)
                                                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                                                                            ,'0.00')
                                                                                                                        ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                                                                        + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                                                                    ) 
                                                                                                            END) >  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                                                                                ,0
                                                                                                                            )
                                                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                                                                                ,0
                                                                                                                            )
                                                                                                                        )
                                                                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                                                                        )
                                                                                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                                                                            )
                                                                                                                ) ) 
                                                                                                            THEN 0
                                                                                            WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                        ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                                        ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                                        + IF(CAST(k.sftime AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, k.sftime)) / 3600,2),'0.00') 
                                                                                                    ) 
                                                                                            WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                            ,IF(CAST(
                                                                                                            IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time )
                                                                                                            AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time ))) / 3600,2),'0.00') 
                                                                                                            ,0
                                                                                                        ) 
                                                                                            ELSE  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                    ,IF(CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(
                                                                                                            IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                                ,a1.work_date
                                                                                                                ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                                                            , ' ', fpshift.ftime) AS DATETIME)
                                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                                                        ,'0.00')
                                                                                                    ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                                                    + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                                                ) 
                                                                                        END)) > IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                                                            ,0
                                                                                                        )
                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                                                            ,0
                                                                                                        )
                                                                                                    )
                                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                                                    )
                                                                                                    + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                                                        )
                                                                                            )
                                                                            THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                                                ,0
                                                                                            )
                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                                                ,0
                                                                                            )
                                                                                        )
                                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                                        )
                                                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                                            )
                                                                                )
                                                                            
                                                                            WHEN p.stat = 1 AND k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                    ,ROUND(TIMESTAMPDIFF(SECOND,CAST(CONCAT(a1.work_date, ' ',IF( CAST(fpshift.stime AS TIME) < CAST(k.stime AS TIME)  ,k.stime , fpshift.stime)
                                                                                                    ) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', k.ftime) AS DATETIME) ) / 3600,2) 
                                                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                                                            ,0
                                                                                                        )
                                                                                                    ,0
                                                                                                )
                                                                            WHEN p.stat = 1 THEN (CASE 
                                                                                                WHEN p.idtimetype = 1 THEN 
                                                                                                        ROUND(TIMESTAMPDIFF(SECOND,CAST(CONCAT(a1.work_date, ' ', p.start_time) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', p.end_time) AS DATETIME) ) / 3600,2) 
                                                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                                                            ,0
                                                                                                        )
                                                                                                WHEN p.idtimetype = 2 THEN 
                                                                                                        ROUND(TIMESTAMPDIFF(SECOND,CAST(CONCAT(a1.work_date, ' ', p.start_time) AS DATETIME), CAST(CONCAT(a1.work_date, ' ',
                                                                                                            IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout , p.end_time)) AS DATETIME) ) / 3600,2)
                                                                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                                AND CAST(fpshift.breakout AS TIME) < CAST(IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout , p.end_time) AS TIME)
                                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                                                                ,0
                                                                                                            )
                                                                                                WHEN p.idtimetype = 3 THEN 
                                                                                                        ROUND(TIMESTAMPDIFF(SECOND,CAST(CONCAT(a1.work_date, ' ', 
                                                                                                            IF(a.timein IS NOT NULL OR a.timein != '' ,a.timein , p.start_time)) AS DATETIME), CAST(CONCAT(a1.work_date, ' ',p.end_time) AS DATETIME) ) / 3600,2)
                                                                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                                AND CAST(fpshift.breakout AS TIME) < CAST(IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout , p.end_time) AS TIME)
                                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                                                                ,0
                                                                                                            )
                                                                                                ELSE ROUND(TIMESTAMPDIFF(SECOND,CAST(CONCAT(a1.work_date, ' ',IF(a.timein IS NOT NULL OR a.timein != '' ,a.timein , p.start_time)) AS DATETIME), 
                                                                                                            CAST(CONCAT(a1.work_date, ' ', IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout , p.end_time)) AS DATETIME) ) / 3600,2)
                                                                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                                AND CAST(fpshift.breakout AS TIME) < CAST(IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout , p.end_time) AS TIME)
                                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                                                                ,0
                                                                                                            )
                                                                                            END) 
                                                                            WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                    ,ROUND(TIMESTAMPDIFF(SECOND,CAST(CONCAT(a1.work_date, ' ',IF( CAST(fpshift.stime AS TIME) < CAST(k.stime AS TIME)  ,k.stime , fpshift.stime)
                                                                                                    ) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', k.ftime) AS DATETIME) ) / 3600,2) 
                                                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                                                            ,0
                                                                                                        )
                                                                                                    ,ROUND(TIMESTAMPDIFF(SECOND,CAST(CONCAT(a1.work_date, ' ',IF( CAST(fpshift.stime AS TIME) < CAST(k.stime AS TIME)  ,k.stime , fpshift.stime)
                                                                                                        ) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', k.ftime) AS DATETIME) ) / 3600,2)
                                                                                                    + ROUND(TIMESTAMPDIFF(SECOND,CAST(CONCAT(a1.work_date, ' ',IF( CAST(fpshift.sstime AS TIME) < CAST(k.sstime AS TIME)  ,k.sstime , fpshift.sstime)
                                                                                                        ) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', k.sftime) AS DATETIME) ) / 3600,2) 
                                                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                                                            ,0
                                                                                                        )
                                                                                                )
                                                                            ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                    ,IF(a.timein IS NOT NULL AND a.timeout IS NOT NULL AND a.timein != '' AND a.timeout != ''
                                                                                            ,ROUND(TIMESTAMPDIFF(SECOND,CAST(CONCAT(a.date_in, ' ', 
                                                                                            IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME)
                                                                                            
                                                                                                ,IF(CAST(a.timein AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                                    ,fpshift.breakout
                                                                                                    ,a.timein 
                                                                                                )
                                                                                            
                                                                                                ,fpshift.stime )) AS DATETIME), CAST(CONCAT(a.date_out, ' ', a.timeout 
                                                                                                
                                                                                                ) AS DATETIME) ) / 3600,2) 
                                                                                                
                                                                                            ,0
                                                                                        )
                                                                                        -  IF(CAST(CONCAT(a.date_in, ' ', IF(CAST(a.timein AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                                            ,fpshift.breakout
                                                                                                            ,a.timein 
                                                                                                        )) AS DATETIME) < IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                        ,IF(fpshift.breakin = '00:00'
                                                                                                                        ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                                        , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                        )
                                                                                                                        ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                                    ) 
                                                                                        AND CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    ,IF(fpshift.breakin = '00:00'
                                                                                                    ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                    , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                    )
                                                                                                    ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                )  					
                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                            )
                                                                                            ,0
                                                                                        )
                                                                                            
                                                                                    ,IF(a.timein IS NOT NULL AND a.timeout IS NOT NULL AND a.timein != '' AND a.timeout != ''
                                                                                            ,ROUND(TIMESTAMPDIFF(SECOND,CAST(CONCAT(a.date_in, ' ', 
                                                                                            IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) ,a.timein ,fpshift.stime )) AS DATETIME), CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) ) / 3600,2) 
                                                                                            ,0
                                                                                        )
                                                                                    + IF(a.timein2 IS NOT NULL AND a.timeout2 IS NOT NULL AND a.timein2 != '' AND a.timeout2 != ''
                                                                                            ,ROUND(TIMESTAMPDIFF(SECOND,CAST(CONCAT(a.date_in2, ' ', 
                                                                                            IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) ,a.timein2 ,fpshift.sstime )) AS DATETIME), CAST(CONCAT(a.date_out2, ' ', a.timeout2) AS DATETIME) ) / 3600,2) 
                                                                                            ,0
                                                                                        )
                                                                                )
                                                                        END) 
                                                                    ELSE 0
                                                                END) > 0
                                                                ,0
                                                                ,IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                )
                                                                                ,0
                                                                            )
                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                    )
                                                                                ,0
                                                                            )
                                                                        )
                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                        )
                                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                            )
                                                                )  - l.hrs
                                                                )
                                    WHEN DATE(NOW()) <= DATE(a1.work_date) THEN 0
                                    WHEN ((CASE 
                                            WHEN l.stat = 1 AND l.idtimeleavetype = 1 THEN 0
                                            WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                        ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                        ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                        + IF(CAST(k.sftime AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, k.sftime)) / 3600,2),'0.00') 
                                                    ) 
                                            WHEN p.stat = 1 AND p.idtimetype = 1 THEN 0
                                            WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                            ,IF(CAST(
                                                            IF(a.timeout IS NOT NULL OR a.timeout != '',
                                                            IF(CAST(a.timeout AS TIME) < CAST(p.end_time AS TIME)
                                                                                        ,p.end_time
                                                                                        ,a.timeout 
                                                                                    ) ,p.end_time )
                                                            AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time ))) / 3600,2),'0.00') 
                                                            ,0
                                                        ) 
                                            WHEN l.stat = 1 AND l.idtimeleavetype = 3 THEN IF( IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                            ,IF(CAST(CONCAT(a.date_out, ' ', IF(CAST(a.timeout AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                                ,fpshift.breakout
                                                                                                ,a.timeout
                                                                                            )) AS DATETIME) < CAST(CONCAT(
                                                                                    IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                        ,a1.work_date
                                                                                        ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                                    , ' ', fpshift.ftime) AS DATETIME)
                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                                ,'0.00')  - IF(CAST(a.timeout AS TIME) < CAST(`fpshift`.`breakout` AS TIME)
                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                        )
                                                                                        ,0
                                                                                        ) 
                                                                                
                                                                            ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                            + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                        
                                                                        )  - l.hrs > 0 
                                                                    ,IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                            ,IF(CAST(CONCAT(a.date_out, ' ', IF(CAST(a.timeout AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                                ,fpshift.breakout
                                                                                                ,a.timeout
                                                                                            )) AS DATETIME) < CAST(CONCAT(
                                                                                    IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                        ,a1.work_date
                                                                                        ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                                    , ' ', fpshift.ftime) AS DATETIME)
                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                                ,'0.00')  - IF(CAST(a.timeout AS TIME) < CAST(`fpshift`.`breakout` AS TIME)
                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                        )
                                                                                        ,0
                                                                                        ) 
                                                                                
                                                                            ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                            + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                        
                                                                        )  - l.hrs 
                                                                    ,0
                                                                    )
                                            ELSE  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                    ,IF(CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(
                                                            IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                ,a1.work_date
                                                                ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                            , ' ', fpshift.ftime) AS DATETIME)
                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(CAST(a.timeout AS TIME) BETWEEN  CAST(`fpshift`.`breakin` AS TIME)  AND CAST(`fpshift`.`breakout` AS TIME)
                                                                    ,`fpshift`.`breakout`
                                                                    ,a.timeout
                                                                ))) / 3600,2)
                                                        
                                                                
                                                                
                                                            - IF(CAST(IF(CAST(a.timeout AS TIME) BETWEEN  CAST(`fpshift`.`breakin` AS TIME)  AND CAST(`fpshift`.`breakout` AS TIME)
                                                                    ,`fpshift`.`breakout`
                                                                    ,a.timeout
                                                                ) AS TIME) < CAST(`fpshift`.`breakout` AS TIME)
                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                ,0
                                                                ) 
                                                        ,'0.00') 
                                                        
                                                    ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                    + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                
                                                ) 
                                        END) + (CASE
                                            WHEN ((CASE  WHEN l.stat = 1 AND l.idtimeleavetype = 1 THEN 0
                                                    WHEN l.stat = 1 AND l.idtimeleavetype != 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                        )
                                                                                        ,0
                                                                                    )
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                        )
                                                                                        ,0
                                                                                    )
                                                                                )
                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                                )
                                                                                + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                                    )
                                                                        )  - l.hrs
                                                    WHEN DATE(NOW()) <= DATE(a1.work_date) THEN 0
                                                    WHEN ((CASE WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ) AS TIME)
                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ), fpshift.stime)) / 3600,2)
                                                                                        ,'0.00'
                                                                                        )
                                                                                    ,0
                                                                                ) 
                                                                    ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                ,'0.00'
                                                                                )
                                                                                - IF(CAST(a.timein AS TIME) > CAST(`fpshift`.`breakout` AS TIME) 
                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                        )
                                                                                        ,0
                                                                                )
                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                                ,'0.00'
                                                                                ) 
                                                                            + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                                ,'0.00'
                                                                                )
                                                                                
                                                                                - IF(CAST(a.timein AS TIME) > CAST(`fpshift`.`breakout` AS TIME) 
                                                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                        )
                                                                                        ,0
                                                                                )
                                                                        ) 
                                                                END)  + (CASE WHEN ((CASE WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ) AS TIME)
                                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ), fpshift.stime)) / 3600,2)
                                                                                                        ,'0.00'
                                                                                                        )
                                                                                                    ,0
                                                                                                ) 
                                                                                                ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                                            
                                                                                                            
                                                                                                            ,'0.00'
                                                                                                            )
                                                                                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                                                            ,'0.00'
                                                                                                            )
                                                                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                                                            ,'0.00'
                                                                                                            )
                                                                                                    ) 
                                                                                            END) + (CASE 
                                                                                                WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                            ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                                            ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                                            + IF(CAST(k.sftime AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, k.sftime)) / 3600,2),'0.00') 
                                                                                                        ) 
                                                                                                WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                                ,IF(CAST(
                                                                                                                IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time )
                                                                                                                AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time ))) / 3600,2),'0.00') 
                                                                                                                ,0
                                                                                                            ) 
                                                                                                ELSE  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                        ,IF(CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(
                                                                                                                IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                                    ,a1.work_date
                                                                                                                    ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                                                                , ' ', fpshift.ftime) AS DATETIME)
                                                                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                                                            ,'0.00')
                                                                                                        ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                                                        + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                                                    ) 
                                                                                            END) >  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                                    )
                                                                                                                ,0
                                                                                                            )
                                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                                )
                                                                                                                ,0
                                                                                                            )
                                                                                                        )
                                                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                                                        )
                                                                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                                                            )
                                                                                                ) ) 
                                                                                            THEN 0
                                                                            WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                        ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                        ,IF(CAST(k.ftime AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, k.ftime)) / 3600,2),'0.00') 
                                                                                        + IF(CAST(k.sftime AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, k.sftime)) / 3600,2),'0.00') 
                                                                                    ) 
                                                                            WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                            ,IF(CAST(
                                                                                            IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time )
                                                                                            AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, IF(a.timeout IS NOT NULL OR a.timeout != '',a.timeout ,p.end_time ))) / 3600,2),'0.00') 
                                                                                            ,0
                                                                                        ) 
                                                                            ELSE  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                    ,IF(CAST(CONCAT(a.date_out, ' ', a.timeout) AS DATETIME) < CAST(CONCAT(
                                                                                            IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                                ,a1.work_date
                                                                                                ,DATE_ADD(a1.work_date, INTERVAL 1 DAY))
                                                                                            , ' ', fpshift.ftime) AS DATETIME)
                                                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2)
                                                                                        ,'0.00')
                                                                                    ,IF(CAST(a.timeout AS TIME) < CAST(fpshift.ftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.ftime, a.timeout)) / 3600,2),'0.00') 
                                                                                    + IF(CAST(a.timeout2 AS TIME) < CAST(fpshift.sftime AS TIME),ROUND(TIME_TO_SEC(TIMEDIFF(fpshift.sftime, a.timeout2)) / 3600,2),'0.00') 
                                                                                ) 
                                                                        END)) > IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                            ,0
                                                                                        )
                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                                )
                                                                                            ,0
                                                                                        )
                                                                                    )
                                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                                    )
                                                                                    + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                                        )
                                                                            )
                                                            THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                )
                                                                                ,0
                                                                            )
                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                )
                                                                                ,0
                                                                            )
                                                                        )
                                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                                        )
                                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                                            )
                                                                )
                                                    WHEN k.stat = 1 THEN 0
                                                    WHEN p.stat = 1 THEN 0
                                                    WHEN m.stat = 1 THEN 0
                                                    WHEN d.type = 'LEGAL' OR d.type = 'LEGAL SPECIAL' THEN 0
                                                    ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                            ,IF(a.timein IS NULL OR a.timeout IS NULL OR a.timein = '' OR a.timeout = ''
                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL			
                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                            )
                                                                            ,0
                                                                        )
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL			
                                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                )
                                                                            ,0
                                                                        )
                                                                    )
                                                                ,0
                                                            )
                                                            ,IF(a.timein IS NULL OR a.timeout IS NULL OR a.timein = '' OR a.timeout = ''
                                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                                    )
                                                                ,0
                                                            ) + IF(a.timein2 IS NULL OR a.timeout2 IS NULL OR a.timein2 = '' OR a.timeout2 = ''
                                                                ,IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2) 
                                                                    )
                                                                ,0
                                                            )
                                                            
                                                        )
                                                END)  
                                                =  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                ,0
                                                            )
                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                            - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                                ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                                ,0
                                                            )
                                                        )
                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                        )
                                                        + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                            )
                                                    ) 
                                                )
                                            THEN 0
                                            WHEN k.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(k.stime AS TIME) AND k.ftime != ''
                                                                ,IF(CAST(k.stime AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a1.work_date, ' ', fpshift.breakin) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a1.work_date, ' ', k.stime) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                )	
                                                                ,'0.00'
                                                                )
                                                                - IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                ,IF(fpshift.breakin = '00:00'
                                                                                                    ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                    , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                    )
                                                                                                ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                            )
                            
                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                    )
                                                                    ,0
                                                                )
                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                ,'0.00'
                                                                ) 
                                                            + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                ,'0.00'
                                                                )
                                                                
                                                                -  IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                            )
                            
                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                    )
                                                                    ,0
                                                                )
                                                        ) 
                                            WHEN p.stat = 1 THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ) AS TIME)
                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(IF(a.timein IS NOT NULL OR a.timein != '',a.timein ,p.start_time ), fpshift.stime)) / 3600,2)
                                                                ,'0.00'
                                                                )
                                                            ,0
                                                        ) 
                                            WHEN l.stat = 1 AND l.idtimeleavetype = 2 THEN IF(  IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                ,IF(CAST(a.timein AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', fpshift.breakin) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                )	
                                                                                ,'0.00'
                                                                                )
                                                                                - IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                ,IF(fpshift.breakin = '00:00'
                                                                                                                    ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                                    , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                    )
                                                                                                                ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                            )
                            
                                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                    )
                                                                                    ,0
                                                                                )
                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                                ,'0.00'
                                                                                ) 
                                                                            + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                                ,'0.00'
                                                                                )
                                                                                
                                                                                -  IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                                ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                            )
                            
                                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                    )
                                                                                    ,0
                                                                                )
                                                                        ) - l.hrs > 0
                                                                        ,IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                ,IF(CAST(a.timein AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', fpshift.breakin) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                    ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                                                )	
                                                                                ,'0.00'
                                                                                )
                                                                                - IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                ,IF(fpshift.breakin = '00:00'
                                                                                                                    ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                                    , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                                    )
                                                                                                                ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                            )
                            
                                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                    )
                                                                                    ,0
                                                                                )
                                                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                                                ,'0.00'
                                                                                ) 
                                                                            + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                                                ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                                                ,'0.00'
                                                                                )
                                                                                
                                                                                -  IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                                                ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                                                ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                                            )
                            
                                                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                                    )
                                                                                    ,0
                                                                                )
                                                                        ) - l.hrs
                                                                        ,0
                                                                        )
                                            
                                            ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                        ,IF(CAST(a.timein AS TIME) BETWEEN CAST(fpshift.breakin AS TIME) AND  CAST(fpshift.breakout AS TIME)
                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', fpshift.breakin) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                            ,ROUND(TIME_TO_SEC(TIMEDIFF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME), CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME))) / 3600,2)
                                                        )	
                                                        ,'0.00'
                                                        )
                                                        - IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                        ,IF(fpshift.breakin = '00:00'
                                                                                            ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                            , CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                            )
                                                                                        ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                    )
                            
                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                            )
                                                            ,0
                                                        )
                                                    ,IF(CAST(fpshift.stime AS TIME) < CAST(a.timein AS TIME) AND a.timeout != ''
                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein, fpshift.stime)) / 3600,2)
                                                        ,'0.00'
                                                        ) 
                                                    + IF(CAST(fpshift.sstime AS TIME) < CAST(a.timein2 AS TIME) AND a.timeout2 != ''
                                                        ,ROUND(TIME_TO_SEC(TIMEDIFF(a.timein2, fpshift.sstime)) / 3600,2)
                                                        ,'0.00'
                                                        )
                                                        
                                                        -  IF(CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME) > IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                                        ,CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakout) AS DATETIME)
                                                                                        ,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME)
                                                                                    )
                            
                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                            )
                                                            ,0
                                                        )
                                                ) 
                                        END) > IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                            ,0
                                                        )
                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                            )
                                                            ,0
                                                        )
                                                    )
                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                    )
                                                    + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                        ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                        )
                                                ))
                                    THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                        )
                                                    ,0
                                                )
                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                    ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                        , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                    )
                                                    ,0
                                                )
                                            )
                                        ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                            ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                            )
                                            + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                )
                                    )
                                    WHEN k.stat = 1 THEN 0
                                    WHEN p.stat = 1 THEN 0
                                    WHEN m.stat = 1 THEN 0
                                    WHEN d.type = 'LEGAL' OR d.type = 'LEGAL SPECIAL' THEN 0
                                    WHEN ((fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule') AND
                                                (CAST(CONCAT(IF(CAST(fpshift.ftime AS TIME) < CAST(fpshift.stime AS TIME)
                                                            ,DATE_ADD(a1.work_date, INTERVAL 1 DAY)
                                                            ,a1.work_date
                                                        ), ' ', fpshift.ftime) AS DATETIME) < CAST(CONCAT(a.date_in, ' ', a.timein) AS DATETIME)))	
                                    THEN IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(u.ftime AS TIME) ) / 3600,2) 
                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                            )
                                                        ,0
                                                    )
                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                    - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL
                                                        ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                            , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                        )
                                                        ,0
                                                    )
                                                )
                                            ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2)
                                                )
                                                + IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2)
                                                    )
                                        ) 
                                    ELSE IF(fpshift.stype = 'Regular Schedule' OR fpshift.stype = 'Compressed Schedule'
                                            ,IF(a.timein IS NULL OR a.timeout IS NULL OR a.timein = '' OR a.timeout = ''
                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL			
                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                            )
                                                            ,0
                                                        )
                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                        - IF(`fpshift`.`breakin` IS NOT NULL AND `fpshift`.`breakout` IS NOT NULL			
                                                            ,IF(CAST(fpshift.breakin AS TIME) < CAST(fpshift.breakout AS TIME)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(`fpshift`.`breakin` AS TIME), CAST(`fpshift`.`breakout` AS TIME) ) / 3600,2)
                                                                    , ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.breakout) AS DATETIME),CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.breakin) AS DATETIME) ) / 3600,2)
                                                                )
                                                            ,0
                                                        )
                                                    )
                                                ,0
                                            )
                                            ,IF(a.timein IS NULL OR a.timeout IS NULL OR a.timein = '' OR a.timeout = ''
                                                ,IF(CAST(fpshift.stime AS TIME) < CAST(fpshift.ftime AS TIME)
                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.stime AS TIME), CAST(fpshift.ftime AS TIME) ) / 3600,2)
                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.stime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.ftime) AS DATETIME) ) / 3600,2) 
                                                    )
                                                ,0
                                            ) + IF(a.timein2 IS NULL OR a.timeout2 IS NULL OR a.timein2 = '' OR a.timeout2 = ''
                                                ,IF(CAST(fpshift.sstime AS TIME) < CAST(fpshift.sftime AS TIME)
                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(fpshift.sstime AS TIME), CAST(fpshift.sftime AS TIME) ) / 3600,2)
                                                    ,ROUND(TIMESTAMPDIFF( SECOND,CAST(CONCAT(a1.work_date, ' ', fpshift.sstime) AS DATETIME), CAST(CONCAT(DATE_ADD(a1.work_date, INTERVAL 1 DAY), ' ', fpshift.sftime) AS DATETIME) ) / 3600,2) 
                                                    )
                                                ,0
                                            )
                                            
                                        ) 
                                END) 
                                * 
                                (IFNULL((SELECT currentbasepay FROM (select id ,requestor ,currentbasepay ,effectivedate,idstatus from tblforms02 union select id ,requestor ,currentbasepay ,effectivedate,idstatus from tblforms03) a WHERE effectivedate > a1.work_date AND idstatus = 1 AND requestor = a1.id LIMIT 1),e.salary) / 26.0833) / 8,2
                                ))
                            AS absent,
                            SUM(IF(l.stat = 1 AND l.idleave = 3
                                ,ROUND(l.hrs * (IFNULL((SELECT currentbasepay FROM (SELECT id ,requestor ,currentbasepay ,effectivedate,idstatus FROM tblforms02 UNION SELECT id ,requestor ,currentbasepay ,effectivedate,idstatus FROM tblforms03) a WHERE effectivedate > a1.work_date AND idstatus = 1 AND requestor = a1.id LIMIT 1),e.salary) / 26.0833) / 8,2)
                                ,0
                            )) 
                            as lwop,
                            SUM(IF(l.stat = 1 AND l.idleave != 3
                                ,ROUND(l.hrs * (IFNULL((SELECT currentbasepay FROM (SELECT id ,requestor ,currentbasepay ,effectivedate,idstatus FROM tblforms02 UNION SELECT id ,requestor ,currentbasepay ,effectivedate,idstatus FROM tblforms03) a WHERE effectivedate > a1.work_date AND idstatus = 1 AND requestor = a1.id LIMIT 1),e.salary) / 26.0833) / 8,2)
                                ,0
                            ))
                            AS lwp,
                            AVG(IFNULL((SELECT currentbasepay FROM (select id ,requestor ,currentbasepay ,effectivedate,idstatus from tblforms02 union select id ,requestor ,currentbasepay ,effectivedate,idstatus from tblforms03) a WHERE effectivedate > a1.work_date AND idstatus = 1 AND requestor = a1.id LIMIT 1),e.salary)) as salary
                            ";
    $Qry->fields        = "e.id IS NOT NULL AND a1.id = '". $idacct ."' AND a1.work_date BETWEEN  '".$startdate."' AND '".$endate."'
                            GROUP BY YEAR(a1.work_date), MONTH(a1.work_date)
                            ORDER BY a1.work_date";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row1=mysqli_fetch_array($rs)){
            $amount = ($row1['salary'] + $row1['lwp']) - ($row1['absent'] + $row1['late'] + $row1['ut']);
            $bonus =  $bonus + $amount;
        }
        return round($bonus / 12,2);
    }
}
?>