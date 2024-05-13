<?php
session_start();
require_once('../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../classPhp.php');

$param = json_decode(file_get_contents('php://input'));


if(!empty($param->username)){
	$Qry = new Query();	
	$Qry->table ="vw_dataemployees AS a LEFT JOIN tblaccttype AS b ON b.id=a.idaccttype LEFT JOIN tblbunits AS c ON c.id = b.`idunit`";
	$Qry->selected ="a.*, (CASE WHEN a.id = c.idhead THEN '1' ELSE b.orgdata END) AS orgdata, b.empportal, b.mngrportal, (CASE WHEN a.id = c.idhead THEN '1' ELSE b.hrportal END) AS hrportal, b.timeportal, b.payportal, b.admportal,b.idunit AS hrunit";
	$Qry->fields ="a.username = '".$param->username."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			$isJapanese = false;
			$dpt 		= getDeputy1($con, $param->accountid); //Jerald
			$promApp 	= getPromotionApprover($con,$param->accountid); //Dan
			$dptmtrx 	= getDeputy2($con, $param->accountid);
			$latTransApp = getLateralTransferApprover($con,$param->accountid); //Dan
			$WageIncApp = getWageIncreaseApprover($con,$param->accountid); //Dan
			
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
			$logo = "assets/images/logo/".$pname.".webp?".time();



			$dbinfo = getDBName($con);
			$dblist = getDbs($con,$row['id']);

			$jempid = mb_substr($row['empid'], 0, 4, "UTF-8");
			if($jempid == 'KPIJ'){
				$isJapanese = true;
			}
			
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
				'undersecretary'=> $dpt, //$_SESSION['selectedcomp'],//$dpt, //Jerald
				'promotion_approver'=> $promApp, //Dan
				'deputymtrx'=>$dptmtrx,
				'lateral_transfer_approver'=> $latTransApp, //Dan
				'wage_increase_approver'=> $WageIncApp, //Dan
				'isAutoFilling'=> isAutoFilling( $con, $row['id'] ) //LxDan
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
	$Qry->selected  = "*";
	$Qry->fields    = "alias='AUTOOT' AND POSITION('".$id."' IN `value`)!=0";

	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			return true;
		}
	}
	return false;
}

?>