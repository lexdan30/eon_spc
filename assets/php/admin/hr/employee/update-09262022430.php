<?php
error_reporting(0);
require_once('../../../activation.php');
$conn = new connector();	 
$con = $conn->connect();
require_once('../../../classPhp.php'); 
require_once('../../../email/emailFunction.php');

$param 	= $_POST; 

$date	= SysDate();
$time	= SysTime();

//print_r($param);

foreach ($param['info'] as $keyzz => $valuezz) {
	if( !is_array($param['info'][$keyzz]) && $keyzz != 'epicFile' && $keyzz !='eprof_pic' ){
		if( strtolower($param['info'][$keyzz]) == 'null' ){
			$param['info'][$keyzz]="";
		}
	}
}

if( !empty($param['accountid']) ){
	
		$param['info']['docs']	= $param['attachment_type'];
		//if( array_key_exists('file',$_FILES) ){ $_FILES['file'] = $_FILES['attachment_files']; }
	
		if( array_key_exists('file',$_FILES) ){
			$valid_formats = array("jpg", "png", "jpeg");	
			foreach ($_FILES['file']['name'] as $f => $name) {
				if ($_FILES['file']['error'][$f] == 4) {
					$return = json_encode(array('status'=>'error','on'=>'img_check'));
					print $return;	
					mysqli_close($con);
					return;
				}
				if ($_FILES['file']['error'][$f] == 0) {
					if(!in_array(pathinfo(strtolower($_FILES['file']['name'][$f]), PATHINFO_EXTENSION), $valid_formats) ){
						$return = json_encode(array('status'=>'error-upload-type'));
						print $return;	
						mysqli_close($con);
						return;
					}
				}
			}
		}
		
		$param['info']['empid'] 		= strtoupper(strtolower(str_replace("'","",$param['info']['empid'])));
		$param['info']['fname'] 		= ((str_replace("'","",$param['info']['fname'])));
		$param['info']['mname'] 		= ((str_replace("'","",$param['info']['mname'])));
		$param['info']['lname'] 		= ((str_replace("'","",$param['info']['lname'])));
		$param['info']['suffix'] 		= ((str_replace("'","",$param['info']['suffix'])));
		$param['info']['addr_st'] 		= ((str_replace("'","",$param['info']['addr_st'])));
		$param['info']['addr_area'] 	= ((str_replace("'","",$param['info']['addr_area'])));
		$param['info']['addr_city'] 	= ((str_replace("'","",$param['info']['addr_city'])));
		$param['info']['addr_prov'] 	= ((str_replace("'","",$param['info']['addr_prov'])));
		$param['info']['addr_code'] 	= ((str_replace("'","",$param['info']['addr_code']))); 
		
		$param['info']['per_st'] 		= ((str_replace("'","",$param['info']['per_st'])));
		$param['info']['per_area'] 		= ((str_replace("'","",$param['info']['per_area'])));
		$param['info']['per_city'] 		= ((str_replace("'","",$param['info']['per_city'])));
		$param['info']['per_prov'] 		= ((str_replace("'","",$param['info']['per_prov'])));
		$param['info']['per_code'] 		= ((str_replace("'","",$param['info']['per_code']))); 
		
		$param['info']['bplace'] 		= ((str_replace("'","",$param['info']['bplace'])));
		$param['info']['spouse'] 		= ((str_replace("'","",$param['info']['spouse'])));
		$param['info']['cnumber'] 		= strtoupper(strtolower(str_replace("'","",$param['info']['cnumber'])));
		$param['info']['idtin'] 		= strtoupper(strtolower(str_replace("'","",$param['info']['idtin'])));
		$param['info']['idsss'] 		= strtoupper(strtolower(str_replace("'","",$param['info']['idsss'])));
		$param['info']['idhealth'] 		= strtoupper(strtolower(str_replace("'","",$param['info']['idhealth'])));
		$param['info']['idibig'] 		= strtoupper(strtolower(str_replace("'","",$param['info']['idibig'])));
		$param['info']['idpayroll'] 	= strtoupper(strtolower(str_replace("'","",$param['info']['idpayroll'])));
		
		$param['info']['idtax'] = 'Z';
		
		if( !empty( $param['info']['empid'] ) ){
			if( $param['info']['empid'] != $param['info']['orig_empid'] ){
				if( checkEmpID( $con, $param['info']['empid'] ) ){
					$return = json_encode(array('status'=>'idexists'));
					print $return;	
					mysqli_close($con);
					return;
				}
			}
			
			$pic='';
			if( array_key_exists('file',$_FILES) ){
				if( array_key_exists(0,$_FILES['file']['name']) ){
					$extMove = pathinfo($_FILES['file']['name'][0], PATHINFO_EXTENSION);
					$pic	 = $param['info']['empid'].".".$extMove;
				}
			}
			
			
			
			if( !empty( $param['info']['fname'] ) ){
				if( !empty( $param['info']['mname'] ) ){
					if( !empty( $param['info']['lname'] ) ){
						if( !empty( $param['info']['sex'] ) ){
							if( !empty( $param['info']['idaccttype'] ) ){
								if( !empty( $param['info']['idemptype'] ) ){
								$address = $param['info']['addr_st'].", ".$param['info']['addr_area'].", ".$param['info']['addr_city'].", ".$param['info']['addr_prov'];
									if( !empty( $address ) ){
										if( !empty( $param['info']['cnumber'] ) ){
											if( !empty( $param['info']['bdate'] ) ){
												//if( !empty( $param['info']['bplace'] ) ){														
													if( !empty( $param['info']['citizenship'] ) ){
														if( !empty( $param['info']['religion'] ) ){
															if( !empty( $param['info']['civilstat'] ) ){																	
																if( (int)$param['info']['civilstat'] > 1 && empty( $param['info']['spouse'] ) ){
																	$return = json_encode(array("status"=>"spouse"));
																}else{
																	if( !empty( $param['info']['idtax'] ) ){																	
																		if( !empty( $param['info']['email'] ) ){
																			if( filter_var( $param['info']['email'], FILTER_VALIDATE_EMAIL)) {
																			}else{
																				$return = json_encode(array('status'=>'email'));
																				print $return;	
																				mysqli_close($con);
																				return;
																			}
																		}
																		
																		// for check
																		//check if duplicate document types

																		// if( count( array_filter($param['info']['docs']) ) != count(array_unique(array_filter($param['info']['docs']))) ){
																		// 	$return = json_encode(array('status'=>'duplicate_attachment','ctr1'=>count( $param['info']['docs'] ), 'ctr2'=>count(array_unique($param['info']['docs']))));
																		// 	print $return;	
																		// 	mysqli_close($con);
																		// 	return;
																		// }
																		
																	//Filter duplicate leave
																	$unique_lv = array();
																	if( (int)$param['info']['empstat'] == 5 ){
																		
																		foreach( $param['info']['leaves'] as $keys=>$val ){																			
																			if( !empty( $val['idleave'] ) && !in_array($val['idleave'],$unique_lv) ){
																				array_push($unique_lv,$val['idleave']);																			
																			}
																			else if( in_array($val['idleave'],$unique_lv) ){
																				$return = json_encode(array('status'=>'duplicate_leaves'));
																				print $return;	
																				mysqli_close($con);
																				return;
																			}																																				
																		}
																	} 
 
																	//Filter male/female/single
																	$unique_lv = array();
																	if( (int)$param['info']['empstat'] == 5 ){
	
																		foreach( $param['info']['leaves'] as $keys=>$val ){
																			if(((int)$val['idleave'] == 10 || (int)$val['idleave'] == 12 || (int)$val['idleave'] == 22) && $param['info']['sex'] == "M" ){
																				$return = json_encode(array('status'=>'leave_male'));
																				print $return;	
																				return;
																			}
																			else if((int)$val['idleave'] == 5 && $param['info']['sex'] == "F" ){
																				$return = json_encode(array('status'=>'leave_female'));
																				print $return;	
																				mysqli_close($con);
																				return;
																			}else if((int)$val['idleave'] == 21 && (int)$param['info']['civilstat'] > 1){
																				$return = json_encode(array('status'=>'notsingle'));
																				print $return;	
																				mysqli_close($con);
																				return;
																			}

																		}
																	}
																

																			
																		//filter duplicate allowance
																		$unique_al = array();
																		foreach( $param['info']['acctallowance'] as $keyss=>$valss ){
																			if( !empty( $valss["idallowance"]) && !in_array($valss['idallowance'],$unique_al) ) {
																				array_push($unique_al,$valss['idallowance']);
																			}else if( in_array($valss['idallowance'],$unique_al) ){
																				$return = json_encode(array('status'=>'duplicate_allowance'));
																				print $return;	
																				mysqli_close($con);
																				return;
																			}
																		}
																		
																		//FIlter for Assign
																		if( empty($param['info']['idlabor']) ){
																			$return = json_encode(array("status"=>"idlabor"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if( empty($param['info']['empstat']) ){
																			$return = json_encode(array("status"=>"empstat"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if( empty($param['info']['idunit']) ){
																			$return = json_encode(array("status"=>"idunit"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if( empty($param['info']['regions']) ){
																			$return = json_encode(array("status"=>"regions"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if( empty($param['info']['provinces']) ){
																			$return = json_encode(array("status"=>"provinces"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if( empty($param['info']['municipality']) ){
																			$return = json_encode(array("status"=>"municipality"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if( empty($param['info']['idpos']) ){
																			$return = json_encode(array("status"=>"idpos"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if( empty($param['info']['wshift']) ){
																			$return = json_encode(array("status"=>"wshift"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if( empty($param['info']['schedtype']) ){
																			$return = json_encode(array("status"=>"schedtype"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		// if( empty($param['info']['idloc']) ){
																		// 	$return = json_encode(array("status"=>"idloc"));
																		// 	print $return;	
																		// 	mysqli_close($con);
																		// 	return;
																		// }
																		// if( empty($param['info']['idregion']) ){
																		// 	$return = json_encode(array("status"=>"idregion"));
																		// 	print $return;	
																		// 	mysqli_close($con);
																		// 	return;
																		// }
																		if( empty($param['info']['idsuperior']) ){
																			// $return = json_encode(array("status"=>"idsuperior"));
																			// print $return;	
																			// mysqli_close($con);
																			// return;
																			$param['info']['idsuperior'] = 0;
																		}
																		if( (int)$param['info']['id'] == (int)$param['info']['idsuperior'] ){
																			$return = json_encode(array("status"=>"invsuperior"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if( empty($param['info']['idpaygrp']) ){
																			$return = json_encode(array("status"=>"idpaygrp"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if( empty($param['info']['idpaystat']) ){
																			$return = json_encode(array("status"=>"idpaystat"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if( empty($param['info']['idrevenue']) ){
																			$return = json_encode(array("status"=>"idrevenue"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if( empty($param['info']['idrelease']) ){
																			$return = json_encode(array("status"=>"idrelease"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if( empty($param['info']['idlvl']) ){
																			$return = json_encode(array("status"=>"idlvl"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if( empty($param['info']['salary']) ){
																			$return = json_encode(array("status"=>"salary"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if($param['info']['salary'] != $param['info']['salary_old']){
																			if( empty($param['info']['efdate']) ){
																				$return = json_encode(array("status"=>"salary_dateeff"));
																				print $return;	
																				mysqli_close($con);
																				return;
																			}
																		}
																		if( empty($param['info']['hdate']) ){
																			$return = json_encode(array("status"=>"hdate"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if( empty($param['info']['idlvlscale']) ){
																			$return = json_encode(array("status"=>"idlvlscale"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		
																		// if( (int)$param['info']['empstat'] == 5 && ( empty( $param['info']['rdate'] ) || strlen($param['info']['rdate']) == 1 ) ){
																		// 	$return = json_encode(array("status"=>"nordate"));
																		// 	print $return;	
																		// 	mysqli_close($con);
																		// 	return;
																		// }
																		if( (int)$param['info']['empstat'] != 8 && (int)$param['info']['empstat'] != 5 && (int)$param['info']['empstat'] != 6 && (int)$param['info']['orig_empstat'] == 5 ){
																			$return = json_encode(array("status"=>"noupdate"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		//if((int)$param['info']['empstat'] != 5 && (int)$param['info']['empstat'] != 6 && (int)$param['info']['rdate'] !=0 && strlen($param['info']['rdate']) > 1 ){
																		if((int)$param['info']['empstat'] != 8 &&(int)$param['info']['empstat'] != 5 && (int)$param['info']['empstat'] != 6 && !empty( $param['info']['rdate'] ) && strlen($param['info']['rdate']) > 1 ){
																			$return = json_encode(array("status"=>"noupdate"));
																			print $return;	
																			mysqli_close($con);
																			return;
																		}
																		if( ("".$param['info']['isprevemp']['checked']) == "true" ){
																			if( !in_array(11,$param['info']['docs'] ) ){
																				$return = json_encode(array("status"=>"nobir2316"));
																				print $return;	
																				mysqli_close($con);
																				return;
																			}
																		}
																		
																		$param['info']['prev_employer']	= strtoupper(strtolower(str_replace("'","",$param['info']['prev_employer'])));
																		$hire_date = strtotime($param['info']['hdate']);
																		if( !empty($param['info']['rdate']) ){
																			$regDate = strtotime($param['info']['rdate']);
																			if( $hire_date > $regDate ){																		
																				$return = json_encode(array("status"=>"errDate1"));
																				print $return;
																				mysqli_close($con);
																				return;
																			}
																		}
																		if( (int)$param['info']['orig_empstat'] == 5 && ((int)$param['info']['empstat'] < 5 || (int)$param['info']['empstat'] == 7)){
																			$return = json_encode(array("status"=>"invemptype"));
																			print $return;
																			mysqli_close($con);
																			return;
																		}
																		if( (int)$param['info']['idemptype'] == 3 ){
																			if( !empty($param['info']['sdate']) ){
																				$seperationDate = strtotime($param['info']['sdate']);
																				if( !empty($param['info']['rdate']) ){
																					$regDate = strtotime($param['info']['rdate']);
																					if( $regDate > $seperationDate ){
																						$return = json_encode(array("status"=>"errDate3"));
																						print $return;
																						mysqli_close($con);
																						return;
																					}else{
																						if( $hire_date > $seperationDate ){
																							$return = json_encode(array("status"=>"errDate4"));
																							print $return;
																							mysqli_close($con);
																							return;
																						}
																					}
																				}else{
																					if( $hire_date > $seperationDate ){
																						$return = json_encode(array("status"=>"errDate4"));
																						print $return;
																						mysqli_close($con);
																						return;
																					}
																				}
																			}else{
																				$return = json_encode(array("status"=>"errDate2"));
																				print $return;
																				mysqli_close($con);
																				return;
																			}																	
																		}
																		// elseif( (int)$param['info']['empstat'] == 5 ){
																		// 	foreach( $param['info']['leaves'] as $keys=>$val ){																	
																		// 		if( (int)$val['hours'] < (int)$val['entitle'] ){
																		// 			$return = json_encode(array("status"=>"errLeave1","err"=>$param['info']['leaves'],"id"=>$val['id']));
																		// 			print $return;
																		// 			mysqli_close($con);
																		// 			return;
																		// 		}
																		// 	}
																		// }
																		
																		/*if( (int)$param['info']['idaccttype'] == 2 ){
																			$array_unit = getapprover($con,$param['info']['idunit']);
																			if( !empty( $array_unit['idhead'] ) ){
																				$return = json_encode(array("status"=>"invaccttype"));
																				print $return;	
																				mysqli_close($con);
																				return;
																			}
																		}*/
																		
																		
																		//INSERT TO DATABASE																		
																		$Qry 			= new Query();	
																		$Qry->table 	= "tblaccount";
																		$Qry->selected 	= "empid		='".$param['info']['empid']."',
																						   idaccttype	='".$param['info']['idaccttype']."',
																						   idemptype	='".$param['info']['idemptype']."',
																						   fname		='".$param['info']['fname']."',
																						   lname		='".$param['info']['lname']."',
																						   mname		='".$param['info']['mname']."',
																						   sex			='".$param['info']['sex']."',
																						   cnumber		='".$param['info']['cnumber']."',
																						   bdate		='".$param['info']['bdate']."',
																						   citizenship	='".$param['info']['citizenship']."',
																						   religion		='".$param['info']['religion']."',
																						   civilstat	='".$param['info']['civilstat']."',
																						   sameaddress	='".$param['info']['sameaddress']."',
																						   isprivacy	='".$param['info']['isprivacy']."'";
																		
																		if( !empty( $param['pword'] ) ){
																			$Qry->selected 	= $Qry->selected . ", password='".md5($param['pword'])."'";																			
																		}
																		if( !empty( $param['info']['nickname'] ) ){
																			$Qry->selected 	= $Qry->selected . ", nickname='".$param['info']['nickname']."'";																			
																		}else{
																			$Qry->selected 	= $Qry->selected . ", nickname=NULL";																		
																		}
																		if( !empty( $param['info']['bplace'] ) ){
																			$Qry->selected 	= $Qry->selected . ", bplace='".$param['info']['bplace']."'";																			
																		}else{
																			$Qry->selected 	= $Qry->selected . ", bplace=NULL";																		
																		}
																		if( !empty( $param['info']['salutation'] ) ){
																			$Qry->selected 	= $Qry->selected . ", salutation='".$param['info']['salutation']."'";																			
																		}else{
																			$Qry->selected 	= $Qry->selected . ", salutation=NULL";																		
																		}
																		if( !empty( $param['info']['suffix'] ) ){
																			$Qry->selected 	= $Qry->selected . ", suffix='".$param['info']['suffix']."'";																			
																		}else{
																			$Qry->selected 	= $Qry->selected . ", suffix=NULL";																		
																		}
																		
																		if( !empty( $param['info']['idpassport'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idpassport='".$param['info']['idpassport']."'";	
																		}else{
																			$Qry->selected 	= $Qry->selected . ", idpassport=NULL";	
																		}
																		if( !empty( $param['info']['license_drive'] ) ){
																			$Qry->selected 	= $Qry->selected . ", license_drive='".$param['info']['license_drive']."'";	
																		}else{
																			$Qry->selected 	= $Qry->selected . ", license_drive=NULL";	
																		}
																		
																		if( !empty( $param['info']['license_prc'] ) ){
																			$Qry->selected 	= $Qry->selected . ", license_prc='".$param['info']['license_prc']."'";	
																		}else{
																			$Qry->selected 	= $Qry->selected . ", license_prc=NULL";	
																		}
																		if( !empty( $param['info']['addr_st'] ) ){
																			$Qry->selected 	= $Qry->selected . ", addr_st='".$param['info']['addr_st']."'";																			
																		}else{
																			$Qry->selected 	= $Qry->selected . ", addr_st=NULL";																			
																		}
																		if( !empty( $param['info']['addr_area'] ) ){
																			$Qry->selected 	= $Qry->selected . ", addr_area='".$param['info']['addr_area']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", addr_area=NULL";
																		}
																		if( !empty( $param['info']['addr_city'] ) ){
																			$Qry->selected 	= $Qry->selected . ", addr_city='".$param['info']['addr_city']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", addr_city=NULL";
																		}
																		if( !empty( $param['info']['addr_prov'] ) ){
																			$Qry->selected 	= $Qry->selected . ", addr_prov='".$param['info']['addr_prov']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", addr_prov=NULL";
																		}
																		if( !empty( $param['info']['addr_code'] ) ){
																			$Qry->selected 	= $Qry->selected . ", addr_code='".$param['info']['addr_code']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", addr_code=NULL";
																		}
																		if( (int)$param['info']['sameaddress'] == 1 ){
																			$param['info']['per_st'] 	= $param['info']['addr_st'];
																			$param['info']['per_area']	= $param['info']['addr_area'];	
																			$param['info']['per_city']	= $param['info']['addr_city'];	
																			$param['info']['per_prov']	= $param['info']['addr_prov'];	
																			$param['info']['per_code']	= $param['info']['addr_code'];	
																		}
																		if( !empty( $param['info']['per_st'] ) ){
																			$Qry->selected 	= $Qry->selected . ", per_st='".$param['info']['per_st']."'";																			
																		}else{
																			$Qry->selected 	= $Qry->selected . ", per_st=NULL";																			
																		}
																		if( !empty( $param['info']['per_area'] ) ){
																			$Qry->selected 	= $Qry->selected . ", per_area='".$param['info']['per_area']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", per_area=NULL";
																		}
																		if( !empty( $param['info']['per_city'] ) ){
																			$Qry->selected 	= $Qry->selected . ", per_city='".$param['info']['per_city']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", per_city=NULL";
																		}
																		if( !empty( $param['info']['per_prov'] ) ){
																			$Qry->selected 	= $Qry->selected . ", per_prov='".$param['info']['per_prov']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", per_prov=NULL";
																		}
																		if( !empty( $param['info']['per_code'] ) ){
																			$Qry->selected 	= $Qry->selected . ", per_code='".$param['info']['per_code']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", per_code=NULL";
																		}
																		if( !empty( $param['info']['email'] ) ){
																			$Qry->selected 	= $Qry->selected . ", email='".$param['info']['email']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", email=NULL";
																		}
																		if( !empty( $param['info']['emergency_number'] ) ){
																			$Qry->selected 	= $Qry->selected . ", emergency_number='".$param['info']['emergency_number']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", emergency_number=NULL";
																		}
																		if( !empty( $param['info']['emergency_name'] ) ){
																			$Qry->selected 	= $Qry->selected . ", emergency_name='".$param['info']['emergency_name']."'";																			
																		}else{
																			$Qry->selected 	= $Qry->selected . ", emergency_name=NULL";
																		}
																		if( !empty( $param['info']['pnumber'] ) ){
																			$Qry->selected 	= $Qry->selected . ", pnumber='".$param['info']['pnumber']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", pnumber=NULL";
																		}
																		if( !empty( $param['info']['bloodtype'] ) ){
																			$Qry->selected 	= $Qry->selected . ", bloodtype='".$param['info']['bloodtype']."'";																			
																		}else{
																			$Qry->selected 	= $Qry->selected . ", bloodtype=NULL";
																		}
																		if( !empty( $param['info']['spouse'] ) ){
																			$Qry->selected 	= $Qry->selected . ", spouse='".$param['info']['spouse']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", spouse=NULL";
																		}
																		if( !empty( $param['info']['idtin'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idtin='".$param['info']['idtin']."'";
																			if( !empty( $param['info']['tin_date'] ) ){
																				$Qry->selected 	= $Qry->selected . ", tin_date='".$param['info']['tin_date']."'";																				
																			}else{
																				$Qry->selected 	= $Qry->selected . ", tin_date=NULL";
																			}
																		}else{
																			$Qry->selected 	= $Qry->selected . ", idtin=NULL";
																			$Qry->selected 	= $Qry->selected . ", tin_date=NULL";
																		}
																		if( !empty( $param['info']['idsss'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idsss='".$param['info']['idsss']."'";
																			if( !empty( $param['info']['sss_date'] ) ){
																				$Qry->selected 	= $Qry->selected . ", sss_date='".$param['info']['sss_date']."'";																				
																			}else{
																				$Qry->selected 	= $Qry->selected . ", sss_date=NULL";
																			}
																		}else{
																			$Qry->selected 	= $Qry->selected . ", idsss=NULL";
																			$Qry->selected 	= $Qry->selected . ", sss_date=NULL";
																		}
																		if( !empty( $param['info']['idhealth'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idhealth='".$param['info']['idhealth']."'";
																			if( !empty( $param['info']['health_date'] ) ){
																				$Qry->selected 	= $Qry->selected . ", health_date='".$param['info']['health_date']."'";
																			}else{
																				$Qry->selected 	= $Qry->selected . ", health_date=NULL";
																			}
																		}else{
																			$Qry->selected 	= $Qry->selected . ", idhealth=NULL";
																			$Qry->selected 	= $Qry->selected . ", health_date=NULL";
																		}
																		if( !empty( $param['info']['idibig'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idibig='".$param['info']['idibig']."'";
																			if( !empty( $param['info']['ibig_date'] ) ){
																				$Qry->selected 	= $Qry->selected . ", ibig_date='".$param['info']['ibig_date']."'";																				
																			}else{
																				$Qry->selected 	= $Qry->selected . ", ibig_date=NULL";
																			}
																		}else{
																			$Qry->selected 	= $Qry->selected . ", idibig=NULL";
																			$Qry->selected 	= $Qry->selected . ", ibig_date=NULL";
																		}
																		if( !empty( $param['info']['idpayroll'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idpayroll='".$param['info']['idpayroll']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", idpayroll=NULL";
																		}
																		if( !empty( $param['info']['idbank'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idbank='".$param['info']['idbank']."'";																			
																			if( !empty( $param['info']['idpayroll'] ) ){
																				$Qry->selected 	= $Qry->selected . ", idpayroll='".$param['info']['idpayroll']."'";																				
																			}else{
																				$Qry->selected 	= $Qry->selected . ", idpayroll=NULL";
																			}
																		}else{
																			$Qry->selected 	= $Qry->selected . ", idbank=NULL";
																		}
																		
																		if( !empty( $param['info']['height_ft'] ) ){
																			$Qry->selected 	= $Qry->selected . ", height_ft='".$param['info']['height_ft']."'";																			
																		}else{
																			$Qry->selected 	= $Qry->selected . ", height_ft=NULL";
																		}
																		if( !empty( $param['info']['height_inch'] ) ){
																			$Qry->selected 	= $Qry->selected . ", height_inch='".$param['info']['height_inch']."'";																			
																		}else{
																			$Qry->selected 	= $Qry->selected . ", height_inch=NULL";
																		}
																		if( !empty( $param['info']['weight_lbs'] ) ){
																			$Qry->selected 	= $Qry->selected . ", weight_lbs='".$param['info']['weight_lbs']."'";																			
																		}else{
																			$Qry->selected 	= $Qry->selected . ", weight_lbs=NULL";
																		}
																		
																		if( !empty( $param['info']['eyecolor'] ) ){
																			$Qry->selected 	= $Qry->selected . ", eyecolor='".$param['info']['eyecolor']."'";																			
																		}else{
																			$Qry->selected 	= $Qry->selected . ", eyecolor=NULL";
																		}
																		if( !empty( $param['info']['haircolor'] ) ){
																			$Qry->selected 	= $Qry->selected . ", haircolor='".$param['info']['haircolor']."'";																			
																		}else{
																			$Qry->selected 	= $Qry->selected . ", haircolor=NULL";	
																		}
																		if( !empty( $param['info']['skincolor'] ) ){
																			$Qry->selected 	= $Qry->selected . ", skincolor='".$param['info']['skincolor']."'";																			
																		}else{
																			$Qry->selected 	= $Qry->selected . ", skincolor=NULL";
																		}
																		if( !empty( $param['info']['buildtype'] ) ){
																			$Qry->selected 	= $Qry->selected . ", buildtype='".$param['info']['buildtype']."'";																			
																		}else{
																			$Qry->selected 	= $Qry->selected . ", buildtype=NULL";
																		}

																		// if( !empty( $param['info']['sdate'] ) ){
																		// 	$Qry->selected 	= $Qry->selected . ", sdate='".$param['info']['sdate']."'";																			
																		// }else{
																		// 	$Qry->selected 	= $Qry->selected . ", sdate=NULL";
																		// }
																		
																		if( !empty($pic) ){
																			$Qry->selected 	= $Qry->selected . ", pic='".$pic."'";																			
																		}else{
																			if( $param['info']['pic_orig'] != $param['info']['eprof_pic']  ){
																				$Qry->selected 	= $Qry->selected . ", pic=NULL";																			
																			}
																		}
																		
																		
																		$Qry->fields 		= "id='".$param['info']['id']."'";
																		$checke 			= $Qry->exe_UPDATE($con);
																		if($checke){
																			$last_id = $param['info']['id'];
																			

																			if($param['info']['salary'] != $param['info']['salary_old']){
																				$updatesalhist = updateSalHist($con,$param['info']['id'],$param['info']['salary'],$param['info']['efdate']);
																				//Send email notification
																				$mailSubject = "KPI HRIS 2.0 - Change Salary Rate";
																				$mailBody = "<h4>201 Record - Change Salary Rate</h4>";
																				$mailBody .= "Employee Name: ".getEmpFullName($con,$param['info']['id']);
																				$mailBody .= "<br />Basic Salary Rate Amount Change From Php ".$param['info']['salary_old'];
																				$mailBody .= "<br />Basic Salary Rate Change To Php ".$param['info']['salary'];
																				$mailBody .= "<br />Basic Salary Rate Change By ".getEmpFullName($con,$param['accountid']);
																				$mailBody .= "<br />Date Change: ".$date; 
																		
																				$mailBody .="<br /><br />This is a system generated notification.<br />";
																				//$return1 = _EMAILSALARY_PROFILE(getEmail($con,$idact = '121'),$mailSubject, $mailBody,$param['accountid']);	// magnolia casimiro
																				//$return2 = _EMAILSALARY_PROFILE(getEmail($con,$idact = '120'),$mailSubject, $mailBody,$param['accountid']); // clare raymundo
																				//$return3 = _EMAILSALARY_PROFILE(getEmail($con,$idact = '135'),$mailSubject, $mailBody,$param['accountid']); // katsuya kagiwada
																			}

																			if($param['info']['idpayroll'] != $param['info']['idpayroll_old']){
																				//Send email notification
																				$mailSubject = "KPI HRIS 2.0 - Change Bank Account Number";
																				$mailBody = "<h4>201 Record - Change Bank Account Number</h4>";
																				$mailBody .= "Employee Name: ".getEmpFullName($con,$param['info']['id']);
																				$mailBody .= "<br />Bank Account Number Change From ".$param['info']['idpayroll_old'];
																				$mailBody .= "<br />Bank Account Number Change To ".$param['info']['idpayroll'];
																				$mailBody .= "<br />Bank Account Number Change By".getEmpFullName($con,$param['accountid']);
																				$mailBody .= "<br />Date Change: ".$date; 
																		
																				$mailBody .="<br /><br />This is a system generated notification.<br />";
																				//$return1 = _EMAILSALARY_PROFILE(getEmail($con,$idact = '121'),$mailSubject, $mailBody,$param['accountid']);	// magnolia casimiro
																				//$return2 = _EMAILSALARY_PROFILE(getEmail($con,$idact = '120'),$mailSubject, $mailBody,$param['accountid']); // clare raymundo
																				//$return3 = _EMAILSALARY_PROFILE(getEmail($con,$idact = '135'),$mailSubject, $mailBody,$param['accountid']); // katsuya kagiwada
																			}
																			
																			//upload profile pic
																			if( array_key_exists('file',$_FILES) ){		
																				if( array_key_exists(0,$_FILES['file']['name']) ){
																					$folder_path 	= $param['targetPath'];
																					$name 			= $_FILES['file']['name'][0];
																					$t				= strtotime($date).time();	
																					$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
																					$save_name		= $param['info']['empid'].'.'.$extMove;	
																					move_uploaded_file($_FILES["file"]["tmp_name"][0], $folder_path.$save_name);
																				}
																			}
																			
																			//upload attachment
																			foreach( $param['info']['docs'] as $ndx => $vv ){
																				if( !empty( $vv ) ){
																					$picNdx = $ndx;
																					$ok_upload = true;
																					if( (int)$vv == 1 ){
																						if( (int)$param['info']['isprivacy'] == 0 ){
																							$ok_upload = false;
																						}
																					}elseif( (int)$vv == 2 ){
																						if( (int)$param['info']['civilstat'] <= 1 || (int)$param['info']['civilstat'] == 3 ){
																							$ok_upload = false;
																						}
																					}elseif( (int)$vv == 11 ){
																						if( ("".$param['info']['isprevemp']['checked']) == "false" ){
																							$ok_upload = false;
																						}
																					}
																					
																					if( array_key_exists('attachment_files',$_FILES) ){		
																						if( array_key_exists( $picNdx ,$_FILES['attachment_files']['name']) ){
																							if( empty( $_FILES['attachment_files']['name'][$picNdx] ) ){
																								$ok_upload = false;
																							}
																						}else{
																							$ok_upload = false;
																						}
																					}else{
																						$ok_upload = false;
																					}
																					
																					if( $ok_upload ){
																						$folder_path 	= $param['docPath'].$param['info']['empid'];
																						if( !is_dir($folder_path) ){
																							mkdir($folder_path);				
																						}
																						$name 			= $_FILES['attachment_files']['name'][$picNdx];
																						$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
																						$save_name		= getDocumentName($con,$vv).'.'.$extMove;	
																						move_uploaded_file($_FILES["attachment_files"]["tmp_name"][$picNdx], $folder_path.'/'.$save_name);
																						if( needtoAdd($con, $vv,  $last_id) ){
																							$Qry 			= new Query();	
																							$Qry->table 	= "tblaccountdoc";
																							$Qry->selected 	= "idacct,
																											   iddoc,
																											   doc";
																							$Qry->fields 	= "'".$last_id."',
																												'".$vv."',
																												'".$save_name."'";
																							$Qry->exe_INSERT($con);
																						}
																					}
																				}
																			}
																			
																			//INSERT GLOBAL ACCOUNT
																			foreach( $param['info']['dbs'] as $z ){
																				$arresult = deleteGlobalAccount($con,$param['info']['id'], $z['id']);
																				if( filter_var($z['checked'], FILTER_VALIDATE_BOOLEAN) ){
																					insertGlobalAccount($con, $param['info']['id'], $z['id'] );
																				}
																			}
																			
																			if( !empty($param['info']['eb']) ){
																				foreach(  $param['info']['eb'] as $value) {
																					$Qryueb 			= new Query();	
																					$Qryueb->table 	    = "tblaccountedubg";
																					$Qryueb->selected 	= "attainment	='". $value['attainment'] ."',
																										school       ='". $value['school'] ."',
																										dfrom	    ='". $value['dfrom'] ."',
																										dto	        ='". $value['dto'] ."'";
																					$Qryueb->fields 	= "id           ='".$value['id']."'";
																					$Qryueb->exe_UPDATE($con);  
																				}
																			}

																			if( !empty($param['info']['eh']) ){
																				foreach(  $param['info']['eh'] as $value) {
																					$Qryueb 			= new Query();	
																					$Qryueb->table 	    = "tblaccountemphis";
																					$Qryueb->selected 	= "company	='". $value['company'] ."',
																										  position  ='". $value['position'] ."',
																										  dfrom	    ='". $value['dfrom'] ."',
																										  dto	        ='". $value['dto'] ."'";
																					$Qryueb->fields 	= "id           ='".$value['id']."'";
																					$Qryueb->exe_UPDATE($con);  
																				}
																			}

																			if( !empty($param['info']['et']) ){
																				foreach(  $param['info']['et'] as $value) {
																					$Qryueb 			= new Query();	
																					$Qryueb->table 	    = "tblaccountet";
																					$Qryueb->selected 	= "et	        ='". $value['et'] ."',
																										  issueorg	    ='". $value['issueorg'] ."',
																										  facilitator	='". $value['facilitator'] ."',
																										  location      ='". $value['location'] ."',
																										  date	        ='". $value['date'] ."'";
																					$Qryueb->fields 	= "id           ='".$value['id']."'";
																					$Qryueb->exe_UPDATE($con);  
																				}
																			}

																			if( !empty($param['info']['tt']) ){
																				foreach(  $param['info']['tt'] as $value) {
																					$Qryueb 			= new Query();	
																					$Qryueb->table 	    = "tblaccountet";
																					$Qryueb->selected 	= "et	        ='". $value['et'] ."',
																										  issueorg	    ='". $value['issueorg'] ."',
																										  facilitator	='". $value['facilitator'] ."',
																										  location      ='". $value['location'] ."',
																										  date	        ='". $value['date'] ."'";
																					$Qryueb->fields 	= "id           ='".$value['id']."'";
																					$Qryueb->exe_UPDATE($con);  
																				}
																			}
																		
																			if( !empty($param['info']['neb']) ){
																				foreach($param['info']['neb'] as $key => $value ) {
																					if( !empty( $value ) ){
																						$ebschool = $param['info']['nebschool'];
																						$ebfrom = $param['info']['nebfrom'];
																						$ebto = $param['info']['nebto'];

																						$Qry 			= new Query();	
																						$Qry->table 	= "tblaccountedubg";
																						$Qry->selected 	= "idacct,
																											attainment,
																											school,
																											dfrom,
																											dto";
																						$Qry->fields 	= "'".$param['info']['id']."',
																											'".$value."',
																											'".$ebschool[$key]."',
																											'".$ebfrom[$key]."',
																											'".$ebto[$key]."'";
																						$Qry->exe_INSERT($con);
																					}
																				}
																			}

																			if( !empty($param['info']['nehcompany']) ){
																				foreach($param['info']['nehcompany'] as $key => $value ) {
																					if( !empty( $value ) ){
																						$ehposition = $param['info']['nehposition'];
																						$ehfrom = $param['info']['nehfrom'];
																						$ehto = $param['info']['nehto'];

																						$Qry 			= new Query();	
																						$Qry->table 	= "tblaccountemphis";
																						$Qry->selected 	= "idacct,
																											company,
																											position,
																											dfrom,
																											dto";
																						$Qry->fields 	= "'".$param['info']['id']."',
																											'".$value."',
																											'".$ehposition[$key]."',
																											'".$ehfrom[$key]."',
																											'".$ehto[$key]."'";
																						$Qry->exe_INSERT($con);
																					}
																				}
																			}

																			if( !empty($param['info']['netexam']) ){
																				foreach($param['info']['netexam'] as $key => $value ) {
																					if( !empty( $value ) ){
																						$etlocation = $param['info']['netlocation'];
																						$etdate = $param['info']['netdate'];
																						$etorg = $param['info']['netorg'];
																						$etfac = $param['info']['netfac'];

																						$Qry 			= new Query();	
																						$Qry->table 	= "tblaccountet";
																						$Qry->selected 	= "idacct,
																											issueorg,
																											facilitator,
																											et,
																											location,
																											date,
																											type";
																						$Qry->fields 	= "'".$param['info']['id']."',
																											'".$etorg[$key]."',
																											'".$etfac[$key]."',
																											'".$value."',
																											'".$etlocation[$key]."',
																											'".$etdate[$key]."',
																											'exam'";
																						$Qry->exe_INSERT($con);
																					}
																				}
																			}

																			if( !empty($param['info']['ntttraining']) ){
																				foreach($param['info']['ntttraining'] as $key => $value ) {
																					if( !empty( $value ) ){
																						$ttlocation = $param['info']['nttlocation'];
																						$ttdate = $param['info']['nttdate'];
																						$etorg = $param['info']['nttorg'];
																						$etfac = $param['info']['nttfac'];

																						$Qry 			= new Query();	
																						$Qry->table 	= "tblaccountet";
																						$Qry->selected 	= "idacct,
																											issueorg,
																											facilitator,
																											et,
																											location,
																											date,
																											type";
																						$Qry->fields 	= "'".$param['info']['id']."',
																											'".$etorg[$key]."','".$etfac[$key]."',
																											'".$value."',
																											'".$ttlocation[$key]."',
																											'".$ttdate[$key]."',
																											'training'";
																						$Qry->exe_INSERT($con);
																					}
																				}
																			}
																			
																			foreach($param['info']['pa'] as $key => $value ) {
																				$pa_ok 			= true;
																				$pa_id			= $value['id'];
																				$equi_tools 	= str_replace("'","",$value['equi_tools']);
																				$serial 		= str_replace("'","",$value['serial']);
																				$asset 			= str_replace("'","",$value['asset']);
																				$quantity 		= $value['quantity'];
																				$date_issued	= $value['date_issued'];
																				$date_returned	= $value['date_returned'];
																				if( !empty($equi_tools) && !empty($serial) && !empty($quantity) && !empty($date_issued) ){
																					$Qry 			= new Query();	
																					$Qry->table 	= "tblaccountpropacc";
																					if( empty( $pa_id ) ){
																						$Qry->selected 	= "idacct,equi_tools,serial,assetcode,quantity,date_issued";
																						$Qry->fields 	= "'".$last_id."','".$equi_tools."','".$serial."','".$asset."','".$quantity."','".$date_issued."'";
																					}else{
																						$Qry->selected 	= "equi_tools='".$equi_tools."',serial='".$serial."',assetcode='".$asset."',quantity='".$quantity."',date_issued='".$date_issued."'";
																					}
																					if( !empty( $date_returned ) ){
																						if( empty( $pa_id ) ){
																							$Qry->selected 	= $Qry->selected 	. ",date_returned";
																							$Qry->fields 	= $Qry->fields 		. ",'".$date_returned."'";
																						}else{
																							$Qry->selected 	= $Qry->selected 	. ",date_returned='".$date_returned."'";
																						}
																						if( strtotime( $date_issued ) > strtotime( $date_returned ) ){
																							$pa_ok = false;
																						}else{
																							$pa_ok = true;
																						}
																					}
																					if( $pa_ok ) {
																						if( empty( $pa_id ) ){
																							$Qry->exe_INSERT($con);
																						}else{
																							$Qry->fields 	= "id = '".$pa_id."'";
																							$Qry->exe_UPDATE($con);
																						}
																					}
																				}
																			}
																			
																			//Assign Update
																			if( empty( $param['info']['idassign'] ) ){
																				$return = insertInfo($con, $param);
																			}else{
																				$return = updateInfo($con, $param);
																			}
																			
																			//ADD TO LOGS
																			$return = inserLogs($con, $param['accountid'], "Updated 201 Data of ".$param['info']['lname']." ,".$param['info']['fname']." ".$param['info']['mname']);
																			$return = json_encode(array("status"=>"success"));
																		}else{
																			$return = json_encode(array("status"=>"error"));
																		}
																	}else{
																		$return = json_encode(array("status"=>"idtax"));
																	}
																}
															}else{
																$return = json_encode(array("status"=>"civilstat"));
															}
														}else{
															$return = json_encode(array("status"=>"religion"));
														}
													}else{
														$return = json_encode(array("status"=>"citizenship"));
													}
												// }else{
												// 	$return = json_encode(array("status"=>"bplace"));
												// }										
											}else{
												$return = json_encode(array("status"=>"bdate"));
											}
										}else{
											$return = json_encode(array("status"=>"cnumber"));
										}
									}else{
										$return = json_encode(array("status"=>"address"));
									}
								}else{
									$return = json_encode(array("status"=>"idemptype"));
								}
							}else{
								$return = json_encode(array("status"=>"idaccttype"));
							}
						}else{
							$return = json_encode(array("status"=>"sex"));
						}
					}else{
						$return = json_encode(array("status"=>"lname"));
					}
				}else{
					$return = json_encode(array("status"=>"mname"));
				}
			}else{
				$return = json_encode(array("status"=>"nofname"));
			}			
		}else{
			$return = json_encode(array("status"=>"noempid"));
		}	
}else{
	$return = json_encode(array("status"=>"notloggedin"));
}


print $return;
mysqli_close($con);


function insertInfo($con, $param){
	
	if( empty( $param['info']['riceallowance'] ) ){
		$param['info']['riceallowance'] = "0.00";
	}
	if( empty( $param['info']['clothingallowance'] ) ){
		$param['info']['clothingallowance'] = "0.00";
	}
	if( empty( $param['info']['laundryallowance'] ) ){
		$param['info']['laundryallowance'] = "0.00";
	}
	if( empty( $param['info']['sss_amt'] ) ){
		$param['info']['sss_amt'] = "0.00";
	}
	if( empty( $param['info']['ibig_amt'] ) ){
		$param['info']['ibig_amt'] = "0.00";
	}
	if( empty( $param['info']['health_amt'] ) ){
		$param['info']['health_amt'] = "0.00";
	}
	
	$param['info']['sss_deduct1']['id'] = 0;
	$param['info']['sss_deduct2']['id'] = 0;
	$param['info']['ibig_deduct1']['id'] = 0;
	$param['info']['ibig_deduct2']['id'] = 0;
	$param['info']['health_deduct1']['id'] = 0;
	$param['info']['health_deduct2']['id'] = 0;
	$param['info']['rev_deduct1']['id'] = 0;
	$param['info']['rev_deduct2']['id'] = 0;
	$param['info']['isprevemp']['id'] = 0;
	if( ("".$param['info']['sss_deduct1']['checked']) == "true" ){
		$param['info']['sss_deduct1']['id'] = 1;
	}
	if( ("".$param['info']['sss_deduct2']['checked']) == "true" ){
		$param['info']['sss_deduct2']['id'] = 1;
	}
	if( ("".$param['info']['ibig_deduct1']['checked']) == "true" ){
		$param['info']['ibig_deduct1']['id'] = 1;
	}
	if( ("".$param['info']['ibig_deduct2']['checked']) == "true" ){
		$param['info']['ibig_deduct2']['id'] = 1;
	}
	if( ("".$param['info']['health_deduct1']['checked']) == "true" ){
		$param['info']['health_deduct1']['id'] = 1;
	}
	if( ("".$param['info']['health_deduct2']['checked']) == "true" ){
		$param['info']['health_deduct2']['id'] = 1;
	}
	if( ("".$param['info']['rev_deduct1']['checked']) == "true" ){
		$param['info']['rev_deduct1']['id'] = 1;
	}
	if( ("".$param['info']['rev_deduct2']['checked']) == "true" ){
		$param['info']['rev_deduct2']['id'] = 1;
	}
	if( ("".$param['info']['isprevemp']['checked']) == "true" ){
		$param['info']['isprevemp']['id'] = 1;
	}
	
	$Qry 			= new Query();	
	$Qry->table 	= "tblaccountjob";
	$Qry->selected 	= "basicpay_type,absences_type,lates_type,undertime_type,idcba,sss_type,ibig_type,health_type,sss_amt,ibig_amt,health_amt,
					   sss_deduct1,sss_deduct2,ibig_deduct1,ibig_deduct2,health_deduct1,health_deduct2,rev_deduct1,rev_deduct2,isprevemp,
					   rice_method,clothing_method,laundry_method,
					   customer,product,idacct,idpos,idunit,regcode,provcode,munid,idlabor,idsuperior,empstat,wshift,schedtype,hdate,salary,idpaygrp,paystat,idrevenue,dependent,idrelease,idlvl,idlvlscale,riceallowance,clothingallowance,laundryallowance";
	$Qry->fields 	= "'".(int)$param['info']['basicpay']."', '".(int)$param['info']['absences']."', '".(int)$param['info']['lates']."', '".(int)$param['info']['undertime']."',
					   '".(int)$param['info']['idcba']."', '".(int)$param['info']['sss_type']."', '".(int)$param['info']['ibig_type']."', '".(int)$param['info']['health_type']."',
					   '".str_replace(",","",$param['info']['sss_amt'])."',
					   '".str_replace(",","",$param['info']['ibig_amt'])."',
					   '".str_replace(",","",$param['info']['health_amt'])."',
					   '".(int)$param['info']['sss_deduct1']['id']."', '".(int)$param['info']['sss_deduct2']['id']."',
					   '".(int)$param['info']['ibig_deduct1']['id']."', '".(int)$param['info']['ibig_deduct2']['id']."',
					   '".(int)$param['info']['health_deduct1']['id']."', '".(int)$param['info']['health_deduct2']['id']."',
					   '".(int)$param['info']['rev_deduct1']['id']."', '".(int)$param['info']['rev_deduct2']['id']."',
					   '".(int)$param['info']['isprevemp']['id']."', '".(int)$param['info']['rice_method']."', '".(int)$param['info']['clothing_method']."','".(int)$param['info']['laundry_method']."', 
					   '".str_replace("'","",$param['info']['customer'])."',
					   '".str_replace("'","",$param['info']['product'])."',
					   '".$param['info']['idacct']."',
	                   '".$param['info']['idpos']."',
					   '".$param['info']['idunit']."',
					   '".$param['info']['regions']."',
					   '".$param['info']['provinces']."',
					   '".$param['info']['municipality']."',
					   '".$param['info']['idlabor']."',
					   '".$param['info']['idsuperior']."',
					   '".$param['info']['empstat']."',
					   '".$param['info']['wshift']."',
					   '".$param['info']['schedtype']."',
					   '".$param['info']['hdate']."',
					   '".str_replace(",","",$param['info']['salary'])."',
					   '".$param['info']['idpaygrp']."',
					   '".$param['info']['idpaystat']."',
					   '".$param['info']['idrevenue']."',
					   '".(int)$param['info']['dependent']."',
					   '".$param['info']['idrelease']."',
					   '".$param['info']['idlvl']."','".$param['info']['idlvlscale']."',
					   '".str_replace(",","",$param['info']['riceallowance'])."',
					   '".str_replace(",","",$param['info']['clothingallowance'])."',
					   '".str_replace(",","",$param['info']['laundryallowance'])."'";
	if( !empty( $param['info']['rdate'] ) ){
		$Qry->selected 	= $Qry->selected . ", rdate";
		$Qry->fields 	= $Qry->fields 	 . ",'".$param['info']['rdate']."'";
	}
	if( !empty( $param['info']['sdate'] ) && (int)$param['info']['empstat'] == 6 ){
		$Qry->selected 	= $Qry->selected . ", sdate";
		$Qry->fields 	= $Qry->fields 	 . ",'".$param['info']['sdate']."'";
	}
	if( !empty( $param['info']['contract_sdate'] ) && ((int)$param['info']['empstat'] == 4 || (int)$param['info']['empstat'] == 8)){
		$Qry->selected 	= $Qry->selected . ", contract_sdate";
		$Qry->fields 	= $Qry->fields 	 . ",'".$param['info']['contract_sdate']."'";
	}
	if( !empty( $param['info']['contract_fdate'] ) && ((int)$param['info']['empstat'] == 4 || (int)$param['info']['empstat'] == 8)){
		$Qry->selected 	= $Qry->selected . ", contract_fdate";
		$Qry->fields 	= $Qry->fields 	 . ",'".$param['info']['contract_fdate']."'";
	}
	if( !empty( $param['info']['prev_employer'] ) ){
		$Qry->selected 	= $Qry->selected . ", prev_employer";
		$Qry->fields 	= $Qry->fields 	 . ",'".$param['info']['prev_employer']."'";
	}
	if( ("".$param['info']['isprevemp']['checked']) == "true" ){
		if( empty( $param['info']['gross'] ) || strlen($param['info']['gross'])==0 ){
			$param['info']['gross'] 	= "0.00";
		}
		if( empty( $param['info']['taxable'] ) || strlen($param['info']['taxable'])==0 ){
			$param['info']['taxable'] 	= "0.00";
		}
		if( empty( $param['info']['nontax'] ) || strlen($param['info']['nontax'])==0 ){
			$param['info']['nontax'] 	= "0.00";
		}
		if( empty( $param['info']['addex'] ) || strlen($param['info']['addex'])==0 ){
			$param['info']['addex'] 	= "0.00";
		}
		if( empty( $param['info']['month13'] ) || strlen($param['info']['month13'])==0 ){
			$param['info']['month13'] 	= "0.00";
		}
		if( empty( $param['info']['deminimis'] ) || strlen($param['info']['deminimis'])==0){
			$param['info']['deminimis'] = "0.00";
		}
	}else{
		$param['info']['gross'] 	= "0.00";
		$param['info']['taxable'] 	= "0.00";
		$param['info']['nontax'] 	= "0.00";
		$param['info']['addex'] 	= "0.00";
		$param['info']['month13'] 	= "0.00";
		$param['info']['deminimis'] = "0.00";
	}
	$Qry->selected 	= $Qry->selected . ", gross, taxable, nontax, addex, month13, deminimis";
	$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['gross']."', '".$param['info']['taxable']."', '".$param['info']['nontax']."', '".$param['info']['addex']."', '".$param['info']['month13']."', '".$param['info']['deminimis']."'";
	
	$checke 		= $Qry->exe_INSERT($con);
	if($checke){
		
		if( (int)$param['info']['dependent'] > 0 ){
			foreach( $param['info']['dependents'] as $kk=>$vv ){
				if( !empty( $vv["name"] ) && !empty( $vv["birthday"] ) ){
					$Qry4 			= new Query();	
					$Qry4->table	= "tblacctdependent";
					$Qry4->selected = "idacct, name, birthday";
					$Qry4->fields 	= "'".$param['info']['idacct']."', '".$vv["name"]."', '".$vv["birthday"]."'";
					$checke4 		= $Qry4->exe_INSERT($con);	
				}
			}
		}
		// if( (int)$param['info']['empstat'] == 5 ){
		// 	$unique_lv = array();
		// 	foreach( $param['info']['leaves'] as $keys=>$val ){
		// 		if( !empty( $val['id'] ) && !in_array($val['id'],$unique_lv) ){
		// 			$ok_proceed = true;
		// 			if( $param['info']['sex'] == "F" && ( (int)$val['id'] == 7 ) ){
		// 				$ok_proceed = false;
		// 			}elseif( $param['info']['sex'] == "M" && ( (int)$val['id'] == 11 || (int)$val['id'] == 20 ) ){
		// 				$ok_proceed = false;
		// 			}
		// 			if( $ok_proceed ){
		// 				$idleave 	= $val['id'];
		// 				$idacct		= $param['info']['idacct'];
		// 				$entitle	= (int)$val['entitle'];
		// 				$used		= getLeaveUsed($con, $idacct, $idleave);
		// 				$balance	= (int)$entitle - (int)$used;
		// 				$Qry2 			= new Query();	
		// 				$Qry2->table	= "tblaccountleaves";
		// 				$Qry2->selected = "idacct, idleave, entitle, used, balance";
		// 				$Qry2->fields 	= "'".$idacct."', '".$idleave."', '".$entitle."', '".$used."', '".$balance."'";
		// 				$checke2 		= $Qry2->exe_INSERT($con);	
		// 			}
		// 			array_push($unique_lv,$val['id']);
		// 		}
		// 	}
		// }
		
		// $unique_al = array();
		// foreach( $param['info']['acctallowance'] as $keyss=>$valss ){
		// 	if( !empty( $valss["idallowance"]) && !in_array($valss['idallowance'],$unique_al) ) {
		// 		array_push($unique_al,$valss['idallowance']);
		// 		if( empty( $valss["amt"] ) ){
		// 			$valss["amt"] = "0.00";
		// 		}
		// 		$idacct			= $param['info']['idacct'];
		// 		$idallowance 	= $valss['idallowance'];
		// 		$idmethod		= $valss['idmethod'];
		// 		$amt			= str_replace(",","",$valss["amt"]);
		// 		$Qry3 			= new Query();	
		// 		$Qry3->table	= "tblacctallowance";
		// 		$Qry3->selected = "idacct, idallowance, idmethod, amt";
		// 		$Qry3->fields 	= "'".$idacct."', '".$idallowance."', '".$idmethod."', '".$amt."'";
		// 		$checke3 		= $Qry3->exe_INSERT($con);	
		// 	}
		// }
		if( $param['info']['wshift'] == $param['info']['ogshift'] ){
			insertTimeKeepingRec( $con, $param['info']['wshift'], $param['info']['idacct'] );
		}else{
			insertTimeKeepingRec2( $con, $param['info']['wshift'], $param['info']['idacct'] );
		}
		//ADD TO LOGS
		$return = inserLogs($con, $param['accountid'], "Added Assigned 201 Data of ".getEmployeeName($con, $param['info']['idacct']));
		return json_encode(array("status"=>"success"));		
	}else{
		return json_encode(array("status"=>"error"));
	}
}

function updateInfo($con, $param){
	
	if( empty( $param['info']['riceallowance'] ) ){
		$param['info']['riceallowance'] = "0.00";
	}
	if( empty( $param['info']['clothingallowance'] ) ){
		$param['info']['clothingallowance'] = "0.00";
	}
	if( empty( $param['info']['laundryallowance'] ) ){
		$param['info']['laundryallowance'] = "0.00";
	}
	if( empty( $param['info']['sss_amt'] ) ){
		$param['info']['sss_amt'] = "0.00";
	}
	if( empty( $param['info']['ibig_amt'] ) ){
		$param['info']['ibig_amt'] = "0.00";
	}
	if( empty( $param['info']['health_amt'] ) ){
		$param['info']['health_amt'] = "0.00";
	}
	
	$param['info']['sss_deduct1']['id'] = 0;
	$param['info']['sss_deduct2']['id'] = 0;
	$param['info']['ibig_deduct1']['id'] = 0;
	$param['info']['ibig_deduct2']['id'] = 0;
	$param['info']['health_deduct1']['id'] = 0;
	$param['info']['health_deduct2']['id'] = 0;
	$param['info']['rev_deduct1']['id'] = 0;
	$param['info']['rev_deduct2']['id'] = 0;
	$param['info']['isprevemp']['id'] = 0;
	if( ("".$param['info']['sss_deduct1']['checked']) == "true" ){
		$param['info']['sss_deduct1']['id'] = 1;
	}
	if( ("".$param['info']['sss_deduct2']['checked']) == "true" ){
		$param['info']['sss_deduct2']['id'] = 1;
	}
	if( ("".$param['info']['ibig_deduct1']['checked']) == "true" ){
		$param['info']['ibig_deduct1']['id'] = 1;
	}
	if( ("".$param['info']['ibig_deduct2']['checked']) == "true" ){
		$param['info']['ibig_deduct2']['id'] = 1;
	}
	if( ("".$param['info']['health_deduct1']['checked']) == "true" ){
		$param['info']['health_deduct1']['id'] = 1;
	}
	if( ("".$param['info']['health_deduct2']['checked']) == "true" ){
		$param['info']['health_deduct2']['id'] = 1;
	}
	if( ("".$param['info']['rev_deduct1']['checked']) == "true" ){
		$param['info']['rev_deduct1']['id'] = 1;
	}
	if( ("".$param['info']['rev_deduct2']['checked']) == "true" ){
		$param['info']['rev_deduct2']['id'] = 1;
	}
	if( ("".$param['info']['isprevemp']['checked']) == "true" ){
		$param['info']['isprevemp']['id'] = 1;
	}

	if(!empty($param['info']['batchnum'])){
		$batchnum = implode (",", $param['info']['batchnum']);
	}

	$Qry 			= new Query();	
	$Qry->table 	= "tblaccountjob";
	$Qry->selected 	= " idacct='".$param['info']['idacct']."',
						customer='".str_replace("'","",$param['info']['customer'])."',
						product='".str_replace("'","",$param['info']['product'])."',
						idpos='".$param['info']['idpos']."',
						site='".$param['info']['site']."',
						idunit='".$param['info']['idunit']."',
						idlabor='".$param['info']['idlabor']."',
						idsuperior='".$param['info']['idsuperior']."',
						empstat='".$param['info']['empstat']."',
						batchnum='".$batchnum."',
						wshift='".$param['info']['wshift']."',
						schedtype='".$param['info']['schedtype']."',
						hdate='".$param['info']['hdate']."',
						salary='".str_replace(",","",$param['info']['salary'])."',
						idpaygrp='".$param['info']['idpaygrp']."',
						paystat='".$param['info']['idpaystat']."',
						idrevenue='".$param['info']['idrevenue']."',
						dependent='".(int)$param['info']['dependent']."',
						idrelease='".$param['info']['idrelease']."',
						idlvl='".$param['info']['idlvl']."',
						idlvlscale='".$param['info']['idlvlscale']."',
						riceallowance='".str_replace(",","",$param['info']['riceallowance'])."',
						clothingallowance='".str_replace(",","",$param['info']['clothingallowance'])."',
						laundryallowance='".str_replace(",","",$param['info']['laundryallowance'])."',
						basicpay_type='".(int)$param['info']['basicpay']."',
						absences_type='".(int)$param['info']['absences']."',
						lates_type='".(int)$param['info']['lates']."',
						undertime_type='".(int)$param['info']['undertime']."',
						idcba='".(int)$param['info']['idcba']."',
						sss_type='".(int)$param['info']['sss_type']."',
						ibig_type='".(int)$param['info']['ibig_type']."',
						health_type='".(int)$param['info']['health_type']."',
						daysmonth='".str_replace(",","",$param['info']['daysmonth'])."',
						sss_amt='".str_replace(",","",$param['info']['sss_amt'])."',
						ibig_amt='".str_replace(",","",$param['info']['ibig_amt'])."',
						health_amt='".str_replace(",","",$param['info']['health_amt'])."',
						sss_deduct1='".(int)$param['info']['sss_deduct1']['id']."',
						sss_deduct2='".(int)$param['info']['sss_deduct2']['id']."',
						ibig_deduct1='".(int)$param['info']['ibig_deduct1']['id']."',
						ibig_deduct2='".(int)$param['info']['ibig_deduct2']['id']."',
						health_deduct1='".(int)$param['info']['health_deduct1']['id']."',
						health_deduct2='".(int)$param['info']['health_deduct2']['id']."',
						rev_deduct1='".(int)$param['info']['rev_deduct1']['id']."',
						rev_deduct2='".(int)$param['info']['rev_deduct2']['id']."',
						isprevemp='".(int)$param['info']['isprevemp']['id']."',
						rice_method='".(int)$param['info']['rice_method']."',
						clothing_method='".(int)$param['info']['clothing_method']."',
						regcode='".$param['info']['regions']."',
						provcode='".$param['info']['provinces']."',
						munid='".(int)$param['info']['municipality']."',
						laundry_method='".(int)$param['info']['laundry_method']."'";
						//print_r((int)$param['info']['rdate'].'-');
	if( (int)$param['info']['rdate'] != 0){
		$Qry->selected 	= $Qry->selected . ", rdate='".$param['info']['rdate']."'";
	}else{
		$Qry->selected 	= $Qry->selected . ", rdate=NULL";
	}

	if(((int)$param['info']['empstat'] == 4 || (int)$param['info']['empstat'] == 8)){
		$Qry->selected 	= $Qry->selected . ", rdate=NULL";
	}

	if( !empty( $param['info']['sdate'] ) && (int)$param['info']['idemptype'] == 3 ){
		$Qry->selected 	= $Qry->selected . ", sdate='".$param['info']['sdate']."'";
	}else{
		$Qry->selected 	= $Qry->selected . ", sdate=NULL";
	}

	if($param['info']['sss1'] == 'true' ){
		$Qry->selected 	= $Qry->selected . ", sss_deduct1='1'";
	}else{
		$Qry->selected 	= $Qry->selected . ", sss_deduct1='0'";
	}
	if($param['info']['sss2'] == 'true' ){
		$Qry->selected 	= $Qry->selected . ", sss_deduct2='1'";
	}else{
		$Qry->selected 	= $Qry->selected . ", sss_deduct2='0'";
	}
	if($param['info']['pagibig1'] == 'true' ){
		$Qry->selected 	= $Qry->selected . ", ibig_deduct1='1'";
	}else{
		$Qry->selected 	= $Qry->selected . ", ibig_deduct1='0'";
	}
	if($param['info']['pagibig2'] == 'true' ){
		$Qry->selected 	= $Qry->selected . ", ibig_deduct2='1'";
	}else{
		$Qry->selected 	= $Qry->selected . ", ibig_deduct2='0'";
	}
	if($param['info']['philhealth1'] == 'true' ){
		$Qry->selected 	= $Qry->selected . ", health_deduct1='1'";
	}else{
		$Qry->selected 	= $Qry->selected . ", health_deduct1='0'";
	}
	if($param['info']['philhealth2'] == 'true' ){
		$Qry->selected 	= $Qry->selected . ", health_deduct2='1'";
	}else{
		$Qry->selected 	= $Qry->selected . ", health_deduct2='0'";
	}
	if($param['info']['rev1'] == 'true' ){
		$Qry->selected 	= $Qry->selected . ", rev_deduct1='1'";
	}else{
		$Qry->selected 	= $Qry->selected . ", rev_deduct1='0'";
	}
	if($param['info']['rev2'] == 'true' ){
		$Qry->selected 	= $Qry->selected . ", rev_deduct2='1'";
	}else{
		$Qry->selected 	= $Qry->selected . ", rev_deduct2='0'";
	}

	if( !empty( $param['info']['contract_sdate']) && ((int)$param['info']['empstat'] == 4 || (int)$param['info']['empstat'] == 8)){
		$Qry->selected 	= $Qry->selected . ", contract_sdate='".$param['info']['contract_sdate']."'";
	}else{
		$Qry->selected 	= $Qry->selected . ", contract_sdate=NULL";
	}
	if( !empty( $param['info']['contract_fdate']) && ((int)$param['info']['empstat'] == 4 || (int)$param['info']['empstat'] == 8)){
		$Qry->selected 	= $Qry->selected . ", contract_fdate='".$param['info']['contract_fdate']."'";
	}else{
		$Qry->selected 	= $Qry->selected . ", contract_fdate=NULL";
	}
	if( !empty( $param['info']['prev_employer'] ) ){
		$Qry->selected 	= $Qry->selected . ", prev_employer='".$param['info']['prev_employer']."'";
	}else{
		$Qry->selected 	= $Qry->selected . ", prev_employer=NULL";
	}
	
	if( ("".$param['info']['isprevemp']['checked']) == "true" ){
		if( empty( $param['info']['gross'] ) || strlen($param['info']['gross'])==0 ){
			$param['info']['gross'] 	= "0.00";
		}
		if( empty( $param['info']['taxable'] ) || strlen($param['info']['taxable'])==0 ){
			$param['info']['taxable'] 	= "0.00";
		}
		if( empty( $param['info']['nontax'] ) || strlen($param['info']['nontax'])==0 ){
			$param['info']['nontax'] 	= "0.00";
		}
		if( empty( $param['info']['addex'] ) || strlen($param['info']['addex'])==0 ){
			$param['info']['addex'] 	= "0.00";
		}
		if( empty( $param['info']['month13'] ) || strlen($param['info']['month13'])==0 ){
			$param['info']['month13'] 	= "0.00";
		}
		if( empty( $param['info']['deminimis'] ) || strlen($param['info']['deminimis'])==0){
			$param['info']['deminimis'] = "0.00";
		}
	}else{
		$param['info']['gross'] 	= "0.00";
		$param['info']['taxable'] 	= "0.00";
		$param['info']['nontax'] 	= "0.00";
		$param['info']['addex'] 	= "0.00";
		$param['info']['month13'] 	= "0.00";
		$param['info']['deminimis'] = "0.00";
	}
	$Qry->selected 	= $Qry->selected . ", gross='".$param['info']['gross']."', taxable='".$param['info']['taxable']."', nontax='".$param['info']['nontax']."', addex='".$param['info']['addex']."', month13='".$param['info']['month13']."', deminimis='".$param['info']['deminimis']."'";
	
	
	$Qry->fields 	= "id='".$param['info']['idassign']."'";
	$checke 		= $Qry->exe_UPDATE($con);
	
	if($checke){
		
		if( (int)$param['info']['dependent'] > 0 || ( (int)$param["info"]["orgdependent"] > 0 && (int)$param['info']['dependent'] == 0 ) ){
			$Qry3 			= new Query();	
			$Qry3->table	= "tblacctdependent";
			$Qry3->fields 	= "idacct = '".$param['info']['idacct']."' ";
			$checke3 		= $Qry3->exe_DELETE($con);			
		}
		if( (int)$param['info']['dependent'] > 0 ){
			foreach( $param['info']['dependents'] as $kk=>$vv ){
				if( !empty( $vv["name"] ) && !empty( $vv["birthday"] ) ){
					$Qry4 			= new Query();	
					$Qry4->table	= "tblacctdependent";
					$Qry4->selected = "idacct, name, birthday";
					$Qry4->fields 	= "'".$param['info']['idacct']."', '".$vv["name"]."', '".$vv["birthday"]."'";
					$checke4 		= $Qry4->exe_INSERT($con);	
				}
			}
		}		
	
		if( (int)$param['info']['empstat'] != 1 || (int)$param['info']['empstat'] != 4 || (int)$param['info']['empstat'] != 7){
			
			$unique_lv = array();
			$x=0;
			foreach( $param['info']['leaves'] as $keys=>$val ){	
				if( !empty( $val['idleave'] ) && !in_array($val['idleave'],$unique_lv) ){
					array_push($unique_lv,$val['idleave']);

						$idacct			= $param['info']['idacct'];
						$idleave 		= $val['idleave'];
						$entitle		= ((int)$val['entitle']*8);
						//$used			= getLeaveUsed($con, $idacct, $idleave);
						//$balance		= (int)$entitle - (int)$used;

						//print("'".$idacct."', '".$idleave."', '".$entitle."', '".$used."', '".$balance."'");

						$Qry2 			= new Query();	
						$Qry2->table	= "tblaccountleaves";
						if(empty($val['id'] )){
							$Qry2->selected = "idacct, idleave, entitle";
							$Qry2->fields 	= "'".$idacct."', '".$idleave."', '".$entitle."'";
							$checke2 		= $Qry2->exe_INSERT($con);
							// if($checke2){
							// 	return json_encode(array("status"=>"successacctleaves"));
							// }
							// else{
							// 	return json_encode(array("status"=>"error"));
							// }
						}else{
							$Qry2->selected = "entitle='".$entitle."'";
							$Qry2->fields 	= "id='".$val['id']."'";
							$checke2 		= $Qry2->exe_UPDATE($con);			
						}				
				}
				$x++;
				//print_r($x);
			}
			
		}
		$unique_al = array();
		foreach( $param['info']['acctallowance'] as $keyss=>$valss ){
			if( !empty( $valss["idallowance"]) && !in_array($valss['idallowance'],$unique_al) ) {
				array_push($unique_al,$valss['idallowance']);

				if( empty( $valss["amt"] ) ){
					$valss["amt"] = "0.00";
				}
				if( empty($valss['firsthalf']['checked'] ) ){
					$valss['firsthalf'] = 0;
				}
				if( empty( $valss['secondhalf']['checked'] ) ){
					$valss['secondhalf'] = 0;
				}

				if( empty( $valss['gl']['checked'] ) ){
					$valss['gl'] = 0;
				}

				if( ($valss['firsthalf']['checked']) == 'true' ){
					$valss['firsthalf'] = 1;
				}
				if( ($valss['firsthalf']['checked']) == 'false' ){
					$valss['firsthalf'] = 0;
				}
				
				if( ($valss['secondhalf']['checked']) == 'true' ){
					$valss['secondhalf'] = 1;
				}
				if( ($valss['secondhalf']['checked']) == 'false' ){
					$valss['secondhalf'] = 0;
				}  
				if( ($valss['gl']['checked']) == 'true' ){
					$valss['gl'] = 1;
				}
				if( ($valss['gl']['checked']) == 'false' ){
					$valss['gl'] = 0;
				} 
				$idacct			= $param['info']['idacct'];
				$idallowance 	= $valss['idallowance'];
				$idmethod		= $valss['idmethod'];
				$amt			= str_replace(",","",$valss["amt"]);
				$firsthalf		= $valss['firsthalf'];
				$secondhalf		= $valss['secondhalf'];
				$gl				= $valss['gl'];
				$Qry3 			= new Query();	
				$Qry3->table	= "tblacctallowance";

				if( empty( $valss['id'] ) ){
					$Qry3->selected = "idacct, idallowance, idmethod, amt, firsthalf, secondhalf, gl";
					$Qry3->fields 	= "'".$idacct."', '".$idallowance."', '".$idmethod."', '".$amt."', '".$firsthalf."', '".$secondhalf."', '".$gl."'";
					$checke3 		= $Qry3->exe_INSERT($con);	
				}else{
					$Qry3->selected = "idacct='".$idacct."', idallowance='".$idallowance."', idmethod='".$idmethod."', amt='".$amt."', firsthalf='".$firsthalf."', secondhalf='".$secondhalf."', gl='".$gl."' ";
					$Qry3->fields 	= "id='".$valss['id']."'";
					$checke3 		= $Qry3->exe_UPDATE($con);	
				}
			}
		}
		if( $param['info']['wshift'] == $param['info']['ogshift'] ){
			insertTimeKeepingRec( $con, $param['info']['wshift'], $param['info']['idacct'] );
		}else{
			insertTimeKeepingRec2( $con, $param['info']['wshift'], $param['info']['idacct'] );
		}
		//ADD TO LOGS
		$return = inserLogs($con, $param['accountid'], "Updated Assigned 201 Data of ".getEmployeeName($con, $param['info']['idacct']));
		return json_encode(array("status"=>"success"));
	}else{
		return json_encode(array("status"=>"error"));
	}
}

function getEmail($con,$idacct){
    $Qry = new Query();	
    $Qry->table ="tblaccount";	
    $Qry->selected ="email";
    $Qry->fields ="id='".$idacct."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['email'];
        }
    }
    return '';
}

function getEmpFullName($con,$idacct){
    $Qry = new Query();	
    $Qry->table ="vw_dataemployees";	
    $Qry->selected ="empname";
    $Qry->fields ="id='".$idacct."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['empname'];
        }
    }
    return '';
}

function updateSalHist($con,$id,$sal,$efdate){
    $Qry3           = new Query();
	$Qry3->table    = "tblacctsalary";
	$Qry3->selected = "idacct,salary,effectivity_date";
	$Qry3->fields   = "'".$id."','".str_replace(",","",$sal)."','".$efdate."'";
	$checke = $Qry3->exe_INSERT($con);
}																		
?>