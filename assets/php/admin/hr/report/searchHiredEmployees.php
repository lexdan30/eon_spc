<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

    $search ='';

    if( !empty( $param->empid ) ){ $search=$search." AND empid like 	'%".$param->empid."%' "; }
    if( !empty( $param->position ) ){ $search=$search." AND post like   '%".$param->position."%' "; }
    // if( !empty( $param->department ) ){ $search=$search." AND business_unit like   '%".$param->department."%' "; }
    if( !empty( $param->department_code ) ){ $search=$search." AND business_unit_code like   '%".$param->department_code."%' "; }

    //HIRED SEARCH
    if( !empty($param->hired_date_from) && empty($param->hired_date_to)){
        $search=$search." AND hdate BETWEEN DATE('".$param->hired_date_from."') AND DATE('".$param->hired_date_from."') ";
    }
    
    if( !empty($param->hired_date_from) && !empty($param->hired_date_to) ){
        $search=$search." AND hdate BETWEEN DATE('".$param->hired_date_from."') AND DATE('".$param->hired_date_to."') ";
       
    }

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


    $Qry = new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "*";
    $Qry->fields    = "id>0".$search;
    $rs = $Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>= 1){ 
        while($row=mysqli_fetch_array($rs)){
        //Format date for display
        $hired_date_format=date_create($row['hdate']);


            $data[] = array( 
            "id"        	        => $row['id'],
            "empid"			        => $row['empid'],
            "empname" 		        => (($row['empname'])),
            "department_code" 		=> ucwords(strtolower($row['business_unit_code'])),
            "department" 		    => ucwords($row['business_unit']),
            "position" 		        => ucwords($row['post']),
            "hire_date"             => date_format($hired_date_format,"m/d/Y"),


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