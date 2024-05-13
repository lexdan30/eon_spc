 <?php 
 	require_once('../../../activation.php');
    require_once('../../../classPhp.php');
    $conn = new connector();
    $con = $conn->connect();
    $param = $_POST;
    $return = null;	
	$search='';

	$data  = array();
	$filtered_by = '';
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
		
		$filtered_by = "Department";
		
    }
	
	if( !empty( $param['position_title'] ) ){ $search=$search." AND PositionTitle like   '%".$param['position_title']."%' "; if( !empty( $filtered_by ) ){ $filtered_by = $filtered_by." And Position Title "; }else{ $filtered_by = "Position Title"; } }
	if( !empty( $param['employee'] ) ){ $search=$search." AND EmployeeID like   '%".$param['employee']."%' "; if( !empty( $filtered_by ) ){ $filtered_by = $filtered_by." And Employee "; }else{ $filtered_by = "Employee"; } }
	if( !empty( $param['pay_group'] ) ){ $search=$search." AND PayGroup like   '%".$param['pay_group']."%' "; if( !empty( $filtered_by ) ){ $filtered_by = $filtered_by." And Pay Group "; }else{ $filtered_by = "Pay Group"; } }
	if( !empty( $param['pay_status'] ) ){ $search=$search." AND PayStatus like   '%".$param['pay_status']."%' "; if( !empty( $filtered_by ) ){ $filtered_by = $filtered_by." And Pay Status "; }else{ $filtered_by = "Pay Status"; } }
	if( !empty( $param['employment_type'] ) ){ $search=$search." AND EmploymentType like   '%".$param['employment_type']."%' "; if( !empty( $filtered_by ) ){ $filtered_by = $filtered_by." And Employment Type "; }else{ $filtered_by = "Employment Type"; } }
	if( !empty( $param['employment_status'] ) ){ $search=$search." AND EmploymentStatus like   '%".$param['employment_status']."%' "; if( !empty( $filtered_by ) ){ $filtered_by = $filtered_by." And Employment Status "; }else{ $filtered_by = "Employment Status"; } }


	$where = $search; 
	//sort nga funtion sa table
	if( $param['order'][0]['column'] !='' ){//default 
		$arrCols = array("EmployeeID",
						"empname",
						"PositionCode",
						"PositionTitle",
						"PayGroup",
						"LaborType",
						"HireDate",
						"RegDate",
						"SeparationDate",
						"BusinessUnit",
						"Manager",
						"shift",
						"ExemptCode",
						"SSSNo",
						"PagibigNo",
						"TIN",
						"PhilhealthNo",
						"PayrollAccount",
						"Gender",
						"EmploymentType",
						"EmploymentStatus");//mao ra ang mailisan na declare na sa ubos php
		$search=$search." ORDER BY ". $arrCols[$param['order'][0]['column']] ." ".$param['order'][0]['dir'];//default
	}


	if( $param['length'] !='' ){
		$search=$search." LIMIT ".$param['length'];	
	}
	if( $param['start'] !='' ){
		$search=$search." OFFSET ".$param['start'];
	}


    	$Qry = new Query();	
		$Qry->table     = "vw_employee_data_report";
		$Qry->selected  = "*";
		$Qry->fields    = "id!=1 ".$search;
		$rs = $Qry->exe_SELECT($con);
		$recFiltered = getTotalRows($con,$where);

		//
		if(mysqli_num_rows($rs)>= 1){ // if 1 e execute ang while if 0 sa else

			$data = array( 
		        "draw"=> $param['draw'],
		        "recordsTotal"=> mysqli_num_rows($rs),
		        "recordsFiltered"=> $recFiltered,
		        "qry"=>$Qry->fields,
				"filter"=>$filtered_by,
		        "data"=>array()
			);
			
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
				
				$data["data"][] = array(
					//ang display sa mga information sa table
					"employeeid"   		=> $row['EmployeeID'],
					"lastname"     		=> $row['LastName'],
					"firstname"    		=> $row['FirstName'],
					"namesuffix"   		=> $row['NameSuffix'],
					"middlename"   		=> $row['MiddleName'],
					"name" 				=> $row['EmpName'],
					"nickname" 			=> $row['Nickname'],
					"positioncode"		=> ucfirst ($row['PositionCode']),
					"positiontitle"		=> ucfirst ($row['PositionTitle']),
					"joblvl"			=> ucfirst ($row['JobLevel']),
					"paygroup"			=> ucfirst ($row['PayGroup']),
					"paystatus"			=> ucfirst ($row['PayStatus']),
					"labortype"			=> ucfirst ($row['LaborType']),
					"hiredate"			=> date_format(date_create($row['HireDate']),"m/d/Y"),
					"regdate"			=> date_format(date_create($row['RegDate']),"m/d/Y"),
					"separationdate"	=> $separation_date_format,
					"no_yrs"			=> $yrservice,
					"businessunit"		=> ucfirst ($row['BusinessUnit']),
					"manager"			=> ucfirst ($row['Manager']),
					"shift"				=> ucfirst ($row['shift']),
					"exemptcode"		=> ucfirst ($row['ExemptCode']),
					"sssno"				=> ucfirst ($row['SSSNo']),
					"pagibigno"			=> ucfirst ($row['PagibigNo']),
					"tin"				=> ucfirst ($row['TIN']),
					"philhealthno"		=> ucfirst ($row['PhilhealthNo']),
					"payrollaccount"	=> ucfirst ($row['PayrollAccount']),
					"gender"			=> ucfirst ($row['Gender']),
					"employmenttype"	=> ucfirst ($row['EmploymentType']),
					"employmentstatus"  => ucfirst ($row['EmploymentStatus'])
		        );
			}		
			$return = json_encode($data);
		}else {
			//ig search nmo sa table nya way pareha naay mo gwas no data available
			$data = array( 
		        "draw"=> $param['draw'],
		        "recordsTotal"=> mysqli_num_rows($rs),
		        "recordsFiltered"=> mysqli_num_rows($rs),
				"qry"=>$Qry->fields,
				"filter"=>$filtered_by,
		        "data"=>array()
		    );
		    $return =  json_encode($data);
		}


print $return;
mysqli_close($con);

	function getTotalRows($con,$search){
		$Qry = new Query();	
		$Qry->table ="vw_employee_data_report";
		$Qry->selected ="*";
		$Qry->fields ="id > 0 ".$search;
		$rs = $Qry->exe_SELECT($con);
		return mysqli_num_rows($rs);
	}
?>