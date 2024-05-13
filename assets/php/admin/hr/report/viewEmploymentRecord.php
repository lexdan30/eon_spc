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
$Qry->fields    = "id > 0";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_array($rs)){
	
		// $address = '';
		// if( !empty( $row['addr_st'] ) ){
		// 	$address = $address .  $row['addr_st'] . ',';
		// }
		// if( !empty( $row['addr_area'] ) ){
		// 	$address = $address .  $row['addr_area'] . ',';
		// }
		// if( !empty( $row['addr_city'] ) ){
		// 	$address = $address .  $row['addr_city'] . ',';
		// }
		// if( !empty( $row['addr_prov'] ) ){
		// 	$address = $address .  $row['addr_prov'] . ',';
		// }
		// if( !empty( $row['addr_code'] ) ){
		// 	$address = $address .  $row['addr_code'] . ',';
		// }
		// $address = substr($address,0, strlen($address)-1);
		
		
		//Get the current UNIX timestamp.
		$now = time();
		 
		//Get the timestamp of the person's date of birth.
		$dob = strtotime( $row['bdate'] );
		 
		//Calculate the difference between the two timestamps.
		$difference = $now - $dob;
		 
		//There are 31556926 seconds in a year.
		$age = floor($difference / 31556926);


		//Years in Service
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
		
        $data = array( 
            "id"        	        => ucwords(strtolower($row['id'])),
            "empid"			        => ucwords(strtolower($row['empid'])),
			"fname" 		        => (($row['fname'])),
			"lname" 		        => (($row['lname'])),
			"mname" 		        => (($row['mname'])),
            "suffix" 		        => ucwords(strtolower($row['suffix'])),
            "empname" 		        => (($row['empname'])),
            "nickname" 		        => (($row['nickname'])),	
			"position_code" 	    => ucwords($row['jobcode']),
			"position_title" 	    => ucwords(strtolower($row['post'])),
			"joblvl" 	            => ucwords($row['joblvl']),
			"pay_grp"			    => ucwords($row['pay_grp']),
			"pay_stat"			    => ucwords($row['pay_status']),
			"labor_type"		    => ucwords($row['labor_type']),
			"hire_date"			    => $row['hdate'],
			"reg_date"		        => $row['rdate'],
            "separation_date"		=> $row['sdate'],
            "nos_yrs"		        => $yrservice,
			// "organization"		=> ucwords(strtolower($row['organization'])),
			// "office"	            => ucwords(strtolower($row['office'])),
			// "plant"		        => ucwords(strtolower($row['plant'])),
			// "division"		    => ucwords(strtolower($row['division'])),
			"department"	        => ucwords(strtolower($row['business_unit'])),
			// "department_code"    => ucwords(strtolower($row['spouse'])),
			// "section"			=> $row['idtin'],
			// "unit"			    => $row['idsss'],
			// "sub_unit"		    => $row['idhealth'],
			// "line"		        => $row['idibig'],
			"superior"		        => $row['superior'],
			// "manager"			=> $row['idtax'],
            "shift"		            => $row['wshift_name'],
			// "exempt_code"	    => ucwords(strtolower($row['labor_type'])),
			"sss"	                => $row['idsss'],
			"pagibig"	            => $row['idibig'],
			"tin"	                => $row['idtin'],
			"philhealth"			=> $row['idhealth'],
			"payroll"	            => $row['idpayroll'],
			"gender"			    => ucwords($row['sex']),
			"employment_type"		=> ucwords(strtolower($row['etype'])),
			"employment_status"	    => ucwords(strtolower($row['emp_status'])),

			
        );
    }
}

$return = json_encode($data);
print $return;
mysqli_close($con);
?>