<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

if(!empty($param->accountid)){
	if(!empty($param->info->alias)){
		if( !empty( $param->info->ain ) ){
			if( !empty( $param->info->aout ) ){
				if( !empty( $param->info->bin ) ){
					if( !empty( $param->info->bout ) ){
						if( checkConfigName($con,$param->info->alias, $param->info->id) ){
							$return = json_encode(array("status"=>"dupname"));
							print $return;
							mysqli_close($con);
							return;
						}else{
							$param->info->descript  = ucwords(strtolower(str_replace("'","",$param->info->descript)));
							$param->info->note  	= ucwords(strtolower(str_replace("'","",$param->info->note)));
							$param->info->ain  		= ((str_replace("'","",$param->info->ain)));
							$param->info->aout  	= ((str_replace("'","",$param->info->aout)));
							$param->info->bin  		= ((str_replace("'","",$param->info->bin)));
							$param->info->bout  	= ((str_replace("'","",$param->info->bout)));
							
							$Qry3           = new Query();
							$Qry3->table    = "tbltimelogsconf";
							$Qry3->selected = "alias='".$param->info->alias."',emp='".implode(",",array_map( function($value) { return (int)$value; }, $param->info->emp ))."',wdate='".implode(",",array_map( function($value) { return (int)$value; }, $param->info->wdate ))."',wtime='".implode(",",array_map( function($value) { return (int)$value; }, $param->info->wtime ))."',atype='".implode(",",array_map( function($value) { return (int)$value; }, $param->info->atype ))."',
												ain='".$param->info->ain."',aout='".$param->info->aout."',bin='".$param->info->bin."',bout='".$param->info->bout."'";
							
							if( !empty( $param->info->descript ) ){
								$Qry3->selected = $Qry3->selected . ",descript='".$param->info->descript."'";
							}else{
								$Qry3->selected = $Qry3->selected . ",descript=NULL";
							}
							
							if( !empty( $param->info->note ) ){
								$Qry3->selected = $Qry3->selected . ",note='".$param->info->note."'";
							}else{
								$Qry3->selected = $Qry3->selected . ",note=NULL";
							}
							
							$Qry3->fields   = "id='".$param->info->id."'";
							$checke = $Qry3->exe_UPDATE($con);
							if($checke){
								$return = json_encode(array("status"=>"success"));
							}else{
								$return = json_encode(array('status'=>'error'));
							}	
						}
					}else{
						$return = json_encode(array('status'=>'bout'));
					}
				}else{
					$return = json_encode(array('status'=>'bin'));
				}
			}else{
				$return = json_encode(array('status'=>'aout'));
			}
		}else{
			$return = json_encode(array('status'=>'ain'));
		}
	}else{
		$return = json_encode(array('status'=>'name'));
	}
}else{
	 $return = json_encode(array('status'=>'notloggedin'));
}


print $return;
mysqli_close($con);
?>