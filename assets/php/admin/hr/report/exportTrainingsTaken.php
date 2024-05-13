<?php 
 	require_once('../../../activation.php');
    require_once('../../../classPhp.php');
    $conn = new connector();
    $con = $conn->connect();
	// $param = $_GET;
	$param = json_decode(file_get_contents('php://input'));
	$date=SysDate();

	$search='';

    if( !empty( $param->empid ) ){ $search=$search." AND empid like 	'%".$param->empid."%' "; }
    if( !empty( $param->trainingstaken ) ){ $search=$search." AND trainingstaken like 	'%".$param->trainingstaken."%' "; }
    
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
	
	$search.=" ORDER BY empname ASC";
	
	//$name23 = array();
	$Qry = new Query();	
	$Qry->table     = "vw_dataemployees";
	$Qry->selected  = "*";
	$Qry->fields    = "id>0 AND trainingstaken is not null".$search;
	$where = $Qry->fields;	
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_array($rs)){
			
			$trainings = getTrainingTaken($con,$row['id']);

			$str = '';
			$str1 = '';
			$str2 = '';
			$ctr = 1;

			if($trainings){
				foreach($trainings as $val){
					$str=$str . $ctr . ". " . $val['training_taken']."\n";
					$str1=$str1 . $ctr . ". " . $val['location']."\n";
					$str2=$str2 . $ctr . ". " . $val['date']."\n";
					$ctr++;
				}
			}

			$data[] = array(
							"empid"			        => $row['empid'],
							"empname" 		        => $row['empname'],
							// "orgunit" 		        => $org,
							// "position" 		        => ucwords($row['post']),
							// "labor_type" 		    => ucwords($row['labor_type']),
							// "product" 		    => ucwords($row['product']),
							// "division" 		        => $division,
							// "department" 		    => $department,
							// "section" 		        => $section,
							// "sub_section" 		    => $subsection,
							// "unit" 		            => $unit,
							// "sub_unit" 		        => $subunit,
							// "operator" 		        => $operator,
							// "base_location" 		=> $row['per_st'],
							// "specific_location" 	=> $row['addr_st'],
							// "gender" 		        => ucwords(strtolower($row['sexstr'])),
							// "marital_stat" 		    => ucwords(strtolower($row['civil_status'])),
							// "birth_place" 		    => ucwords(strtolower($row['bplace'])),
							// "address_city" 		    => ucwords(strtolower($row['addr_city'])),
							// "address_prov" 		    => ucwords(strtolower($row['addr_prov'])),
							// "count" 		        => ucwords(strtolower($row['count'])),
							"trainings_taken"       => $str,
							"location"              => $str1,
							"date"              	=> $str2
                    );
		}
		$return = json_encode($data);
	}else{
		$return = json_encode(array('status'=>'empty'));
	}

	// header('Content-Type: text/csv; charset=utf-8');
	// header('Content-Disposition: attachment; filename=CompensationReport_'.$date.'.csv');


	// $output = fopen('php://output', 'w');
	// fputcsv($output, array('EmployeeID',
	// 						'EmployeeID',
	// 						'PositionTitle',
	// 						'JobLevel',
	// 						'PayGroup',
	// 						'LaborType',
	// 						'HireDate',
	// 						'RegDate',
	// 						'Department',
	// 						// 'Section',
	// 						'Salary',
	// 						'Clothing Allowance',
	// 						'Laundry Allowance',
	// 						'Rice Allowance',
	// 						// 'Total Monthly Compensation',
	// 						// 'Annual Gross Income'
                            
							
    //                     )); 
	 
	// if (count($name23) > 0) {
	// 	foreach ($name23 as $row23) {
	// 		fputcsv($output, $row23);
	// 	}
	// }

	function getTrainingTaken($con, $idacct){
        $Qry=new Query();
        $Qry->table="tblaccountet";
        $Qry->selected="*";
        $Qry->fields="id>0 AND idacct='".$idacct."' AND type='training'";
        $rs=$Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>=1){
            while($row=mysqli_fetch_array($rs)){
                $data[] = array(
                    'training_taken'=>$row['et'],
                    'location'	    =>$row['location'],
                    'date'	        =>$row['date']
                );
            }
            return $data;
        }
        return null;
	}
	
print $return;	
mysqli_close($con);


?>