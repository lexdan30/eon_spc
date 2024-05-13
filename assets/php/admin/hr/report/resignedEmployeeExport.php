<?php 
     require_once('../../../activation.php');
     $conn = new connector();
     $con = $conn->connect();
    require_once('../../../classPhp.php');

    $param = json_decode(file_get_contents('php://input'));
	$date=SysDate();

    $search='';

    if( !empty( $param->empid ) ){ $search=$search." AND empid like 	'%".$param->empid."%' "; }
    if( !empty( $param->position ) ){ $search=$search." AND post like 	'%".$param->position."%' "; }
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

    //HIRED SEARCH
    if( !empty($param->hired_from) && empty($param->hired_to)){
        $search=$search." AND hdate BETWEEN DATE('".$param->hired_from."') AND DATE('".$param->hired_from."') "; 
    }
    
    if( !empty($param->hired_from) && !empty($param->hired_to) ){
        $search=$search." AND hdate BETWEEN DATE('".$param->hired_from."') AND DATE('".$param->hired_to."') ";
        
    }
    //RESIGNED SEARCH
    if( !empty($param->resigned_from) && empty($param->resigned_to)){
        $search=$search." AND sdate BETWEEN DATE('".$param->resigned_from."') AND DATE('".$param->resigned_from."') ";
    }
    
    if( !empty($param->resigned_from) && !empty($param->resigned_to) ){
        $search=$search." AND sdate BETWEEN DATE('".$param->resigned_from."') AND DATE('".$param->resigned_to."') ";
    
    }

	$Qry = new Query();	
	$Qry->table     = "vw_dataemployees";
	$Qry->selected  = "*";
	$Qry->fields    = "id>0  AND hdate IS NOT NULL AND sdate IS NOT NULL ".$search;
    $where = $Qry->fields;	
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_array($rs)){
            
            //Format date for display
            $hired_date_format=date_create($row['hdate']);
            
            $data[] = array(
                            // ""  => $row['id'],
                            "EMPLOYEE ID"      => $row['empid'],
                            "EMPLOYEE NAME"    => $row['empname'],
                            "DEPARTMENT CODE"  => ucwords(strtolower(utf8_decode($row['business_unit_code']))),
                            "DEPARTMENT NAME"  => ucwords(strtolower(utf8_decode($row['business_unit']))),
                            "POSITION"         => ucwords(strtolower(utf8_decode($row['post']))),
                            "HIRED DATE"       => $row['hdate'],
                            "SEPARATION DATE"  => $row['sdate'],
                             
                    );
        }
        $return = json_encode($data);
	}else{
        $return = json_encode(array('status'=>'empty'));
    }

print $return;	
mysqli_close($con);
  
?>