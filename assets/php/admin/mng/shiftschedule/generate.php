<?php
require_once('../../../logger.php');
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php');

    $param = json_decode(file_get_contents('php://input'));

    $shift_cols = array("monday"	=>"mon", 
    "tuesday"	=>"tue",
    "wednesday"	=>"wed",
    "thursday"	=>"thu",
    "friday"	=>"fri",
    "saturday"	=>"sat", 
    "sunday"	=>"sun");

    $Qry=new Query();
    $Qry->table="
    (SELECT idacct, tid AS empID, idshift, fshfname AS shift_status, work_date, holiday_id, holiday_type FROM vw_mng_timesheetfinal) AS a LEFT JOIN
    (SELECT idunit, business_unit, lname, fname, mname, id FROM vw_dataemployees) AS b ON (a.empID = b.id)";
    $Qry->fields="a.empID = '".$param->idacct."' AND (a.work_date BETWEEN '".$param->datefrom."' AND '".$param->dateto."') ORDER BY CONCAT(a.work_date,b.lname) ASC";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
    //echo $Qry->fields;
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
            $holiday = '';
            $drpending ='';
            $drsubmitted ='';
            $mngsvd = '';

            if( !empty($row['holiday_id']) ){
                $row['shift_status'] = ucwords(strtolower($row['holiday_type'])).' Holiday';
                $holiday = $row['shift_status'];
            }

            if( empty($row['shift_status']) ){
                $shift_field			= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];
                $shift_info 			= getDateShiftData( $con, $row['empID'], $shift_field, $row['work_date'] );
                $row['shift_status']	= $shift_info[0];
            }

            if($row['shift_status'] == 'Rest Day'){
                $backgroundColor = '#00b050';
            }else{
                $backgroundColor = '#f39c12';  
            }

           
            $drpending = checkprevdr($con, $param->idacct, $row['work_date'], $param->accountid);
            $drsubmitted =  '';
            $mngsave =  '';
        

            
            
            $data[] = array(
                'status'=>'success',
                'id'    =>  $param->idacct,
                'bg'    => $backgroundColor,
                'drpndng'      => $drpending,
                'drpndngcount'  => $drpending,
                'drsbmt'      => $drsubmitted,
                'mngsave'      => $mngsave,
                'shift_status'  => $row['shift_status'],
                'work_date' =>$row['work_date'],
                'holiday' =>$holiday,
            );
        }
        $return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'empty'));
    }
    
print $return;
mysqli_close($con);

function checkCS($con, $idacct, $date){
    $Qry=new Query();
    $Qry->table="
    (SELECT idshift, idacct, `date`, stat, cancelby FROM tbltimeshift) AS tbltimeshift LEFT JOIN 
    (SELECT id, `name` FROM tblshift) AS tblshift ON (tbltimeshift.idshift = tblshift.id)";
    $Qry->selected="tblshift.name";
    $Qry->fields="tbltimeshift.idacct='".$idacct."' AND tbltimeshift.date='".$date."' AND tbltimeshift.stat=3 AND tbltimeshift.cancelby IS NULL";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'checkCS');
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['name'];
    }
    return '';
}



function checkDRIfMNG($con, $idacct, $date){
    $Qry=new Query();
    $Qry->table="
    (SELECT idshift, idacct, `date`, secretary, type_creator, manager FROM tbldutyroster) AS tbldutyroster LEFT JOIN 
    (SELECT id FROM tblshift) AS tblshift ON (tbldutyroster.idshift = tblshift.id)";
    $Qry->selected="tblshift.id as sid";
    $Qry->fields="tbldutyroster.idacct='".$idacct."' AND tbldutyroster.date='".$date."' AND type_creator=1 AND secretary is null AND manager=0";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'checkDRIfMNG');
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['sid'];
    }
    return '';
}

function checkprevdr($con, $idacct, $date, $accountid){
    $newdate = new DateTime($date);
    $newdate->modify("-14 day");
    $newdate = $newdate->format("Y-m-d");

    $Qry=new Query();
    $Qry->table="
    (SELECT idacct, `date`, id FROM tbldutyroster) AS tbldutyroster LEFT JOIN
    (SELECT id FROM tblshift) AS tblshift ON (tbldutyroster.idshift = tblshift.id)";
    $Qry->selected="tblshift.id  as sid";
    $Qry->fields="tbldutyroster.idacct='".$idacct."' AND tbldutyroster.date='".$newdate."' ORDER by tbldutyroster.id DESC LIMIT 1";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'checkprevdr');
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['sid'];
    }
    return '';
}


?>