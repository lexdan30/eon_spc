<?php 
 	require_once('../../../activation.php');
    require_once('../../../classPhp.php');
    $conn = new connector();
	$con = $conn->connect();
	$date=SysDate();
	$param = json_decode(file_get_contents('php://input'));

    $search='';

	if( !empty( $param->empid ) ){ $search=$search." AND empid like 	'%".$param->empid."%' "; }
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
	if( !empty( $param->birthdate_from ) && empty( $param->birthdate_to )){
		$search=$search." AND bdate BETWEEN DATE('".$param->birthdate_from."') AND DATE('".$param->birthdate_from."') ";
	}
	
	if( !empty( $param->birthdate_from ) && !empty( $param->birthdate_to ) ){
		$search=$search." AND bdate BETWEEN DATE('".$param->birthdate_from."') AND DATE('".$param->birthdate_to."') ";
		
	}

	$Qry = new Query();	
	$Qry->table     = "vw_dataemployees";
	$Qry->selected  = "*";
	$Qry->fields    = "id>0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	$where = $Qry->fields;	
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_array($rs)){
			
			//Format date for display
			$bday_date_format=date_create($row['bdate']);

            $data[] = array(
							  "EMPLOYEE ID" 	=> $row['empid'],
							  "EMPLOYEE NAME"   => ucwords(strtolower($row['empname'])),
							  "BIRTHDAY"  		=> date_format($bday_date_format,"m/d/Y"), 
							  "DEPARTMENT NAME" => ucwords(strtolower(utf8_decode($row['business_unit'])))
                    );
		}
		$return = json_encode($data);
	}else{
		$return = json_encode(array('status'=>'empty'));
	}

print $return;	
mysqli_close($con);
?>