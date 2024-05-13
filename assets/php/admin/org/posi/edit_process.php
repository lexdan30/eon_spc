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

if(!empty($param->info->idunit)){
	$idunit = implode (",", $param->info->idunit);
}

if(!empty($param->accountid)){
    if(!empty($param->info->id) ){
        if(!empty($param->info->alias)){
            if(!empty($param->info->name)){    
				if(!empty($param->info->idunit)){
                    $param->info->alias     = strtoupper(strtolower(str_replace("'","",$param->info->alias))); 
                    $param->info->name      = ((str_replace("'","",$param->info->name)));    
					if( !checkUnitName($con, $param->info->name, $param->info->id, 'tblposition') ){  
						if( !checkAlias($con, $param->info->alias, $param->info->id, 'tblposition') ){    
							$Qry3           = new Query();
							$Qry3->table    = "tblposition";
							$Qry3->selected = " name     ='".$param->info->name."',
												alias    ='".$param->info->alias."',
												isactive ='".$param->info->stat."',
												idunit	 ='".$idunit."' ";
							$Qry3->fields   = "id='".$param->info->id."'";                        
							$checke = $Qry3->exe_UPDATE($con);
							if($checke){
								$return = json_encode(array("status"=>"success"));
							}else{
								$return = json_encode(array('status'=>'error'));
							}
						}else{
							$return = json_encode(array('status'=>'dupalias'));
						}
					}else{
						$return = json_encode(array('status'=>'duplicatepos'));//for duplicate name
					}
                }else{
					$return = json_encode(array('status'=>'nounit'));
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