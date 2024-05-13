<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();
$date1 = SysDatePadLeft();

    $search ='';

    if( !empty( $param->empid ) ){ $search=$search." AND de.empid like '%".$param->empid."%' "; }
    if( !empty( $param->emptemp ) ){ $search=$search." AND dt.temp = '".$param->emptemp."' "; }

    if( !empty($param->dfrom) && empty($param->dto)){
        $search=$search." AND dt.date BETWEEN DATE('".$param->dfrom."') AND DATE('".$param->dfrom."') "; 
    }
    
    if( !empty($param->dfrom) && !empty($param->dto) ){
        $search=$search." AND dt.date BETWEEN DATE('".$param->dfrom."') AND DATE('".$param->dto."') ";
       
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

    $search.=" ORDER BY de.empname ASC";
    $Qry 			= new Query();	
    $Qry->table     = "vw_dataemployees AS de LEFT JOIN vw_data_timesheet AS dt ON de.id = dt.idacct";
    $Qry->selected  = "de.id, de.empid, de.empname, de.business_unit,dt.temp,dt.date";
    $Qry->fields    = "(dt.temp IS NOT NULL OR dt.temp != '')".$search;
    $rs = $Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>= 1){ 
        while($row=mysqli_fetch_array($rs)){

            $data[] = array( 
                "id"        	    => $row['id'],
                "empid"			    => $row['empid'],
                "empname" 		    => $row['empname'],
                "temp" 		        => $row['temp'],
                "date" 		        => $row['date'],
                "department"        => ucwords(strtolower($row['business_unit'])),


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