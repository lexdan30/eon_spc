<?php 
	 require_once('../../../activation.php');
	 $conn = new connector();
	 $con = $conn->connect();
	require_once('../../../classPhp.php');
  
	$param = $_GET;
	$return = null;	
	$date=SysDate();
	$date1 = SysDatePadLeft();

    $search='';

	if( !empty( $param['empid'] ) ){ $search=$search." AND empid like 	'%".$param['empid']."%' "; }
	if( !empty( $param['position'] ) ){ $search=$search." AND post like   '%".$param['position']."%' "; } 
	if( !empty( $param['dept_code'] ) ){ $search=$search." AND business_unit_code like   '%".$param['dept_code']."%' "; }
	//Search Department
    if( !empty( $param['department'] ) ){
        $arr_id = array();
        $arr 	= getHierarchy($con,$param['department']);
        array_push( $arr_id, $param['department'] );
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
	if( !empty( $param['hired_date_from'] ) && empty( $param['hired_date_to'] )){
		$search=$search." AND hdate BETWEEN DATE('".$param['hired_date_from']."') AND DATE('".$param['hired_date_from']."') ";
	}
	
	if( !empty( $param['hired_date_from'] ) && !empty( $param['hired_date_to'] ) ){
		$search=$search." AND hdate BETWEEN DATE('".$param['hired_date_from']."') AND DATE('".$param['hired_date_to']."') ";
		
	}


	$name23 = array();
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

            $name23[] = array(
							// $row['id'],
							$row['empid'],
							// ucwords(strtolower($row['empname'])),
							(( $row['empname'])),
							utf8_decode($row['business_unit_code']),
							ucwords(strtolower(utf8_decode($row['business_unit']))),
							ucwords(strtolower(utf8_decode($row['post']))),
							date_format($hired_date_format,"m/d/Y"), 
                             

                    );
		}
	}

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=HiredEmployeesReport'.$date.'.csv');
	$output = fopen('php://output','w');
	// fputcsv($output,array($param['company']));
	fputcsv($output, array("New Wolrd Makati Hotel"));
	fputcsv($output,array("Hired Employees Report"));
	if( !empty($param['filterby'])){
		fputcsv($output,array($param['filterby']));
	}
	if( !empty($param['hiredto'])){
		fputcsv($output,array($param['hiredto']));
	}
	fputcsv($output,array("Export Generated on ".SysDatePadLeft().' '.SysTime() ));
	fputcsv($output,array(
						'Employee ID', 
						'Employee NAME',
						'DEPARTMENT CODE',
						'DEPARTMENT NAME',
						'POSITION', 
						'HIRED DATE'
	));
	if (count($name23) > 0) {
		foreach ($name23 as $row23) {
			fputcsv($output, $row23);
		}
	}

?>