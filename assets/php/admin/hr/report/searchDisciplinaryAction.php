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
    if( !empty( $param->emp_action ) ){ $search=$search." AND emp_action like '%".$param->emp_action."%' "; }

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
$Qry->fields    = "id>0 AND emp_action IS NOT NULL".$search;
$rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){ 
    while($row=mysqli_fetch_array($rs)){
    
        $getDisciplinaryActions = getDisciplinaryActions($con, $row['id']);

        $data[] = array( 
            "id"        	        => $row['id'],
            "empid"			        => $row['empid'],
            "empname" 		        => $row['empname'],
            "post" 		            => ucwords($row['post']),
            "getDisciplinaryActions"=> $getDisciplinaryActions,
            "date"                  => $date,
            "time"                  => date ("H:i:s A",strtotime($time)),
        );
    }

    $return = json_encode($data);

}
else {

    $return = json_encode(array());
}

function getDisciplinaryActions($con, $idacct){
    $Qry=new Query();
    $Qry->table="tblaccountdisact";
    $Qry->selected="*";
    $Qry->fields="id>0 AND idacct='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){

            //Format date for display
            $date_format=date_create($row['date']);
            
            $data[] = array(
                'emp_action'  =>$row['emp_action'],
                'penalty'     =>$row['penalty'],
                "pen_date"    => date_format($date_format,"m/d/Y"),
            );
        }
        return $data;
    }
    return null;
}



$return = json_encode($data);
print $return;
mysqli_close($con);
?>