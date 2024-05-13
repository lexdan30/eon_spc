<?php
require_once('../../../activation.php');
$param = json_decode(file_get_contents('php://input'));
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 



 

if(!empty($param->accountid)){
    if(!empty($param->info->alias)){
        if(!empty($param->info->name)){
			if(!empty($param->info->idmeasure)){
				if(!empty($param->info->value)){
					$param->info->alias     = str_replace("'","",$param->info->alias); 
					$param->info->name      = ((str_replace("'","",$param->info->name))); 
					$param->info->remarks   = ((str_replace("'","",$param->info->remarks))); 
					$Qry3           = new Query();
					$Qry3->table    = "tblpreference";
					$Qry3->selected = "alias,prefname,idmeasure,value,date_create,createdby";
					$Qry3->fields   = "'".$param->info->alias."',
										'".$param->info->name."',
										'".$param->info->idmeasure."',
										'".$param->info->value."',
										'".SysDatePadLeft()."',
										'".$param->accountid."'";      
					
					if( !empty($param->info->remarks) ){
						$Qry3->selected = $Qry3->selected . ",remarks";
						$Qry3->fields   = $Qry3->fields   . ",'".$param->info->remarks."'";
					}					
					$checke = $Qry3->exe_INSERT($con);
					if($checke){
						$return = json_encode(array("status"=>"success"));
					}else{
						$return = json_encode(array('status'=>'error'));
					}
				}else{
					$return = json_encode(array('status'=>'noval'));
				}
			}else{
				$return = json_encode(array('status'=>'nomeasure'));
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