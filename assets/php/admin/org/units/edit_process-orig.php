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
    if(!empty($param->info->id) ){
        if(!empty($param->info->alias)){
            if(!empty($param->info->name)){ 
                if(!empty($param->info->utype)){      
					if( !empty( $param->info->dept) ){
						$param->info->alias     = strtoupper(strtolower(str_replace("'","",$param->info->alias))); 
						$param->info->name      = ((str_replace("'","",$param->info->name)));         
						if( !checkAlias($con, $param->info->alias, $param->info->id, 'tblbunits') ){    
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
							if( (int)$allow == 1 ){
								$Qry3           = new Query();
								$Qry3->table    = "tblbunits";
								$Qry3->selected = "	name     ='".$param->info->name."',
													alias    ='".$param->info->alias."',
													unittype ='".$param->info->utype."',
													isactive ='".$param->info->stat."'";
								$Qry3->fields   = "id='".$param->info->id."'";
								
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
								$checke = $Qry3->exe_UPDATE($con);
								if($checke){
									$return = json_encode(array("status"=>"success"));
								}else{
									$return = json_encode(array('status'=>'error'));
								}
							}
						}else{
							$return = json_encode(array('status'=>'dupalias'));
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
        $return = json_encode(array('status'=>'error'));
    }
}else{
    $return = json_encode(array('status'=>'notloggedin'));
}

print $return;
mysqli_close($con);
?>