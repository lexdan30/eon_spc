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

    if( !empty( $param->empid ) ){ $search=$search." AND empid like 	'%".$param->empid."%' "; }
    if( !empty( $param->attainment ) ){ $search=$search." AND attainment like 	'%".$param->attainment."%' "; }
    if( !empty( $param->school ) ){ $search=$search." AND school like 	'%".$param->school."%' "; }
    
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
    $Qry->fields    = "id>0 AND attainment is not null AND school is not null ".$search;
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
    
            $educ = getEducAttainment($con,$row['id']);
    
            $data[] = array( 
                "id"        	        => $row['id'],
                "empid"			        => $row['empid'],
                "empname" 		        => $row['empname'],
                "educ"                  => $educ,
                "date"                  => $date,
                "time"                  => date ("H:i:s A",strtotime($time))
            );
            $return = json_encode($data);
        }
    }else{
        $return = json_encode(array('status'=>'error'));
    }

    function getEducAttainment($con, $idacct){
        $Qry=new Query();
        $Qry->table="tblaccountedubg";
        $Qry->selected="*";
        $Qry->fields="id>0 AND idacct='".$idacct."'";
        $rs=$Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>=1){
            while($row=mysqli_fetch_array($rs)){
                $data[] = array(
                    'attainment'    =>$row['attainment'],
                    'school'	    =>$row['school'],
                    'from'	        =>$row['dfrom'],
                    'to'	        =>$row['dto']
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