<?php 
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 
  
	// $param = $_GET;
	$param = json_decode(file_get_contents('php://input'));
	$date=SysDate();

    $search='';

	if( !empty( $param->empid ) ){ $search=$search." AND empid like 	'%".$param->empid."%' "; }
	if( !empty( $param->position ) ){ $search=$search." AND post like   '%".$param->position."%' "; } 
	if( !empty( $param->dept_code ) ){ $search=$search." AND business_unit_code like   '%".$param->dept_code."%' "; }
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


	// //HIRED SEARCH
	if( !empty( $param->hired_date_from ) && empty( $param->hired_date_to )){
		$search=$search." AND hdate BETWEEN DATE('".$param->hired_date_from."') AND DATE('".$param->hired_date_from."') ";
	}
	
	if( !empty( $param->hired_date_from ) && !empty( $param->hired_date_to ) ){
		$search=$search." AND hdate BETWEEN DATE('".$param->hired_date_from."') AND DATE('".$param->hired_date_to."') ";
		
	}


	// $name23 = array();
	$Qry = new Query();	
	$Qry->table     = "vw_dataemployees";
	$Qry->selected  = "*";
	$Qry->fields    = "id>0 ".$search;
	$where = $Qry->fields;	
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_array($rs)){
			
			//Format date for display
			$hired_date_format=date_create($row['hdate']);

            $data[] = array(
							// "ID" 				=> $row['id'],
							"EMPLOYEE ID" 		=> $row['empid'],
							// "EMPLOYEE NAME"  	=> ucwords(strtolower($row['empname'])),
							"EMPLOYEE NAME"  	=> (( $row['empname'])),
							"DEPARTMENT CODE" 	=> utf8_decode($row['business_unit_code']),
							"DEPARTMENT NAME" 	=> ucwords(strtolower(utf8_decode($row['business_unit']))),
							"POSITION" 			=> ucwords(strtolower(utf8_decode($row['post']))),
							"HIRED DATE"  		=> date_format($hired_date_format,"m/d/Y"), 
                             

                    );
		}
		$return = json_encode($data);
	}else{
		$return = json_encode(array('status'=>'empty'));
	}

	// header('Content-Type: text/csv; charset=utf-8');
	// header('Content-Disposition: attachment; filename=HiredEmployeesReport_'.$date.'.csv');
	// $output = fopen('php://output', 'w');
    // fputcsv($output, array('ID',
    //                         'EmployeeID',
	// 						'EmployeeName',
	// 						'DepartmentCode',
    //                         'DepartmentName',
    //                         'Position',
	// 						'HiredDate',
                            
							
    //                     )); 
	 
	// if (count($name23) > 0) {
	// 	foreach ($name23 as $row23) {
	// 		fputcsv($output, $row23);
	// 	}
	// }
print $return;	
mysqli_close($con);

?>