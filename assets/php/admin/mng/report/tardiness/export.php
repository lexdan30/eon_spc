<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_GET;
$data  = array();
$date = SysDatePadLeft();
$pay_period = getPayPeriod($con);


$search ='';

if( !empty( $param['search_acct'] ) ){ $search=$search." AND idacct 	= '".$param['search_acct']."' "; }

if( !empty($param['_from']) && empty($param['_to'])){
    $search=$search." AND work_date BETWEEN DATE('".$param['_from']."') AND DATE('".$param['_from']."') ";
}

if( !empty($param['_from']) && !empty($param['_to']) ){
    $search=$search." AND work_date BETWEEN DATE('".$param['_from']."') AND DATE('".$param['_to']."') ";
    
}

$dept = getIdUnit($con,$param['idsuperior']);

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
(SELECT id, empid, lname, fname, mname, idunit, idemptype FROM vw_dataemployees) AS de
LEFT JOIN (SELECT tid AS empID, work_date, stime AS shiftin, timein AS `in`, late FROM vw_mng_timesheetfinal) AS dt ON (de.id = dt.empID)";
$Qry->selected  = "de.empid, de.lname, de.fname, de.mname, dt.work_date , dt.shiftin , dt.in, dt.late";
$Qry->fields    = "idunit IN (".$ids.") AND (dt.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND (dt.in > shiftin)".$search;
$rs 			= $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        
            $name23[] = array(
                            utf8_decode($row['empname']),
                            $row['work_date'],
                            $row['shiftin'],
                            $row['in'],
                            $row['late'],
                          
                            
            );
 
    }
}


// print_r($name23);
// return;


header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=TardinessReport'.$date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array($param['company']));
fputcsv($output, array("Attendance Today"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array( 'EmpID',
                        'Employee Name',
                        'Date',
                        'Schedule IN',
                        'Actual IN',
                        'Tardy')); 
 
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}

function getIdUnit($con, $idsuperior){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idsuperior."'";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getIdUnit');
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['idunit'];
    }
    return null;
}

function getTimeshet($con,$pay_period,$idacct){
    $data = array();
    $Qry 			= new Query();	
    $Qry->table     = "
    (SELECT id FROM vw_dataemployees) AS de
    LEFT JOIN (SELECT tid AS empID, work_date, stime, timein, late FROM vw_mng_timesheetfinal) AS dt ON (de.id = dt.empID)";
    $Qry->selected  = "dt.work_date , dt.stime AS shiftin, dt.timein AS `in`, dt.late, de.id";
    $Qry->fields    = "de.id='".$idacct."' AND (dt.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND (dt.in > shiftin)";
    $rs 			= $Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTimeshet');
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){

                //Count all row 
                $countin = mysqli_num_rows($rs); 

              

                $data[] = array( 
                    "work_date"     => $row['work_date'],
                    "shiftin"       => $row['shiftin'],
                    "in"            => $row['in'],
                    "count_in"      => $countin,
                    "late"          => $row['late'],
                    "idacct"        => $row['id'],
                );

        }
    }
    return $data;
}



function getTotalTardy($con,$pay_period,$idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="
    (SELECT id FROM vw_dataemployees) AS de
    LEFT JOIN (SELECT tid, work_date, stime, timein, late FROM vw_mng_timesheetfinal) AS dt ON (de.id = dt.tid)";
    $Qry->selected="SUM(dt.late) as total_tardy";
    $Qry->fields="de.id='".$idacct."' AND (dt.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND (dt.in > shiftin)";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTotalTardy');
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
            $data[] = array(
                "total_tardy" => $row['total_tardy']
            );
        }
    }
    return $data;
}


?>