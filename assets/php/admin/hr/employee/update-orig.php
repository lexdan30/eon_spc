<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param 	= $_POST;
$date	= SysDate();
$time	= SysTime();

foreach ($param['info'] as $keyzz => $valuezz) {
	if( !is_array($param['info'][$keyzz]) && $keyzz != 'epicFile' && $keyzz !='eprof_pic' ){
		if( strtolower($param['info'][$keyzz]) == 'null' ){
			$param['info'][$keyzz]="";
		}
	}
}


if( !empty($param['accountid']) ){
		if( array_key_exists('file',$_FILES) ){
			$valid_formats = array("jpg", "png", "jpeg");	
			if ($_FILES['file']['error'] == 4) {
				$return = json_encode(array('status'=>'error','on'=>'img_check'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if ($_FILES['file']['error'] == 0) {
				if(!in_array(pathinfo(strtolower($_FILES['file']['name']), PATHINFO_EXTENSION), $valid_formats) ){
					$return = json_encode(array('status'=>'error-upload-type'));
					print $return;	
					mysqli_close($con);
					return;
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
				$extMove = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
				$pic	 = $param['info']['empid'].".".$extMove;
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
												if( !empty( $param['info']['bplace'] ) ){														
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
																						   bplace		='".$param['info']['bplace']."',
																						   citizenship	='".$param['info']['citizenship']."',
																						   religion		='".$param['info']['religion']."',
																						   civilstat	='".$param['info']['civilstat']."',
																						   idtax		='".$param['info']['idtax']."'";
																		
																		if( !empty( $param['pword'] ) ){
																			$Qry->selected 	= $Qry->selected . ", password='".md5($param['pword'])."'";																			
																		}																		
																		if( !empty( $param['info']['suffix'] ) ){
																			$Qry->selected 	= $Qry->selected . ", suffix='".$param['info']['suffix']."'";																			
																		}else{
																			$Qry->selected 	= $Qry->selected . ", suffix=NULL";																		
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
																		if( !empty( $param['info']['spouse'] ) ){
																			$Qry->selected 	= $Qry->selected . ", spouse='".$param['info']['spouse']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", spouse=NULL";
																		}
																		if( !empty( $param['info']['idtin'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idtin='".$param['info']['idtin']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", idtin=NULL";
																		}
																		if( !empty( $param['info']['idsss'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idsss='".$param['info']['idsss']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", idsss=NULL";
																		}
																		if( !empty( $param['info']['idhealth'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idhealth='".$param['info']['idhealth']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", idhealth=NULL";
																		}
																		if( !empty( $param['info']['idibig'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idibig='".$param['info']['idibig']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", idibig=NULL";
																		}
																		if( !empty( $param['info']['idpayroll'] ) ){
																			$Qry->selected 	= $Qry->selected . ", idpayroll='".$param['info']['idpayroll']."'";
																		}else{
																			$Qry->selected 	= $Qry->selected . ", idpayroll=NULL";
																		}
																		if( !empty($pic) ){
																			$Qry->selected 	= $Qry->selected . ", pic='".$pic."'";																			
																		}
																		$Qry->fields 		= "id='".$param['info']['id']."'";
																		$checke 			= $Qry->exe_UPDATE($con);
																		if($checke){
																			//upload profile pic
																			if( array_key_exists('file',$_FILES) ){																				
																				$folder_path 	= $param['targetPath'];
																				$name 			= $_FILES['file']['name'];
																				$t				= strtotime($date).time();	
																				$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
																				$save_name		= $param['info']['empid'].'.'.$extMove;	
																				move_uploaded_file($_FILES["file"]["tmp_name"], $folder_path.$save_name);
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

																						$Qry 			= new Query();	
																						$Qry->table 	= "tblaccountet";
																						$Qry->selected 	= "idacct,
																											et,
																											location,
																											date,
																											type";
																						$Qry->fields 	= "'".$param['info']['id']."',
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

																						$Qry 			= new Query();	
																						$Qry->table 	= "tblaccountet";
																						$Qry->selected 	= "idacct,
																											et,
																											location,
																											date,
																											type";
																						$Qry->fields 	= "'".$param['info']['id']."',
																											'".$value."',
																											'".$ttlocation[$key]."',
																											'".$ttdate[$key]."',
																											'training'";
																						$Qry->exe_INSERT($con);
																					}
																				}
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
												}else{
													$return = json_encode(array("status"=>"bplace"));
												}										
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
?>