<?php
require_once('../../../activation.php');
$param = json_decode(file_get_contents('php://input'));
$conn = new connector();	
if( (int)$param->conn == 1 ){	
	$con = $conn->connect();
}else{
	$varcon = "connect".(int)$param->conn;
	$con = $conn->$varcon();
}
require_once('../../../classPhp.php'); 

if(!empty($param->accountid)){
    // if(!empty($param->info->id) ){
        if(!empty($param->info->alias)){
            if(!empty($param->info->name)){ 
                if(!empty($param->info->utype)){      
					if( !empty( $param->info->dept) ){
						$param->info->alias     = strtoupper(strtolower(str_replace("'","",$param->info->alias))); 
						$param->info->name      = ((str_replace("'","",$param->info->name)));       
						// if( !checkUnitName($con, $param->info->name, $param->info->id, 'tblbunits') ){
							// if( !checkAlias($con, $param->info->alias, $param->info->id, 'tblbunits') ){    
								if( empty( $param->info->head) && ( !empty( $param->info->deputy1) || !empty( $param->info->deputy2) ) ){
									$return = json_encode(array('status'=>'error'));
									print $return;
									mysqli_close($con);
									return;
								}elseif( empty( $param->info->deputy1) && !empty( $param->info->deputy2) ){
									$return = json_encode(array('status'=>'error'));
									print $return;
									mysqli_close($con);
									return;
								}
								$allow = 1;
								if( (int)$param->info->utype == 1 ){
									if( checkType($con, $param->info->utype, $param->info->id, 'tblbunits') ){
										$allow = 0;
									}
								}
								//check if there is account assigned to
								// if( $param->info->utype != $param->info->origtype ){
								// 	if( checkAssignedAccts($con, $param->info->ids ) ){
								// 		$return = json_encode(array('status'=>'invupdate'));
								// 		print $return;
								// 		mysqli_close($con);
								// 		return;
								// 	}
								// }

								if( (int)$allow == 1 ){
									$Qry3           = new Query();
									$Qry3->table    = "tblbunits";
									$Qry3->selected = "	name     ='".$param->info->name."',
														alias    ='".$param->info->alias."',
														unittype ='".$param->info->utype."',
														isactive ='".$param->info->stat."'";
									$Qry3->fields   = "id='".$param->info->id."'";
									
									if( !empty( $param->info->dept) && (int)$param->info->utype > 3 ){
										$arr_data = getapprovers( $con, $param->info->dept );
										if( !empty($arr_data['idhead']) ){
                                            if(empty($param->info->head)){
                                                $param->info->head = $arr_data['idhead'];
                                            }
                                        }
									}
									
									if( !empty( $param->info->costcenter) ){
										$Qry3->selected = $Qry3->selected.",costcenter='".$param->info->costcenter."'";
									}else{
										$Qry3->selected = $Qry3->selected.",costcenter=NULL";
									}
									if( !empty( $param->info->head) ){
										$Qry3->selected = $Qry3->selected.",idhead='".$param->info->head."'";
									}else{
										$Qry3->selected = $Qry3->selected.",idhead=NULL";
									}
									if( !empty( $param->info->deputy1) ){
										$Qry3->selected = $Qry3->selected.",deputy1='".$param->info->deputy1."'";
									}else{
										$Qry3->selected = $Qry3->selected.",deputy1=NULL";
									}
									if( !empty( $param->info->deputy2) ){
										$Qry3->selected = $Qry3->selected.",deputy2='".$param->info->deputy2."'";
									}else{
										$Qry3->selected = $Qry3->selected.",deputy2=NULL";
									}
									if( !empty( $param->info->dept) ){
										$Qry3->selected = $Qry3->selected.",idunder='".$param->info->dept."'";
									}else{
										$Qry3->selected = $Qry3->selected.",idunder=NULL";
									}
									if( !empty( $param->info->location) ){
										$Qry3->selected = $Qry3->selected.",site='".$param->info->location."'";
									}else{
										$Qry3->selected = $Qry3->selected.",site=NULL";
									}
								

									$checke = $Qry3->exe_UPDATE($con);
									if($checke){
										//update idsuperior in accounts
										if($param->info->prev_head != $param->info->head){
											$unit_id = $param->info->id;
											$Qry33           = new Query();
											$Qry33->table    = "tblaccountjob";
											if( !empty( $param->info->head ) ){
												$Qry33->selected = "	idsuperior  ='".$param->info->head."'";
											}else{
												$Qry33->selected = "	idsuperior  =NULL";
											}
											$Qry33->fields   = "idunit='".$unit_id."'";
											$checke44 = $Qry33->exe_UPDATE($con);
										}
										
										if( $param->info->utype != $param->info->origtype ){
											foreach( $param->info->ids as $key_units => $val_units ){
												if( (int)$key_units > 0 ){
													
													$approver_type = getapprovers($con, $val_units);
													$unit_type	   = (int)$approver_type["unittype"];
													if( $param->info->origtype > $param->info->utype ){ 
														if( $unit_type != 2 ){ $unit_type = $unit_type - 1; }
														if( $unit_type == 6 ){ $unit_type = $unit_type - 1; }
													}elseif( $param->info->origtype < $param->info->utype ){ 
														if( $unit_type != 8 ){ $unit_type = $unit_type + 1; }
														if( $unit_type == 6 ){ $unit_type = $unit_type + 1; }
													}
													
													$Qry4           = new Query();
													$Qry4->table    = "tblbunits";
													$Qry4->selected = "unittype ='".$unit_type."'";
													$Qry4->fields   = "id='".$val_units."'";
													$checke4 		= $Qry4->exe_UPDATE($con);
												}
												
												$Qry5           = new Query();
												$Qry5->table    = "tblappmatrix";
												$Qry5->fields   = "id='".$val_units."'";
												$checke5 		= $Qry5->exe_DELETE($con);
												
											}
										}
										
										$return = json_encode(array("status"=>"success"));
									}else{
										$return = json_encode(array('status'=>'error'));
									}
								}
							// }else{
							// 	$return = json_encode(array('status'=>'dupalias'));
							// }
						// }else{
						// 	$return = json_encode(array('status'=>'error'));
						// }
					}else{
						$return = json_encode(array('status'=>'nodept'));
					}
                }else{
                    $return = json_encode(array('status'=>'notype'));
                }
            }else{
                $return = json_encode(array('status'=>'noname'));
            }
        }else{
            $return = json_encode(array('status'=>'noid'));
        }
    // }else{
    //    $return = json_encode(array('status'=>'error'));
    // }
}else{
    $return = json_encode(array('status'=>'notloggedin'));
}

print $return;
mysqli_close($con);

function checkAssignedAccts( $con, $idunit ){
	$ids 			= implode(",",$idunit);
	$Qry           = new Query();
	$Qry->table     = "tblaccountjob";
	$Qry->selected  = "*";
	$Qry->fields    = "idunit in (".$ids.")";
	$rs 			= $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		return true;
	}
	return false;
}

?>