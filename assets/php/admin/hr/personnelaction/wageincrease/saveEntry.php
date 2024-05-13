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

	//if( array_key_exists('file',$_FILES) ){echo count($param['file']);return;}else{ echo 'y';return;}
	
	// echo "<pre>";
	// print_r( $_FILES['file'] );
	// echo "</pre>";
	
	if( array_key_exists('file',$_FILES) ){
		$ndx=array();
		foreach( $_FILES['file']['name'] as $kk=>$vv ){
			array_push( $ndx, $kk );
		}
	}


	// echo "<pre>";
	// print_r( $param['entry']['allowance'] );
	// echo "</pre>";
	
	// return;


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

		if (array_key_exists("allowance",$param['entry'])){
			foreach ($param['entry']['allowance'] as $keyzz => $valuezz) {
				$param['entry']['allowance'][$keyzz]['new_amount'] 	= (str_replace(",","",$param['entry']['allowance'][$keyzz]['new_amount']));
				$param['entry']['allowance'][$keyzz]['amt'] 		= (str_replace(",","",$param['entry']['allowance'][$keyzz]['amt']));
			}
		}
		
		//Add code here how to check if exist once instructed
		if( !chkpendingForm( $con, "tblforms02", $param['entry']['idacct'] ) ){
			$latest_approved = getLatestRequestWageIncrease( $con, "tblforms02", $param['entry']['idacct'] );
			if( !empty( $latest_approved ) ){
				if(count($latest_approved)==count($param['entry']['allowance'])){
					$counter = 0;
					foreach ($param['entry']['allowance'] as $kkk => $vvv) {
						if($param['entry']['allowance'][$kkk]['new_amount'] != $latest_approved[$kkk]['new_amt']){
							$counter++;
						}
					}
					if($counter==0){
						$return = json_encode(array('status'=>'entryExists'));
						print $return;	
						mysqli_close($con);
						return;
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
        $ticketNumber 	=	"WI".str_pad($linkid1,6,"0",STR_PAD_LEFT);

        $Qry 			= new Query();	
        $Qry->table 	= "tblforms02";
        $Qry->selected 	= "requestor,refferenceno, empid, empname, effectivedate, empactiontaken, currentdeptname, currentdeptmanager, currentimmediatesupervisor, currentsection, currentempstatus, currentjobcode, currentjoblevel, currentpositiontitle, currentpaygroup, currentlabortype, currentbasepay, newbasepay, currenttotalcashcomp, newtotalcashcomp, remarks, createdby, date_created, time_created, start_date, end_date, pay_date";
        $Qry->fields 	= " '".$param['entry']['idacct']."',
							'".$ticketNumber."', 
                            '".$param['entry']['empid']."',
                            '".$param['entry']['empname']."',
                            '".$param['entry']['effectivedate']."',
                            '".$param['entry']['actiontaken']."',
                            '".$param['entry']['currentdeptname']."',
                            '".$param['entry']['currentdeptmanager']."',
                            '".$param['entry']['currentimmediatesupervisor']."',
                            '".$param['entry']['currentsection']."',
                            '".$param['entry']['currentempstatus']."',
                            '".$param['entry']['currentjobcode']."',
                            '".$param['entry']['currentjoblevel']."',
                            '".$param['entry']['currentpositiontitle']."',
                            '".$param['entry']['currentpaygroup']."',
                            '".$param['entry']['currentlabortype']."',
							'".$param['entry']['currentbasepay']."',
							'".$param['entry']['newbasepay']."',
							'".$param['entry']['currenttotalcashcomp']."',
							'".$param['entry']['newtotalcashcomp']."',
                            '".$param['entry']['remarks']."',
                            '".$param['accountid']."',
                            '".SysDate()."',
                            '".SysTime()."',
							'".$start_date."',
							'".$end_date."',
							'".$pay_date."'";
		
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
		if( $checkentry ){

			if (array_key_exists("allowance",$param['entry'])){
				foreach($param['entry']['allowance'] as $keyzzz => $valuezzz){
					// Insert to table
					$Qry2            = new Query();
					$Qry2->table     = "tblformsallowance";
					$Qry2->selected  = "idacct,refno,form_type,idallowance,type,current_amt,new_amt";
					$Qry2->fields    = "'".$param['entry']['idacct']."',
									'".$ticketNumber."',
									'".$param['entry']['actiontaken']."',
									'".$param['entry']['allowance'][$keyzzz]['idallowance']."',
									'".$param['entry']['allowance'][$keyzzz]['description']."',
									'".$param['entry']['allowance'][$keyzzz]['amt']."',
									'".$param['entry']['allowance'][$keyzzz]['new_amount']."'";
					$rs2             = $Qry2->exe_INSERT($con);
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
			if(!empty($recipients)){
				$mailSubject = "HRIS 2.0";
				$mailBody = "<h4>Personnel Action - Wage Increase</h4>";
				$mailBody .= "Document ID: ".$ticketNumber;

				$mailBody .="<br />Entry has been created from HR Department.<br />Waiting for your approval.<br /><br />";

				$return = _EMAILDIRECT_WAGEINCREASE($recipients, $mailSubject, $mailBody,$ticketNumber);
			}else{
				$return = json_encode(array("status"=>"success", "recipients"=>$recipients, "refno"=>$ticketNumber ));
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