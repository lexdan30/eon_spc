<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "*";
$Qry->fields    = "id='".$param->id."'";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_array($rs)){
		$path = 'assets/images/undefined.webp';
		if( !empty( $row['pic'] ) ){
			$path = 'assets/php/admin/hr/employee/pix/'.$row['pic'].'?'.time();
		}
		
		$address = '';
		if( !empty( $row['addr_st'] ) ){
			$address = $address .  $row['addr_st'] . ',';
		}
		if( !empty( $row['addr_area'] ) ){
			$address = $address .  $row['addr_area'] . ',';
		}
		if( !empty( $row['addr_city'] ) ){
			$address = $address .  $row['addr_city'] . ',';
		}
		if( !empty( $row['addr_prov'] ) ){
			$address = $address .  $row['addr_prov'] . ',';
		}
		if( !empty( $row['addr_code'] ) ){
			$address = $address .  $row['addr_code'] . ',';
		}
		$address = substr($address,0, strlen($address)-1);
		
		$address2 = '';
		if( !empty( $row['per_st'] ) ){
			$address2 = $address2 .  $row['per_st'] . ',';
		}
		if( !empty( $row['per_area'] ) ){
			$address2 = $address2 .  $row['per_area'] . ',';
		}
		if( !empty( $row['per_city'] ) ){
			$address2 = $address2 .  $row['per_city'] . ',';
		}
		if( !empty( $row['per_prov'] ) ){
			$address2 = $address2 .  $row['per_prov'] . ',';
		}
		if( !empty( $row['per_code'] ) ){
			$address2 = $address2 .  $row['per_code'] . ',';
		}
		$address2 = substr($address2,0, strlen($address2)-1);
		
		
		//Get the current UNIX timestamp.
		$now = time();
		 
		//Get the timestamp of the person's date of birth.
		$dob = strtotime( $row['bdate'] );
		 
		//Calculate the difference between the two timestamps.
		$difference = $now - $dob;
		 
		//There are 31556926 seconds in a year.
		$age = floor($difference / 31556926);



		$date1 = new DateTime(SysDate());
		$date2 = new DateTime($row['hdate']);
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
		
		$edubackground = [];

        $Qryeb = new Query();	
		$Qryeb->table ="tblaccountedubg";
		$Qryeb->selected ="*";
		$Qryeb->fields 	="idacct='".$param->id."'"; 
        $rseb = $Qryeb->exe_SELECT($con);	
        if(mysqli_num_rows($rseb)>= 1){
			while($roweb=mysqli_fetch_array($rseb)){
				$edubackground[] = array(
					"id"	 		=>	$roweb['id'],
					"attainment" 	=>	$roweb['attainment'],
					"school" 		=>	$roweb['school'],
					"dfrom" 		=>	$roweb['dfrom'],
					"dto" 	    	=>	$roweb['dto'],
				
				);
			}
        }

        $emphistory = [];

        $Qryeh = new Query();	
		$Qryeh->table ="tblaccountemphis";
		$Qryeh->selected ="*";
		$Qryeh->fields 	="idacct='".$param->id."'"; 
        $rseh = $Qryeh->exe_SELECT($con);	
        if(mysqli_num_rows($rseh)>= 1){
			while($roweh=mysqli_fetch_array($rseh)){
				$emphistory[] = array(
					"id"	 		=>	$roweh['id'],
					"company"   	=>	$roweh['company'],
					"position" 		=>	$roweh['position'],
					"dfrom" 		=>	$roweh['dfrom'],
					"dto" 	    	=>	$roweh['dto'],
	
				);
			}
        }


        $examtaken = [];

        $Qryet = new Query();	
		$Qryet->table ="tblaccountet";
		$Qryet->selected ="*";
		$Qryet->fields 	="idacct='".$param->id."' AND type='exam'"; 
        $rset = $Qryet->exe_SELECT($con);	
        if(mysqli_num_rows($rset)>= 1){
			while($rowet=mysqli_fetch_array($rset)){
				$examtaken[] = array(
					"id"	 		=>	$rowet['id'],
					"et"   	        =>	$rowet['et'],
					"location" 		=>	$rowet['location'],
					"date" 		    =>	$rowet['date'],
				);
			}
        }

        $trainingtaken = [];

        $Qrytt = new Query();	
		$Qrytt->table ="tblaccountet";
		$Qrytt->selected ="*";
		$Qrytt->fields 	="idacct='".$param->id."' AND type='training'"; 
        $rstt = $Qrytt->exe_SELECT($con);	
        if(mysqli_num_rows($rstt)>= 1){
			while($rowtt=mysqli_fetch_array($rstt)){
				$trainingtaken[] = array(
					"id"	 		=>	$rowtt['id'],
					"et"   	        =>	$rowtt['et'],
					"location" 		=>	$rowtt['location'],
					"date" 		    =>	$rowtt['date'],
				);
			}
        }
		
        $data[] = array( 
            "id"        	=> ucwords(strtolower($row['id'])),
            "empid"			=> ucwords(strtolower($row['empid'])),
			"orig_empid"	=> ucwords(strtolower($row['empid'])),	
			"type"			=> ucwords(strtolower($row['type'])),	
			"etype"			=> ucwords(strtolower($row['etype'])),
			"fname" 		=> (($row['fname'])),
			"lname" 		=> (($row['lname'])),
			"mname" 		=> (($row['mname'])),
			"suffix" 		=> $row['suffix'],			
			"addr_st" 		=> ucwords(strtolower($row['addr_st'])),
			"addr_area" 	=> ucwords(strtolower($row['addr_area'])),
			"addr_city" 	=> ucwords(strtolower($row['addr_city'])),
			"addr_prov" 	=> ucwords(strtolower($row['addr_prov'])),
			"addr_code" 	=> ucwords(strtolower($row['addr_code'])),
			"per_st" 		=> ucwords(strtolower($row['per_st'])),
			"per_area" 		=> ucwords(strtolower($row['per_area'])),
			"per_city" 		=> ucwords(strtolower($row['per_city'])),
			"per_prov" 		=> ucwords(strtolower($row['per_prov'])),
			"per_code" 		=> ucwords(strtolower($row['per_code'])),
			"addr"			=> (($address)),
			"per"			=> (($address2)),
			"sex"			=> $row['sex'],
			"sexstr"		=> ucwords(strtolower($row['sexstr'])),
			"email"			=> $row['email'],
			"cnumber"		=> $row['cnumber'],
			"bdate"			=> $row['bdate'],
			"bplace"		=> ucwords(strtolower($row['bplace'])),
			"citizenship"	=> ucwords(strtolower($row['citizenship'])),
			"religion"		=> ucwords(strtolower($row['religion'])),
			"civilstat"		=> $row['civilstat'],
			"civil_status"	=> ucwords(strtolower($row['civil_status'])),
			"spouse"		=> ucwords(strtolower($row['spouse'])),
			"idtin"			=> $row['idtin'],
			"idsss"			=> $row['idsss'],
			"idhealth"		=> $row['idhealth'],
			"idibig"		=> $row['idibig'],
			"idpayroll"		=> $row['idpayroll'],
			"idtax"			=> $row['idtax'],
			"taxname"		=> $row['taxname'],
			"epicFile"		=> '',
			"eprof_pic"		=> $path,
			"labor_type"	=> ucwords(strtolower($row['labor_type'])),
			"business_unit"	=> ucwords(strtolower($row['business_unit'])),
			"emp_status"	=> ucwords(strtolower($row['emp_status'])),
			"post"			=> ucwords(strtolower($row['post'])),
			"wshift_name"	=> ucwords(strtolower($row['wshift_name'])),
			"sched"			=> ucwords(strtolower($row['sched'])),
			"job_loc"		=> ucwords(strtolower($row['job_loc'])),
			"job_region"	=> $row['job_region'],
			"pay_grp"		=> ucwords(strtolower($row['pay_grp'])),
			"pay_status"	=> $row['pay_status'],
			"pay_revenue"	=> $row['pay_revenue'],
			"pay_release"	=> $row['pay_release'],
			"dependent"		=> ucwords(strtolower($row['dependent'])),
			"salary"		=> number_format($row['salary'],2),
			"prev_employer"	=> ucwords(strtolower($row['prev_employer'])),
			"hdate"			=> $row['hdate'],
			"rdate"			=> $row['rdate'],
			"sdate"			=> $row['sdate'],
			"superior"		=> ucwords(strtolower($row['superior'])),
			"nos_yrs"		=> $yrservice,
			"age"			=> $age,

			"new_fname"			=> '',
			"new_lname"			=> '',
			"new_mname"			=> '',
			"new_suffix"		=> '',
			"new_nickname"		=> '',
			"new_mari_stat"		=> '',
			"new_emer_name"		=> '',
			"new_emer_cont"		=> '',

			"new_add_st"		=> '',
			"new_add_area"		=> '',
			"new_add_city"		=> '',
			"new_add_prov"		=> '',
			"new_add_code"		=> '',

			"new_pnum"			=> '',
			"new_fax_num"		=> '',
			"new_mnum"			=> '',
			"new_dependent"		=> array("idacct"=>array(),"new_dependent_name"=>array(''),"new_dependent_bdate"=>array(''),"new_dependent_age"=>array('')),

			"salutaion"		=> $row['salutation'],
			"nickname"		=> $row['nickname'],
			"pnumber"		=> $row['pnumber'],
			"fnumber"		=> $row['fnumber'],
			"bloodtype"		=> $row['bloodtype'],
			"idpassport"	=> $row['idpassport'],
			"license_drive"	=> $row['license_drive'],
			"license_prc"	=> $row['license_prc'],
			"emergency_name"=> ucwords(strtolower($row['emergency_name'])),
			"emergency_number"=>$row['emergency_number'],
            "eb"            => $edubackground,
            "eh"            => $emphistory,
            "et"            => $examtaken,
			"tt"            => $trainingtaken,
			"mari_stat"		=> getCivilStatus($con),
			"suffex"		=> getSuffix($con),
			"getdep"		=> getName ($con, $row['id'])
        );
    }
}

$return = json_encode($data);
print $return;
mysqli_close($con);


function getName($con, $accountid){
	$Qry=new Query(); 
	$data=array();
    $Qry->table="tblacctdependent";
    $Qry->selected="*";
    $Qry->fields="idacct='".$accountid."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){

		//Get the current UNIX timestamp.
		$now = time();
		 
		//Get the timestamp of the person's date of birth.
		$dob = strtotime( $row['birthday'] );
		 
		//Calculate the difference between the two timestamps.
		$difference = $now - $dob;
		 
		//There are 31556926 seconds in a year.
		$age = floor($difference / 31556926);


			$data[] = array( 
				"id"        => $row['id'],
				"name" 		=> $row['name'],
				"bdate"	    => $row['birthday'],
				"age"		=>$age				
			);
        }
    }
    return $data;
}




?>