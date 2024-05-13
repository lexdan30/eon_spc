<?php 
	 require_once('../../../activation.php');
	 $conn = new connector();
	 $con = $conn->connect();
    require_once('../../../classPhp.php');

	$param = json_decode(file_get_contents('php://input'));
	$date=SysDate();

	$search='';

	if( !empty( $param->emp ) ){ $search=$search." AND empid like 	'%".$param->emp."%' "; }
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


	if( !empty( $param->search_labor_type ) ){ $search=$search." AND labor_type = '".$param->search_labor_type."' "; }
	if( !empty( $param->post_title ) ){ $search=$search." AND post like   '%".$param->post_title."%' "; }
	if( !empty( $param->pay_grp ) ){ $search=$search." AND pay_grp like   '%".$param->pay_grp."%' "; }
	if( !empty( $param->job_level ) ){ $search=$search." AND joblvl like   '%".$param->job_level."%' "; }
	// if( !empty( $param['SECTION ) ){ $search=$search." AND pay_grp like   '%".$param['SECTION."%' "; }
	
	// //HIRED SEARCH
	if( !empty( $param->search_hired_date_from ) && empty( $param->search_hired_date_to )){
		$search=$search." AND hdate BETWEEN DATE('".$param->search_hired_date_from."') AND DATE('".$param->search_hired_date_from."') ";
	}
	
	if( !empty( $param->search_hired_date_from ) && !empty( $param->search_hired_date_to ) ){
		$search=$search." AND hdate BETWEEN DATE('".$param->search_hired_date_from."') AND DATE('".$param->search_hired_date_to."') ";
	}

	// //REGULARIZATION SEARCH
	if( !empty( $param->search_reg_date_from ) && empty( $param->search_reg_date_to )){
		$search=$search." AND rdate BETWEEN DATE('".$param->search_reg_date_from."') AND DATE('".$param->search_reg_date_from."') ";
	}
	
	if( !empty( $param->search_reg_date_from ) && !empty( $param->search_reg_date_to ) ){
		$search=$search." AND rdate BETWEEN DATE('".$param->search_reg_date_from."') AND DATE('".$param->search_reg_date_to."') ";
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


            $name23[] = array(
							
					$row['empid'],							
					(( $row['empname'])),
					ucwords(strtolower(utf8_decode($row['post']))),
					$row['joblvl'],
					ucwords(utf8_decode($row['pay_grp'])),
					ucwords(utf8_decode($row['labor_type'])),
					date_format($hired_date_format,"m/d/Y"), 
					$reg_date_format,
					ucwords(strtolower(utf8_decode($row['business_unit']))),
					number_format($row['salary'], 2, '.', ','),
					$str,
					$str1,
					$row['tot_compensation'],
					$row['annual_gross']
                    );
		}
	}
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=AgeDistributionExport'.$date.'.csv');
	$output = fopen('php://output','w');
	fputcsv($output,array($param['company']));
	fputcsv($output,array("Compensation & Benefits"));
	if( !empty($param['filterby'])){
		fputcsv($output,array($param['filterby']));
	}
	fputcsv($output,array("Export Generated on ".SysDatePadLeft().' '.SysTime() ));
	fputcsv($output,array(
						'EMPLOYEE ID', 
						'EMPLOYEE NAME',
						'POSITION TITLE',
						'JOB LEVEL',
						'PAY GROUP',
						'LABOR TYPE',
						'HIRED DATE',
						'REGULARIZATION DATE',
						'DEPARTMENT NAME',
						'SALARY',
						'type of allowance',
						'amount',
						'TOTAL MONTHLY ALLOWANCE',
						'ANNUAL GROSS COMPENSATION',
	));
	if (count($name23) > 0) {
		foreach ($name23 as $row23) {
			fputcsv($output, $row23);
		}
	}
	
?>