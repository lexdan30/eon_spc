<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();
$date  = SysDateDan();
$time  = SysTime();
$search ='';

    if( !empty( $param->empid ) ){ $search=$search." AND empid like '%".$param->empid."%' "; }	
    if( !empty( $param->desig_licen ) && $param->desig_licen == 'prc'){ $search=$search." AND license_prc IS NOT NULL "; }
    if( !empty( $param->desig_licen ) && $param->desig_licen == 'dri'){ $search=$search." AND license_drive IS NOT NULL "; }
    if( !empty( $param->desig_licen ) && $param->desig_licen == 'pas'){ $search=$search." AND idpassport IS NOT NULL "; }

    //Search Department
    if( !empty( $param->department ) ){
        $arr_id = array();
        $arr 	= getHierarchy($con,$param->department);
        array_push( $arr_id, $param->department );
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
        $search.=" AND idunit in (".$ids.") "; 
    }

$search.=" ORDER BY empname ASC";
$Qry = new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "*";
$Qry->fields    = "id > 0 AND (idpassport IS NOT NULL OR license_drive IS NOT NULL OR license_prc IS NOT NULL) ".$search;
$rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){ 
    while($row=mysqli_fetch_array($rs)){


        $data[] = array( 
            "id"        	        => $row['id'],
            "empid"			        => $row['empid'],
            "empname" 		        => $row['empname'],
            "idpassport"            => $row['idpassport'],
            "license_drive"         => $row['license_drive'],
            "license_prc"           => $row['license_prc'],
            "department"            => $row['business_unit']

        );
    }

    $return = json_encode($data);

}
else {

    $return = json_encode(array());
}


$return = json_encode($data);
print $return;
mysqli_close($con);
?>