<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php');
require_once('../../../../email/emailFunction.php');

    $param 	= $_POST;
    $date 	= SysDate();
    $time 	= SysTime();
	$return = null;
	$oldminwage = '';

	if(checkIfExist($con, $param['entry']['wono'])){
		$return = json_encode(array('status'=>'entryExists'));
		print $return;	
		mysqli_close($con);
		return;
	}
	
	$param['entry']['minwage'] = (str_replace(",","",$param['entry']['minwage']));
	$param['entry']['minwage'] = number_format($param['entry']['minwage'],2);
	$param['entry']['minwage'] = (str_replace(",","",$param['entry']['minwage']));
	$oldminwage = getOldMinWage($con,$param['entry']['region']);
	$oldminwage = number_format($oldminwage,4);
	
    if(!empty($param['accountid'])){
		
		if( array_key_exists('file',$_FILES) ){
			$valid_formats = array("pdf","jpg", "png", "jpeg");
			if ($_FILES['file']['error'] == 4) {
				$return = json_encode(array('status'=>'error','on'=>'img_check'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if ($_FILES['file']['error'] == 0) {
				if(!in_array(pathinfo(strtolower($_FILES['file']['name']), PATHINFO_EXTENSION), $valid_formats) ){
					$return = json_encode(array('status'=>'error-upload-type'));
					print $return;	
					mysqli_close($con);
					return;
				}
			}
		}
		
        $Qry 			= new Query();	
        $Qry->table 	= "tblmwi";
        $Qry->selected 	= "idupdatedby, dateupdated, timeupdated, wageorderno, effectivedate, oldminwage, newminwage, region";
        $Qry->fields 	= " '".$param['accountid']."',
							'".SysDate()."',
							'".SysTime()."',
                            '".$param['entry']['wono']."',
                            '".$param['entry']['effdate']."',
                            '".$oldminwage."',
                            '".$param['entry']['minwage']."',
                            '".$param['entry']['region']."'";
		
		if( array_key_exists('file',$_FILES) ){	
			$folder_path 	= $param['targetPath'];
			$name 			= $_FILES['file']['name'];
			$t				= strtotime($date).time();
			$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
			$save_name		= $param['entry']['wono'].'.'.$extMove;	
			$Qry->selected 	= $Qry->selected . ", attachment";
			$Qry->fields 	= $Qry->fields 	 . ", '".$save_name."'";
		}
        $checkentry 	= $Qry->exe_INSERT($con); 
		if( $checkentry ){

			foreach($param['employees'] as $key=>$value){
				$Qrya 			= new Query();	
				$Qrya->table 	= "tblmwilogs";
				$Qrya->selected = "wageorderno, empid, lname, fname, mi, idunit, business_unit, region, olddailyrate, newdailyrate";
				$Qrya->fields 	= " '".$param['entry']['wono']."',
									'".$param['employees'][$key]['id']."',
									'".$param['employees'][$key]['lname']."',
									'".$param['employees'][$key]['fname']."',
									'".$param['employees'][$key]['mname']."',
									'".$param['employees'][$key]['id_unit']."',
									'".$param['employees'][$key]['business_unit']."',
									'".$param['employees'][$key]['job_region']."',
									'".$param['employees'][$key]['salary']."',
									'".$param['entry']['minwage']."'";
				
				$checkentrya 	= $Qrya->exe_INSERT($con); 
				if($checkentrya){
					$newsal = 0;
					if($param['employees'][$key]['idpaygrp']==3){
						$newsal = $param['entry']['minwage'] * $param['employees'][$key]['daysmonth'];
					}
					if($param['employees'][$key]['idpaygrp']==5){
						$newsal = $param['entry']['minwage'];
					}
					if($param['employees'][$key]['idpaygrp']==11){
						$newsal = $param['entry']['minwage'] * $param['employees'][$key]['daysmonth'];
					}
					//Update to 201 tblaccountjob column salary
					$Qryb 			= new Query();	
					$Qryb->table 	= "tblaccountjob";
					$Qryb->selected = "salary	= '".round($newsal,2)."'";
					$Qryb->fields 	= "idacct = '".$param['employees'][$key]['idacct']."'";
					$checkentryb 	= $Qryb->exe_UPDATE($con); 

					$Qry3b           = new Query();
					$Qry3b->table    = "tblacctsalary";
					$Qry3b->selected = "idacct,salary,effectivity_date";
					$Qry3b->fields   = "'".$param['employees'][$key]['idacct']."','".round($newsal,2)."','".$param['entry']['effdate']."'";
					$checke3b = $Qry3b->exe_INSERT($con);
				}
			}

			//Update to 201 tblaccountjob column salary
			$Qryb 			= new Query();	
			$Qryb->table 	= "tblregion";
			$Qryb->selected = "min_wage = '".$param['entry']['minwage']."'";
			$Qryb->fields 	= "regDesc = '".$param['entry']['region']."'";
			$checkentryb 	= $Qryb->exe_UPDATE($con); 

			//Update to 201 tbljobwage column min_wage
			$Qryc 			= new Query();	
			$Qryc->table 	= "tbljobwage";
			$Qryc->selected = "min_wage = '".$param['entry']['minwage']."',max_wage = '".$param['entry']['minwage']."'";
			$Qryc->fields 	= "idloc = '".getIdJobLoc($con, $param['entry']['region'])."' AND idjoblvl in (6,7,8,9)";
			$checkentryc 	= $Qryc->exe_UPDATE($con); 
			
			//Upload Attachment
			if( array_key_exists('file',$_FILES) ){	
				$folder_path 	= $param['targetPath'];
				$name 			= $_FILES['file']['name'];
				$t				= strtotime($date).time();
				$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
				$save_name		= $param['entry']['wono'].'.'.$extMove;
				move_uploaded_file($_FILES["file"]["tmp_name"], $folder_path.$save_name);

				$return = json_encode(array('status'=>'success'));
			}else{
				$return = json_encode(array('status'=>'error-iddoc'));
			}
			
		}else{
			$return = json_encode(array('status'=>'error'));
		}
    }else{
        $return = json_encode(array('status'=>'notloggedin'));
    }

print $return;
mysqli_close($con);

function getOldMinWage($con, $region){
    $Qry=new Query();
    $Qry->table="tblregion";
    $Qry->selected="*";
    $Qry->fields="id>0 AND regDesc='".$region."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['min_wage'];
        }
    }
    return null;
}

function getIdJobLoc($con, $region){
    $Qry=new Query();
    $Qry->table="tbljoblocation";
    $Qry->selected="*";
    $Qry->fields="region='".$region."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['id'];
        }
    }
    return null;
}

function checkIfExist($con, $wageorderno){
    $Qry=new Query();
    $Qry->table="tblmwi";
    $Qry->selected="wageorderno";
    $Qry->fields="wageorderno='".$wageorderno."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return true;
        }
    }
    return false;
}

?>