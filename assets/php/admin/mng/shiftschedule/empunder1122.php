<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php');

    $param = json_decode(file_get_contents('php://input'));
    
    $start = $param->datefrom;
    $end = $param->dateto;


    $arr_id = array();
    if($param->under != ''){
        $idunits = getTblUnits($con, $param->accountid);
        foreach( $idunits AS $value ){
            $dept = $value;
            $ids=0;if( !empty( $dept ) ){
                $arr 	= getHierarchy($con,$dept);
                array_push( $arr_id, $dept );
                if( !empty( $arr["nodechild"] ) ){
                    $a = getChildNode($arr_id, $arr["nodechild"]);
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
        }
    }else{

        $dept = getIdUnit($con,$param->accountid);
        $ids=0;if( !empty( $dept ) ){
            $arr 	= getHierarchy($con,$dept);
            array_push( $arr_id, $dept );
            if( !empty( $arr["nodechild"] ) ){
                $a = getChildNode($arr_id, $arr["nodechild"]);
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
    }

    // $bunit = getDepartment($con, $param->unit);
    $bunit = getDepartmentss($con, $param->unit);
    $idpayperiod = getidpayperiod($con, $param->datefrom, $param->dateto);

    $Qry3=new Query();
    $Qry3->table="tbldutyrosterstat";
    $Qry3->selected="*";
    $Qry3->fields="id_payperiod = '". $idpayperiod  ."' AND id_department = '". $bunit ."'";
    $rs3=$Qry3->exe_SELECT($con);

    if(mysqli_num_rows($rs3)>=1){
        if($row3=mysqli_fetch_array($rs3)){
            $drlock = $row3['status'];
        }
    }else{
            $drlock = 0;
    }


    $Qry=new Query();
    $Qry->table="tblaccount as a Left join tblaccountjob as b ON a.id = b.idacct";
    $Qry->selected="a.id,a.empid,a.lname,a.mname,a.fname,a.suffix ";
    $Qry->fields="a.idemptype = 1 AND (b.idunit IN (".$ids.") or b.idsuperior='".$param->accountid."') ORDER BY a.lname";
    $rs=$Qry->exe_SELECT($con);
    //echo $Qry->fields;
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
       
            if($row['suffix']!=null){
                $fullname = $row['lname']. ' ' .$row['suffix']. ', ' .$row['fname']. ' ' .substr($row['mname'], 0, 1). '.';
            }else{
                $fullname = $row['lname']. ', ' .$row['fname']. ' ' .substr($row['mname'], 0, 1). '.';
            }

            $sched = getSched($con, $row['id'], $start, $end, $param->under);

            $data[] = array(
                'id'        =>$row['id'],
                'empid'     =>$row['empid'],
                'lname'     =>$row['lname'],
                'suffix'    =>$row['suffix'],
                'fname'     =>$row['fname'],
                'mname'     =>$row['mname'],
                'drlock'    =>$drlock,
                'sched'     =>$sched,
                'fullname'  =>trim($fullname),
				'w'=>$Qry->fields
            );
        }
        $return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'empty'));
    }
    
print $return;
mysqli_close($con);

function getIdUnit($con, $idacct){
    $Qry=new Query();
    $Qry->table="tblaccountjob";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['idunit'];
        }
    }
    return null;
}

function getTblUnits($con, $idacct){
    $data = array();
    $Qry=new Query();
    $Qry->table="tblbunits";
    $Qry->selected="id";
    $Qry->fields="scheduler='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            array_push($data, $row['id']);
        }
    }
    return $data;
}

function getSched($con, $idacct, $start, $end, $usec){
    $Qry=new Query();
    $Qry->table="vw_datacurrentworkdates2 AS a
    RIGHT JOIN (SELECT '".$idacct."' AS id,`date` FROM tbldate) AS dd ON dd.date = a.work_date AND '".$idacct."' = a.id
    LEFT JOIN (SELECT * FROM tbltimesheet GROUP BY idacct,`date`) AS b ON b.date = a.work_date AND b.idacct = a.id
    LEFT JOIN tblaccount AS c ON c.id = a.id
    LEFT JOIN tblshift AS d ON d.id = b.idshift 
    LEFT JOIN tblaccountjob AS w ON w.idacct = a.id
    LEFT JOIN tblcalendar AS e ON e.id = w.wshift
    LEFT JOIN tblshift AS f ON f.id = (CASE 
                        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Sunday' THEN e.shiftsun
                        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Monday' THEN e.shiftmon
                        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Tuesday' THEN e.shifttue
                        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Wednesday' THEN e.shiftwed
                        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Thursday' THEN e.shiftthu
                        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Friday' THEN e.shiftfri
                        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Saturday' THEN e.shiftsat
                        ELSE b.idshift
                    END)
    LEFT JOIN tblholidays AS g ON g.date = dd.date 
    LEFT JOIN tblholidaytype AS h ON h.id = g.idtype
    LEFT JOIN (SELECT * FROM tbldutyroster WHERE (manager=0 OR manager IS NULL) AND secretary =1  GROUP BY DATE,idacct) AS i ON i.idacct = a.id AND i.date = dd.date
    LEFT JOIN tblshift AS i2 ON i2.id = i.idshift 
    LEFT JOIN (SELECT * FROM tbldutyroster WHERE type_creator=2 AND secretary=1 AND (manager=0 OR manager IS NULL) GROUP BY DATE,idacct) AS j ON j.idacct = a.id AND  j.date = dd.date
    LEFT JOIN tblshift AS j2 ON j2.id = j.idshift 
    LEFT JOIN (SELECT * FROM tbldutyroster WHERE type_creator=1 AND secretary=1 AND (manager=0 OR manager IS NULL) GROUP BY DATE,idacct) AS k ON k.idacct = a.id AND  k.date = dd.date
    LEFT JOIN tblshift AS k2 ON k2.id = k.idshift 
    LEFT JOIN (SELECT * FROM tbldutyroster WHERE type_creator=2 AND secretary=0 AND (manager=0 OR manager IS NULL) GROUP BY DATE,idacct) AS l ON l.idacct = a.id AND  l.date = dd.date
    LEFT JOIN tblshift AS l2 ON l2.id = l.idshift ";
    $Qry->selected="dd.date,
    c.lname,
    c.fname,
    c.mname,
    c.suffix,
    c.id,
    c.empid,
    h.alias,
    h.type AS holidaytype,
    (CASE 
        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Sunday' THEN e.shiftsun
        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Monday' THEN e.shiftmon
        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Tuesday' THEN e.shifttue
        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Wednesday' THEN e.shiftwed
        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Thursday' THEN e.shiftthu
        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Friday' THEN e.shiftfri
        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Saturday' THEN e.shiftsat
        ELSE b.idshift
    END) AS idshift,
    f.name,
   
    i.idshift AS mngsaved,
    i2.name AS mngsavedshiftstatus,
    j.idshift AS secsubmitted,
    j2.name AS secsubmittedshiftstatus,
    k.idshift AS secsave,
    k2.name AS secsaveshiftstatus,
    l.idshift AS secpending,
    l2.name AS secpendingshiftstatus,
    (CASE 
        WHEN DAYNAME(dd.date) = 'Sunday' THEN IF(e.shiftsun = 4 AND f.name != 'Rest Day', CONCAT ('RD ', f.name),f.name)
        WHEN DAYNAME(dd.date) = 'Monday' THEN IF(e.shiftmon = 4 AND f.name != 'Rest Day', CONCAT ('RD ', f.name),f.name)
        WHEN DAYNAME(dd.date) = 'Tuesday' THEN IF(e.shifttue = 4 AND f.name != 'Rest Day', CONCAT ('RD ', f.name),f.name)
        WHEN DAYNAME(dd.date) = 'Wednesday' THEN IF(e.shiftwed = 4 AND f.name != 'Rest Day', CONCAT ('RD ', f.name),f.name)
        WHEN DAYNAME(dd.date) = 'Thursday' THEN IF(e.shiftthu = 4 AND f.name != 'Rest Day', CONCAT ('RD ', f.name),f.name)
        WHEN DAYNAME(dd.date) = 'Friday' THEN IF(e.shiftfri = 4 AND f.name != 'Rest Day', CONCAT ('RD ', f.name),f.name)
        WHEN DAYNAME(dd.date) = 'Saturday' THEN IF(e.shiftsat = 4 AND f.name != 'Rest Day', CONCAT ('RD ', f.name),f.name)
        ELSE ''
    END) AS defaultsched,
    (CASE 
        WHEN DAYNAME(dd.date) = 'Sunday' THEN IF(f.name = 'Rest Day' OR h.type IS NOT NULL OR e.shiftsun = 4,'#00b050','#f39c12') 
        WHEN DAYNAME(dd.date) = 'Monday' THEN IF(f.name = 'Rest Day' OR h.type IS NOT NULL OR e.shiftmon = 4,'#00b050','#f39c12') 
        WHEN DAYNAME(dd.date) = 'Tuesday' THEN IF(f.name = 'Rest Day' OR h.type IS NOT NULL OR e.shifttue = 4,'#00b050','#f39c12') 
        WHEN DAYNAME(dd.date) = 'Wednesday' THEN IF(f.name = 'Rest Day' OR h.type IS NOT NULL OR e.shiftwed = 4,'#00b050','#f39c12') 
        WHEN DAYNAME(dd.date) = 'Thursday' THEN IF(f.name = 'Rest Day' OR h.type IS NOT NULL OR e.shiftthu = 4,'#00b050','#f39c12') 
        WHEN DAYNAME(dd.date) = 'Friday' THEN IF(f.name = 'Rest Day' OR h.type IS NOT NULL OR e.shiftfri = 4,'#00b050','#f39c12')  
        WHEN DAYNAME(dd.date) = 'Saturday' THEN IF(f.name = 'Rest Day' OR h.type IS NOT NULL OR e.shiftsat = 4,'#00b050','#f39c12') 
        ELSE ''
    END) AS bg";
    $Qry->fields="dd.id = '".$idacct."' AND (dd.`date` BETWEEN '".$start."' AND '".$end."') ORDER BY dd.date"; //CONCAT(a.work_date,c.lname) ASC";
    $rs=$Qry->exe_SELECT($con);
    //echo $Qry->fields;
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            $holiday = '';

            $row['name']   = $row['defaultsched'];
        
            
            if( !empty($row['holidaytype']) ){
                $row['name'] = ucwords(strtolower($row['alias'])).'H ' . $row['name'];
                $holiday = $row['name'];
            }

            if($usec!=''){
                $drpending = $row['secpending'];
                $drpendingstatus = $row['secpendingshiftstatus'];
                $drsubmitted = $row['secsubmitted'];
                $drsubmittedstatus = $row['secsubmittedshiftstatus'];
                $mngsave = $row['secsave'];
                $mngsavestatus = $row['secsaveshiftstatus'];
            }else{
                $drpending = $row['mngsaved'];
                $drpendingstatus = $row['mngsavedshiftstatus'];
                $drsubmitted =  '';
                $drsubmittedstatus =  '';
                $mngsave =  '';
                $mngsavestatus =  '';
            }

            $data[] = array(
                'bg'                    => $row['bg'],
                'shift_status'          => $row['name'],
                'work_date'             => $row['work_date'],
                'holiday'               => $holiday,
                'drpndng'               => $drpending,
                'drpendingstatus'       => $drpendingstatus,
                'drsbmt'                => $drsubmitted,
                'drsubmittedstatus'     => $drsubmittedstatus,
                'mngsave'               => $mngsave,
                'mngsavestatus'         => $mngsavestatus
            );
        }
        return $data;
    }else{
        $return = json_encode(array('status'=>'empty'));

        return $return;
    }
}

// function checkDRMngSaved($con, $idacct, $start, $end){
//     $Qry=new Query();
//     $Qry->table="vw_datacurrentworkdates2 AS a 
//                     LEFT JOIN (SELECT * FROM tbldutyroster WHERE (manager=0 OR manager IS NULL) AND secretary =1) AS b ON b.idacct = a.id AND b.date = a.work_date
//                     LEFT JOIN tblshift AS c ON c.id = b.idshift";
//     $Qry->selected="b.*";
//     $Qry->fields="a.id= '".$idacct."' AND a.work_date BETWEEN '".$start."' AND '".$end."' ORDER by a.work_date";
//     $rs=$Qry->exe_SELECT($con);
//     //echo $Qry->fields;
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
//             $data[] = array(
//                 'drpending' => $row['id'],
//             ); 
//         }
//         return $data;
//     }else{
//         $return = json_encode(array('status'=>'empty'));
//         return $return;
//     }
// }

function getDepartmentss( $con, $idunit ){
    $id = 0;
    $Qry 			= new Query();	
    $Qry->table     = "vw_databusinessunits";
    $Qry->selected  = "*";
    $Qry->fields    = "id = '".$idunit."'";
    $rs 			= $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            if($row['stype'] == 'Department'){
                $id = $row['id'];
            }else{
                $id = getDepartmentss( $con, $row['idunder']);
            }
        }
    }
    return $id;
}


function getidpayperiod( $con, $from, $to ){
    $Qry 			= new Query();	
    $Qry->table     = "vw_payperiod";
    $Qry->selected  = "id";
    $Qry->fields    = "period_start = '".$from."' AND period_end = '".$to."'";
    $rs 			= $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            $id = $row['id'];
        }
    }
    return $id;
}

?>