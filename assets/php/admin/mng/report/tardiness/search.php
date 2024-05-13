<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

// $param = $_POST;
$param       = json_decode(file_get_contents('php://input'));
$data  = array();
$date = SysDatePadLeft();
$pay_period = getPayPeriod($con);


$search ='';

if( !empty( $param->search_acct ) ){ $search=$search." AND id = '".$param->search_acct."' "; }

if( !empty($param->_from) && empty($param->_to)){
    $search=$search." AND work_date BETWEEN DATE('".$param->_from."') AND DATE('".$param->_from."') ";
}

if( !empty($param->_from) && !empty($param->_to) ){
    $search=$search." AND work_date BETWEEN DATE('".$param->_from."') AND DATE('".$param->_to."') ";
    
}




$dept = getIdUnit($con,$param->idsuperior);

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
$Qry->table     = "
(SELECT empid, id, empname, lname, fname, mname, idunit, empstat FROM vw_dataemployees) AS de LEFT JOIN 
(SELECT tid, work_date, timein AS `in`, stime AS shiftin, late FROM vw_mng_timesheetfinal) AS dt ON (de.id = dt.tid)";
$Qry->selected  = "de.empname";
$Qry->fields       = "idunit IN (".$ids.") AND (work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND (late > 0)".$search;
$rs 			= $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){

    $array_id =array();
   
    while($row=mysqli_fetch_assoc($rs)){
        
        if(!in_array($row['tid'],$array_id)){
            
            $data[] = array( 
                "empname" 		=> $row['empname'],
                "getTimeshet"   => getTimeshet($con,$pay_period,$row['tid']),
                "getTotalTardy" => getTotalTardy($con,$pay_period,$row['tid']),
                // "pay_start" => $pay_period['pay_start'],
                // "pay_end" =>$pay_period['pay_end'],

			
            );
            array_push($array_id, $row['tid']);
        }
        

        $return = json_encode($data);
    }
}else{
   $return = json_encode(array());
}


print $return;
mysqli_close($con);

function getTimeshet($con,$pay_period,$idacct){
    $data = array();
    $Qry 			= new Query();	
    $Qry->table     = "
    (SELECT id FROM vw_dataemployees) AS de LEFT JOIN
    (SELECT tid, work_date, stime, timein, late FROM vw_mng_timesheetfinal) AS dt ON (de.id = dt.tid)";
    $Qry->selected  = "dt.work_date, dt.stime, dt.timein, dt.late";
    $Qry->fields       = "tid = '".$idacct."' AND (work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."')  AND (late > 0)";
    $rs 			= $Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTimeshet');
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){

            // $time1 = strtotime($row['in']);
            // $time2 =  strtotime($row['shiftin']);
            // $hours = ($time1 - $time2)/3600;
            // $real = floor($hours) ;
            // $real1 = ($hours-floor($real)) * 60 ;

                //Count all row 
                $countin = mysqli_num_rows($rs); 

              

                $data[] = array( 
                    "work_date"     => $row['work_date'],
                    "shiftin"       => $row['stime'],
                    "in"            => $row['timein'],
                    "count_in"      => $countin,
                    "late"          => $row['late'],

                    // "hours"         => $real,
                    // "minutes"       => $real1,
                    // "late_hrs"          => $interval->format('%I hours'),
                
  
                );

        }
    }
    return $data;
}

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


function getTotalTardy($con,$pay_period,$idacct){
    $Qry=new Query();
    $Qry->table="
    (SELECT id FROM vw_dataemployees) AS de LEFT JOIN 
    (SELECT tid AS empID, late, work_date, timein, stime FROM vw_mng_timesheetfinal) AS dt ON (de.id = dt.empID)";
    $Qry->selected="SUM(dt.late) as total_tardy";
    $Qry->fields=" tid='".$idacct."' AND work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' ORDER BY work_date ASC";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTotalTardy');
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
            $data[] = array(
                "total_tardy" => $row['total']
            );
        }
    }
    return $data;
}





?>