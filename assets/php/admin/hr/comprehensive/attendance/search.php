<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();
$date = date('Y-m-d');
$search ='';

if( !empty( $param->search_acct ) ){ $search=$search." AND de.id 	= '".$param->search_acct."' "; }
if( !empty( $param->search_post ) ){ $search=$search." AND de.post = '".$param->search_post."' "; }

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees AS de 
                    LEFT JOIN vw_data_timesheet AS dt ON de.id = dt.empID 
                    LEFT JOIN tblbunits AS bu ON de.idunit = bu.id";
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
                    dt.shift_status,
                    dt.in,
                    dt.out,
                    dt.in2,
                    dt.out2,
                    dt.shiftin,
                    dt.shiftout,
                    (SELECT 
                    GROUP_CONCAT(reso_txt) 
                    FROM
                    vw_resocenter 
                    WHERE idacct = de.id 
                    AND reso_date = dt.work_date) AS reso";
$Qry->fields    = "dt.work_date = '".$date."'".$search."order by dept, sec, de.empname";
// $Qry->fields    = "dt.work_date = '".$date."' and id = 62 order by dept, sec, de.empname";
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
            "dept"			  => $row['dept'],
            "sec"			  => $row['sec'],
            "pic"			  => $row['pic'],
            "empname" 		  => $row['empname'],
            "temp" 		      => $temp,
            "post" 		      => $row['post'],
            "shift_status" 	  => $row['shift_status'],
            "in" 		      => $row['in'],
            "out" 		      => $row['out'],
	        "in2" 		      => $row['in2'],
            "out2" 		      => $row['out2'],
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
            "reso"			  => $row['reso']
        );
        $return = json_encode($data);
    }
}else{
    $return = json_encode($data);
}
print $return;
mysqli_close($con);
?>