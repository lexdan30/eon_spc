<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

    $search ='';

    if( !empty( $param->empid ) ){ $search=$search." AND empid like 	'%".$param->empid."%' "; }
    // if( !empty( $param->department ) ){ $search=$search." AND business_unit like   '%".$param->department."%' "; }
    //HIRED SEARCH
    if( !empty($param->birthdate_from) && empty($param->birthdate_to)){
        $search=$search." AND CONCAT(DATE_FORMAT(NOW(),'%Y-'),DATE_FORMAT(bdate,'%m-%d')) BETWEEN DATE('".$param->birthdate_from."') AND DATE('".$param->birthdate_from."') ";
    }
    
    if( !empty($param->birthdate_from) && !empty($param->birthdate_to) ){
        $search=$search." AND CONCAT(DATE_FORMAT(NOW(),'%Y-'),DATE_FORMAT(bdate,'%m-%d')) BETWEEN DATE('".$param->birthdate_from."') AND DATE('".$param->birthdate_to."') ";
       
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
			$bday_date_format=date_create($row['bdate']);

            $data[] = array( 
            "id"        	        => $row['id'],
            "empid"			        => $row['empid'],
            "empname" 		        => (($row['empname'])),            
            "birthdate" 		    => date_format($bday_date_format,"m/d/Y"),
            "department" 		    => ucwords($row['business_unit']),


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