<?php
$data = array( 
    "empid" 			=> '',
	"empidlngth"		=> '7',
	"idaccttype"		=> '',
	"idemptype"  		=> '2',
	"fname"				=> '',
    "lname" 			=> '',
    "mname"  			=> '',
    "suffix"  			=> '',
	"nickname"			=> '',
		
	"addr_st"			=> '',
	"addr_area"			=> '',
	"addr_city"			=> '',
	"addr_prov"			=> '',
	"addr_code"			=> '',
		
	"per_st"			=> '',
	"per_area"			=> '',
	"per_city"			=> '',
	"per_prov"			=> '',
	"per_code"			=> '',
		
	"sex"				=> 'M',	
	"email"				=> '',
	"cnumber"			=> '',	
	"bdate"				=> '',
	"bplace"			=> '',
	"citizenship"		=> '',
	"religion"			=> '',
	"civilstat"			=> '1',
	"spouse"			=> '',
	"idtin"				=> '',
	"idsss"				=> '',
	"idhealth"			=> '',
	"idibig"			=> '',
	"idtax"				=> 'Z',
	"idpayroll"			=> '',
	"prof_pic"			=> 'assets/images/undefined.webp?'.time(),
	"picFile"			=> array('',''),
		
	"salutation"		=> '',
	"isprivacy"			=> '1',
	"pnumber"			=> '',
	"emergency_number"	=> '',
	"emergency_name"	=> '',
	"sameaddress"		=> '1',
	"age"				=> '',
	"bloodtype"			=> '',	
	"height_inch"		=> '',
	"height_ft"			=> '',
	"weight_lbs"		=> '',
	"eyecolor"			=> '',
	"haircolor"			=> '',
	"skincolor"			=> '',
	"buildtype"			=> '',
	"idbank"			=> '1', //default
	"tin_date"			=> '',
	"sss_date"			=> '',
	"health_date"		=> '',
	"ibig_date"			=> '',
	"docs"				=> array("1"),
	"license_prc"		=> '',
	"license_drive"		=> '',
	"idpassport"		=> '',
	"pa"				=> array(
								array(
									"equi_tools"	=> '',
									"serial"		=> '',
									"asset"		=> '',
									"quantity"		=> '',
									"date_issued"	=> '',
									"date_returned"	=> ''
								)
						   )
);
$return = json_encode($data);
print $return;
?>