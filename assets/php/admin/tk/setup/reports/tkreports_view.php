 <?php 
require_once('../../../../activation.php');
require_once('../../../../classPhp.php');
$conn = new connector();
$con = $conn->connect();


$param = $_POST;
$return = null;	
$data  = array();
$pay_period = getPayPeriod($con);
$search='';	

//trappings sa date 
//  if ( $param['tkdatefrom'] >  $param['tkdateto']){

// 	$data = array( 
// 		"draw"=> $param['draw'],
// 		"recordsTotal"=> mysqli_num_rows($rs),
// 		"recordsFiltered"=> mysqli_num_rows($rs),
// 		"qry"=>$Qry->fields,
// 		"data"=>array()
// 	);
// 	$return =  json_encode($data);


//  }

	//sa table function, search
// 	if( !empty( $param['tkselect'] ) ){ 
// 		if($param['tkselect']!="INACTIVE") {
// 			$param['tkselect']="ACTIVE";
// 		}
// 		$search=$search." AND EmploymentType = '".$param['tkselect']."' "; 
// 	}else{
// 		$data = array( 
// 			"draw"=> $param['draw'],
// 			"recordsTotal"=> 0,
// 			"recordsFiltered"=> 0,
		
// 			"data"=>array()
// 		);
// 		$return =  json_encode($data);
// 		print $return;
// 		mysqli_close($con);
// 		return;
// 	}

if( !empty( $param['search_acct'] ) ){ $search=$search." AND de.id = '".$param['search_acct']."' "; }
if( !empty($param['tkdatefrom']) && empty($param['tkdateto'])){
    $search=$search." AND hdate BETWEEN DATE('".$param['tkdatefrom']."') AND DATE('".$param['tkdatefrom']."') ";
}
if( !empty($param['tkdatefrom']) && !empty($param['tkdateto']) ){
    $search=$search." AND hdate BETWEEN DATE('".$param['tkdatefrom']."') AND DATE('".$param['tkdateto']."') ";   
}


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
    $search.=" AND de.idunit in (".$ids.") "; 
}

// if( !empty( $param['alldepartment'] ) ){ $search=$search." AND dept_name = '".$param['alldepartment']."' "; }
// if( !empty( $param['tkgender'] ) ){ $search=$search." AND Gender = '".$param['tkgender']."' "; }
// if( !empty( $param['position'] ) ){ $search=$search." AND position_title = '".$param['position']."' "; }
// if( !empty( $param['joblocation'] ) ){ $search=$search." AND joblocation = '".$param['joblocation']."' "; }
// if( !empty( $param['paygroup'] ) ){ $search=$search." AND paygroup = '".$param['paygroup']."' "; }
// if( !empty( $param['labortype'] ) ){ $search=$search." AND LaborType = '".$param['labortype']."' "; }


$where = $search; 
if( $param['order'][0]['column'] !='' ){
	$arrCols = array("empid",
		            "empname",
		            "",
		            "",
					"hdate",
					"pay_grp",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",
					"",);
					
					
    $search=$search." GROUP BY dt.empID ORDER BY ". $arrCols[$param['order'][0]['column']] ." ".$param['order'][0]['dir'];
}

if( $param['length'] !='' ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}

	// $Qry = new Query();	
	// $Qry->table     = "vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID=de.id";
	// $Qry->selected  = "dt.date,de.id, de.empid, de.empname, de.business_unit, de.post, dt.work_date,
	// dt.idshift, dt.shift_status, dt.shiftin, dt.shiftout, dt.in, dt.out, dt.reghrs,
	// dt.absent, dt.late, dt.ut, dt.reg_ot, dt.rd_ot, dt.spcl_hol, dt.legal_hol,
	// dt.spcl_rd, dt.legal_rd, dt.reg_ot8, dt.rd_ot8, dt.spcl_hol8";
	// $Qry->fields    = "(dt.date is not null OR dt.date <> '') AND ('".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."')".$search;
	// $rs = $Qry->exe_SELECT($con);
	// $recFiltered = getTotalRows($con,$where);

	$Qry = new Query();	
	$Qry->table     = "vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID=de.id";
	$Qry->selected  = "de.idunit,de.id,de.empid, de.empname, de. business_unit, de.post, de.hdate, de.pay_grp,
	SUM(dt.acthrs) AS acthrs, SUM(dt.late) AS late, SUM(dt.ut) AS ut, SUM(dt.absent) AS absent,
	SUM(CASE WHEN dt.idleave = 6 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totcompen,
	SUM(CASE WHEN dt.idleave = 1 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totsl,
	SUM(CASE WHEN dt.idleave = 2 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totvl,
	SUM(CASE WHEN dt.idleave = 9 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totemer,
	SUM(CASE WHEN dt.idleave = 10 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totmagnacar,
	SUM(CASE WHEN dt.idleave = 5 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totpater,
	SUM(CASE WHEN dt.idleave = 4 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totsolo,
	SUM(CASE WHEN dt.idleave = 11 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totbereav,
	SUM(CASE WHEN dt.idleave = 12 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totmater,
	SUM(dt.reg_ot) AS reg_ot, SUM(dt.rd_ot) AS rd_ot, SUM(dt.spcl_hol) AS spcl_hol, SUM(dt.spcl_rd) AS spcl_rd, SUM(dt.legal_hol) AS legal_hol, SUM(dt.legal_rd) AS legal_rd,
	SUM(dt.reg_np) AS reg_np, SUM(dt.rd_np) AS rd_np, SUM(dt.spcl_rd_np) AS spcl_rd_np, SUM(dt.spcl_np) AS spcl_np, SUM(dt.legal_np) AS legal_np,
	SUM(dt.rd_ot8) rd_ot8, SUM(dt.spcl_rd8) AS spcl_rd8, SUM(dt.legal_rd8) AS legal_rd8, SUM(dt.spcl_hol8) AS spcl_hol8, SUM(dt.legal_hol8) AS legal_hol8";
	$Qry->fields    = "dt.work_date IS NOT NULL" .$search;
	$rs = $Qry->exe_SELECT($con);
	$recFiltered = getTotalRows($con,$where);

	if(mysqli_num_rows($rs)>= 1){
		$data = array( 
			"draw"=> $param['draw'],
			"recordsTotal"=> mysqli_num_rows($rs),
			"recordsFiltered"=> $recFiltered,
			"qry"=>$Qry->fields,
			"data"=>array()
		);

		$total_acthrs 	  = 0;
		$total_late 	  = 0;
		$total_ut 		  = 0;
		$total_absent 	  = 0;
		$total_regot 	  = 0;
		$total_rdot 	  = 0;
		$total_spcl_hol   = 0;
		$total_spcl_rd 	  = 0;
		$total_legal_hol  = 0;
		$total_legal_rd   = 0;
		$total_reg_np 	  = 0;
		$total_rd_np 	  = 0;
		$total_spcl_rd_np = 0;
		$total_spcl_np    = 0;
		$total_legal_np   = 0;
		$total_rd_ot8 	  = 0;
		$total_spcl_rd8   = 0;
		$total_legal_rd8  = 0;
		$total_spcl_hol8  = 0;
		$total_legal_hol8 = 0;
		$total_totcompen  = 0;
		$total_totsl 	  = 0;
		$total_totvl 	  = 0;
		$total_totemer 	  = 0;
		$total_totmagnacar= 0;
		$total_totpater   = 0;
		$total_totsolo 	  = 0;
		$total_totbereav  = 0;
		$total_totmater   = 0;
		

		while($row=mysqli_fetch_array($rs)){
			//Format date for display
			$date_format=date_create($row['hdate']);

			$total_acthrs 		= round($total_acthrs + $row['acthrs'],2);
			$total_late   		= $total_late + $row['late'];
			$total_ut  	  		= $total_ut + $row['ut'];
			$total_absent 		= $total_absent + $row['absent'];
			$total_regot 		= $total_regot + $row['reg_ot'];
			$total_rdot 		= $total_rdot + $row['rd_ot'];
			$total_spcl_hol 	= $total_spcl_hol + $row['spcl_hol'];
			$total_spcl_rd  	= $total_spcl_rd + $row['spcl_rd'];
			$total_legal_hol 	= $total_legal_hol + $row['legal_hol'];
			$total_legal_rd 	= $total_legal_rd + $row['legal_rd'];
			$total_reg_np 		= $total_reg_np + $row['reg_np'];
			$total_rd_np 		= $total_rd_np + $row['rd_np'];
			$total_spcl_rd_np 	= $total_spcl_rd_np + $row['spcl_rd_np'];
			$total_spcl_np 		= $total_spcl_np + $row['spcl_np'];
			$total_legal_np 	= $total_legal_np + $row['legal_np'];
			$total_rd_ot8 		= $total_rd_ot8 + $row['rd_ot8'];
			$total_spcl_rd8 	= $total_spcl_rd8 + $row['spcl_rd8'];
			$total_legal_rd8 	= $total_legal_rd8 + $row['legal_rd8'];
			$total_spcl_hol8 	= $total_spcl_hol8 + $row['spcl_hol8'];
			$total_legal_hol8 	= $total_legal_hol8 + $row['legal_hol8'];
			$total_totcompen 	= $total_totcompen + $row['totcompen'];
			$total_totsl 		= $total_totsl + $row['totsl'];
			$total_totvl 		= $total_totvl + $row['totvl'];
			$total_totemer 		= $total_totemer + $row['totemer'];
			$total_totmagnacar  = $total_totmagnacar + $row['totmagnacar'];
			$total_totpater 	= $total_totpater + $row['totpater'];
			$total_totsolo 		= $total_totsolo + $row['totsolo'];
			$total_totbereav 	= $total_totbereav + $row['totbereav'];
			$total_totmater 	= $total_totmater + $row['totmater'];

			$data["data"][] = array(
				"id" 				=> $row['id'],
				"empid" 			=> $row['empid'],
				"empname"			=> ucfirst ($row['empname']),
				"deptname"			=> ucfirst ($row['business_unit']),
				"positiontitle"		=> ucfirst ($row['post']),
				"hdate"    			=> date_format($date_format,"m/d/Y"),
				"pay_grp"    		=> $row['pay_grp'],
				"acthrs"			=> round($row['acthrs'],2),
				"late"				=> $row['late'],
				"ut"				=> $row['ut'],
				"absent"			=> $row['absent'],
				"regot"				=> $row['reg_ot']? $row['reg_ot']:0,
				"rdot"				=> $row['rd_ot']? $row['rd_ot']:0,
				"spclhol"			=> $row['spcl_hol']? $row['spcl_hol']:0,	
				"spclrd"			=> $row['spcl_rd']? $row['spcl_rd']:0,		
				"legalhol"			=> $row['legal_hol']? $row['legal_hol']:0,	
				"legalrd"			=> $row['legal_rd']? $row['legal_rd']:0,	
				"regnp"				=> $row['reg_np']? $row['reg_np']:0,		
				"rdnp"				=> $row['rd_np']? $row['rd_np']:0,			
				"spclrdnp"			=> $row['spcl_rd_np']? $row['spcl_rd_np']:0,
				"spclnp"			=> $row['spcl_np']? $row['spcl_np']:0,		
				"legalnp"			=> $row['legal_np']? $row['legal_np']:0,	
				"rdot8"				=> $row['rd_ot8']? $row['rd_ot8']:0,		
				"spclrd8"			=> $row['spcl_rd8']? $row['spcl_rd8']:0,	
				"legalrd8"			=> $row['legal_rd8']? $row['legal_rd8']:0,	
				"spclhol8"			=> $row['spcl_hol8']? $row['spcl_hol8']:0,	
				"legalhol8"			=> $row['legal_hol8']? $row['legal_hol8']:0,
				"totcompen"			=> $row['totcompen']? $row['totcompen']:0,	
				"totsl"				=> $row['totsl']? $row['totsl']:0,			
				"totvl"				=> $row['totvl']? $row['totvl']:0,			
				"totemer"			=> $row['totemer']? $row['totemer']:0,		
				"totmagnacar"		=> $row['totmagnacar']? $row['totmagnacar']:0,
				"totpater"			=> $row['totpater']? $row['totpater']:0,	
				"totsolo"			=> $row['totsolo']? $row['totsolo']:0,		
				"totbereav"			=> $row['totbereav']? $row['totbereav']:0,	
				"totmater"			=> round($row['totmater'],2)? $row['totmater']:0,
				"total_acthrs" 		=> $total_acthrs,
				"total_late"		=> $total_late,
				"total_ut"			=> $total_ut,
				"total_absent"		=> $total_absent,
				"total_regot"		=> $total_regot,	
				"total_rdot"		=> $total_rdot,	
				"total_spcl_hol"	=> $total_spcl_hol, 	
				"total_spcl_rd"		=> $total_spcl_rd,	
				"total_legal_hol"	=> $total_legal_hol, 	
				"total_legal_rd"	=> $total_legal_rd, 	
				"total_reg_np"		=> $total_reg_np, 		
				"total_rd_np"		=> $total_rd_np, 		
				"total_spcl_rd_np"	=> $total_spcl_rd_np, 	
				"total_spcl_np"		=> $total_spcl_np,		
				"total_legal_np"	=> $total_legal_np,
				"total_rd_ot8"		=> $total_rd_ot8, 		
				"total_spcl_rd8"	=> $total_spcl_rd8, 	
				"total_legal_rd8"	=> $total_legal_rd8, 	
				"total_spcl_hol8"	=> $total_spcl_hol8, 	
				"total_legal_hol8"	=> $total_legal_hol8, 	
				"total_totcompen"	=> $total_totcompen, 	
				"total_totsl"		=> $total_totsl, 		
				"total_totvl"		=> $total_totvl, 		
				"total_totemer"		=> $total_totemer, 		
				"total_totmagnacar"	=> $total_totmagnacar,
				"total_totpater"	=> $total_totpater,
				"total_totsolo"		=> $total_totsolo,		
				"total_totbereav"	=> $total_totbereav,
				"total_totmater"	=> round($total_totmater,2),
				
				
			);
		}		
		$return = json_encode($data);
	}else {
		$data = array( 
			"draw"=> $param['draw'],
			"recordsTotal"=> mysqli_num_rows($rs),
			"recordsFiltered"=> mysqli_num_rows($rs),
			"qry"=>$Qry->fields,
			"data"=>array()
		);
		$return =  json_encode($data);
	}


print $return;
mysqli_close($con);


function getTotalRows($con,$search){
	$Qry = new Query();	
	$Qry->table     = "vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID=de.id";
	$Qry->selected  = "de.idunit,de.id,de.empid, de.empname, de. business_unit, de.post, de.hdate, de.pay_grp,
	SUM(dt.acthrs) AS acthrs, SUM(dt.late) AS late, SUM(dt.ut) AS ut, SUM(dt.absent) AS absent,
	SUM(CASE WHEN dt.idleave = 6 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totcompen,
	SUM(CASE WHEN dt.idleave = 1 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totsl,
	SUM(CASE WHEN dt.idleave = 2 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totvl,
	SUM(CASE WHEN dt.idleave = 9 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totemer,
	SUM(CASE WHEN dt.idleave = 10 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totmagnacar,
	SUM(CASE WHEN dt.idleave = 5 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totpater,
	SUM(CASE WHEN dt.idleave = 4 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totsolo,
	SUM(CASE WHEN dt.idleave = 11 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totbereav,
	SUM(CASE WHEN dt.idleave = 12 AND leavestat = 1 THEN dt.leavehrs ELSE 0 END) AS totmater,
	SUM(dt.reg_ot) AS reg_ot, SUM(dt.rd_ot) AS rd_ot, SUM(dt.spcl_hol) AS spcl_hol, SUM(dt.spcl_rd) AS spcl_rd, SUM(dt.legal_hol) AS legal_hol, SUM(dt.legal_rd) AS legal_rd,
	SUM(dt.reg_np) AS reg_np, SUM(dt.rd_np) AS rd_np, SUM(dt.spcl_rd_np) AS spcl_rd_np, SUM(dt.spcl_np) AS spcl_np, SUM(dt.legal_np) AS legal_np,
	SUM(dt.rd_ot8) rd_ot8, SUM(dt.spcl_rd8) AS spcl_rd8, SUM(dt.legal_rd8) AS legal_rd8, SUM(dt.spcl_hol8) AS spcl_hol8, SUM(dt.legal_hol8) AS legal_hol8";
	$Qry->fields    = "dt.work_date IS NOT NULL  ".$search." GROUP BY dt.empID";	
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);


}





?>