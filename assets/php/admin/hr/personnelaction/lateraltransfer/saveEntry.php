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
	
	if( array_key_exists('file',$_FILES) ){
		$ndx=array();
		foreach( $_FILES['file']['name'] as $kk=>$vv ){
			array_push( $ndx, $kk );
		}
	}

    if(!empty($param['accountid'])){
		
		//filter $param['entry']['effectivedate']
		if( strtotime($param['entry']['effectivedate']) < strtotime(SysDatePadLeft()) ){
			$month3before = date("Y-m-d", strtotime(" -3 month", strtotime(date(SysDatePadLeft()))));
			if( strtotime($param['entry']['effectivedate']) < strtotime($month3before) ){
				$return = json_encode(array('status'=>'invdate','on'=>'img_check'));
				print $return;	
				mysqli_close($con);
				return;
			}
			$pay_period2 = getLatePayPeriod($con, SysDatePadLeft());
		}else{
			$pay_period2 = getLatePayPeriod($con, $param['entry']['effectivedate']);
		}
		$pay_period1 = getLatePayPeriod($con, $param['entry']['effectivedate']);
		
		$start_date  = $pay_period1['pay_start'];
		$end_date    = $pay_period2['pay_end'];
		$pay_date	 = $pay_period2['pay_date'];
		
		
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
			if( !empty($param['entry']['doc_job_desc']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['doc_perf_appr']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['doc_promotion']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
		}
		
		//Add code here how to check if exist once instructed
		if( !chkpendingForm( $con, "tblforms01", $param['entry']['idacct'] ) ){
			$latest_approved = getLatestRequest( $con, "tblforms01", $param['entry']['idacct'] );
			if( !empty( $latest_approved ) ){
				if( $param['entry']['currentdeptname'] == $latest_approved['currentdeptname'] ){
					if( $param['entry']['newdeptname'] == $latest_approved['newiddept'] ){
						if( $param['entry']['currentsection'] == $latest_approved['currentsection'] ){
							if( $param['entry']['newsection'] == $latest_approved['newidsection'] ){
								$return = json_encode(array('status'=>'entryExists'));
								print $return;	
								mysqli_close($con);
								return;
							}
						}
					}
				}
			}
		}else{
			$return = json_encode(array('status'=>'haspending'));
			print $return;	
			mysqli_close($con);
			return;
		}

		foreach ($param['entry'] as $keyzz => $valuezz) {
			if( !is_array($param['entry'][$keyzz]) && $keyzz != 'picFile' ){
				if( strtolower($param['entry'][$keyzz]) == 'null' ){
					$param['entry'][$keyzz]="";
				}
			}
		}

        $linkid 		=	getReqCtr($con);
        $linkid1 		=	$linkid + 1;
        $ticketNumber 	=	"LT".str_pad($linkid1,6,"0",STR_PAD_LEFT);

        $Qry 			= new Query();	
        $Qry->table 	= "tblforms01";
        $Qry->selected 	= "requestor,refferenceno, empid, empname, effectivedate, empactiontaken, currentdeptname, newdeptname, newiddept, currentdeptmanager, newdeptmanager, newidmngr ,currentimmediatesupervisor, newimmediatesupervisor, newidsuperior, currentsection, newsection, currentempstatus, newempstatus, currentjobcode, newjobcode, currentjoblevel, newjoblevel, currentpositiontitle, newpositiontitle, currentpaygroup, newpaygroup, currentlabortype, newlabortype, remarks, createdby, date_created, time_created, start_date, end_date, pay_date";
		$Qry->fields 	= " '".$param['entry']['idacct']."','".$ticketNumber."','".$param['entry']['empid']."','".$param['entry']['empname']."','".$param['entry']['effectivedate']."','".$param['entry']['actiontaken']."','".$param['entry']['currentdeptname']."','".$param['entry']['str_dept']."','".$param['entry']['newdeptname']."','".$param['entry']['currentdeptmanager']."','".$param['entry']['str_mngr']."','".$param['entry']['newdeptmanager']."','".$param['entry']['currentimmediatesupervisor']."','".$param['entry']['str_super']."','".$param['entry']['newimmediatesupervisor']."','".$param['entry']['currentsection']."','".$param['entry']['str_section']."','".$param['entry']['currentempstatus']."','".$param['entry']['newempstatus']."','".$param['entry']['currentjobcode']."','".$param['entry']['newjobcode']."','".$param['entry']['currentjoblevel']."','".$param['entry']['newjoblevel']."','".$param['entry']['currentpositiontitle']."','".$param['entry']['newpositiontitle']."','".$param['entry']['currentpaygroup']."','".$param['entry']['newpaygroup']."','".$param['entry']['currentlabortype']."','".$param['entry']['newlabortype']."','".$param['entry']['remarks']."','".$param['accountid']."','".SysDate()."','".SysTime()."','".$start_date."','".$end_date."','".$pay_date."'";

		if( !empty($param['entry']['str_section']) ){
			$Qry->selected 	= $Qry->selected . ", newidsection";
			$Qry->fields 	= $Qry->fields 	 . ", '".$param['entry']['newsection']."'";
		}
		
		if( array_key_exists('file',$_FILES) ){	

			foreach( $ndx as $ndxval ){
				if($ndxval==0){
					$folder_path 	= $param['targetPath'];
					$name 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();
					$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
					$save_name		= $ticketNumber.'JD.'.$extMove;	
					$Qry->selected 	= $Qry->selected . ", jobdescdoc, jobdescfile";
					$Qry->fields 	= $Qry->fields 	 . ", '".$param['entry']['doc_job_desc']."', '".$save_name."'";
				}
				if($ndxval==1){
					$folder_path 	= $param['targetPath'];
					$name1 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();
					$extMove1 		= pathinfo($name1, PATHINFO_EXTENSION);
					$save_name1		= $ticketNumber.'PA.'.$extMove1;	
					$Qry->selected 	= $Qry->selected . ", perfapprdoc, perfapprfile";
					$Qry->fields 	= $Qry->fields 	 . ", '".$param['entry']['doc_perf_appr']."', '".$save_name1."'";
				}
				if($ndxval==2){
					$folder_path 	= $param['targetPath'];
					$name2 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();
					$extMove2 		= pathinfo($name2, PATHINFO_EXTENSION);
					$save_name2		= $ticketNumber.'PR.'.$extMove2;	
					$Qry->selected 	= $Qry->selected . ", promdoc, promfile";
					$Qry->fields 	= $Qry->fields 	 . ", '".$param['entry']['doc_promotion']."', '".$save_name2."'";
				}
			}

		}
        $checkentry 	= $Qry->exe_INSERT($con); 
		//exit();
		if( $checkentry ){
			
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
						if(!empty($recipients)){
							$mailSubject = "HRIS 2.0";
							$mailBody = "<h4>Personnel Action - Lateral Transfer</h4>";
							$mailBody .= "Document ID: ".$ticketNumber;

							$mailBody .="<br />Entry has been created from HR Department.<br />Waiting for your approval.<br /><br />";

							$return = _EMAILDIRECT_LATERALTRANSFER($recipients, $mailSubject, $mailBody,$ticketNumber);
						}else{
							$return = json_encode(array("status"=>"success", "recipients"=>$recipients, "refno"=>"$ticketNumber" ));
						}
			}else{
				$return = json_encode(array('status'=>'error'));
		}
    }else{
        $return = json_encode(array('status'=>'empty'));
    }

print $return;
mysqli_close($con);



function getReqCtr($con){
    $Qry=new Query();
    $Qry->table="tblforms01";
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