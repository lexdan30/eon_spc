 <?php 
 	require_once('../../../activation.php');
    require_once('../../../classPhp.php');
    $conn = new connector();
    $con = $conn->connect();
    $param = $_GET;
	$date=SysDate();

    $search='';

	//if( !empty( $param['department'] ) ){ $search=$search." AND BusinessUnit like 	'%".$param['department']."%' "; }
	
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
	
	if( !empty( $param['position_title'] ) ){ $search=$search." AND PositionTitle like   '%".$param['position_title']."%' "; }
	if( !empty( $param['employee'] ) ){ $search=$search." AND EmployeeID like   '%".$param['employee']."%' "; }
	if( !empty( $param['pay_group'] ) ){ $search=$search." AND PayGroup like   '%".$param['pay_group']."%' "; }
	if( !empty( $param['pay_status'] ) ){ $search=$search." AND PayStatus like   '%".$param['pay_status']."%' "; }
	if( !empty( $param['employment_type'] ) ){ $search=$search." AND EmploymentType like   '%".$param['employment_type']."%' "; }
	if( !empty( $param['employment_status'] ) ){ $search=$search." AND EmploymentStatus like   '%".$param['employment_status']."%' "; }

	$name23 = array();
	$Qry = new Query();	
	$Qry->table     = "vw_employee_data_report";
	$Qry->selected  = "*";
	$Qry->fields    = "id!=1 ".$search;
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_array($rs)){
			
			//Years in Service
			$date1 = new DateTime(SysDate());
			$date2 = new DateTime($row['HireDate']);
			$interval = $date2->diff($date1);
			$yrservice = '';
			if( (int)$interval->format('%Y') > 0 ){
				$yrservice = $yrservice . $interval->format('%Y') . ' yr(s)';		
			}
			if( (int)$interval->format('%M') > 0 ){
				$yrservice = $yrservice . $interval->format('%M') . ' mo(s)';			
			}
			if( (int)$interval->format('%d') > 0 ){
				if( !empty($yrservice) ){
					$yrservice = $yrservice . ' & '. $interval->format('%d') . ' day(s)';			
				}else{
					$yrservice = $yrservice . $interval->format('%d') . ' day(s)';
				}
			}
			
			if(!empty($row['SeparationDate'])){
				$separation_date_format=date_create($row['SeparationDate']);
				$separation_date_format=date_format($separation_date_format,"m/d/Y ");
			}else{
				$separation_date_format = '';
			}
			
			//mga column sa database
			$name23[] = array($row['EmployeeID'],
						utf8_decode($row['LastName']),
						utf8_decode($row['FirstName']),
						utf8_decode($row['MiddleName']),
						utf8_decode($row['EmpName']),
						utf8_decode($row['Nickname']),
						$row['PositionCode'],
						$row['PositionTitle'],
						$row['JobLevel'],
						$row['PayGroup'],
						$row['PayStatus'],
						$row['LaborType'],
						date_format(date_create($row['HireDate']),"m/d/Y"),
						date_format(date_create($row['RegDate']),"m/d/Y"),
						$separation_date_format,
						$yrservice,
						$row['BusinessUnit'],
						utf8_decode($row['Manager']),
						$row['shift'],
						$row['ExemptCode'],
						$row['SSSNo'],
						$row['PagibigNo'],
						$row['TIN'],
						$row['PhilhealthNo'],
						$row['PayrollAccount'],
						$row['Gender'],
						$row['EmploymentType'],
						$row['EmploymentStatus']);
		}
	}

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=EmployeeReport_'.$date.'.csv');
	$output = fopen('php://output', 'w');
	fputcsv($output, array($param['company']));
	fputcsv($output, array("Employees Report"));
	if( !empty( $param['filterby'] ) ){
		fputcsv($output, array($param['filterby']));
	}
	fputcsv($output, array("Report Generated on " . $param['datenow'] ));
	fputcsv($output, array('EmployeeID',
							'LastName',
							'FirstName',
							'MiddleName',
							'EmpName',
							'Nickname',
							'PositionCode',
							'PositionTitle',
							'JobLevel',
							'PayGroup',
							'PayStatus',
							'LaborType',
							'HireDate',
							'RegDate',
							'SeparationDate',
							'YearsInService',
							'BusinessUnit',
							'Manager',
							'shift',
							'ExemptCode',
							'SSSNo',
							'PagibigNo',
							'TIN',
							'PhilhealthNo',
							'PayrollAccount',
							'Gender',
							'EmploymentType',
							'EmploymentStatus')); 
	 
	if (count($name23) > 0) {
		foreach ($name23 as $row23) {
			fputcsv($output, $row23);
		}
	}

?>