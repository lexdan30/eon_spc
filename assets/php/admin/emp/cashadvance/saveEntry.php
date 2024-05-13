<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php');
require_once('../../../email/emailFunction.php');

    $param 	= $_POST;
    $date 	= SysDate();
    $time 	= SysTime();
	$return = null;

	$param['entry']['explanation'] = (str_replace("'","\"",$param['entry']['explanation']));
	
	if( array_key_exists('file',$_FILES) ){
		$ndx=array();
		foreach( $_FILES['file']['name'] as $kk=>$vv ){
			array_push( $ndx, $kk );
		}
	}

    if(!empty($param['accountid'])){
		
		if( array_key_exists('file',$_FILES) ){
			$valid_formats = array("pdf","jpg", "png", "jpeg");
			
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
			if( !empty($param['entry']['medcert']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['docpresc']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['ormeddoc']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['assessform']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['billstate']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['orsch']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['pbsor']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['hospmedcert']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
		}

		foreach ($param['entry'] as $keyzz => $valuezz) {
			if( !is_array($param['entry'][$keyzz]) && $keyzz != 'aFile' ){
				if( strtolower($param['entry'][$keyzz]) == 'null' ){
					$param['entry'][$keyzz]="";
				}
			}
		}

        $linkid 		=	getReqCtr($con);
        $linkid1 		=	$linkid + 1;
        $ticketNumber 	=	"CA".str_pad($linkid1,6,"0",STR_PAD_LEFT);

        $Qry 			= new Query();	
        $Qry->table 	= "tblforms04";
        $Qry->selected 	= "empid,refferenceno, empname, position, department, datehired, datecreated, timecreated, reason, explanation, loanamount , payabledate, terms, newloanamount, newterms, loanbalance";
        $Qry->fields 	= " '".$param['entry']['empid']."',
							'".$ticketNumber."',
                            '".$param['entry']['empname']."',
                            '".$param['entry']['position']."',
                            '".$param['entry']['department']."',
                            '".$param['entry']['datehired']."',
                            '".SysDate()."',
							'".SysTime()."',
                            '".$param['entry']['reason']."',
                            '".$param['entry']['explanation']."',
							'".$param['entry']['loanamount']."',
                            '".$param['entry']['payabledate']."',
                            '".$param['entry']['terms']."',
							'".$param['entry']['newloanamount']."',
                            '".$param['entry']['newterms']."',
							'".$param['entry']['loanbalance']."'";
		if( array_key_exists('file',$_FILES) ){	

			foreach( $ndx as $ndxval ){
				if($ndxval==0){
					$folder_path 	= $param['targetPath'];
					$name 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();
					$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
					$save_name		= $ticketNumber.'MC.'.$extMove;	
					$Qry->selected 	= $Qry->selected . ", medcert, medcertfile";
					$Qry->fields 	= $Qry->fields 	 . ", '".$param['entry']['medcert']."', '".$save_name."'";
				}
				if($ndxval==1){
					$folder_path 	= $param['targetPath'];
					$name1 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();
					$extMove1 		= pathinfo($name1, PATHINFO_EXTENSION);
					$save_name1		= $ticketNumber.'DP.'.$extMove1;	
					$Qry->selected 	= $Qry->selected . ", docpresc, docprescfile";
					$Qry->fields 	= $Qry->fields 	 . ", '".$param['entry']['docpresc']."', '".$save_name1."'";
				}
				if($ndxval==2){
					$folder_path 	= $param['targetPath'];
					$name2 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();
					$extMove2 		= pathinfo($name2, PATHINFO_EXTENSION);
					$save_name2		= $ticketNumber.'ORMD.'.$extMove2;	
					$Qry->selected 	= $Qry->selected . ", ormeddoc, ormeddocfile";
					$Qry->fields 	= $Qry->fields 	 . ", '".$param['entry']['ormeddoc']."', '".$save_name2."'";
				}
				if($ndxval==3){
					$folder_path 	= $param['targetPath'];
					$name3 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();
					$extMove3 		= pathinfo($name3, PATHINFO_EXTENSION);
					$save_name3		= $ticketNumber.'AF.'.$extMove3;	
					$Qry->selected 	= $Qry->selected . ", assessform, assessformfile";
					$Qry->fields 	= $Qry->fields 	 . ", '".$param['entry']['assessform']."', '".$save_name3."'";
				}
				if($ndxval==4){
					$folder_path 	= $param['targetPath'];
					$name4 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();
					$extMove4 		= pathinfo($name4, PATHINFO_EXTENSION);
					$save_name4		= $ticketNumber.'BS.'.$extMove4;	
					$Qry->selected 	= $Qry->selected . ", billstate, billstatefile";
					$Qry->fields 	= $Qry->fields 	 . ", '".$param['entry']['billstate']."', '".$save_name4."'";
				}
				if($ndxval==5){
					$folder_path 	= $param['targetPath'];
					$name5 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();
					$extMove5 		= pathinfo($name5, PATHINFO_EXTENSION);
					$save_name5		= $ticketNumber.'ORSF.'.$extMove5;	
					$Qry->selected 	= $Qry->selected . ", orsch, orschfile";
					$Qry->fields 	= $Qry->fields 	 . ", '".$param['entry']['orsch']."', '".$save_name5."'";
				}
				if($ndxval==6){
					$folder_path 	= $param['targetPath'];
					$name6 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();
					$extMove6 		= pathinfo($name6, PATHINFO_EXTENSION);
					$save_name6		= $ticketNumber.'PBSOR.'.$extMove6;	
					$Qry->selected 	= $Qry->selected . ", pbsor, pbsorfile";
					$Qry->fields 	= $Qry->fields 	 . ", '".$param['entry']['pbsor']."', '".$save_name6."'";
				}
				if($ndxval==7){
					$folder_path 	= $param['targetPath'];
					$name7 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();
					$extMove7 		= pathinfo($name7, PATHINFO_EXTENSION);
					$save_name7		= $ticketNumber.'HMC.'.$extMove7;	
					$Qry->selected 	= $Qry->selected . ", hospmedcert, hospmedcertfile";
					$Qry->fields 	= $Qry->fields 	 . ", '".$param['entry']['hospmedcert']."', '".$save_name7."'";
				}
			}

		}
        $checkentry 	= $Qry->exe_INSERT($con); 
		if( $checkentry ){
			
			//Upload Attachment
			if( array_key_exists('file',$_FILES) ){	
				foreach( $ndx as $ndxval ){
					if($ndxval==0){
						$folder_path 	= $param['targetPath'];
						$name 			= $_FILES['file']['name'][$ndxval];
						$t				= strtotime($date).time();
						$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
						$save_name		= $ticketNumber.'MC.'.$extMove;
						move_uploaded_file($_FILES["file"]["tmp_name"][$ndxval], $folder_path.$save_name);
					}
					if($ndxval==1){
						$folder_path 	= $param['targetPath'];
						$name 			= $_FILES['file']['name'][$ndxval];
						$t				= strtotime($date).time();
						$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
						$save_name		= $ticketNumber.'DP.'.$extMove;
						move_uploaded_file($_FILES["file"]["tmp_name"][$ndxval], $folder_path.$save_name);
					}
					if($ndxval==2){
						$folder_path 	= $param['targetPath'];
						$name 			= $_FILES['file']['name'][$ndxval];
						$t				= strtotime($date).time();
						$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
						$save_name		= $ticketNumber.'ORMD.'.$extMove;
						move_uploaded_file($_FILES["file"]["tmp_name"][$ndxval], $folder_path.$save_name);
					}
					if($ndxval==3){
						$folder_path 	= $param['targetPath'];
						$name 			= $_FILES['file']['name'][$ndxval];
						$t				= strtotime($date).time();
						$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
						$save_name		= $ticketNumber.'AF.'.$extMove;
						move_uploaded_file($_FILES["file"]["tmp_name"][$ndxval], $folder_path.$save_name);
					}
					if($ndxval==4){
						$folder_path 	= $param['targetPath'];
						$name 			= $_FILES['file']['name'][$ndxval];
						$t				= strtotime($date).time();
						$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
						$save_name		= $ticketNumber.'BS.'.$extMove;
						move_uploaded_file($_FILES["file"]["tmp_name"][$ndxval], $folder_path.$save_name);
					}
					if($ndxval==5){
						$folder_path 	= $param['targetPath'];
						$name 			= $_FILES['file']['name'][$ndxval];
						$t				= strtotime($date).time();
						$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
						$save_name		= $ticketNumber.'ORSF.'.$extMove;
						move_uploaded_file($_FILES["file"]["tmp_name"][$ndxval], $folder_path.$save_name);
					}
					if($ndxval==6){
						$folder_path 	= $param['targetPath'];
						$name 			= $_FILES['file']['name'][$ndxval];
						$t				= strtotime($date).time();
						$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
						$save_name		= $ticketNumber.'PBSOR.'.$extMove;
						move_uploaded_file($_FILES["file"]["tmp_name"][$ndxval], $folder_path.$save_name);
					}
					if($ndxval==7){
						$folder_path 	= $param['targetPath'];
						$name 			= $_FILES['file']['name'][$ndxval];
						$t				= strtotime($date).time();
						$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
						$save_name		= $ticketNumber.'HMC.'.$extMove;
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
				$mailBody = "<h4>Loans - Cash Advance</h4>";
				$mailBody .= "Document ID: ".$ticketNumber;

				$mailBody .="<br />Entry has been created.<br />Waiting for your approval.<br /><br />";

				$return = _EMAILDIRECT_CASHADVANCE($recipients, $mailSubject, $mailBody,$ticketNumber);
			}else{
				$return = json_encode(array("status"=>"success", "recipients"=>$recipients, "refno"=>"$ticketNumber" ));
			}
		}else{
			$return = json_encode(array('status'=>'error',"err"=>mysqli_error($con)));
		}
    }else{
        $return = json_encode(array('status'=>'empty'));
    }

print $return;
mysqli_close($con);



function getReqCtr($con){
    $Qry=new Query();
    $Qry->table="tblforms04";
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