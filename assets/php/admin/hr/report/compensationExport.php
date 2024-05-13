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


	if( !empty( $param['search_labor_type'] ) ){ $search=$search." AND labor_type = '".$param['search_labor_type']."' "; }
	if( !empty( $param['post_title'] ) ){ $search=$search." AND post like   '%".$param['post_title']."%' "; }
	if( !empty( $param['pay_grp'] ) ){ $search=$search." AND pay_grp like   '%".$param['pay_grp']."%' "; }
	if( !empty( $param['job_level'] ) ){ $search=$search." AND joblvl like   '%".$param['job_level']."%' "; }
	// if( !empty( $param['SECTION ) ){ $search=$search." AND pay_grp like   '%".$param['SECTION."%' "; }
	
	// //HIRED SEARCH
	if( !empty( $param['search_hired_date_from'] ) && empty( $param['search_hired_date_to'] )){
		$search=$search." AND hdate BETWEEN DATE('".$param['search_hired_date_from']."') AND DATE('".$param['search_hired_date_from']."') ";
	}
	
	if( !empty( $param['search_hired_date_from'] ) && !empty( $param['search_hired_date_to'] ) ){
		$search=$search." AND hdate BETWEEN DATE('".$param['search_hired_date_from']."') AND DATE('".$param['search_hired_date_to']."') ";
	}

	// //REGULARIZATION SEARCH
	if( !empty( $param['search_reg_date_from'] ) && empty( $param['search_reg_date_to'] )){
		$search=$search." AND rdate BETWEEN DATE('".$param['search_reg_date_from']."') AND DATE('".$param['search_reg_date_from']."') ";
	}
	
	if( !empty( $param['search_reg_date_from'] ) && !empty( $param['search_reg_date_to'] ) ){
		$search=$search." AND rdate BETWEEN DATE('".$param['search_reg_date_from']."') AND DATE('".$param['search_reg_date_to']."') ";
	}

	$name23 = array();

	//$name23 = array();
	$Qry = new Query();	
	$Qry->table     = "vw_dataemployees";
	$Qry->selected  = "*";
	// $Qry->fields    = "id>0 and lname not like '%ñ%'".$search;
	$Qry->fields    = "id>0 ".$search;
	$where = $Qry->fields;	
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_array($rs)){
			
			//Format date for display
			$hired_date_format=date_create($row['hdate']);

			if(!empty($row['rdate'])){
				$reg_date_format=date_create($row['rdate']);
				$reg_date_format=date_format($reg_date_format,"m/d/Y ");
			}else{
				$reg_date_format = '';
			}

			$getCompAllowance = getCompAllowance($con, $row['id']); 
			$str = '';
			$str1 = '';
			$ctr = 1;
			
			if($getCompAllowance){
				foreach($getCompAllowance as $val){
					$str=$str . $ctr . ". " . $val['description']."\n";
					$str1=$str1 . $ctr . ". " . $val['amt']."\n";
					$ctr++;
				}
			}
			
			$name23[] = array(
							$row['empid'],							
							// ucwords(strtolower($row['empname'])),
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
			// "RICE ALLOWANCE"  		=> number_format($row['riceallowance'], 2, '.', ','),
			// "CLOTHING ALLOWANCE"  	=> number_format($row['clothingallowance'], 2, '.', ','),
			// "LAUNDRY ALLOWANCE"  	=> number_format($row['laundryallowance'], 2, '.', ','),
		}

	}

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=CompensationReport'.$date.'.csv');
	$output = fopen('php://output','w');
	// fputcsv($output,array($param['company']));
	fputcsv($output, array("New World Makati Hotel"));
	fputcsv($output,array("Compensation & Benefits Report"));
	if( !empty($param['filterby'])){
		fputcsv($output,array($param['filterby']));
	}
	fputcsv($output,array("Export Generated on ".SysDatePadLeft().' '.SysTime() ));
	fputcsv($output,array(
						'Employee ID', 
						'Employee NAME',
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
						'ANNUAL GROSS COMPENSATION'
	));
	if (count($name23) > 0) {
		foreach ($name23 as $row23) {
			fputcsv($output, $row23);
		}
	}


function getCompAllowance($con, $accountid){
	$Qry=new Query(); 
	$data=array();
    $Qry->table="tblacctallowance LEFT JOIN tblallowance ON tblacctallowance.idallowance = tblallowance.id";
    $Qry->selected="*";
    $Qry->fields="tblacctallowance.id>0  AND idacct='".$accountid."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){

			$data[] = array( 
				"id"            => $row['id'],
                "idacct"        => $row['idacct'],
                "description"   => $row['description'],
                // "idmethod"      => $row['idmethod'] ? $row['idmethod'] : '1',
                "amt"           => $row['amt'] ? number_format($row['amt'],2) : '0.00',
			);
        }
    }
    return $data;
}


?>