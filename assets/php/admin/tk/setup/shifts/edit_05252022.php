<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

if(!empty($param->accountid)){
	if(!empty($param->info->name)){	
		$param->info->name  = strtoupper(strtolower(str_replace("'","",$param->info->name))); 
		if( checkShiftName($con,$param->info->name, $param->info->id) ){
			$return = json_encode(array("status"=>"exists1"));
			print $return;
			mysqli_close($con);
			return;
		}		
		
		$Qry3           = new Query();
		$Qry3->table    = "tblshift";
		$Qry3->selected = "name='".$param->info->name."'";
		
		if( !empty($param->info->stime) ){			
	
			$Qry3->selected = $Qry3->selected	.	",stime='".$param->info->stime."'";
		}else{
			$Qry3->selected = $Qry3->selected	.	",stime=NULL";
		}
		
		if( !empty($param->info->ftime) ){
		
			$Qry3->selected = $Qry3->selected	.	",ftime='".$param->info->ftime."'";
		}else{
			$Qry3->selected = $Qry3->selected	.	",ftime=NULL";
		}
		
		if( !empty($param->info->break) ){
			$Qry3->selected = $Qry3->selected	.	",break='".$param->info->break."'";
		}else{
			$Qry3->selected = $Qry3->selected	.	",break=NULL";
        }
        

        if( !empty($param->info->sstime) ){			
			
			$Qry3->selected = $Qry3->selected	.	",sstime='".$param->info->sstime."'";
		}else{
			$Qry3->selected = $Qry3->selected	.	",sstime=NULL";
		}
		
		if( !empty($param->info->sftime) ){
			
			$Qry3->selected = $Qry3->selected	.	",sftime='".$param->info->sftime."'";
		}else{
			$Qry3->selected = $Qry3->selected	.	",sftime=NULL";
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
            $Qry3->selected = $Qry3->selected	.	",stype='".$param->info->stype."',reghrs='".$reghrs."'";
        }else{
			$Qry3->selected = $Qry3->selected	.	",stype=NULL";
        }
		
		
		$Qry3->fields   = "id='".$param->info->id."'";                    
        $checke = $Qry3->exe_UPDATE($con);
        
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