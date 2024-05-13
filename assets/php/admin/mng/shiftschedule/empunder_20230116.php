<?php
require_once('../../../logger.php');
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
        print_r($idunits);
        foreach( $idunits AS $value ){
            $dept = $value;
            $ids='0';
            //Get Managers Under person
            if( !empty( $dept ) ){
                $arr_id = array();
                $arr 	= getHierarchy($con,$dept);
                if( !empty( $arr["nodechild"] ) ){     
                    $ids = join(',', flatten($arr['nodechild']));
                    print_r($ids);
                } else {
                    $ids = '0';
                }
            }
        }
        //print_r($ids);
    }else{

        $dept = getIdUnit($con,$param->accountid);
        $ids='0';
        //Get Managers Under person
        if( !empty( $dept ) ){
            $arr_id = array();
            $arr 	= getHierarchy($con,$dept);
            if( !empty( $arr["nodechild"] ) ){     
                $ids = join(',', flatten($arr['nodechild']));
            } else {
                $ids = '0';
            }
        }
    }

    // $bunit = getDepartment($con, $param->unit);
    $bunit = getDepartmentss($con, $param->unit);
    $idpayperiod = getidpayperiod($con, $param->datefrom, $param->dateto);
    
    $Qry3=new Query();
    $Qry3->table="tbldutyrosterstat";
    $Qry3->selected="status";
    $Qry3->fields="id_payperiod = '". $idpayperiod  ."' AND id_department = '". $bunit ."'";
    $rs3=$Qry3->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs3));
    if(mysqli_num_rows($rs3)>=1){
        if($row3=mysqli_fetch_assoc($rs3)){
            $drlock = $row3['status'];
        }
    }else{
            $drlock = 0;
    }

    
    if($param->under != ''){
        $idhead = getidHead( $con, $param->accountid);
        $Qry=new Query();
        $Qry->table="
        (SELECT id, empid, lname, mname, fname, suffix, idemptype FROM tblaccount) AS a 
        LEFT JOIN (SELECT idacct, idunit, idsuperior FROM tblaccountjob) AS b ON (a.id = b.idacct)
        LEFT JOIN (SELECT id, `name`, idhead FROM tblbunits) AS u  ON (b.idunit = u.id)
        LEFT JOIN (SELECT id, joblvl, concat_sup_fname_lname FROM vw_dataemployees) AS e  ON (a.id = e.id)";
        $Qry->selected="a.id,a.empid,a.lname,a.mname,a.fname,a.suffix, u.name,e.concat_sup_fname_lname,e.joblvl,u.idhead";
        $Qry->fields="a.idemptype = 1 AND ((b.idunit IN (".$ids.") or b.idsuperior='".$idhead."')) ORDER BY u.name,e.joblvl,a.lname";
        $rs=$Qry->exe_SELECT($con);
        //echo $Qry->fields;
        Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
        if(mysqli_num_rows($rs)>=1){
            $prevname = '';   
            $prevmgr = '';  
            $classmgr= ''; 
            while($row=mysqli_fetch_assoc($rs)){
                if($row['id'] != $row['idhead']){
                    if($row['suffix']!=null){
                        $fullname = $row['lname']. ' ' .$row['suffix']. ', ' .$row['fname']. ' ' .substr($row['mname'], 0, 1). '.';
                    }else{
                        $fullname = $row['lname']. ', ' .$row['fname']. ' ' .substr($row['mname'], 0, 1). '.';
                    }
        
                    $sched = getSched($con, $row['id'], $start, $end, $param->under);
                    if($prevname != $row['name']){
                        $uname = $row['name'];
                    }else{
                        $uname = '';
                    }
                    if($prevmgr != $row['concat_sup_fname_lname']){
                       $superior = $row['concat_sup_fname_lname'];
                    }else{
                        if($prevname != $row['name']){
                            $superior = $row['concat_sup_fname_lname'];
                        }else{
                            $superior = '';
                        }
                    }
                    
                    $prevname = $row['name'];
                    $prevmgr = $row['concat_sup_fname_lname'];

                    $data[] = array(
                        'id'        =>$row['id'],
                        'empid'     =>$row['empid'],
                        'lname'     =>$row['lname'],
                        'suffix'    =>$row['suffix'],
                        'fname'     =>$row['fname'],
                        'mname'     =>$row['mname'],
                        'uname'     =>$uname,
                        'superior'     =>$superior,
                        'drlock'    =>$drlock,
                        'sched'     =>$sched,
                        'fullname'  =>trim($fullname),
                        'w'=>$Qry->fields
                    );
                }
                
            }
            $return = json_encode($data);
        }else{
            $return = json_encode(array('status'=>'empty'));
        }
    }else{
        $Qry=new Query();
        $Qry->table="(SELECT suffix, id, empid, lname, fname, mname, idemptype FROM tblaccount) as a Left join (SELECT idacct, idsuperior FROM tblaccountjob) as b ON (a.id = b.idacct)";
        $Qry->selected="a.id,a.empid,a.lname,a.mname,a.fname,a.suffix ";
        $Qry->fields="a.idemptype = 1 AND ((b.idsuperior='".$param->accountid."')) ORDER BY a.lname";
        $rs=$Qry->exe_SELECT($con);
        //echo $Qry->fields;
        Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
        if(mysqli_num_rows($rs)>=1){
            while($row=mysqli_fetch_assoc($rs)){
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
                    'uname'     =>'',
                    'superior'     =>'',
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
    }
    
    
print $return;
mysqli_close($con);

function getIdUnit($con, $idacct){
    $Qry=new Query();
    $Qry->table="tblaccountjob";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getIdUnit');
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['idunit'];
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
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTblUnits');
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
            array_push($data, $row['id']);
        }
    }
    return $data;
}

function getSched($con, $idacct, $start, $end, $usec){
    $Qry=new Query();
    $Qry->table="
    (SELECT id, work_date FROM vw_datacurrentworkdates2) AS a
    RIGHT JOIN (SELECT '".$idacct."' AS id,`date` FROM tbldate) AS dd ON dd.date = a.work_date AND '".$idacct."' = a.id
    LEFT JOIN (SELECT `date`, idshift, idacct FROM tbltimesheet GROUP BY idacct,`date`) AS b ON b.date = a.work_date AND b.idacct = a.id
    LEFT JOIN (SELECT munid, provcode, idacct, wshift FROM tblaccountjob) AS w ON w.idacct = a.id
    LEFT JOIN (SELECT id, shiftsun, shiftmon, shifttue, shiftwed, shiftthu, shiftfri, shiftsat FROM tblcalendar) AS e ON e.id = w.wshift
    LEFT JOIN (SELECT id, `name` FROM tblshift) AS f ON f.id = (CASE 
                        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Sunday' THEN e.shiftsun
                        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Monday' THEN e.shiftmon
                        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Tuesday' THEN e.shifttue
                        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Wednesday' THEN e.shiftwed
                        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Thursday' THEN e.shiftthu
                        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Friday' THEN e.shiftfri
                        WHEN b.idshift IS NULL AND DAYNAME(dd.date) = 'Saturday' THEN e.shiftsat
                        ELSE b.idshift
                    END)
    LEFT JOIN (SELECT `date`, idtype, provcode, munid FROM tblholidays) AS g ON g.date = dd.date 
    LEFT JOIN (SELECT alias, id, `type` FROM tblholidaytype) AS h ON h.id = g.idtype
    LEFT JOIN (SELECT idshift, idacct, `date` FROM tbldutyroster WHERE (manager=0 OR manager IS NULL) AND secretary =1  GROUP BY DATE,idacct) AS i ON i.idacct = a.id AND i.date = dd.date
    LEFT JOIN (SELECT `name`, id FROM tblshift) AS i2 ON i2.id = i.idshift 
    LEFT JOIN (SELECT `date`, idacct, idshift FROM tbldutyroster WHERE type_creator=2 AND secretary=1 AND (manager=0 OR manager IS NULL) GROUP BY DATE,idacct) AS j ON j.idacct = a.id AND  j.date = dd.date
    LEFT JOIN (SELECT id, `name` FROM tblshift) AS j2 ON j2.id = j.idshift 
    LEFT JOIN (SELECT idshift, `date`, idacct FROM tbldutyroster WHERE type_creator=1 AND secretary=1 AND (manager=0 OR manager IS NULL) GROUP BY DATE,idacct) AS k ON k.idacct = a.id AND  k.date = dd.date
    LEFT JOIN (SELECT id, `name` FROM tblshift) AS k2 ON k2.id = k.idshift 
    LEFT JOIN (SELECT idacct, `date`, idshift FROM tbldutyroster WHERE type_creator=2 AND secretary=0 AND (manager=0 OR manager IS NULL) GROUP BY DATE,idacct) AS l ON l.idacct = a.id AND  l.date = dd.date
    LEFT JOIN (SELECT id, `name` FROM tblshift) AS l2 ON l2.id = l.idshift";
    $Qry->selected="
    dd.date,
    j2.name AS secsubmittedshiftstatus,
    h.alias,
    i.idshift AS mngsaved,
    i2.name AS mngsavedshiftstatus,
    j.idshift AS secsubmitted,
    k.idshift AS secsave,
    k2.name AS secsaveshiftstatus,
    f.name,
    l.idshift AS secpending,
    l2.name AS secpendingshiftstatus,
    (CASE 
        WHEN DAYNAME(dd.date) = 'Sunday' THEN IF(e.shiftsun = 4 AND f.name != 'Rest Day', CONCAT ('', f.name),f.name)
        WHEN DAYNAME(dd.date) = 'Monday' THEN IF(e.shiftmon = 4 AND f.name != 'Rest Day', CONCAT ('', f.name),f.name)
        WHEN DAYNAME(dd.date) = 'Tuesday' THEN IF(e.shifttue = 4 AND f.name != 'Rest Day', CONCAT ('', f.name),f.name)
        WHEN DAYNAME(dd.date) = 'Wednesday' THEN IF(e.shiftwed = 4 AND f.name != 'Rest Day', CONCAT ('', f.name),f.name)
        WHEN DAYNAME(dd.date) = 'Thursday' THEN IF(e.shiftthu = 4 AND f.name != 'Rest Day', CONCAT ('', f.name),f.name)
        WHEN DAYNAME(dd.date) = 'Friday' THEN IF(e.shiftfri = 4 AND f.name != 'Rest Day', CONCAT ('', f.name),f.name)
        WHEN DAYNAME(dd.date) = 'Saturday' THEN IF(e.shiftsat = 4 AND f.name != 'Rest Day', CONCAT ('', f.name),f.name)
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
    END) AS bg, 
    g.provcode AS holidayProvid,
    g.munid AS holidayMunid,
    w.munid AS employeeMunid,
    w.provcode As employeeProvid";
    $Qry->fields="dd.id = '".$idacct."' AND (dd.`date` BETWEEN '".$start."' AND '".$end."') ORDER BY dd.date"; //CONCAT(a.work_date,c.lname) ASC";
    $rs=$Qry->exe_SELECT($con);
    //echo $Qry->fields;
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs)."stat: ".$con->stat, 'getSched');
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
            $holiday = '';

            $row['name']   = $row['defaultsched'];
            if($row['name'] != 'Rest Day'){
                $row['bg'] = '#f39c12'; 
            }
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
                'work_date'             => $row['date'],
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
    $Qry->selected  = "stype, id, idunder";
    $Qry->fields    = "id = '".$idunit."'";
    $rs 			= $Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getDepartmentss');
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
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
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getidpayperiod');
    if(mysqli_num_rows($rs)>= 1){
        return mysqli_fetch_assoc($rs)['id'];
    }
}

function getidHead( $con, $scheduler){
    $Qry 			= new Query();	
    $Qry->table     = "tblbunits";
    $Qry->selected  = "idhead";
    $Qry->fields    = "scheduler = '".$scheduler."'";
    $rs 			= $Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getidHead');
    if(mysqli_num_rows($rs)>= 1){
        return mysqli_fetch_assoc($rs)['idhead'];
    }
}

?>