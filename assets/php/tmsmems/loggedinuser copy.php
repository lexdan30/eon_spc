<?php
require_once('../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../classPhp.php');

	$param = json_decode(file_get_contents('php://input'));
	
	if(!empty($param->accountid)){
		$Qry = new Query();	
		$Qry->table ="(SELECT 
		fname, lname, mname, pw_resetdate, idaccttype, 
		empid, suffix, empname, addr_st, addr_city,
		addr_prov, addr_code, email, cnumber, bdate,
		citizenship, religion, civil_status, spouse, sex,
		sexstr, idtin, idsss, idhealth, idibig,
		idpayroll, idtax, taxname, `type`, etypeid,
		etype, idsuperior, superior, superior_email, idlabor,
		labor_type, idunit, business_unit, empstat, emp_status,
		idpos, post, idlvl, wshift, wshift_name,
		schedtype, sched, idloc, joblvl, job_loc,
		idpaygrp, pay_grp, paystat, pay_status, idrevenue,
		pay_release, dependent, salary, prev_employer, hdate,
		rdate, sdate, id, idcomp, pic,
		bplace, civilstat, addr_area, pay_revenue, idrelease, pw_flag FROM vw_dataemployees) AS a 
		LEFT JOIN (SELECT id, orgdata, empportal, mngrportal, hrportal, timeportal, payportal, admportal, idunit FROM tblaccttype) AS b ON (b.id=a.idaccttype) 
		LEFT JOIN (SELECT id, idhead FROM tblbunits) AS c ON (c.id = b.`idunit`)";
		$Qry->selected ="
		a.fname, a.lname, a.mname, a.pw_resetdate, a.idaccttype, 
		a.empid, a.suffix, a.empname, a.addr_st, a.addr_city,
		a.addr_prov, a.addr_code, a.email, a.cnumber, a.bdate,
		a.citizenship, a.religion, a.civil_status, a.spouse, a.sex,
		a.sexstr, a.idtin, a.idsss, a.idhealth, a.idibig,
		a.idpayroll, a.idtax, a.taxname, a.`type`, a.etypeid,
		a.etype, a.idsuperior, a.superior, a.superior_email, a.idlabor,
		a.labor_type, a.idunit, a.business_unit, a.empstat, a.emp_status,
		a.idpos, a.post, a.idlvl, a.wshift, a.wshift_name,
		a.schedtype, a.sched, a.idloc, a.joblvl, a.job_loc,
		a.idpaygrp, a.pay_grp, a.paystat, a.pay_status, a.idrevenue,
		a.pay_release, a.dependent, a.salary, a.prev_employer, a.hdate,
		a.rdate, a.sdate, a.id, a.idcomp, a.pic,
		a.bplace, a.civilstat, a.addr_area, a.pay_revenue, a.idrelease,
		(CASE WHEN a.id = c.idhead THEN '1' ELSE b.orgdata END) AS orgdata, 
		b.empportal, b.mngrportal,
		(CASE WHEN a.id = c.idhead THEN '1' ELSE b.hrportal END) AS hrportal,
		b.timeportal, b.payportal, b.admportal,b.idunit AS hrunit, a.pw_flag";
		$Qry->fields ="a.id = '".$param->accountid."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_assoc($rs)){
				$isJapanese = false;
				$dpt 		= getDeputy1($con, $param->accountid); //Jerald
				$promApp 	= getPromotionApprover($con,$param->accountid); //Dan
				$dptmtrx 	= getDeputy2($con, $param->accountid);
				$latTransApp = getLateralTransferApprover($con,$param->accountid); //Dan
				$WageIncApp = getWageIncreaseApprover($con,$param->accountid); //Dan
				
				if($param->accountid == '546'){ 
					$row['orgdata'] = '1';
				}

				if($dpt!=''){ //Jerald
					$row['mngrportal'] = '1';
				}
				if($promApp != 0 || $latTransApp != 0 || $WageIncApp != 0){ //Dan
					$row['hrportal'] = '1';
				}
				if( !empty($dptmtrx) ){ 
					$row['mngrportal'] = '1';
				}
				
				if( !file_exists( "../admin/hr/employee/pix/".$row['pic'] ) || empty($row['pic']) ){
					$img_prof = "assets/images/undefined.webp?".time();
				}else{
					$img_prof = "assets/php/admin/hr/employee/pix/".$row['pic']."?".time();
				}	

				$pname= getPropertyName($con);
				// $logo = "assets/images/logo/".$pname.".webp?".time();

				//$logo = "assets/images/logo/".$pname.".webp";
				$logo = "assets/images/npax_slogo.png";
				
				$dbinfo = getDBName($con);
				$dblist = getDbs($con,$row['id']);


				// $jempid = mb_substr($row['empid'], 0, 4, "UTF-8");
				// if($jempid == 'KPIJ'){
				// 	$isJapanese = true;
				// }


				if (getaccessportal($con, $row['id'], $access='hrportal')) {
					$row['hrportal']=1;
					$row['type'] = 'HR';
				}
				if (getaccessportal($con, $row['id'], $access='timeportal')) {	
					$row['timeportal']=1;
					$row['type'] = 'TIMEKEEPER';
				}

				if (getaccessportal($con, $row['id'], $access='mngrportal')) {
					$row['mngrportal']=1;
					$row['type'] = 'MANAGER';
				}

				if (getaccessportal($con, $row['id'], $access='payportal')) {
					$row['payportal']=1;
					$row['type'] = 'PAYROLL';
				}



				// if($row['id'] == '747' || $row['id'] == '748' || $row['id'] == '641' || $row['id'] == '708'){
				// 	$row['hrportal']=1;
				// 	$row['type'] = 'HR';
				// }

				// if($row['id'] == '641' || $row['id'] == '708'){
				// 	$row['timeportal']=1;
				// 	$row['mngrportal']=1;
				// 	$row['type'] = 'MANAGER';
				// }

				// if($row['id'] == '490'){
				// 	$row['payportal']=1;
				// }

			


				
				$data = array(
					'id'=>$row['id'],
					'fname'=>ucfirst($row['fname']),
					'lname'=>ucfirst($row['lname']),
					'mname'=>ucfirst($row['mname']),
					'hrunit'=>$row['hrunit'],
					'pw_resetdate'=>$row['pw_resetdate'],
					'pw_flag'=>$row['pw_flag'],
					'idtype'=>$row['idaccttype'],					
					'pic'=>$img_prof,
					'logo'=>$logo,
					'empid'=>$row['empid'],
					'isJapanese'=>$isJapanese,
					'suffix'=>$row['suffix'],
					'empname'=>$row['empname'],
					'addr_st'=>$row['addr_st'],
					'addr_area'=>$row['addr_area'],
					'addr_city'=>$row['addr_city'],
					'addr_prov'=>$row['addr_prov'],
					'addr_code'=>$row['addr_code'],
					'email'=>$row['email'],
					'cnumber'=>$row['cnumber'],
					'bdate'	=>$row['bdate'],
					'bplace'=>$row['bplace'],
					'citizenship'=>$row['citizenship'],
					'religion'=>$row['religion'],
					'civilstat'=>$row['civilstat'],
					'civil_status'=>$row['civil_status'],
					'spouse'=>$row['spouse'],
					'sex'=>$row['sex'],
					'sexstr'=>$row['sexstr'],
					'idtin'=>$row['idtin'],
					'idsss'=>$row['idsss'],
					'idhealth'=>$row['idhealth'],
					'idibig'=>$row['idibig'],
					'idpayroll'=>$row['idpayroll'],
					'idtax'=>$row['idtax'],
					'taxname'=>$row['taxname'],
					'type'=>$row['type'],
					'etypeid'=>$row['etypeid'],
					'etype'=>$row['etype'],
					'idsuperior'=>$row['idsuperior'],
					'superior'=>$row['superior'],
					'superior_email'=>$row['superior_email'],
					'idlabor'=>$row['idlabor'],
					'labor_type'=>$row['labor_type'],
					'idunit'=>$row['idunit'],
					'business_unit'=>$row['business_unit'],
					'empstat'=>$row['empstat'],
					'emp_status'=>$row['emp_status'],
					'idpos'=>$row['idpos'],
					'post'=>$row['post'],
					'idlvl'=>$row['idlvl'],
					'wshift'=>$row['wshift'],
					'wshift_name'=>$row['wshift_name'],
					'schedtype'=>$row['schedtype'],
					'sched'=>$row['sched'],
					'idloc'=>$row['idloc'],
					'joblvl'=>$row['joblvl'],
					'job_loc'=>$row['job_loc'],
					'idpaygrp'=>$row['idpaygrp'],
					'pay_grp'=>$row['pay_grp'],
					'paystat'=>$row['paystat'],
					'pay_status'=>$row['pay_status'],
					'idrevenue'=>$row['idrevenue'],
					'pay_revenue'=>$row['pay_revenue'],
					'idrelease'=>$row['idrelease'],
					'pay_release'=>$row['pay_release'],
					'dependent'=>$row['dependent'],
					'salary'=>$row['salary'],
					'prev_employer'=>$row['prev_employer'],
					'hdate'=>$row['hdate'],
					'rdate'=>$row['rdate'],
					'sdate'=>$row['sdate'],
					'orgdata'=>$row['orgdata'], 
					'empportal'=>$row['empportal'],
					'mngrportal'=>$row['mngrportal'],
					'hrportal'=>$row['hrportal'], 
					'timeportal'=>$row['timeportal'],
					'payportal'=>$row['payportal'],
					'admportal'=>$row['admportal'],
					'db'   	=> $dbinfo['id'],
					'dbinfo'=>$dbinfo,					
					'dblist'=> $dblist,
					'mission'=> getMission($con, $row['idcomp']),
					'vision'=> getVision($con, $row['idcomp']),
					'undersecretary'=> $dpt, //Jerald
					'promotion_approver'=> $promApp, //Dan
					'deputymtrx'=>$dptmtrx,
					'lateral_transfer_approver'=> $latTransApp, //Dan
					'wage_increase_approver'=> $WageIncApp, //Dan
					'isAutoFilling'=> isAutoFilling( $con, $row['id'] ), //LxDan
					'tkportal'=> getTimekeepingPortal($con,  $row['id']), //Darwin
					'superadmin'=> getSuperAdminPortal($con,  $row['id']), //Darwin
					'isMonthliesProcessor'=> isMonthliesProcessor($con,  $row['id']), //Darwin
					'isManagersProcessor'=> isManagerProcessor($con,  $row['id']) //Darwin
				);
				$return = json_encode($data);
				 
			}
		}else{
			$return = json_encode(array('status'=>'error','w'=>1));
		}
	}else{
		$return = json_encode(array('status'=>'error','w'=>2));
	}
print $return;	
mysqli_close($con);

function isAutoFilling( $con, $id ){	
	$Qry = new Query();	
	$Qry->table     = "tblpreference";
	$Qry->selected  = "id";
	$Qry->fields    = "alias='AUTOOT' AND POSITION('".$id."' IN `value`)!=0";

	$rs = $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'isAutoFilling');
	return mysqli_num_rows($rs) == 1;
}

function isMonthliesProcessor( $con, $id ){	
	$Qry = new Query();	
	$Qry->table     = "tblpreference";
	$Qry->selected  = "id";
	$Qry->fields    = "alias='PROCESS_MONTHLIES' AND POSITION('".$id."' IN `value`)!=0";

	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs) == 1;
}

function isManagerProcessor( $con, $id ){	
	$Qry = new Query();	
	$Qry->table     = "tblpreference";
	$Qry->selected  = "id";
	$Qry->fields    = "alias='PROCESS_MANAGER' AND POSITION('".$id."' IN `value`)!=0";

	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs)>= 1;
}

function getTimekeepingPortal($con,$id){
	$Qry 			= new Query();	
	$Qry->table     = "tblportal";
	$Qry->selected  = "idacct";
	$Qry->fields    = "access='tkportal' AND POSITION('".$id."' IN `idacct`)!=0";
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs)>= 1;
}

function getaccessportal($con,  $id, $access){
	$Qry 			= new Query();	
	$Qry->table     = "tblportal";
	$Qry->selected  = "idacct";
	$Qry->fields    = "access='".$access."' AND FIND_IN_SET ('".$id."',`idacct`)!=0";
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs)>= 1;
}

function getSuperAdminPortal($con,$id){
	$Qry 			= new Query();	
	$Qry->table     = "tblportal";
	$Qry->selected  = "idacct";
	$Qry->fields    = "access='admin' AND POSITION('".$id."' IN `idacct`)!=0";
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs)>= 1;
}

?>