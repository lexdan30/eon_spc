<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();
$pay_period = getPayPeriod($con);
$search ='';

if( !empty( $param->empid ) ){ $search=$search." AND de.empid like 	'%".$param->empid."%' "; }
// if( !empty($param->d_from) && empty($param->d_to)){
//     $search=$search." AND dt.date BETWEEN DATE('".$param->d_from."') AND DATE('".$param->d_from."') ";
// }
// if( !empty($param->d_from) && !empty($param->d_to) ){
//     $search=$search." AND dt.date BETWEEN DATE('".$param->d_from."') AND DATE('".$param->d_to."') ";
// }

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



$Qry 			= new Query();	
$Qry->table     = "vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID=de.id";
$Qry->selected  = "de.id,de.empid, de.empname, de.post, SUM(dt.absent) AS awol, COUNT(dt.absent) AS awolCounter, de.concat_sup_fname_lname AS manager,de.idunit";
$Qry->fields    = "(dt.idleave is null or dt.idleave = '') and (dt.in IS NULL OR dt.in = '') and (dt.out IS NULL OR dt.out = '') and dt.idshift !=4
and (dt.work_date between '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') ".$search." group by dt.empID";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        $data[] = array( 
            "id"        	 => $row['id'],
            "empid"			 => $row['empid'],
            "empname" 		 => $row['empname'],
            "post"           => $row['post'],
            "manager"        => $row['manager'],
            "awolCounter"    => $row['awolCounter'],
            "awol"           => $row['awol'],


        );
    }

    $return = json_encode($data);

}
else {
    $return = json_encode(array());
}

print $return;
mysqli_close($con);
?>