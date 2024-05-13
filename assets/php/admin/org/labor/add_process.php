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
            $param->info->alias     = strtoupper(strtolower(str_replace("'","",$param->info->alias))); 
            // $param->info->name      = strtoupper(strtolower(str_replace("'","",$param->info->name)));         
            if( !checkAlias($con, $param->info->alias, '', 'tbllabortype') ){    
                $Qry3           = new Query();
                $Qry3->table    = "tbllabortype";
                $Qry3->selected = "type,alias";
                $Qry3->fields   = "'".$param->info->name."',
                                    '".$param->info->alias."'";                    
                $checke = $Qry3->exe_INSERT($con);
                if($checke){
                    $return = json_encode(array("status"=>"success"));
                }else{
                    $return = json_encode(array('status'=>'error'));
                }
            }else{
                $return = json_encode(array('status'=>'dupalias'));
            }            
        }else{
            $return = json_encode(array('status'=>'noname'));
        }
    }else{
        $return = json_encode(array('status'=>'noid'));
    }
}else{
    $return = json_encode(array('status'=>'notloggedin'));
}

print $return;
mysqli_close($con);
?>