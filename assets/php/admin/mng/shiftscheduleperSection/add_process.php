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
    if(!empty($param->info->alias)){
        if(!empty($param->info->name)){
            if(!empty($param->info->utype)){       
                if( !empty( $param->info->dept) ){
					$param->info->alias     = strtoupper(strtolower(str_replace("'","",$param->info->alias))); 
					$param->info->name      = ((str_replace("'","",$param->info->name)));         
					if( !checkUnitName($con, $param->info->name, '', 'tblbunits') ){
						if( !checkAlias($con, $param->info->alias, '', 'tblbunits') ){    
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
								if( checkType($con, $param->info->utype, '', 'tblbunits') ){
									$allow = 0;
								}
							}
							if( (int)$allow == 1 ){
								$Qry3           = new Query();
								$Qry3->table    = "tblbunits";
								$Qry3->selected = "name,alias,unittype,isactive";
								$Qry3->fields   = "'".$param->info->name."',
												   '".$param->info->alias."',
												   '".$param->info->utype."',
												   '".$param->info->stat."'
												   ";
								
								if( !empty( $param->info->dept) ){
									$arr_data = getapprovers( $con, $param->info->dept );
									if( !empty($arr_data['idhead']) ){
                                        if(empty($param->info->head)){
                                            $param->info->head = $arr_data['idhead'];
                                        }
									}
								}
								
								if( !empty( $param->info->costcenter) ){
									$Qry3->selected = $Qry3->selected.",costcenter";
									$Qry3->fields   = $Qry3->fields.", '".$param->info->costcenter."'";
								}
								if( !empty( $param->info->deputy1) ){
									$Qry3->selected = $Qry3->selected.",deputy1";
									$Qry3->fields   = $Qry3->fields.", '".$param->info->deputy1."'";
								}
								if( !empty( $param->info->deputy2) ){
									$Qry3->selected = $Qry3->selected.",deputy2";
									$Qry3->fields   = $Qry3->fields.", '".$param->info->deputy2."'";
								}
								if( !empty( $param->info->head) ){
									$Qry3->selected = $Qry3->selected.",idhead";
									$Qry3->fields   = $Qry3->fields.", '".$param->info->head."'";
								}
								if( !empty( $param->info->dept) ){
									$Qry3->selected = $Qry3->selected.",idunder";
									$Qry3->fields   = $Qry3->fields.", '".$param->info->dept."'";
								}
								if( !empty( $param->info->location) ){
									$Qry3->selected = $Qry3->selected.",site";
									$Qry3->fields   = $Qry3->fields.", '".$param->info->location."'";
								}
								$checke = $Qry3->exe_INSERT($con);
								if($checke){
									//update idsuperior in accounts
									/*$last_id = mysqli_insert_id($con);
									$Qry33           = new Query();
									$Qry33->table    = "tblaccountjob";
									if( !empty( $param->info->head ) ){
										$Qry33->selected = "	idsuperior  ='".$param->info->head."'";
									}else{
										$Qry33->selected = "	idsuperior  =NULL";
									}
									$Qry33->fields   = "idunit='".$last_id."'";
									$checke44 = $Qry33->exe_UPDATE($con);
									if($checke44){
										$return = json_encode(array("status"=>"success"));
									}else{
										$return = json_encode(array('status'=>'error','w'=>mysqli_error($con)));
									}*/
									$return = json_encode(array("status"=>"success"));
								}else{
									$return = json_encode(array('status'=>'error','qq'=>mysqli_error($con)));
								}
							}else{
								$return = json_encode(array('status'=>'dupalias'));
							}
						}else{
							$return = json_encode(array('status'=>'dupalias'));
						}
					}else{
						$return = json_encode(array('status'=>'error_duplicate'));
					}
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
}else{
    $return = json_encode(array('status'=>'notloggedin','w'=>$param->accountid));
}

print $return;
mysqli_close($con);
?>