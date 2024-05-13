<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

if(!empty($param->accountid)){
	if(!empty($param->info->name)){	
		$param->info->name  = strtoupper(strtolower(str_replace("'","",$param->info->name))); 		
		if( checkShiftName($con,$param->info->name, '') ){
			$return = json_encode(array("status"=>"exists1"));
			print $return;
			mysqli_close($con);
			return;
		}

		$Qry3           = new Query();
		$Qry3->table    = "tblshift";
		$Qry3->selected = "name";
		$Qry3->fields   = "'".$param->info->name."'";  

		
		
		if( !empty($param->info->stime) ){			
			
			$Qry3->selected = $Qry3->selected	.	",stime";
			$Qry3->fields   = $Qry3->fields		.	",'".$param->info->stime."'";
		}
		if( !empty($param->info->ftime) ){
			
			$Qry3->selected = $Qry3->selected	.	",ftime";
			$Qry3->fields   = $Qry3->fields		.	",'".$param->info->ftime."'";
        }
        
		if( !empty($param->info->break) ){
			$Qry3->selected = $Qry3->selected	.	",break";
			$Qry3->fields   = $Qry3->fields		.	",'".$param->info->break."'";
		}
	
        if( !empty($param->info->sstime) ){			
				
			$Qry3->selected = $Qry3->selected	.	",sstime";
			$Qry3->fields   = $Qry3->fields		.	",'".$param->info->sstime."'";
		}
		if( !empty($param->info->sftime) ){
		
			$Qry3->selected = $Qry3->selected	.	",sftime";
			$Qry3->fields   = $Qry3->fields		.	",'".$param->info->sftime."'";
        }


        if( !empty($param->info->stype) ){
			$reghrs			= "8.00";
			if( $param->info->stype == "Compressed Schedule" ){
				$reghrs		= "9.50";
			}
			if( strtotime($param->info->stime) > strtotime($param->info->ftime) ) {
				$date_out 	= date('Y-m-d', strtotime("+1 day", strtotime( SysDate() )));
			}else{
				$date_out 	= date('Y-m-d', strtotime(SysDate()));				
			}
			$difference		= sprintf('%0.2f', (strtotime($date_out.' '.$param->info->ftime) - strtotime(SysDate().' '.$param->info->stime)) / ( 60 * 60 ));
			if( $difference < 8 ){
				$reghrs			= "4.00";
				if( $param->info->stype == "Compressed Schedule" ){
					$reghrs			= "4.75";
				}
			}
			$Qry3->selected = $Qry3->selected	.	",stype,reghrs";
			$Qry3->fields   = $Qry3->fields		.	",'".$param->info->stype."','".$reghrs."'";
        }
		
		$checke = $Qry3->exe_INSERT($con);
		if($checke){
			$return = json_encode(array("status"=>"success"));
		}else{
			$return = json_encode(array('status'=>'error'));
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