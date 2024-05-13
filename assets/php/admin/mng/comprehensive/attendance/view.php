<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();
$time  = SysTime();
$date = SysDatePadLeft();

// // $pay_period = getPayPeriod($con);
// echo($date);

$dept = getIdUnit($con,$param->accountid);

// print_r($dept);
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

$Qry 			= new Query();	
$Qry->table     = "(SELECT id, sexstr, idunit, pic, empname, post, etypeid, idsuperior FROM vw_dataemployees) AS de 
LEFT JOIN (SELECT
tid AS empID, work_date, idshift, temp, leaveidtype AS idleave,
leaveappstatus AS leavestat, fshfname AS shift_status, stime AS shiftin, timein AS `in`, timeout AS `out`,
timein2 AS in2, timeout AS out2, ftime AS shiftout FROM vw_mng_timesheetfinal) AS dt ON (de.id = dt.empID) 
LEFT JOIN (SELECT id,`name`, unittype, idunder FROM tblbunits) AS bu ON (de.idunit = bu.id)
LEFT JOIN (SELECT id, idacct, idsun, idmon, idtue, idwed, idthu, idfri, idsat, sun, mon, tue, wed, thu, fri, sat FROM `vw_datatimesched` 
WHERE id IN (SELECT MAX(id) FROM vw_datatimesched GROUP BY idacct)) AS dd ON de.id = dd.idacct
LEFT JOIN (SELECT idacct, wshift FROM vw_dataassign) AS da ON de.id = da.idacct
LEFT JOIN (SELECT shiftsun, shiftmon, shifttue, shiftwed, shiftthu, shiftfri, shiftsat, id FROM tblcalendar) AS i ON da.wshift = i.id 
LEFT JOIN (SELECT id, `name`, stime, ftime FROM tblshift) AS sh
ON sh.id = (IF(dd.id IS NULL,
CASE WEEKDAY(dt.work_date) 
WHEN 6 THEN i.`shiftsun`
WHEN 0 THEN i.`shiftmon`
WHEN 1 THEN i.`shifttue`
WHEN 2 THEN i.`shiftwed`
WHEN 3 THEN i.`shiftthu`
WHEN 4 THEN i.`shiftfri`
WHEN 5 THEN i.`shiftsat`
END,
IF(dt.idshift IS NULL,
CASE WEEKDAY(dt.work_date) 
WHEN 6 THEN dd.`idsun`
WHEN 0 THEN dd.`idmon`
WHEN 1 THEN dd.`idtue`
WHEN 2 THEN dd.`idwed`
WHEN 3 THEN dd.`idthu`
WHEN 4 THEN dd.`idfri`
WHEN 5 THEN dd.`idsat`
END,dt.idshift)))";
$Qry->selected  = "dt.temp,
de.id,
de.sexstr,
de.idunit,
IF(bu.unittype = 3, bu.name, (SELECT `name` FROM tblbunits WHERE id = bu.idunder)) AS dept,
IF(bu.unittype = 4, bu.name, IF(bu.unittype = 3, bu.name, (SELECT `name` FROM tblbunits WHERE id = bu.idunder))) AS sec,
dt.idleave,
dt.leavestat,
dt.work_date,
de.pic,
de.empname,
de.post,
IF(dd.id IS NULL,
sh.name
,
IF(dt.shift_status IS NULL,
CASE WEEKDAY(dt.work_date)
WHEN 6 THEN dd.`sun` 
WHEN 0 THEN dd.`mon` 
WHEN 1 THEN dd.`tue` 
WHEN 2 THEN dd.`wed` 
WHEN 3 THEN dd.`thu` 
WHEN 4 THEN dd.`fri` 
WHEN 5 THEN dd.`sat`
END
,dt.shift_status)) AS shift_status,
IF(dd.id IS NULL,
CASE WEEKDAY(dt.work_date) 
WHEN 6 THEN i.`shiftsun`
WHEN 0 THEN i.`shiftmon`
WHEN 1 THEN i.`shifttue`
WHEN 2 THEN i.`shiftwed`
WHEN 3 THEN i.`shiftthu`
WHEN 4 THEN i.`shiftfri`
WHEN 5 THEN i.`shiftsat`
END,
IF(dt.idshift IS NULL,
CASE WEEKDAY(dt.work_date) 
WHEN 6 THEN dd.`idsun`
WHEN 0 THEN dd.`idmon`
WHEN 1 THEN dd.`idtue`
WHEN 2 THEN dd.`idwed`
WHEN 3 THEN dd.`idthu`
WHEN 4 THEN dd.`idfri` 
WHEN 5 THEN dd.`idsat`
END,dt.idshift)) AS idshift,
da.wshift,
(SELECT TIME(DATE_ADD(CONCAT(dt.work_date,' ',IF(dt.shiftin IS NULL, sh.stime ,dt.shiftin)),INTERVAL +1 HOUR))) AS flexmax_in,
IF(dt.in < IF(dt.shiftin IS NULL, sh.stime ,dt.shiftin),
TIME(DATE_ADD(CONCAT(dt.work_date,' ',IF(dt.shiftin IS NULL, sh.stime ,dt.shiftin)),INTERVAL +9 HOUR)),
TIME(DATE_ADD(CONCAT(dt.work_date,' ',dt.in),INTERVAL +9 HOUR))
) AS flexmin_out,
dt.in,
dt.out,
dt.in2,
dt.out2,
IF(dt.shiftin IS NULL, sh.stime ,dt.shiftin) AS `shiftin`,
IF(dt.shiftout IS NULL, sh.ftime ,dt.shiftout) AS `shiftout`,
IF((SELECT 
GROUP_CONCAT(reso_txt) 
FROM
vw_resocenter 
WHERE idacct = de.id 
AND reso_date = dt.work_date) IS NULL,
CASE
WHEN dt.in IS NOT NULL AND dt.out IS NULL AND
(CAST(dt.in AS TIME)<=CAST((SELECT TIME(DATE_ADD(CONCAT(dt.work_date,' ',(SELECT TIME(DATE_ADD(CONCAT(dt.work_date,' ',IF(dt.shiftin IS NULL, sh.stime ,dt.shiftin)),INTERVAL +2 HOUR)))), INTERVAL +29 MINUTE))) AS TIME)) THEN 'Incomplete Logs'
WHEN (CAST(dt.in AS TIME)>CAST((SELECT TIME(DATE_ADD(CONCAT(dt.work_date,' ',(SELECT TIME(DATE_ADD(CONCAT(dt.work_date,' ',IF(dt.shiftin IS NULL, sh.stime ,dt.shiftin)),INTERVAL +2 HOUR)))), INTERVAL +29 MINUTE))) AS TIME)) THEN 'Halfday'
WHEN (dt.in IS NULL AND dt.out IS NULL AND 
(IF(dd.id IS NULL,
sh.name
,
IF(dt.shift_status IS NULL,
CASE WEEKDAY(dt.work_date)
WHEN 6 THEN dd.`sun` 
WHEN 0 THEN dd.`mon` 
WHEN 1 THEN dd.`tue` 
WHEN 2 THEN dd.`wed` 
WHEN 3 THEN dd.`thu` 
WHEN 4 THEN dd.`fri` 
WHEN 5 THEN dd.`sat`
END,dt.shift_status))) <> 'Rest Day') THEN 'No Logs'
END
,
(SELECT 
GROUP_CONCAT(reso_txt) 
FROM
vw_resocenter 
WHERE idacct = de.id 
AND reso_date = dt.work_date)
) AS reso";
$Qry->fields    = "de.etypeid=1 AND de.id != '".$param->accountid."' AND /*dt.shift_status IS NOT NULL AND*/ dt.work_date = '".$date."' AND (de.idunit IN (".$ids.") or de.idsuperior='".$param->accountid."') ORDER BY de.empname ";
$rs 			= $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
    
        $absent='';
        $sl='';
        $vl='';
        $lwop='';
        $spl='';
        $pl='';
        $spel='';
        $bl='';
        $el='';
        $mcl='';
        $berl='';
        $ml='';
        $late='';

        if( (strtotime($row['in']) == '' || strtotime($row['in']) == null && strtotime($row['out']) == '' || strtotime($row['out']) == null) && empty($row['idleave']) ) {
            $absent = 'Absent';
            if(!empty($param->dateatt)){
                $absent = 'Absent';
            }else{
                $absent = '';
            }
        }
        if(strtotime($row['in']) > strtotime($row['shiftin'])) {
            $late = $row['in'];
        }
        if($row['idleave'] == '1' && $row['leavestat'] == '1') {
            $sl = 'Sick Leave';
        }
        if($row['idleave'] == '2' && $row['leavestat'] == '1') {
            $vl = 'Vacation Leave';
        }
        if($row['idleave'] == '3' && $row['leavestat'] == '1') {
            $lwop = 'Leave Without Pay';
        }
        if($row['idleave'] == '4' && $row['leavestat'] == '1') {
            $spl = 'Solo Parent Leave';
        }
        if($row['idleave'] == '6' && $row['sexstr'] == 'MALE' && $row['leavestat'] == '1') {
            $pl = 'Paternity Leave';
        }
        if($row['idleave'] == '7' && $row['leavestat'] == '1') {
            $spel = 'Special Leave';
        }
        if($row['idleave'] == '8' && $row['leavestat'] == '1') {
            $bl = 'Birthday Leave';
        }
        if($row['idleave'] == '9' && $row['leavestat'] == '1') {
            $el = 'Emergency Leave';
        }
        if($row['idleave'] == '10' && $row['sexstr'] == 'FEMALE' && $row['leavestat'] == '1') {
            $mcl = 'Magna Carta Leave';
        }
        if($row['idleave'] == '11' && $row['leavestat'] == '1') {
            $berl = 'Bereavement Leave';
        }
        if($row['idleave'] == '12' && $row['sexstr'] == 'FEMALE' && $row['leavestat'] == '1') {
            $mcl = 'Maternity Leave';
        }

		if( empty($row['pic']) ){
			$row['pic'] = "undefined.webp";
        }
        
        if($row['temp'] > 37.4){
            $temp = '<p style="color:red">'.$row['temp']. ' &deg;C</p>';
        }else if(empty($row['temp'])){
            $temp='';
        }else{
            $temp = '<p>'.$row['temp']. ' &deg;C</p>';
        }
        if($row['idshift'] == '89' || $row['idshift'] == '93'){
            $flexi = true;
        }else{
            $flexi = false;
        }

        if(!empty($row['reso'])){
            $reso = $row['reso'];
        }else{
            $reso = '';
        }
        if($row['shift_status'] == 'Rest Day' && !empty($row['in'])){
            $reso = 'Duty on Rest Day';
        }


        $data[] = array( 
            "pic"			  => $row['pic'],
            "empname" 		  => $row['empname'],
            "temp" 		      => $temp,
            "post" 		      => $row['post'],
            "shift_status" 	  => $row['shift_status'],
            "flexi" 	      => $flexi,
            "flexmax_in"      => substr($row['flexmax_in'],0,5),
            "flexmin_out"      => substr($row['flexmin_out'],0,5),
            "in" 		      => $row['in'],
            "out" 		      => $row['out'],
            "in2" 		      => $row['in2'],
            "out2" 		      => $row['out2'],
            "shiftin"         => $row['shiftin'],
            "shiftout"        => $row['shiftout'],
            "date"            => $date,
            "absent"          => $absent,
            "sickleave"       => $sl,
            "vacationleave"   => $vl,
            "lwop"            => $lwop,
            "spl"             => $spl,
            "pl"              => $pl,
            "spel"            => $spel,
            "bl"              => $bl,
            "el"              => $el,
            "mcl"             => $mcl,
            "berl"            => $berl,
            "ml"              => $ml,
            "late"            => $late,
			"reso"			  =>  $reso,
        );
        $return = json_encode($data);
    }
}else{
    // $return = json_encode(array('status'=>'error'));
    $return = json_encode($data);
}


print $return;
mysqli_close($con);


function getIdUnit($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getIdUnit');
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['idunit'];
    }
    return null;
}




?>