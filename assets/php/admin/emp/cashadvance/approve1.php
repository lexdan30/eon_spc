<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php');
require_once('../../../email/emailFunction.php');

    $param  = json_decode(file_get_contents('php://input'));
    $date 	= SysDate();
    $time 	= SysTime();
    $return = null;
	
    if( !empty($param->accountid) ){
		$Qry = new Query();	
		$Qry->table ="tblforms04";
		$Qry->selected ="*";
		$Qry->fields ="id='".$param->info->id."'";
		$rs = $Qry->exe_SELECT($con);
		if( mysqli_num_rows($rs)==1 ){
			if( $row = mysqli_fetch_array($rs) ){
				if( !empty( $row['approver1'] ) ){
					$return = json_encode(array('status'=>'Requestalreadyapprovedordeclined'));
					print $return;
					mysqli_close($con);
					return;
				}
				
				$ticketNumber	=	$param->info->refferenceno;
				
				//get All approvers
				$approver_ctr = getCtrFormApprover($con, $param->form_id);
				
				$Qrye 			= new Query();	
				$Qrye->table 	= "tblforms04";
				$Qrye->selected = " approver1		=	'".$param->accountid."',						
									approver1_date	=	'".$date."',
									approver1_time	=	'".$time."',
									approver1_status=	'1' ";
				
				if( (int)$approver_ctr == 1 ){					
					$Qrye->selected = $Qrye->selected . ", idstatus = '1' ";
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
					if( (int)$approver_ctr > 1 ){
						$QryA			=	new Query();
						$QryA->table	=	"tblformsetup";
						$QryA->selected	=	"approver_type_2a, approver_type_2b, approver_2a, approver_2b";
						$QryA->fields	=	"idform = '".$param->form_id."'";
						$rsA			=	$QryA->exe_SELECT($con);
						if(mysqli_num_rows($rsA)>=1){
							if($rowA=mysqli_fetch_array($rsA)){
								if( !empty( getAccountEmail( $con, $rowA['approver_2a'] ) ) ){
									$recipients[] = array(
										getAccountEmail( $con, $rowA['approver_2a'] ) => getAccountName( $con, $rowA['approver_2a'] )
									);
								}

								if( !empty( getAccountEmail( $con, $rowA['approver_2b'] ) ) ){
									$recipients[] = array(
										getAccountEmail( $con, $rowA['approver_2b'] ) => getAccountName( $con, $rowA['approver_2b'] )
									);
								}
							}
						}
						//send email to next approver
						if(!empty($recipients)){
							$mailSubject = "HRIS 2.0";
							$mailBody = "<h4>Loans - Cash Advance</h4>";
							$mailBody .= "Document ID: ".$ticketNumber;
			
							$mailBody .="<br />Entry has been approved by Approver 1.<br />Waiting for your approval.<br /><br />";
			
							$return = _EMAILDIRECT_CASHADVANCE($recipients, $mailSubject, $mailBody,$ticketNumber);
						}else{
							$return = json_encode(array("status"=>"success", "recipients"=>$recipients, "refno"=>$ticketNumber ));
						}
					}else{
						//send email that request is archived
						$recipients 	= array();
						$recipients[] 	= array(
							getAccountEmail( $con, $param->info->accountid ) => getAccountName( $con, $param->info->accountid )
							);
						if(!empty($recipients)){
							$mailSubject = "HRIS 2.0";
							$mailBody = "<h4>Loans - Cash Advance</h4>";
							$mailBody .= "Document ID: ".$ticketNumber;
			
							$mailBody .="<br />Entry has been archived.<br /><br />";
			
							$return = _EMAILDIRECT_CASHADVANCE($recipients, $mailSubject, $mailBody,$ticketNumber);
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