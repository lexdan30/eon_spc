<?php
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

$ids=0;
//Get Managers Under person
if( !empty( $dept ) ){
    //print_r($dept);
    $arr_id = array();
    $arr 	= getHierarchy($con,$dept);
    //print_r($arr["nodechild"]);
    array_push( $arr_id, 0 );
    if( !empty( $arr["nodechild"] ) ){     
        $a = getChildNode($arr_id, $arr["nodechild"]);  
        if( !empty($a) ){
            foreach( $a as $v ){
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





$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees AS de LEFT JOIN vw_data_timesheet AS dt ON de.id = dt.empID LEFT JOIN (SELECT * FROM `vw_datatimesched` WHERE id IN (SELECT MAX(id) FROM vw_datatimesched GROUP BY idacct)) AS dd ON de.id = dd.idacct" ;
$Qry->selected  = "dt.temp,de.id,de.sexstr,de.idunit,dt.idleave,dt.leavestat, dt.work_date, de.pic, de.empname, de.post, 
IF(dt.shift_status IS NULL,
CASE WEEKDAY('2021-05-18')
WHEN 6
THEN dd.`sun`

WHEN 0
THEN dd.`mon`

WHEN 1
THEN dd.`tue`

WHEN 2
THEN dd.`wed`

WHEN 3
THEN dd.`thu`

WHEN 4
THEN dd.`fri`

WHEN 5
THEN dd.`sat`
END,dt.shift_status) AS shift_status, dt.in, dt.out,dt.in2, dt.out2,dt.shiftin,dt.shiftout,(SELECT GROUP_CONCAT(reso_txt) FROM vw_resocenter WHERE idacct = de.id AND reso_date = dt.work_date) AS reso";
$Qry->fields    = "de.etypeid=1 AND de.id != '".$param->accountid."' AND /*dt.shift_status IS NOT NULL AND*/ dt.work_date = '".$date."' AND (de.idunit IN (".$ids.") or de.idsuperior='".$param->accountid."') ORDER BY de.empname ";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
    
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


        $data[] = array( 
            "pic"			  => $row['pic'],
            "empname" 		  => $row['empname'],
            "temp" 		      => $temp,
            "post" 		      => $row['post'],
            "shift_status" 	  => $row['shift_status'],
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
			"reso"			  => $row['reso'],
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
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['idunit'];
        }
    }
    return null;
}




?>