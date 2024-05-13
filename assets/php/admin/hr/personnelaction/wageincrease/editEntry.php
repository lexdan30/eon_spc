<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

    $param 	= $_POST;
    $date 	= SysDate();
    $time 	= SysTime();
	$return = null;
	
	if( array_key_exists('file',$_FILES) ){
		$ndx=array();
		foreach( $_FILES['file']['name'] as $kk=>$vv ){
			array_push( $ndx, $kk );
		}
	}

    if(!empty($param['accountid'])){
		
		
		foreach ($param['entry'] as $keyzz => $valuezz) {
			if( !is_array($param['entry'][$keyzz]) && $keyzz != 'picFile' ){
				if( strtolower($param['entry'][$keyzz]) == 'null' ){
					$param['entry'][$keyzz]="";
				}
			}
		}

		if (array_key_exists("allowance",$param['entry'])){
			foreach ($param['entry']['allowance'] as $keyzz => $valuezz) {
				$param['entry']['allowance'][$keyzz]['new_amt'] 	= (str_replace(",","",$param['entry']['allowance'][$keyzz]['new_amt']));
				$param['entry']['allowance'][$keyzz]['current_amt'] = (str_replace(",","",$param['entry']['allowance'][$keyzz]['current_amt']));
			}
		}
		
		//check if request in progress
		if( checkFormProgressWageIncrease($con, $param['form_id'], $param['entry']['id']) ){
			$return = json_encode(array('status'=>'error','w'=>mysqli_error($con)));
			print $return;	
			mysqli_close($con);
			return;
		}
		
		//filter $param['entry']['effectivedate']
		if( strtotime($param['entry']['effectivedate']) < strtotime(  $param['entry']['datecreated']  ) ){
			$month3before = date("Y-m-d", strtotime(" -3 month", strtotime(date( $param['entry']['datecreated'] ))));
			if( strtotime($param['entry']['effectivedate']) < strtotime($month3before) ){
				$return = json_encode(array('status'=>'invdate','on'=>'img_check'));
				print $return;	
				mysqli_close($con);
				return;
			}
			$pay_period2 = getLatePayPeriod($con, $param['entry']['datecreated'] );
		}else{
			$pay_period2 = getLatePayPeriod($con, $param['entry']['effectivedate']);
		}
		$pay_period1 = getLatePayPeriod($con, $param['entry']['effectivedate']);
		$start_date  = $pay_period1['pay_start'];
		$end_date    = $pay_period2['pay_end'];
		$pay_date	 = $pay_period2['pay_date'];
		
		$ticketNumber= $param['entry']['refferenceno'];
		
		if( array_key_exists('file',$_FILES) ){
			$valid_formats = array("pdf");	
			
			foreach( $ndx as $ndxval ){
				if ($_FILES['file']['error'][$ndxval] == 4) {
					$return = json_encode(array('status'=>'error','on'=>'img_check'));
					print $return;	
					mysqli_close($con);
					return;
				}
				if ($_FILES['file']['error'][$ndxval] == 0) {
					if(!in_array(pathinfo(strtolower($_FILES['file']['name'][$ndxval]), PATHINFO_EXTENSION), $valid_formats) ){
						$return = json_encode(array('status'=>'error-upload-type'));
						print $return;	
						mysqli_close($con);
						return;
					}
				}
			}
		}else{
			if( !empty($param['entry']['doc_job_desc']) && empty($param['entry']['jobdescfile']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['doc_perf_appr']) && empty($param['entry']['perfapprfile']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['doc_promotion']) && empty($param['entry']['promfile']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
		}		

        $Qry 			= new Query();	
        $Qry->table 	= "tblforms02";
        $Qry->selected 	= " effectivedate 			= 	'".$param['entry']['effectivedate']."',
                            newbasepay				=	'".$param['entry']['newbasepay']."',
							newtotalcashcomp		=	'".$param['entry']['newtotalcashcomp']."',
                            remarks					=	'".$param['entry']['remarks']."',
							start_date				=	'".$start_date."',
							end_date				=	'".$end_date."',
							pay_date				=	'".$pay_date."'";
		
		if( array_key_exists('file',$_FILES) ){	
			
			foreach( $ndx as $ndxval ){
				if($ndxval==0){
					$folder_path 	= $param['targetPath'];
					$name 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();	
					$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
					$save_name		= $param['entry']['refferenceno'].'JD.'.$extMove;	
					$Qry->selected 	= $Qry->selected.",jobdescfile='".$save_name."', jobdescdoc='".$param['entry']['doc_job_desc']."'";
				}

				if($ndxval==1){
					$folder_path 	= $param['targetPath'];
					$name1 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();	
					$extMove1 		= pathinfo($name1, PATHINFO_EXTENSION);
					$save_name1		= $param['entry']['refferenceno'].'PA.'.$extMove1;	
					$Qry->selected 	= $Qry->selected.",perfapprfile='".$save_name1."', perfapprdoc='".$param['entry']['doc_perf_appr']."'";
				}

				if($ndxval==2){
					$folder_path 	= $param['targetPath'];
					$name2 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();	
					$extMove2 		= pathinfo($name2, PATHINFO_EXTENSION);
					$save_name2		= $param['entry']['refferenceno'].'PR.'.$extMove2;	
					$Qry->selected 	= $Qry->selected.",promfile='".$save_name2."', promdoc='".$param['entry']['doc_promotion']."'";
				}
			}

			if($param['entry']['doc_job_desc']==''||$param['entry']['doc_job_desc']==NULL||$param['entry']['doc_job_desc']==0){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['refferenceno'].'JD';
				if( file_exists($folder.$filename.'.pdf') ){
					unlink( $folder.$filename.'.pdf' );
				}
				$Qry->selected 	= $Qry->selected.",jobdescfile='', jobdescdoc=''";
			}
			if($param['entry']['doc_perf_appr']==''||$param['entry']['doc_perf_appr']==NULL||$param['entry']['doc_perf_appr']==0){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['refferenceno'].'PA';
				if( file_exists($folder.$filename.'.pdf') ){
					unlink( $folder.$filename.'.pdf' );
				}
				$Qry->selected 	= $Qry->selected.",perfapprfile='', perfapprdoc=''";
			}
			if($param['entry']['doc_promotion']==''||$param['entry']['doc_promotion']==NULL||$param['entry']['doc_promotion']==0){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['refferenceno'].'PR';
				if( file_exists($folder.$filename.'.pdf') ){
					unlink( $folder.$filename.'.pdf' );
				}
				$Qry->selected 	= $Qry->selected.",promfile='', promdoc=''";
			}

		}else{
			if($param['entry']['doc_job_desc']==''||$param['entry']['doc_job_desc']==NULL||$param['entry']['doc_job_desc']==0){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['refferenceno'].'JD';
				if( file_exists($folder.$filename.'.pdf') ){
					unlink( $folder.$filename.'.pdf' );
				}
				$Qry->selected 	= $Qry->selected.",jobdescfile='', jobdescdoc=''";
			}
			if($param['entry']['doc_perf_appr']==''||$param['entry']['doc_perf_appr']==NULL||$param['entry']['doc_perf_appr']==0){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['refferenceno'].'PA';
				if( file_exists($folder.$filename.'.pdf') ){
					unlink( $folder.$filename.'.pdf' );
				}
				$Qry->selected 	= $Qry->selected.",perfapprfile='', perfapprdoc=''";
			}
			if($param['entry']['doc_promotion']==''||$param['entry']['doc_promotion']==NULL||$param['entry']['doc_promotion']==0){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['refferenceno'].'PR';
				if( file_exists($folder.$filename.'.pdf') ){
					unlink( $folder.$filename.'.pdf' );
				}
				$Qry->selected 	= $Qry->selected.",promfile='', promdoc=''";
			}
		}
		$Qry->fields 	= "id = '". $param['entry']['id'] ."'";
		$checkentry 	= $Qry->exe_UPDATE($con); 
		if( $checkentry ){

			if (array_key_exists("allowance",$param['entry'])){
				foreach($param['entry']['allowance'] as $keyzzz => $valuezzz){
					// Insert to table
					$Qry2            = new Query();
					$Qry2->table     = "tblformsallowance";
					$Qry2->selected  = "current_amt='".$param['entry']['allowance'][$keyzzz]['current_amt']."',
										new_amt='".$param['entry']['allowance'][$keyzzz]['new_amt']."'";
					$Qry2->fields 	 = "id = '".$param['entry']['allowance'][$keyzzz]['id']."'";
					$rs2             = $Qry2->exe_UPDATE($con);
					if(!$rs2){
						$return = json_encode(array('status'=>mysqli_error($con)));
						print $return;
						mysqli_close($con);
						return;
					}
				}
			}
			
			//Upload Attachment
			if( array_key_exists('file',$_FILES) ){																				
				foreach( $ndx as $ndxval ){
					if($ndxval==0){
						$folder_path 	= $param['targetPath'];
						$name 			= $_FILES['file']['name'][$ndxval];
						$t				= strtotime($date).time();
						$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
						$save_name		= $ticketNumber.'JD.'.$extMove;
						move_uploaded_file($_FILES["file"]["tmp_name"][$ndxval], $folder_path.$save_name);
					}
					if($ndxval==1){
						$folder_path 	= $param['targetPath'];
						$name 			= $_FILES['file']['name'][$ndxval];
						$t				= strtotime($date).time();
						$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
						$save_name		= $ticketNumber.'PA.'.$extMove;
						move_uploaded_file($_FILES["file"]["tmp_name"][$ndxval], $folder_path.$save_name);
					}
					if($ndxval==2){
						$folder_path 	= $param['targetPath'];
						$name 			= $_FILES['file']['name'][$ndxval];
						$t				= strtotime($date).time();
						$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
						$save_name		= $ticketNumber.'PR.'.$extMove;
						move_uploaded_file($_FILES["file"]["tmp_name"][$ndxval], $folder_path.$save_name);
					}
				}
			}
			
			//get All approvers
			$approver_ctr = getCtrFormApprover($con, $param['form_id']);
			$formcols = "id,idform";
			for( $xx=1; $xx <= $approver_ctr; $xx++ ){
				$formcols = $formcols . ", approver_type_" . $xx . "a, approver_type_" . $xx ."b, approver_". $xx ."a, approver_". $xx ."b " ;
			}
			
			$recipients 	= array();
			$QryA			=	new Query();
			$QryA->table	=	"tblformsetup";
			$QryA->selected	=	"approver_type_1a, approver_type_1b, approver_1a, approver_1b";
			$QryA->fields	=	"idform = '".$param['form_id']."'";
			$rsA			=	$QryA->exe_SELECT($con);
			if(mysqli_num_rows($rsA)>=1){
				if($rowA=mysqli_fetch_array($rsA)){
					if( (int)$rowA['approver_type_1a'] == 1 ){
						if( !empty( getSuperiorEmail( $con,$param['entry']['idacct'] ) ) ){
							$recipients[] = array(
							   getSuperiorEmail( $con,$param['entry']['idacct'] ) => getSuperiorName( $con,$param['entry']['idacct'] )
							);
						}
					}else{
						if( !empty( getAccountEmail( $con, $rowA['approver_1a'] ) ) ){
							$recipients[] = array(
							   getAccountEmail( $con, $rowA['approver_1a'] ) => getAccountName( $con, $rowA['approver_1a'] )
							);
						}
					}
					
					if( (int)$rowA['approver_type_1b'] == 1 ){
						if( !empty( getSuperiorEmail( $con,$param['entry']['idacct'] ) ) ){
							$recipients[] = array(
							   getSuperiorEmail( $con,$param['entry']['idacct'] ) => getSuperiorName( $con,$param['entry']['idacct'] )
							);
						}
					}else{
						if( !empty( getAccountEmail( $con, $rowA['approver_1b'] ) ) ){
							$recipients[] = array(
							   getAccountEmail( $con, $rowA['approver_1b'] ) => getAccountName( $con, $rowA['approver_1b'] )
							);
						}
					}
				}
			}
			$return = json_encode(array("status"=>"success", "recipients"=>$recipients, "refno"=>$ticketNumber ));
		}else{
			$return = json_encode(array('status'=>'error','w'=> mysqli_error($con), 'f'=>$Qry->selected, 'a'=>$Qry->fields ));
		}
    }else{
        $return = json_encode(array('status'=>'empty'));
    }

print $return;
mysqli_close($con);



function getReqCtr($con){
    $Qry=new Query();
    $Qry->table="tblforms02";
    $Qry->selected="count(id) as ctr";
    $Qry->fields="id>0";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['ctr'];
        }
    }
    return null;
}

?>