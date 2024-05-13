<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date = getPayPeriodts($con);

$idpayperiod = $date['id'];

// print_r( $_SESSION['selectedcomp']);

if(!empty($param->dfrom)){
    $idpayperiod = getIdPeriod($con,$param->dfrom,$param->dto); 
    if(empty($idpayperiod)){
        $idpayperiod = $date['id'];
    }
    $start = $param->dfrom;
    $end = $param->dto;
    $nend = new DateTime($end);
}else{
    $start = $date['pay_start'];
    $end = $date['pay_end'];
    $nend = new DateTime($date['pay_end']);
}

$nend->modify('+1 day'); 
$nend = $nend->format('Y-m-d'); 
$search='';
$where = $search;
$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod(new DateTime($start), $interval,  new DateTime($nend));

foreach ($period as $dt) {
    $workdate[] = $dt->format("Y-m-d");
}

$Qry=new Query(); 
$Qry->table="vw_databusinessunits";
$Qry->selected="*";
$Qry->fields="isactive = 1 AND unittype = 3 ". $search . " LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
$rs=$Qry->exe_SELECT($con);
//echo $Qry->fields;
if(mysqli_num_rows($rs)>=1){
    while($row=mysqli_fetch_array($rs)){
        $arr_id = array();
        $dept = $row['id'];
        if( !empty( $dept ) ){
            $arr 	= getHierarchy($con,$dept);
            array_push( $arr_id, $dept );
            
            if( !empty( $arr["nodechild"] ) ){
                $a = getChildNodes($arr_id, $arr["nodechild"]);
                if( !empty($a) ){ 
                    foreach( $a AS $v ){
                        array_push( $arr_id, $v );
                    }
                }
            }
            if( count($arr_id) == 1 ){
                $ids 			= $arr_id[0];
            }else{
                $ids 			= implode(",",$arr_id);
            }
        }

        $emps = [];
        $sched =array();
        $Qry2=new Query();
        $Qry2->table="tblaccount as a LEFT join tblaccountjob as b ON a.id = b.idacct";
        $Qry2->selected="a.lname,a.fname,a.mname,a.suffix,a.id,a.empid";
        $Qry2->fields="(idunit IN (".$ids.")) AND (empstat NOT IN (6,7) ) ORDER BY a.lname";
        $rs2=$Qry2->exe_SELECT($con);
        //echo $Qry->fields;
        if(mysqli_num_rows($rs2)>=1){
            while($row2=mysqli_fetch_array($rs2)){
                if($row2['suffix']!=null){
                    $fullname = $row2['lname']. ' ' .$row2['suffix']. ', ' .$row2['fname']. ' ' .substr($row2['mname'], 0, 1). '.';
                }else{
                    $fullname = $row2['lname']. ', ' .$row2['fname']. ' ' .substr($row2['mname'], 0, 1). '.';
                }

                array_push($sched,getSched( $con, $row2['id'], $start, $end ));

                $emps[] = array(
                    'status'=>'success',
                    'id'        =>$row2['id'],
                    'empid'     =>$row2['empid'],
                    'fullname'  =>trim( $fullname),
                );
            }
        }


        $Qry3=new Query();
        $Qry3->table="tbldutyrosterstat";
        $Qry3->selected="*";
        $Qry3->fields="id_payperiod = '". $idpayperiod ."' AND id_department = '". 	$row['id'] ."'";
        $rs3=$Qry3->exe_SELECT($con);
      
        if(mysqli_num_rows($rs3)>=1){
            while($row3=mysqli_fetch_array($rs3)){
                $drlock = $row3['status'];
            }
        }else{
              $drlock = 0;
        }

        


        $data[] = array(
            'status'		=>	'success',
            'id'			=>	$row['id'],
            'idpayperiod'	=>	$idpayperiod,
            'departmentname'=>	$row['name'],
            'employees'     =>	$emps,
            'workdate'      =>	$workdate,
            'sched'         =>	$sched,
            'drlock'        =>	$drlock,
        );
        
    }

    $myData = array('status' => 'success', 'result' => $data, 'totalItems' => getTotal($con , $where));
    $return = json_encode($myData);
}else{
    $return = json_encode(array('status'=>'empty'));
}



$myData = array('status' => 'success', 'result' => $data, 'totalItems' => getTotal($con , $where));
$return =  json_encode($myData);
print $return;
mysqli_close($con);



function getSched($con, $idacct, $start, $end){
    $shift_cols = array("monday"	=>"mon", 
    "tuesday"	=>"tue",
    "wednesday"	=>"wed",
    "thursday"	=>"thu",
    "friday"	=>"fri",
    "saturday"	=>"sat", 
    "sunday"	=>"sun");


    $Qry=new Query();
    $Qry->table="vw_datacurrentworkdates2 as a
                    LEFT JOIN tbltimesheet as b on b.date = a.work_date AND b.idacct = a.id
                    LEFT JOIN tblaccount as c ON c.id = a.id
                    LEFT JOIN tblshift AS d ON d.id = b.idshift 
                    LEFT JOIN tblaccountjob AS w ON w.idacct = a.id
                    LEFT JOIN tblcalendar AS e ON e.id = w.wshift
                    LEFT JOIN tblshift AS f ON f.id = (CASE 
                                        WHEN b.idshift is null and dayname(a.work_date) = 'Sunday' THEN e.shiftsun
                                        WHEN b.idshift is null and dayname(a.work_date) = 'Monday' THEN e.shiftmon
                                        WHEN b.idshift is null and dayname(a.work_date) = 'Tuesday' THEN e.shifttue
                                        WHEN b.idshift is null and dayname(a.work_date) = 'Wednesday' THEN e.shiftwed
                                        WHEN b.idshift is null and dayname(a.work_date) = 'Thursday' THEN e.shiftthu
                                        WHEN b.idshift is null and dayname(a.work_date) = 'Friday' THEN e.shiftfri
                                        WHEN b.idshift is null and dayname(a.work_date) = 'Saturday' THEN e.shiftsat
                                        ELSE b.idshift
                                    END)
                    LEFT JOIN tblholidays AS g ON g.date = a.work_date 
                    LEFT JOIN tblholidaytype AS h ON h.id = g.idtype ";
    $Qry->selected="a.work_date,
                        c.lname,
                        c.fname,
                        c.mname,
                        c.suffix,
                        c.id,
                        c.empid,
                        h.type AS holidaytype,
                        h.alias,
                        (CASE 
                            WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Sunday' THEN e.shiftsun
                            WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Monday' THEN e.shiftmon
                            WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Tuesday' THEN e.shifttue
                            WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Wednesday' THEN e.shiftwed
                            WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Thursday' THEN e.shiftthu
                            WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Friday' THEN e.shiftfri
                            WHEN b.idshift IS NULL AND DAYNAME(a.work_date) = 'Saturday' THEN e.shiftsat
                            ELSE b.idshift
                        END) AS idshift,
                        f.name,
                        IF(f.name = 'Rest Day'  OR h.type IS not null
                            ,'#00b050'
                            ,'#f39c12'
                        ) AS bg, 
                        g.provcode AS holidayProvid,
                        g.munid AS holidayMunid,
                        w.munid AS employeeMunid,
                        w.provcode As employeeProvi";
    $Qry->fields="a.id = '".$idacct."' AND (a.`work_date` BETWEEN '".$start."' AND '".$end."') ORDER BY CONCAT(a.work_date,c.lname) ASC";
    $rs=$Qry->exe_SELECT($con);
    //echo $Qry->fields;
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            $holiday = '';
            
            // if( !empty($row['holidaytype']) ){
            //     $row['name'] = ucwords(strtolower($row['holidaytype'])).' Holiday';
            //     $holiday = $row['name'];
            // }
            // if( !empty($row['holidaytype']) ){
            //     $row['name'] = ucwords(strtolower($row['alias'])).'H ' . $row['name'];
            //     $holiday = $row['name'];
            // }

            if( !empty($row['holidaytype']) ){
                if(!empty($row['holidayProvid'])){
                    if(empty($row['holidayMunid']) && $row['employeeProvid'] == $row['holidayProvid'])
                    {
                        $row['name'] = ucwords(strtolower($row['alias'])).'H ' . $row['name'];
                        $holiday = $row['name'];
                    }elseif($row['holidayMunid'] == $row['employeeMunid']){
                        $row['name'] = ucwords(strtolower($row['alias'])).'H ' . $row['name'];
                        $holiday = $row['name'];
                    }
				}else{
                    $row['name'] = ucwords(strtolower($row['alias'])).'H ' . $row['name'];
                    $holiday = $row['name'];
                }
            }

            if( empty($row['name']) ){
                $shift_field			= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];
                $shift_info 			= getDateShiftData( $con, $row['id'], $shift_field, $row['work_date'] );
                //$row['name']	        = $shift_info[0];

            }
            
            $data[] = array(
                'status'=>'success',
                'bg'    => $row['bg'],
                'shift_status'  => $row['name'],
                'work_date' =>$row['work_date'],
                'holiday' =>$holiday,
            );
        }
        return $data;
    }else{
        $return = json_encode(array('status'=>'empty'));

        return $return;
    }
}

function getTotal($con,$search){
    $Qry = new Query();	
    $Qry->table     = "vw_databusinessunits";
    $Qry->selected  = "count(*) as total";
    $Qry->fields = "isactive = 1 AND unittype = 3 ".$search;
    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['total'];
			}
		}
		return 0;
}

?>