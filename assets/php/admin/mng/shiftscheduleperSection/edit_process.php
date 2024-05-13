<?php
require_once('../../../logger.php');
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
									
									// if( !empty( $param->info->dept) && (int)$param->info->utype > 3 ){
									// 	$arr_data = getapprovers( $con, $param->info->dept );
									// 	if( !empty($arr_data['idhead']) ){
                                    //         if(empty($param->info->head)){
                                    //             $param->info->head = $arr_data['idhead'];
                                    //         }
                                    //     }
									// }
									
									if( !empty( $param->info->head) ){
										$Qry3->selected = $Qry3->selected.",scheduler='".$param->info->head."'";
									}else{
										 	
										$Qry3->selected = $Qry3->selected.",scheduler=NULL";
										
									}


									$checke = $Qry3->exe_UPDATE($con);
									$return = json_encode(array('status'=>'success'));
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
	$Qry->selected  = "id";
	$Qry->fields    = "idunit in (".$ids.")";
	$rs 			= $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
	return mysqli_num_rows($rs) >= 1;
}

?>