<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 
require_once('../../../../email/emailFunction.php');

    $param  = json_decode(file_get_contents('php://input'));
    $date 	= SysDate();
    $time 	= SysTime();
	$return = null;

    if( !empty($param->accountid) ){
		$Qry = new Query();	
		$Qry->table ="tblforms02";
		$Qry->selected ="*";
		$Qry->fields ="id='".$param->info->id."'";
		$rs = $Qry->exe_SELECT($con);
		if( mysqli_num_rows($rs)==1 ){
			if( $row = mysqli_fetch_array($rs) ){
				if( !empty( $row['approver3'] ) ){
					$return = json_encode(array('status'=>'Requestalreadyapprovedordeclined'));
					print $return;
					mysqli_close($con);
					return;
				}
				
				$ticketNumber	=	$param->info->refferenceno;
				
				//get All approvers
				$approver_ctr = getCtrFormApprover($con, $param->form_id);
				
				$Qrye 			= new Query();	
				$Qrye->table 	= "tblforms02";
				$Qrye->selected = " approver3		=	'".$param->accountid."',						
									approver3_date	=	'".$date."',
									approver3_time	=	'".$time."',
									approver3_status=	'1' ";
				
				if( (int)$approver_ctr == 3 ){					
					$Qrye->selected = $Qrye->selected . ", idstatus = '1' ";

					if($row['effectivedate']<=$row['date_created']){
						$Qrye->selected = $Qrye->selected . ", 201update = '1' ";
					}
				}
				
				$Qrye->fields 	= "id='".$param->info->id."'";
				$checke = $Qrye->exe_UPDATE($con);
				if($checke){
					/*
					$formcols = "id,idform";
					for( $xx=1; $xx <= $approver_ctr; $xx++ ){
						$formcols = $formcols . ", approver_type_" . $xx . "a, approver_type_" . $xx ."b, approver_". $xx ."a, approver_". $xx ."b " ;
					}
					*/
					$recipients 	= array();
					if( (int)$approver_ctr > 3 ){
						$QryA			=	new Query();
						$QryA->table	=	"tblformsetup";
						$QryA->selected	=	"approver_type_4a, approver_type_4b, approver_4a, approver_4b";
						$QryA->fields	=	"idform = '".$param->form_id."'";
						$rsA			=	$QryA->exe_SELECT($con);
						if(mysqli_num_rows($rsA)>=1){
							if($rowA=mysqli_fetch_array($rsA)){
								if( (int)$rowA['approver_type_4a'] == 1 ){
									if( !empty( getSuperiorEmail( $con,$param->info->createdbyid ) ) ){
										$recipients[] = array(
										   getSuperiorEmail( $con,$param->entry->createdbyid ) => getSuperiorName( $con,$param->entry->createdbyid )
										);
									}
								}else{
									if( !empty( getAccountEmail( $con, $rowA['approver_4a'] ) ) ){
										$recipients[] = array(
										   getAccountEmail( $con, $rowA['approver_4a'] ) => getAccountName( $con, $rowA['approver_4a'] )
										);
									}
								}
								
								if( (int)$rowA['approver_type_4b'] == 1 ){
									if( !empty( getSuperiorEmail( $con,$param->info->createdbyid ) ) ){
										$recipients[] = array(
										   getSuperiorEmail( $con,$param->info->createdbyid ) => getSuperiorName( $con,$param->info->createdbyid )
										);
									}
								}else{
									if( !empty( getAccountEmail( $con, $rowA['approver_4b'] ) ) ){
										$recipients[] = array(
										   getAccountEmail( $con, $rowA['approver_4b'] ) => getAccountName( $con, $rowA['approver_4b'] )
										);
									}
								}
							}
						}
						//send email to next approver
						if(!empty($recipients)){
							$mailSubject = "HRIS 2.0";
							$mailBody = "<h4>Personnel Action - Wage Increase</h4>";
							$mailBody .= "Document ID: ".$ticketNumber;
			
							$mailBody .="<br />Entry has been approved by Approver 3.<br />Waiting for your approval.<br /><br />";
			
							$return = _EMAILDIRECT_WAGEINCREASE($recipients, $mailSubject, $mailBody,$ticketNumber);
						}else{
							$return = json_encode(array("status"=>"success", "recipients"=>$recipients, "refno"=>$ticketNumber ));
						}
					}else{
						if($row['effectivedate']<=$row['date_created']){
							$new = getCurrentData2($con, $param->info->id );
							$chk = updateaccountjob1( $con, $new );
							if( $chk ){
								//send email that request is archived
								$recipients 	= array();
								$recipients[] 	= array(
									getAccountEmail( $con, $param->info->createdbyid ) => getAccountName( $con, $param->info->createdbyid )
								 );
								if(!empty($recipients)){
									$mailSubject = "HRIS 2.0";
									$mailBody = "<h4>Personnel Action - Wage Increase</h4>";
									$mailBody .= "Document ID: ".$ticketNumber;
					
									$mailBody .="<br />Entry has been archived.<br /><br />";
					
									$return = _EMAILDIRECT_WAGEINCREASE($recipients, $mailSubject, $mailBody,$ticketNumber);
								}
							}
						}else{
							//send email that request is archived
							$recipients 	= array();
							$recipients[] 	= array(
								getAccountEmail( $con, $param->info->createdbyid ) => getAccountName( $con, $param->info->createdbyid )
								);
							if(!empty($recipients)){
								$mailSubject = "HRIS 2.0";
								$mailBody = "<h4>Personnel Action - Wage Increase</h4>";
								$mailBody .= "Document ID: ".$ticketNumber;
				
								$mailBody .="<br />Entry has been archived.<br />201 will be updated on ".$param->info->effectivedate."<br /><br />";
				
								$return = _EMAILDIRECT_WAGEINCREASE($recipients, $mailSubject, $mailBody,$ticketNumber);
							}
						}
					}
				}
			}
		}
    }else{
        $return = json_encode(array('status'=>'empty'));
    }

print $return;
mysqli_close($con);

?>