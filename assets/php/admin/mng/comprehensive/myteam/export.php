<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_GET;
$return = null;	
$date=SysDate();
$date1=SysDatePadLeft();

$search='';

if( !empty( $param['acctt'] ) ){ $search=$search." AND id 	= '".$param['acctt']."' "; }
if( !empty( $param['postt'] ) ){ $search=$search." AND idpos 	= '".$param['postt']."' "; }

$dept = getIdUnit($con,$param['idsuperior']);
$ids=0;

//Get Managers Under person
$ids=0;if( !empty( $dept ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$dept);
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

$name23=array();
$Qry = new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "addr_st, addr_area, addr_city, addr_prov, addr_code, empname, post, email, pnumber, fnumber, cnumber, emergency_name, emergency_number";
$Qry->fields    = "id != '".$param['idsuperior']."' AND (idsuperior='".$param['idsuperior']."' or idunit IN (".$ids.")) ".$search." ORDER BY empname ASC ";
$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        
        $address = '';
		if( !empty( $row['addr_st'] ) ){
			$address = $address .  $row['addr_st'] . ',';
		}
		if( !empty( $row['addr_area'] ) ){
			$address = $address .  $row['addr_area'] . ',';
		}
		if( !empty( $row['addr_city'] ) ){
			$address = $address .  $row['addr_city'] . ',';
		}
		if( !empty( $row['addr_prov'] ) ){
			$address = $address .  $row['addr_prov'] . ',';
		}
		if( !empty( $row['addr_code'] ) ){
			$address = $address .  $row['addr_code'] . ',';
		}
		$address = substr($address,0, strlen($address)-1);
		

		//mga column sa database
		$name23[] = array(
						utf8_decode($row['empname']),
						$row['post'],
						$address,
						$row['email'],
                        $row['pnumber'],
                        $row['fnumber'],
                        $row['cnumber'],
                        $row['emergency_name'],
                        $row['emergency_number'],
		);
	
    }
}
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=myTeam201Records_'.$date.'.csv');
$output = fopen('php://output', 'w');
fputcsv($output, array($param['company']));
fputcsv($output, array("myTeam 201 Records"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Employee Name',
						'Position',
						'Address',
						'Email',
						'Phone Number',
                        'Fax Number',
                        'Cellphone Number',
                        'Emergency Name',
                        'Emergency Number')); 
 
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
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

?>