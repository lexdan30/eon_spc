<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_GET;
$data  = array();
$date = SysDatePadLeft();
$pay_period = getPayPeriod($con);

$search ='';

if( !empty( $param['search_acct'] ) ){ $search=$search." AND rc.idacct 	= '".$param['search_acct']."' "; }

if( !empty($param['_from']) && empty($param['_to'])){
    $search=$search." AND rc.reso_date BETWEEN DATE('".$param['_from']."') AND DATE('".$param['_from']."') ";
}

if( !empty($param['_from']) && !empty($param['_to']) ){
    $search=$search." AND rc.reso_date BETWEEN DATE('".$param['_from']."') AND DATE('".$param['_to']."') ";
}

$dept = getIdUnit($con,$param['idsuperior']);

//Get Managers Under person
$ids=0;if( !empty( $dept ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$dept);
    array_push( $arr_id, $dept );
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
$Qry->table     = "vw_resocenter AS rc LEFT JOIN vw_dataemployees AS de ON rc.idacct = de.id";
$Qry->selected  = "rc.idacct,de.idunit, rc.empname, rc.reso_date, rc.reso_txt";
$Qry->fields    = "de.idunit IN (".$ids.") AND (rc.reso_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."')".$search;
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

            $name23[] = array(
                            utf8_decode($row['empname']),
                            $row['reso_date'],
            
            );
 
    }
}


// print_r($name23);
// return;


header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=ResolutionCenter'.$date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array($param['company']));
fputcsv($output, array("Resolution Center Report"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Employee Name',
                        'Date')); 
 
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
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['idunit'];
        }
    }
    return null;
}

?>