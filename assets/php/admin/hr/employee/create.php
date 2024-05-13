<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$date=SysDate();
$time=SysTime();

if( !empty($param['accountid']) ){
	
		$param['info']['docs']	= $param['attachment_type'];
		if( array_key_exists('file',$_FILES) ){ $_FILES['file'] = $_FILES['attachment_files']; }
		
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
		
		$param['info']['emergency_name'] 	= strtoupper(strtolower(str_replace("'","",$param['info']['emergency_name'])));
		$param['info']['emergency_number'] 	= strtoupper(strtolower(str_replace("'","",$param['info']['emergency_number'])));
		
		
		if( !empty( $param['info']['empid'] ) ){
			if( checkEmpID( $con, $param['info']['empid'] ) ){
				$return = json_encode(array('status'=>'idexists'));
				print $return;	
				mysqli_close($con);
				return;
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
											//if( !empty( $param['info']['bdate'] ) ){
												//if( !empty( $param['info']['bplace'] ) ){														
													if( !empty( $param['info']['citizenship'] ) ){
														//if( !empty( $param['info']['religion'] ) ){
															if( !empty( $param['info']['civilstat'] ) ){																	
																if( (int)$param['info']['civilstat'] > 1 && empty( $param['info']['spouse'] ) ){
																	$return = json_encode(array("status"=>"spouse"));
																}else{
																	//if( !empty( $param['info']['idtax'] ) ){																	
																		if( !empty( $param['info']['email'] ) ){
																			if( filter_var( $param['info']['email'], FILTER_VALIDATE_EMAIL)) {
																			}else{
																				$return = json_encode(array('status'=>'email'));
																				print $return;	
																				mysqli_close($con);
																				return;
																			}
																		}
																		//check if duplicate document types
																		// print_r(count( $param['info']['docs'] ) . ' '.((int)(count(array_unique($param['info']['docs'])))));
																		// return;
																		if((int)count(array_unique($param['info']['docs'])) > 1){
																			if( count( $param['info']['docs'] ) != count(array_unique($param['info']['docs'])) ){
																				$return = json_encode(array('status'=>'duplicate_attachment'));
																				print $return;	
																				mysqli_close($con);
																				return;
																			}
																		}
																		$uname = substr($param['info']['fname'], 0, 1).$param['info']['lname'];
																		//INSERT TO DATABASE
																		$Qry 			= new Query();	
																		$Qry->table 	= "tblaccount";
																		$Qry->selected 	= "username,empid,password,idaccttype,fname,lname,mname,sex,cnumber,citizenship,civilstat,date_create";
																		$Qry->fields 	= "'".strtolower($uname)."',
																						'".$param['info']['empid']."',
																						'".md5($param['pword'])."',
																						'".$param['info']['idaccttype']."',
																						'".$param['info']['fname']."',
																						'".$param['info']['lname']."',
																						'".$param['info']['mname']."',
																						'".$param['info']['sex']."',
																						'".$param['info']['cnumber']."',
																						'".$param['info']['citizenship']."',
																						'".$param['info']['civilstat']."',
																						'".$date."'";

																		if( !empty( (int)$param['info']['sameaddress'] ) ){
																			$Qry->selected 	= $Qry->selected . ", sameaddress";
																			$Qry->fields 	= $Qry->fields 	 . ", '".(int)$param['info']['sameaddress']."'";
																		}
																		if( !empty( $param['info']['isprivacy'] ) ){
																			$Qry->selected 	= $Qry->selected . ", isprivacy";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['isprivacy']."'";
																		}
																		if( !empty( $param['info']['idemptype']) ){
																			$Qry->selected 	= $Qry->selected . ", idemptype";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['idemptype']."'";
																		}
																		if( !empty( $param['info']['bdate']) ){
																			$Qry->selected 	= $Qry->selected . ", bdate";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['bdate']."'";
																		}
																		if( !empty( $param['info']['bplace']) ){
																			$Qry->selected 	= $Qry->selected . ", bplace";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['bplace']."'";
																		}
																		if( !empty( $param['info']['religion']) ){
																			$Qry->selected 	= $Qry->selected . ", religion";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['religion']."'";
																		}
																		if( !empty( $param['info']['idtax']) ){
																			$Qry->selected 	= $Qry->selected . ", idtax";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['idtax']."'";
																		}
																		if( !empty( $param['info']['salutation'] ) ){
																			$Qry->selected 	= $Qry->selected . ", salutation";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['salutation']."'";
																		}
																		
																		if( !empty( $param['info']['suffix'] ) ){
																			$Qry->selected 	= $Qry->selected . ", suffix";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['suffix']."'";
																		}
																		
																		if( !empty( $param['info']['nickname'] ) ){
																			$Qry->selected 	= $Qry->selected . ", nickname";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['nickname']."'";
																		}
																		
																		if( !empty( $param['info']['addr_st'] ) ){
																			$Qry->selected 	= $Qry->selected . ", addr_st";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['addr_st']."'";
																		}
																		if( !empty( $param['info']['addr_area'] ) ){
																			$Qry->selected 	= $Qry->selected . ", addr_area";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['addr_area']."'";
																		}
																		if( !empty( $param['info']['addr_city'] ) ){
																			$Qry->selected 	= $Qry->selected . ", addr_city";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['addr_city']."'";
																		}
																		if( !empty( $param['info']['addr_prov'] ) ){
																			$Qry->selected 	= $Qry->selected . ", addr_prov";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['addr_prov']."'";
																		}
																		if( !empty( $param['info']['addr_code'] ) ){
																			$Qry->selected 	= $Qry->selected . ", addr_code";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['addr_code']."'";
																		}
																		
																		if( (int)$param['info']['sameaddress'] == 1 ){
																			$param['info']['per_st'] 	= $param['info']['addr_st'];
																			$param['info']['per_area']	= $param['info']['addr_area'];	
																			$param['info']['per_city']	= $param['info']['addr_city'];	
																			$param['info']['per_prov']	= $param['info']['addr_prov'];	
																			$param['info']['per_code']	= $param['info']['addr_code'];	
																		}
																		
																		if( !empty( $param['info']['per_st'] ) ){
																			$Qry->selected 	= $Qry->selected . ", per_st";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['per_st']."'";
																		}
																		if( !empty( $param['info']['per_area'] ) ){
																			$Qry->selected 	= $Qry->selected . ", per_area";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['per_area']."'";
																		}
																		if( !empty( $param['info']['per_city'] ) ){
																			$Qry->selected 	= $Qry->selected . ", per_city";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['per_city']."'";
																		}
																		if( !empty( $param['info']['per_prov'] ) ){
																			$Qry->selected 	= $Qry->selected . ", per_prov";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['per_prov']."'";
																		}
																		if( !empty( $param['info']['per_code'] ) ){
																			$Qry->selected 	= $Qry->selected . ", per_code";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['per_code']."'";
																		}
																		
																		if( !empty( $param['info']['emergency_number'] ) ){
																			$Qry->selected 	= $Qry->selected . ", emergency_number";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['emergency_number']."'";
																		}
																		if( !empty( $param['info']['emergency_name'] ) ){
																			$Qry->selected 	= $Qry->selected . ", emergency_name";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['emergency_name']."'";
																		}
																		
																		if( !empty( $param['info']['pnumber'] ) ){
																			$Qry->selected 	= $Qry->selected . ", pnumber";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['pnumber']."'";
																		}
																		if( !empty( $param['info']['bloodtype'] ) ){
																			$Qry->selected 	= $Qry->selected . ", bloodtype";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['bloodtype']."'";
																		}
																		
																		if( !empty( $param['info']['email'] ) ){
																			$Qry->selected 	= $Qry->selected . ", email";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['email']."'";
																		}
																		if( !empty( $param['info']['spouse'] ) ){
																			$Qry->selected 	= $Qry->selected . ", spouse";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['spouse']."'";
																		}
																		if( !empty( $param['info']['idtin'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idtin";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['idtin']."'";
																			if( !empty( $param['info']['tin_date'] ) ){
																				$Qry->selected 	= $Qry->selected . ", tin_date";
																				$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['tin_date']."'";
																			}
																		}
																		if( !empty( $param['info']['idsss'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idsss";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['idsss']."'";
																			if( !empty( $param['info']['sss_date'] ) ){
																				$Qry->selected 	= $Qry->selected . ", sss_date";
																				$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['sss_date']."'";
																			}
																		}
																		if( !empty( $param['info']['idhealth'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idhealth";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['idhealth']."'";
																			if( !empty( $param['info']['health_date'] ) ){
																				$Qry->selected 	= $Qry->selected . ", health_date";
																				$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['health_date']."'";
																			}
																		}
																		if( !empty( $param['info']['idibig'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idibig";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['idibig']."'";
																			if( !empty( $param['info']['ibig_date'] ) ){
																				$Qry->selected 	= $Qry->selected . ", ibig_date";
																				$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['ibig_date']."'";
																			}
																		}
																		if( !empty( $param['info']['idbank'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idbank";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['idbank']."'";
																			if( !empty( $param['info']['idpayroll'] ) ){
																				$Qry->selected 	= $Qry->selected . ", idpayroll";
																				$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['idpayroll']."'";
																			}
																		}
																		if( !empty( $param['info']['idpassport'] ) ){
																			$Qry->fields 	= $Qry->fields . ", '".$param['info']['idpassport']."'";	
																			$Qry->selected 	= $Qry->selected . ", idpassport";
																		}
																		if( !empty( $param['info']['license_prc'] ) ){
																			$Qry->selected 	= $Qry->selected . ", license_prc";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['license_prc']."'";
																		}
																		if( !empty( $param['info']['license_drive'] ) ){
																			$Qry->selected 	= $Qry->selected . ", license_drive";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['license_drive']."'";
																		}
																		if( !empty($pic) ){
																			$Qry->selected 	= $Qry->selected . ", pic";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$pic."'";
																		}
																		if( !empty( $param['info']['height_ft'] ) ){
																			$Qry->selected 	= $Qry->selected . ", height_ft";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['height_ft']."'";
																		}
																		if( !empty( $param['info']['height_inch'] ) ){
																			$Qry->selected 	= $Qry->selected . ", height_inch";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['height_inch']."'";
																		}
																		if( !empty( $param['info']['weight_lbs'] ) ){
																			$Qry->selected 	= $Qry->selected . ", weight_lbs";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['weight_lbs']."'";
																		}
																		if( !empty( $param['info']['eyecolor'] ) ){
																			$Qry->selected 	= $Qry->selected . ", eyecolor";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['eyecolor']."'";
																		}
																		if( !empty( $param['info']['haircolor'] ) ){
																			$Qry->selected 	= $Qry->selected . ", haircolor";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['haircolor']."'";
																		}
																		if( !empty( $param['info']['skincolor'] ) ){
																			$Qry->selected 	= $Qry->selected . ", skincolor";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['skincolor']."'";
																		}
																		if( !empty( $param['info']['buildtype'] ) ){
																			$Qry->selected 	= $Qry->selected . ", buildtype";
																			$Qry->fields 	= $Qry->fields 	 . ", '".$param['info']['buildtype']."'";
																		}
																		
																		
																		$checke 			= $Qry->exe_INSERT($con);
																		if($checke){
																			$lastid = getlastaccountid($con);
																			$last_id= $lastid; 
																			
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
																					$picNdx = $ndx + 1;
																					$ok_upload = true;
																					if( (int)$vv == 1 ){
																						if( (int)$param['info']['isprivacy'] == 0 ){
																							$ok_upload = false;
																						}
																					}elseif( (int)$vv == 2 ){
																						if( (int)$param['info']['civilstat'] <= 1 || (int)$param['info']['civilstat'] == 3 ){
																							$ok_upload = false;
																						}
																					}
																					
																					if( array_key_exists('file',$_FILES) ){		
																						if( array_key_exists( $picNdx ,$_FILES['file']['name']) ){
																							if( empty( $_FILES['file']['name'][$picNdx] ) ){
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
																						$name 			= $_FILES['file']['name'][$picNdx];
																						$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
																						$save_name		= getDocumentName($con,$vv).'.'.$extMove;	
																						move_uploaded_file($_FILES["file"]["tmp_name"][$picNdx], $folder_path.'/'.$save_name);
																						
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
																			
																			//INSERT GLOBAL ACCOUNT
																			$dbname = getDBName($con);
																			insertGlobalAccount($con,$lastid, $dbname['id'] );
																			
																			if( !empty($param['info']['eb']) ){
																				foreach($param['info']['eb'] as $key => $value ) {
																					if( !empty( $value ) ){
																						$ebschool = $param['info']['ebschool'];
																						$ebfrom = $param['info']['ebfrom'];
																						$ebto = $param['info']['ebto'];

																						$Qry 			= new Query();	
																						$Qry->table 	= "tblaccountedubg";
																						$Qry->selected 	= "idacct,
																											attainment,
																											school,
																											dfrom,
																											dto";
																						$Qry->fields 	= "'".$last_id."',
																											'".$value."',
																											'".$ebschool[$key]."',
																											'".$ebfrom[$key]."',
																											'".$ebto[$key]."'";
																						$Qry->exe_INSERT($con);
																					}
																				}
																			}

																			if( !empty($param['info']['ehcompany']) ){
																				foreach($param['info']['ehcompany'] as $key => $value ) {
																					if( !empty( $value ) ){
																						$ehposition = $param['info']['ehposition'];
																						$ehfrom = $param['info']['ehfrom'];
																						$ehto = $param['info']['ehto'];

																						$Qry 			= new Query();	
																						$Qry->table 	= "tblaccountemphis";
																						$Qry->selected 	= "idacct,
																											company,
																											position,
																											dfrom,
																											dto";
																						$Qry->fields 	= "'".$last_id."',
																											'".$value."',
																											'".$ehposition[$key]."',
																											'".$ehfrom[$key]."',
																											'".$ehto[$key]."'";
																						$Qry->exe_INSERT($con);
																					}
																				}
																			}

																			if( !empty($param['info']['etexam']) ){
																				foreach($param['info']['etexam'] as $key => $value ) {
																					if( !empty( $value ) ){
																						$etlocation = $param['info']['etlocation'];
																						$etorg 		= $param['info']['etorg'];
																						$etdate 	= $param['info']['etdate'];
																						$etfac 		= $param['info']['etfac'];
																				  
																						$Qry 			= new Query();	
																						$Qry->table 	= "tblaccountet";
																						$Qry->selected 	= "idacct,issueorg,facilitator,
																											et,
																											location,
																											date,
																											type";
																						$Qry->fields 	= "'".$last_id."','".str_replace("'","",$etorg[$key])."','".str_replace("'","",$etfac[$key])."',
																											'".$value."',
																											'".str_replace("'","",$etlocation[$key])."',
																											'".$etdate[$key]."',
																											'exam'";
																						$Qry->exe_INSERT($con);
																					}
																				}
																			}

																			if( !empty($param['info']['tttraining']) ){
																				foreach($param['info']['tttraining'] as $key => $value ) {
																					if( !empty( $value ) ){
																						$ttlocation = $param['info']['ttlocation'];
																						$ttorg 		= $param['info']['ttorg'];
																						$ttdate 	= $param['info']['ttdate'];
																						$ttfac		= $param['info']['ttfac'];
																				  
																						$Qry 			= new Query();	
																						$Qry->table 	= "tblaccountet";
																						$Qry->selected 	= "idacct,issueorg,facilitator,
																											et,
																											location,
																											date,
																											type";
																						$Qry->fields 	= "'".$last_id."','".str_replace("'","",$ttorg[$key])."','".str_replace("'","",$ttfac[$key])."',
																											'".$value."',
																											'".str_replace("'","",$ttlocation[$key])."',
																											'".$ttdate[$key]."',
																											'training'";
																						$Qry->exe_INSERT($con);
																					}
																				}
																			}
																			
																			foreach($param['info']['pa'] as $key => $value ) {
																				$pa_ok 			= true;
																				$equi_tools 	= str_replace("'","",$value['equi_tools']);
																				$serial 		= str_replace("'","",$value['serial']);
																				$asset 			= str_replace("'","",$value['asset']);
																				$quantity 		= $value['quantity'];
																				$date_issued	= $value['date_issued'];
																				$date_returned	= $value['date_returned'];
																				if( !empty($equi_tools) && !empty($serial) && !empty($quantity) && !empty($date_issued) ){
																					$Qry 			= new Query();	
																					$Qry->table 	= "tblaccountpropacc";
																					$Qry->selected 	= "idacct,equi_tools,serial,assetcode,quantity,date_issued";
																					$Qry->fields 	= "'".$last_id."','".$equi_tools."','".$serial."','".$asset."','".$quantity."','".$date_issued."'";
																					if( !empty( $date_returned ) ){
																						$Qry->selected 	= $Qry->selected 	. ",date_returned";
																						$Qry->fields 	= $Qry->fields 		. ",'".$date_returned."'";
																						if( strtotime( $date_issued ) > strtotime( $date_returned ) ){
																							$pa_ok = false;
																						}else{
																							$pa_ok = true;
																						}
																					}
																					if( $pa_ok ) {
																						$Qry->exe_INSERT($con);
																					}
																				}
																			}
																			
																			//ADD TO LOGS
																			$return = inserLogs($con, $param['accountid'], "Created 201 Data of ".$param['info']['lname']." ,".$param['info']['fname']." ".$param['info']['mname']);
																			$return = json_encode(array("status"=>"success"));
																		}else{
																			$return = json_encode(array("status"=>"error"));
																		}
																	// }else{
																	// 	$return = json_encode(array("status"=>"idtax"));
																	// }
																}
															}else{
																$return = json_encode(array("status"=>"civilstat"));
															}
														// }else{
														// 	$return = json_encode(array("status"=>"religion"));
														// }
													}else{
														$return = json_encode(array("status"=>"citizenship"));
													}
												// }else{
												// 	$return = json_encode(array("status"=>"bplace"));
												// }										
											// }else{
											// 	$return = json_encode(array("status"=>"bdate"));
											// }
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
return;
?>