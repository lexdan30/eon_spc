<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

$Qry 			= new Query();	
$Qry->table     = "tblaccount AS a LEFT JOIN vw_dataassign AS b ON a.id = b.idacct";
$Qry->selected  = " a.*,
					b.id AS idassign,
					b.idacct ,
					b.idpos ,
					b.idunit ,
					b.idlabor ,
					b.idsuperior ,
					b.empstat ,
					b.idloc ,
					b.wshift ,
					b.schedtype ,
					b.hdate ,
					b.rdate ,
					b.sdate ,
					b.salary,
					b.idemptype ,
					b.idlvlscale,
					b.idlvl ,
					b.lvl ,
					b.acct_name ,
					b.pos ,
					b.unit ,
					b.product,
					b.customer,
					b.labor ,			
					b.supperior ,
					b.estat ,
					b.loc ,
					b.idregion,
					b.region,
					b.workshift ,
					b.schedule ,
					b.idpaygrp ,
					b.paygroup ,
					b.idpaystat ,
					b.paystatus ,
					b.idrevenue ,
					b.payrevenue ,
					b.dependent ,
					b.idrelease ,
					b.payrelease ,
					b.prev_employer,
					b.daysmonth,
					b.riceallowance,
					b.clothingallowance,
					b.laundryallowance,
					b.basicpay_type,
					b.absences_type,
					b.lates_type,
					b.undertime_type,
					b.idcba,
					b.sss_type,		
					b.ibig_type,		
					b.health_type,	
					b.sss_amt,		
					b.ibig_amt,		
					b.health_amt,
					b.sss_deduct1,		
					b.sss_deduct2,
					b.ibig_deduct1,		
					b.ibig_deduct2,
					b.health_deduct1,		
					b.health_deduct2,
					b.rev_deduct1,		
					b.rev_deduct2,
					b.isprevemp,
					b.rice_method,
					b.clothing_method,
					b.laundry_method,
					b.gross,
					b.taxable,	
					b.nontax,	
					b.addex,		
					b.month13,	
					b.deminimis";
$Qry->fields    = "a.id='".$param->id."'";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_array($rs)){
		
		//primary pic
		$path = 'assets/images/undefined.webp';
		if( !empty( $row['pic'] ) ){
			$path = 'assets/php/admin/hr/employee/pix/'.$row['pic'].'?'.time();
		}
		
		//check if there are attachments
		$path_privacy 	= '';
		$path_marriage 	= '';
		$path_baranggay = '';
		$path_birth 	= '';
		$path_cedula 	= '';
		$path_diploma 	= '';
		$path_drugs 	= '';
		$path_contract 	= '';
		$path_labres	= '';
		$path_nbi 		= '';
		$path_bir 		= '';
		$docs			= array();
		$docs_file      = array();
		$Qryatt 			= new Query();	
		$Qryatt->table 		="tblaccountdoc";
		$Qryatt->selected 	="*";
		$Qryatt->fields 	="idacct='".$param->id."' ORDER BY iddoc ASC"; 
        $rsatt = $Qryatt->exe_SELECT($con);	
        if(mysqli_num_rows($rsatt)>= 1){
			while($rowatt=mysqli_fetch_array($rsatt)){
				$ok_add = false;
				if( (int)$rowatt['iddoc'] == 1 && (int)$row['isprivacy'] == 1 ){ //Data Privacy
					$path_privacy = $row['empid'] . '/' . $rowatt['doc'];
					$ok_add = true;
				}elseif( (int)$rowatt['iddoc'] == 2 && (int)$row['civilstat'] != 1 && (int)$row['civilstat'] != 3 ){ //Marriage Contract
					$path_marriage = $row['empid'] . '/' . $rowatt['doc'];
					$ok_add = true;
				}elseif( (int)$rowatt['iddoc'] == 3 ){ //Barangay Clearance
					$path_baranggay = $row['empid'] . '/' . $rowatt['doc'];
					$ok_add = true;
				}elseif( (int)$rowatt['iddoc'] == 4 ){ //Birth Certificate
					$path_birth = $row['empid'] . '/' . $rowatt['doc'];
					$ok_add = true;
				}elseif( (int)$rowatt['iddoc'] == 5 ){ //Cedula
					$path_cedula = $row['empid'] . '/' . $rowatt['doc'];
					$ok_add = true;
				}elseif( (int)$rowatt['iddoc'] == 6 ){ //Diploma
					$path_diploma = $row['empid'] . '/' . $rowatt['doc'];
					$ok_add = true;
				}elseif( (int)$rowatt['iddoc'] == 7 ){ //Drug Test Result
					$path_drugs = $row['empid'] . '/' . $rowatt['doc'];
					$ok_add = true;
				}elseif( (int)$rowatt['iddoc'] == 8 ){ //Employment Contract
					$path_contract =  $row['empid'] . '/' . $rowatt['doc'];
					$ok_add = true;
				}elseif( (int)$rowatt['iddoc'] == 9 ){ //Laboratory Result
					$path_labres = $row['empid'] . '/' . $rowatt['doc'];
					$ok_add = true;
				}elseif( (int)$rowatt['iddoc'] == 10 ){ //NBI
					$path_nbi = $row['empid'] . '/' . $rowatt['doc'];
					$ok_add = true;
				}elseif( (int)$rowatt['iddoc'] == 11 && (int)$row['isprevemp'] == 1 ){ //BIR2316
					$path_bir = $row['empid'] . '/' . $rowatt['doc'];
					$ok_add = true;
				}
				if( $ok_add ){
					array_push( $docs	  , $rowatt['iddoc'] );
					array_push( $docs_file, $row['empid'] . '/' . $rowatt['doc'] );
				}
			}
        }
		if( empty( $docs ) ){
			$docs 		= array("1");
			$docs_file  = array();
		}
		
		
		$otherbranch = array();
		
		$Qryaz = new Query();	
		$Qryaz->table ="tbldb";
		$Qryaz->selected ="*";
		$Qryaz->fields 	="id>0"; 
		$rsaz = $Qryaz->exe_SELECT($con);	
		if(mysqli_num_rows($rsaz)>= 1){
			while($rowaz=mysqli_fetch_array($rsaz)){
				$abled	= false;
				$checked= false;
				if( (int)$rowaz['isorig'] == 1 ){
					$abled	= true;
				}
				if( checkGlobalAcct($con, $rowaz['id'], $param->id) || (int)$rowaz['id'] == 1 ){
					$checked= true;
				}
				
				
				$otherbranch[] = array(
					"id"	 		=>	$rowaz['id'],
					"dbname" 		=>	$rowaz['dbname'],
					"company" 		=>	$rowaz['company'],
					"alias" 		=>	$rowaz['alias'],
					"isorig" 		=>	$rowaz['isorig'],
					"abled"			=>  $abled,
					"checked"		=>  $checked
				);
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
				$emphistory[]       = array(
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
				$examtaken[]        = array(
					"id"	 		=>	$rowet['id'],
					"issueorg"		=>	$rowet['issueorg'] ? $rowet['issueorg'] : '',
					"facilitator"	=>  $rowet['facilitator'] ? $rowet['facilitator'] : '',
					"et"   	        =>	$rowet['et'] ? $rowet['et'] : '',
					"location" 		=>	$rowet['location'] ? $rowet['location'] : '',
					"date" 	    	=>	$rowet['date'] ? $rowet['date'] : '',
				
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
				$trainingtaken[]        = array(
					"id"	 		=>	$rowtt['id'],
					"issueorg"		=>	$rowtt['issueorg'] ? $rowtt['issueorg'] : '',
					"facilitator"	=>  $rowtt['facilitator'] ? $rowtt['facilitator'] : '',
					"et"   	        =>	$rowtt['et'] ? $rowtt['et'] : '',
					"location" 		=>	$rowtt['location'] ? $rowtt['location'] : '',
					"date" 	    	=>	$rowtt['date'],
				
				);
			}
        }
		
		$propertyacc = [];
        $Qrypa 			= new Query();	
		$Qrypa->table 	="tblaccountpropacc";
		$Qrypa->selected="*";
		$Qrypa->fields 	="idacct='".$param->id."'"; 
        $rspa 			= $Qrypa->exe_SELECT($con);	
        if(mysqli_num_rows($rspa)>= 1){
			while($rowpa=mysqli_fetch_array($rspa)){
				$propertyacc[]        = array(
					"id"			=> $rowpa['id'],
					"equi_tools"	=> $rowpa['equi_tools'],
					"serial"		=> $rowpa['serial'],
					"quantity"		=> $rowpa['quantity'],
					"date_issued"	=> $rowpa['date_issued'],
					"date_returned"	=> $rowpa['date_returned'] ? $rowpa['date_returned'] : ''
				);
			}
        }else{
			$propertyacc[]        = array(
				"id"			=> '',
				"equi_tools"	=> '',
				"serial"		=> '',
				"quantity"		=> '',
				"date_issued"	=> '',
				"date_returned"	=> ''
			);
		}
		
		$ctr_dependent = 0;
		$dependents = array();
		if( (int)$row['dependent'] > 0 ){
			$Qrychild = new Query();	
			$Qrychild->table ="tblacctdependent";
			$Qrychild->selected ="*";
			$Qrychild->fields 	="idacct='".$row['id']."'"; 
			$rschild = $Qrychild->exe_SELECT($con);	
			if(mysqli_num_rows($rschild)>= 1){
				while($rowchild=mysqli_fetch_array($rschild)){
					$birthdate1  = new DateTime( $rowchild['birthday'] );
					$today1   	= new DateTime('today');
					$dependents[]        = array(
						"id"		=>  $rowchild['id'],
						"idacct"	=>	$rowchild['idacct'],
						"name"		=>	$rowchild['name'],
						"birthday"	=>	$rowchild['birthday'],
						"age"		=>  $birthdate1->diff($today1)->y
					);
				}
			}
			$ctr_dependent = mysqli_num_rows($rschild);
		}
		for( $x=$ctr_dependent; $x < 10; $x++ ){
			$dependents[$x]        = array(
				"id"		=>  '',
				"idacct"	=>	$row['id'],
				"name"		=>	'',
				"birthday"	=>	'',
				"age"		=>  ''
			);
		}
		
		
		$birthdate  = new DateTime( $row['bdate'] );
        $today   	= new DateTime('today');
		
		if( empty($row['idbank']) || (!empty($row['idbank']) && empty($row['idpayroll'])) ){
			$row['idrelease'] = '1';
			$row['payrelease']= 'Cash';
		}
		
        $data = array( 
            "id"        	=> $row['id'],
            "empid"			=> $row['empid'],
			"orig_empid"	=> $row['empid'],
			"idaccttype"	=> $row['idaccttype'],
			"idemptype" 	=> getLocalEmployeeType($con,$param->id),
			"salutation"	=> $row['salutation'],
			"fname" 		=> $row['fname'],
			"lname" 		=> $row['lname'],
			"mname" 		=> $row['mname'],
			"suffix" 		=> $row['suffix'] ? $row['suffix'] : '',
			"nickname"		=> $row['nickname'],
			"addr_st" 		=> $row['addr_st'],
			"addr_area" 	=> $row['addr_area'],
			"addr_city" 	=> $row['addr_city'],
			"addr_prov" 	=> $row['addr_prov'],
			"addr_code" 	=> $row['addr_code'],
			"per_st" 		=> $row['per_st'],
			"per_area" 		=> $row['per_area'],
			"per_city" 		=> $row['per_city'],
			"per_prov" 		=> $row['per_prov'],
			"per_code" 		=> $row['per_code'],
			"sameaddress"	=> $row['sameaddress'],	
			"sex"			=> $row['sex'],
			"email"			=> $row['email'],
			"cnumber"		=> $row['cnumber'],
			"bdate"			=> $row['bdate'],
			"bplace"		=> $row['bplace'],
			"age"			=> $birthdate->diff($today)->y,
			"citizenship"	=> $row['citizenship'],
			"religion"		=> $row['religion'],
			"civilstat"		=> $row['civilstat'],
			"path_marriage" => $path_marriage,
			"spouse"		=> $row['spouse'],
			"ospouse"		=> $row['spouse'],
			"idtin"			=> $row['idtin'],
			"tin_date"		=> $row['tin_date'],
			"idsss"			=> $row['idsss'],
			"sss_date"		=> $row['sss_date'],
			"idhealth"		=> $row['idhealth'],
			"health_date"	=> $row['health_date'],			
			"idibig"		=> $row['idibig'],
			"ibig_date"		=> $row['ibig_date'],
			"idbank"		=> $row['idbank'],
			"idpayroll"		=> $row['idpayroll'],
			"idtax"			=> $row['idtax'],
			"idpassport"	=> $row['idpassport'],
			"license_prc"	=> $row['license_prc'],
			"license_drive"	=> $row['license_drive'],
			"epicFile"		=> array(''),
			"eprof_pic"		=> $path,
			"pic_orig"		=> $path,
			"empidlngth"	=> '4',
			"iscorporate"	=> $row['iscorp'],
			"dbs"			=> $otherbranch,
			"eb"            => $edubackground,
            "eh"            => $emphistory,
            "et"            => $examtaken,
            "tt"            => $trainingtaken,
			"isprivacy"		=> $row['isprivacy'],
			"path_privacy"  => $path_privacy,
			"pnumber"		=> $row['pnumber'],
			"fnumber"		=> $row['fnumber'],
			"bloodtype"		=> $row['bloodtype'],
			"height_ft"		=> $row['height_ft'],
			"height_inch"	=> $row['height_inch'],
			"weight_lbs"	=> $row['weight_lbs'],
			"eyecolor"		=> $row['eyecolor'],
			"haircolor"		=> $row['haircolor'],
			"skincolor"		=> $row['skincolor'],
			"buildtype"		=> $row['buildtype'],
			"emergency_name"=> $row['emergency_name'],
			"emergency_number"=> $row['emergency_number'],
			"docs"			=> $docs,
			"docs_file"		=> $docs_file,
			
			
			
			"idassign"      => $row['idassign'],
            "idacct"		=> $row['id'],
			"idpos"			=> $row['idpos'],
			"idunit"		=> $row['idunit'],
			"idlabor"		=> $row['idlabor'],
			"idsuperior"	=> $row['idsuperior'],
			"empstat"		=> $row['empstat'],
			"orig_empstat"	=> $row['empstat'],
			"idloc"			=> $row['idloc'],
			"idregion"		=> $row['idregion'],
			"region"		=> $row['region'],
			"wshift"		=> $row['wshift'],
			"ogshift"		=> $row['wshift'],
			"schedtype"		=> !$row['schedtype'] ? '1' : $row['schedtype'],
			"hdate"			=> $row['hdate'],
			"rdate"			=> $row['rdate'],
			"sdate"			=> $row['sdate'],
			"salary"		=> number_format($row['salary'],2),
			"product"		=> $row['product'],
			"customer"		=> $row['customer'],
			//"idemptype"		=> $row['idemptype'],
			"idlvl"			=> $row['idlvl'],
			"lvl"			=> $row['lvl'],
			"idlvlscale"	=> $row['idlvlscale'],
			"acct_name"		=> $row['acct_name'],
			"pos"			=> $row['pos'],
			"unit"			=> $row['unit'],
			"labor"			=> $row['labor'],			
			"supperior"		=> $row['supperior'],
			"estat"			=> $row['estat'],
			"loc"			=> $row['loc'],
			"workshift"		=> $row['workshift'],
			"scheduleType"	=> $row['schedule'],
			"idpaygrp"		=> $row['idpaygrp'],
			"paygroup"		=> $row['paygroup'],
			"idpaystat"		=> $row['idpaystat'] ? $row['idpaystat'] : '2',
			"paystatus"		=> $row['paystatus'],
			"idrevenue"		=> $row['idrevenue'] ? $row['idrevenue'] : '1',
			"payrevenue"	=> $row['payrevenue'],
			"orgdependent"	=> "" . (int)$row['dependent'],
			"dependent"		=> "" . (int)$row['dependent'],
			"dependents"	=> $dependents,
			"ctr_dependent" => $ctr_dependent,
			"idrelease"		=> $row['idrelease'],
			"payrelease"	=> $row['payrelease'],
			"prev_employer"	=> $row['prev_employer'],
			"daysmonth"		=> $row['daysmonth'] ? $row['daysmonth'] : '26.0833',
			"supperiors"	=> getAccounts($con, $row['idacct'] ),
			"estats"		=> getEmpStatus($con),
			"locs"			=> getJobLocation($con),
			"workshifts"	=> getWShifts($con),
			"schedules"		=> getSchedType($con),
			"labors"		=> getLabors($con),
			"units"			=> getBusinessUnits($con),
			"poss"			=> getPositions($con),
			"paygroups"		=> getPayGroups($con),
			"paystat"		=> getPayStatus($con),
			"payrevenues"	=> getPayRevenues($con),
			"payreleases"	=> getPayRelease($con),
			"leaves"		=> getAccountleaves($con, $param->id ),
			"leavesdrop"	=> getAccountleaves2($con, $param->id ),
			"allleaves"		=> getAccountleaves3($con),
			"allallowance"	=> getAccountAllowance($con),
			"acctallowance"	=> getAccountAllowance1($con, $param->id),
			"riceallowance"	=> $row['riceallowance'] ? $row['riceallowance'] : '0.00',
			"clothingallowance"	=> $row['clothingallowance'] ? $row['clothingallowance'] : '0.00',
			"laundryallowance"	=> $row['laundryallowance'] ? $row['laundryallowance'] : '0.00',
			"remain_period"	=> getCountOpenPeriod($con),
			"basicpay"		=> $row['basicpay_type'] ? $row['basicpay_type'] : '1',
			"absences"		=> $row['absences_type'] ? $row['absences_type'] : '1',
			"lates"			=> $row['lates_type'] ? $row['lates_type'] : '1',
			"undertime"		=> $row['undertime_type'] ? $row['undertime_type'] : '1',
			"idcba"			=> $row['idcba'] ? $row['idcba'] : '2',
			"sss_type"		=> $row['sss_type'] ? $row['sss_type'] : '1',
			"ibig_type"		=> $row['ibig_type'] ? $row['ibig_type'] : '1',
			"health_type"	=> $row['health_type'] ? $row['health_type'] : '1',
			"sss_amt"		=> $row['sss_amt'] ? $row['sss_amt'] : '0.00',
			"ibig_amt"		=> $row['ibig_amt'] ? $row['ibig_amt'] : '0.00',
			"health_amt"	=> $row['health_amt'] ? $row['health_amt'] : '0.00',
			"sss_deduct1"	=> array("id"=> (int)$row['sss_deduct1'],"checked"=> (int)$row['sss_deduct1']==0 ? false : true ),
			"sss_deduct2"	=> array("id"=> (int)$row['sss_deduct2'],"checked"=> (int)$row['sss_deduct2']==0 ? false : true ),
			"ibig_deduct1"	=> array("id"=> (int)$row['ibig_deduct1'],"checked"=> (int)$row['ibig_deduct1']==0 ? false : true ),
			"ibig_deduct2"	=> array("id"=> (int)$row['ibig_deduct2'],"checked"=> (int)$row['ibig_deduct2']==0 ? false : true ),
			"health_deduct1"=> array("id"=> (int)$row['health_deduct1'],"checked"=> (int)$row['health_deduct1']==0 ? false : true ),
			"health_deduct2"=> array("id"=> (int)$row['health_deduct2'],"checked"=> (int)$row['health_deduct2']==0 ? false : true ),
			"rev_deduct1"	=> array("id"=> (int)$row['rev_deduct1'],"checked"=> (int)$row['rev_deduct1']==0 ? false : true ),
			"rev_deduct2"	=> array("id"=> (int)$row['rev_deduct2'],"checked"=> (int)$row['rev_deduct2']==0 ? false : true ),
			"isprevemp"		=> array("id"=> (int)$row['isprevemp'],"checked"=> (int)$row['isprevemp']==0 ? false : true ),
			"path_bir"		=> $path_bir,
			"rice_method"	=> $row['rice_method'] ? $row['rice_method'] : '1',
			"clothing_method"=> $row['clothing_method'] ? $row['clothing_method'] : '1',
			"laundry_method"=> $row['laundry_method'] ? $row['laundry_method'] : '1',
			"gross"			=> $row['gross'],
			"taxable"		=> $row['taxable'],
			"nontax"		=> $row['nontax'],
			"addex"			=> $row['addex'],
			"month13"		=> $row['month13'],
			"deminimis"		=> $row['deminimis'],
			"pa"			=> $propertyacc
        );
    }
}

$return = json_encode($data);
print $return;
mysqli_close($con);

function getLocalEmployeeType($con, $idacct){	
	$Qry 			= new Query();	
	$Qry->table     = "tblaccount";
	$Qry->selected  = "idemptype";
	$Qry->fields    = "id='".$idacct."' ";
	$rs 			= $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			return $row['idemptype'];
		}
	}
	return '';
}

function getCountOpenPeriod($con){
	$Qry 			= new Query();	
	$Qry->table     = "tblpayperiod";
	$Qry->selected  = "count(id) as ctr";
	$Qry->fields    = "stat='0' ";
	$rs 			= $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			return $row['ctr'];
		}
	}
	return 0;
}

?>