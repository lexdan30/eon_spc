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
    if( !empty( $param->skill_name ) ){ $search=$search." AND skill_name like '%".$param->skill_name."%' "; }
    if( !empty( $param->skill_type ) ){ $search=$search." AND skill_type like '%".$param->skill_type."%' "; }

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
$Qry->fields    = "id > 0 AND (skill_name IS NOT NULL OR skill_type IS NOT NULL) ".$search;
$rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){ 
    while($row=mysqli_fetch_array($rs)){


        $data[] = array( 
            "id"        	        => $row['id'],
            "empid"			        => $row['empid'],
            "empname" 		        => $row['empname'],
            'skill_name'            => $row['skill_name'],
            'skill_type'            => $row['skill_type']


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