<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date	= SysDate();
$time	= SysTime();

if( !empty($param->accountid) ){
	if( !empty($param->info->idlabor) ){
		if( !empty($param->info->empstat) ){
			if( !empty($param->info->idunit) ){
				if( !empty($param->info->idpos) ){
					if( !empty($param->info->wshift) ){
						if( !empty($param->info->schedtype) ){
							if( !empty($param->info->idloc) ){
								if( !empty($param->info->idsuperior) ){
									if( !empty($param->info->idpaygrp) ){
										if( !empty($param->info->idpaystat) ){
											if( !empty($param->info->idrevenue) ){
												if( !empty($param->info->idrelease) ){
													if( !empty($param->info->idlvl) ){
														if( !empty($param->info->salary) ){															
															if( !empty($param->info->hdate) ){															
																$param->info->prev_employer	= strtoupper(strtolower(str_replace("'","",$param->info->prev_employer)));
																$hire_date = strtotime($param->info->hdate);
																if( !empty($param->info->rdate) ){
																	$regDate = strtotime($param->info->rdate);
																	if( $hire_date > $regDate ){																		
																		$return = json_encode(array("status"=>"errDate1"));
																		print $return;
																		mysqli_close($con);
																		return;
																	}
																}
																if( (int)$param->info->empstat == 6 ){
																	if( !empty($param->info->sdate) ){
																		$seperationDate = strtotime($param->info->sdate);
																		if( !empty($param->info->rdate) ){
																			$regDate = strtotime($param->info->rdate);
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

																foreach( $param->info->leaves as $keys=>$val ){																	
																	if( (int)$val->hours < (int)$val->entitle ){
																		$return = json_encode(array("status"=>"errLeave1"));
																		print $return;
																		mysqli_close($con);
																		return;
																	}
																}
																
																if( empty( $param->info->id ) ){
																	$return = insertInfo($con, $param);
																}else{
																	$return = updateInfo($con, $param);
																}
															}else{
																$return = json_encode(array("status"=>"hdate"));
															}															
														}else{
															$return = json_encode(array("status"=>"salary"));
														}
													}else{
														$return = json_encode(array("status"=>"error"));
													}
												}else{
													$return = json_encode(array("status"=>"idrelease"));
												}
											}else{
												$return = json_encode(array("status"=>"idrevenue"));
											}
										}else{
											$return = json_encode(array("status"=>"idpaystat"));
										}
									}else{
										$return = json_encode(array("status"=>"idpaygrp"));
									}
								}else{
									$return = json_encode(array("status"=>"idsuperior"));
								}
							}else{
								$return = json_encode(array("status"=>"idloc"));
							}
						}else{
							$return = json_encode(array("status"=>"schedtype"));
						}
					}else{
						$return = json_encode(array("status"=>"wshift"));
					}
				}else{
					$return = json_encode(array("status"=>"idpos"));
				}
			}else{
				$return = json_encode(array("status"=>"idunit"));
			}
		}else{
			$return = json_encode(array("status"=>"empstat"));
		}
	}else{
		$return = json_encode(array("status"=>"idlabor"));
	}
}else{
	$return = json_encode(array("status"=>"notloggedin"));
}


print $return;
mysqli_close($con);

function insertInfo($con, $param){
	$Qry 			= new Query();	
	$Qry->table 	= "tblaccountjob";
	$Qry->selected 	= "idacct,idpos,idunit,idlabor,idsuperior,empstat,idloc,wshift,schedtype,hdate,salary,idpaygrp,paystat,idrevenue,dependent,idrelease,idlvl";
	$Qry->fields 	= "'".$param->info->idacct."',
	                   '".$param->info->idpos."',
					   '".$param->info->idunit."',
					   '".$param->info->idlabor."',
					   '".$param->info->idsuperior."',
					   '".$param->info->empstat."',
					   '".$param->info->idloc."',
					   '".$param->info->wshift."',
					   '".$param->info->schedtype."',
					   '".$param->info->hdate."',
					   '".str_replace(",","",$param->info->salary)."',
					   '".$param->info->idpaygrp."',
					   '".$param->info->idpaystat."',
					   '".$param->info->idrevenue."',
					   '".(int)$param->info->dependent."',
					   '".$param->info->idrelease."',
					   '".$param->info->idlvl."'";
	if( !empty( $param->info->rdate ) ){
		$Qry->selected 	= $Qry->selected . ", rdate";
		$Qry->fields 	= $Qry->fields 	 . ",'".$param->info->rdate."'";
	}
	if( !empty( $param->info->sdate ) ){
		$Qry->selected 	= $Qry->selected . ", sdate";
		$Qry->fields 	= $Qry->fields 	 . ",'".$param->info->sdate."'";
	}
	if( !empty( $param->info->prev_employer ) ){
		$Qry->selected 	= $Qry->selected . ", prev_employer";
		$Qry->fields 	= $Qry->fields 	 . ",'".$param->info->prev_employer."'";
	}					   
	$checke 		= $Qry->exe_INSERT($con);
	if($checke){		
		foreach( $param->info->leaves as $keys=>$val ){
			$idleave 	= $val->id;
			$idacct		= $param->info->idacct;
			$entitle	= (int)$val->entitle;
			$used		= getLeaveUsed($con, $idacct, $idleave);
			$balance	= (int)$entitle - (int)$used;
			$Qry2 			= new Query();	
			$Qry2->table	= "tblaccountleaves";
			$Qry2->selected = "idacct, idleave, entitle, used, balance";
			$Qry2->fields 	= "'".$idacct."', '".$idleave."', '".$entitle."', '".$used."', '".$balance."'";
			$checke2 		= $Qry2->exe_INSERT($con);			
		}
		
		insertTimeKeepingRec($con, $param->info->wshift, $param->info->idacct );
		//ADD TO LOGS
		$return = inserLogs($con, $param->accountid, "Added Assigned 201 Data of ".getEmployeeName($con, $param->info->idacct));
		return json_encode(array("status"=>"success"));		
	}else{
		return json_encode(array("status"=>"error"));
	}
}

function updateInfo($con, $param){
	$Qry 			= new Query();	
	$Qry->table 	= "tblaccountjob";
	$Qry->selected 	= " idacct='".$param->info->idacct."',
						idpos='".$param->info->idpos."',
						idunit='".$param->info->idunit."',
						idlabor='".$param->info->idlabor."',
						idsuperior='".$param->info->idsuperior."',
						empstat='".$param->info->empstat."',
						idloc='".$param->info->idloc."',
						wshift='".$param->info->wshift."',
						schedtype='".$param->info->schedtype."',
						hdate='".$param->info->hdate."',
						salary='".str_replace(",","",$param->info->salary)."',
						idpaygrp='".$param->info->idpaygrp."',
						paystat='".$param->info->idpaystat."',
						idrevenue='".$param->info->idrevenue."',
						dependent='".(int)$param->info->dependent."',
						idrelease='".$param->info->idrelease."',
						idlvl='".$param->info->idlvl."'";
	if( !empty( $param->info->rdate ) ){
		$Qry->selected 	= $Qry->selected . ", rdate='".$param->info->rdate."'";
	}else{
		$Qry->selected 	= $Qry->selected . ", rdate=NULL";
	}
	if( !empty( $param->info->sdate ) ){
		$Qry->selected 	= $Qry->selected . ", sdate='".$param->info->sdate."'";
	}else{
		$Qry->selected 	= $Qry->selected . ", sdate=NULL";
	}
	if( !empty( $param->info->prev_employer ) ){
		$Qry->selected 	= $Qry->selected . ", prev_employer='".$param->info->prev_employer."'";
	}else{
		$Qry->selected 	= $Qry->selected . ", prev_employer=NULL";
	}		
	$Qry->fields 	= "id='".$param->info->id."'";
	$checke 		= $Qry->exe_UPDATE($con);
	if($checke){
		foreach( $param->info->leaves as $keys=>$val ){
			$idleave 		= $val->id;
			$idacct			= $param->info->idacct;
			$entitle		= (int)$val->entitle;
			$used			= getLeaveUsed($con, $idacct, $idleave);
			$balance		= (int)$entitle - (int)$used;
			$Qry2 			= new Query();	
			$Qry2->table	= "tblaccountleaves";
			$Qry2->selected = "entitle='".$entitle."', used='".$used."', balance='".$balance."'";
			$Qry2->fields 	= "id='".$val->idacctleave."'";
			$checke2 		= $Qry2->exe_UPDATE($con);			
		}
		insertTimeKeepingRec($con, $param->info->wshift, $param->info->idacct );
		//ADD TO LOGS
		$return = inserLogs($con, $param->accountid, "Updated Assigned 201 Data of ".getEmployeeName($con, $param->info->idacct));
		return json_encode(array("status"=>"success"));
	}else{
		return json_encode(array("status"=>"error"));
	}
}

?>