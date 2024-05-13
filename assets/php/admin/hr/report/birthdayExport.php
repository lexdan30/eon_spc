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

	if( !empty( $param['emp'] ) ){ $search=$search." AND empid like 	'%".$param['emp']."%' "; }
	//Search Department
    if( !empty( $param['department'] ) ){
        $arr_id = array();
        $arr 	= getHierarchy($con,$param['department']);
        array_push( $arr_id, $param['department']);
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
	if( !empty( $param['search_age'] ) ){ $search=$search." AND age = '".$param['search_age']."' "; }

	//BIRTHDATE SEARCH
	if( !empty( $param['birthdate_from']) && empty( $param['birthdate_to'] )){
		$search=$search." AND bdate BETWEEN DATE('".$param['birthdate_from']."') AND DATE('".$param['birthdate_from']."') ";
	}
	
	if( !empty( $param['birthdate_from'] ) && !empty( $param['birthdate_to'] ) ){
		$search=$search." AND bdate BETWEEN DATE('".$param['birthdate_from']."') AND DATE('".$param['birthdate_to']."') ";
	}

	$name22 = array();
	$Qry = new Query();	
	$Qry->table     = "vw_dataemployees";
	$Qry->selected  = "*";
	$Qry->fields    = "id>0 ".$search;
	$where = $Qry->fields;	
	$rs = $Qry->exe_SELECT($con);
	
	if(mysqli_num_rows($rs)>= 1){
	
		while($row=mysqli_fetch_array($rs)){
			
			//Format date for display
			$bday_date_format=date_create($row['bdate']);

            $name22[] = array(
					// ""  => $row['id'],
					$row['empid'],
					(( $row['empname'])),
					date_format($bday_date_format,"m/d/Y"),
					ucwords(strtolower(utf8_decode($row['business_unit'])))
			);
		}

	}
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=BirthdayCelebrantsReports'.$date.'.csv');
	$output = fopen('php://output','w');
	// fputcsv($output,array($param['company']));
	fputcsv($output, array("New World Makati Hotel"));
	fputcsv($output,array("Birthday Celebrants Report"));
	if( !empty($param['filterby'])){
		fputcsv($output,array($param['filterby']));
	}
	fputcsv($output,array("Export Generated on ".SysDatePadLeft().' '.SysTime() ));
	fputcsv($output,array(
						'Employee ID', 
						'Employee NAME',
						'BIRTHDAY',
						'DEPARTMENT NAME'
	));
	if (count($name22) > 0) {
		foreach ($name22 as $row22) {
			fputcsv($output, $row22);
		}
	}
?>