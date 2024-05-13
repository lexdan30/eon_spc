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
				if( !empty( $row['approver1'] ) ){
					$return = json_encode(array('status'=>'Requestalreadyapprovedordeclined'));
					print $return;
					mysqli_close($con);
					return;
				}
				
				$ticketNumber	=	$param->info->refferenceno;
				
				$Qrye 			= new Query();	
				$Qrye->table 	= "tblforms02";
				$Qrye->selected = " approver1		=	'".$param->accountid."',						
									approver1_date	=	'".$date."',
									approver1_time	=	'".$time."',
									approver1_status=	'2',
									idstatus 		= 	'2'";
				
				$Qrye->fields 	= "id='".$param->info->id."'";
				$checke = $Qrye->exe_UPDATE($con);
				if($checke){
					//send email to archive and request is declined
					$recipients 	= array();
					$recipients[] 	= array(
						getAccountEmail( $con, $param->info->createdbyid ) => getAccountName( $con, $param->info->createdbyid )
					 );
					if(!empty($recipients)){
						$mailSubject = "HRIS 2.0";
						$mailBody = "<h4>Personnel Action - Promotion</h4>";
						$mailBody .= "Document ID: ".$ticketNumber;
		
						$mailBody .="<br />Entry has been disapproved.<br /><br />";
		
						$return = _EMAILDIRECT_WAGEINCREASE($recipients, $mailSubject, $mailBody,$ticketNumber);
					}
				}
			}
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