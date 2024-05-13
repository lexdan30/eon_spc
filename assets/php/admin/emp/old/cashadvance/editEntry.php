<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

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
			if( !empty($param['entry']['medcert']) && empty($param['entry']['medcertfile']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['docpresc']) && empty($param['entry']['docprescfile']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['ormeddoc']) && empty($param['entry']['ormeddocfile']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['assessform']) && empty($param['entry']['assessformfile']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['billstate']) && empty($param['entry']['billstate']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['orsch']) && empty($param['entry']['orschfile']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['pbsor']) && empty($param['entry']['pbsorfile']) ){
				$return = json_encode(array('status'=>'error-iddoc'));
				print $return;	
				mysqli_close($con);
				return;
			}
			if( !empty($param['entry']['hospmedcert']) && empty($param['entry']['hospmedcertfile']) ){
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
		
		$ticketNumber= $param['entry']['refferenceno'];

        $Qry 			= new Query();	
        $Qry->table 	= "tblforms04";
        $Qry->selected 	= " reason 					= 	'".$param['entry']['reason']."',
							explanation				=	'".$param['entry']['explanation']."',
							loanamount				=	'".$param['entry']['loanamount']."',
							payabledate				=	'".$param['entry']['payabledate']."',
							terms					=	'".$param['entry']['terms']."',
							newloanamount			=	'".$param['entry']['newloanamount']."',
							newpayabledate			=	'".$param['entry']['newpayabledate']."',
                            newterms				=	'".$param['entry']['newterms']."',
							loanbalance				=	'".$param['entry']['loanbalance']."'";
		
		if( array_key_exists('file',$_FILES) ){	

			foreach( $ndx as $ndxval ){
				if($ndxval==0){
					$folder_path 	= $param['targetPath'];
					$name 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();	
					$extMove 		= pathinfo($name, PATHINFO_EXTENSION);
					$save_name		= $param['entry']['refferenceno'].'MC.'.$extMove;	
					$Qry->selected 	= $Qry->selected.",medcertfile='".$save_name."', medcert='".$param['entry']['medcert']."'";
				}

				if($ndxval==1){
					$folder_path 	= $param['targetPath'];
					$name1 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();	
					$extMove1 		= pathinfo($name1, PATHINFO_EXTENSION);
					$save_name1		= $param['entry']['refferenceno'].'DP.'.$extMove1;	
					$Qry->selected 	= $Qry->selected.",docprescfile='".$save_name1."', docpresc='".$param['entry']['docpresc']."'";
				}

				if($ndxval==2){
					$folder_path 	= $param['targetPath'];
					$name2 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();	
					$extMove2 		= pathinfo($name2, PATHINFO_EXTENSION);
					$save_name2		= $param['entry']['refferenceno'].'ORMD.'.$extMove2;	
					$Qry->selected 	= $Qry->selected.",ormeddocfile='".$save_name2."', ormeddoc='".$param['entry']['ormeddoc']."'";
				}

				if($ndxval==3){
					$folder_path 	= $param['targetPath'];
					$name3 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();	
					$extMove3 		= pathinfo($name3, PATHINFO_EXTENSION);
					$save_name3		= $param['entry']['refferenceno'].'AF.'.$extMove3;	
					$Qry->selected 	= $Qry->selected.",assessformfile='".$save_name3."', assessform='".$param['entry']['assessform']."'";
				}

				if($ndxval==4){
					$folder_path 	= $param['targetPath'];
					$name4 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();	
					$extMove4 		= pathinfo($name4, PATHINFO_EXTENSION);
					$save_name4		= $param['entry']['refferenceno'].'BS.'.$extMove4;	
					$Qry->selected 	= $Qry->selected.",billstatefile='".$save_name4."', billstate='".$param['entry']['billstate']."'";
				}

				if($ndxval==5){
					$folder_path 	= $param['targetPath'];
					$name5 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();	
					$extMove5 		= pathinfo($name5, PATHINFO_EXTENSION);
					$save_name5		= $param['entry']['refferenceno'].'ORSF.'.$extMove5;	
					$Qry->selected 	= $Qry->selected.",orschfile='".$save_name5."', orsch='".$param['entry']['orsch']."'";
				}

				if($ndxval==6){
					$folder_path 	= $param['targetPath'];
					$name6 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();	
					$extMove6 		= pathinfo($name6, PATHINFO_EXTENSION);
					$save_name6		= $param['entry']['refferenceno'].'PBSOR.'.$extMove6;	
					$Qry->selected 	= $Qry->selected.",pbsorfile='".$save_name6."', pbsor='".$param['entry']['pbsor']."'";
				}

				if($ndxval==7){
					$folder_path 	= $param['targetPath'];
					$name7 			= $_FILES['file']['name'][$ndxval];
					$t				= strtotime($date).time();	
					$extMove7 		= pathinfo($name7, PATHINFO_EXTENSION);
					$save_name7		= $param['entry']['refferenceno'].'HMC.'.$extMove7;	
					$Qry->selected 	= $Qry->selected.",hospmedcertfile='".$save_name7."', hospmedcert='".$param['entry']['hospmedcert']."'";
				}
			}

			if(($param['entry']['medcert']==''||$param['entry']['medcert']==NULL||$param['entry']['medcert']==0)&&($param['entry']['temp_medcertfile']!=''||$param['entry']['temp_medcertfile']!=null)){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['temp_medcertfile'];
				if( file_exists($folder.$filename) ){
					unlink( $folder.$filename );
				}
				$Qry->selected 	= $Qry->selected.",medcertfile='', medcert=''";
			}
			if(($param['entry']['docpresc']==''||$param['entry']['docpresc']==NULL||$param['entry']['docpresc']==0)&&($param['entry']['temp_docprescfile']!=''||$param['entry']['temp_docprescfile']!=null)){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['temp_docprescfile'];
				if( file_exists($folder.$filename) ){
					unlink( $folder.$filename );
				}
				$Qry->selected 	= $Qry->selected.",docprescfile='', docpresc=''";
			}
			if(($param['entry']['ormeddoc']==''||$param['entry']['ormeddoc']==NULL||$param['entry']['ormeddoc']==0)&&($param['entry']['temp_ormeddocfile']!=''||$param['entry']['temp_ormeddocfile']!=null)){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['temp_ormeddocfile'];
				if( file_exists($folder.$filename) ){
					unlink( $folder.$filename );
				}
				$Qry->selected 	= $Qry->selected.",ormeddocfile='', ormeddoc=''";
			}
			if(($param['entry']['assessform']==''||$param['entry']['assessform']==NULL||$param['entry']['assessform']==0)&&($param['entry']['temp_assessformfile']!=''||$param['entry']['temp_assessformfile']!=null)){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['temp_assessformfile'];
				if( file_exists($folder.$filename) ){
					unlink( $folder.$filename );
				}
				$Qry->selected 	= $Qry->selected.",assessformfile='', assessform=''";
			}
			if(($param['entry']['billstate']==''||$param['entry']['billstate']==NULL||$param['entry']['billstate']==0)&&($param['entry']['temp_billstatefile']!=''||$param['entry']['temp_billstatefile']!=null)){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['temp_billstatefile'];
				if( file_exists($folder.$filename) ){
					unlink( $folder.$filename );
				}
				$Qry->selected 	= $Qry->selected.",billstatefile='', billstate=''";
			}
			if(($param['entry']['orsch']==''||$param['entry']['orsch']==NULL||$param['entry']['orsch']==0)&&($param['entry']['temp_orschfile']!=''||$param['entry']['temp_orschfile']!=null)){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['temp_orschfile'];
				if( file_exists($folder.$filename) ){
					unlink( $folder.$filename );
				}
				$Qry->selected 	= $Qry->selected.",orschfile='', orsch=''";
			}
			if(($param['entry']['pbsor']==''||$param['entry']['pbsor']==NULL||$param['entry']['pbsor']==0)&&($param['entry']['temp_pbsorfile']!=''||$param['entry']['temp_pbsorfile']!=null)){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['temp_pbsorfile'];
				if( file_exists($folder.$filename) ){
					unlink( $folder.$filename );
				}
				$Qry->selected 	= $Qry->selected.",pbsorfile='', pbsor=''";
			}
			if(($param['entry']['hospmedcert']==''||$param['entry']['hospmedcert']==NULL||$param['entry']['hospmedcert']==0)&&($param['entry']['temp_hospmedcertfile']!=''||$param['entry']['temp_hospmedcertfile']!=null)){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['temp_hospmedcertfile'];
				if( file_exists($folder.$filename) ){
					unlink( $folder.$filename );
				}
				$Qry->selected 	= $Qry->selected.",hospmedcertfile='', hospmedcert=''";
			}

		}else{
			if(($param['entry']['medcert']==''||$param['entry']['medcert']==NULL||$param['entry']['medcert']==0)&&($param['entry']['temp_medcertfile']!=''||$param['entry']['temp_medcertfile']!=null)){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['temp_medcertfile'];
				if( file_exists($folder.$filename) ){
					unlink( $folder.$filename );
				}
				$Qry->selected 	= $Qry->selected.",medcertfile='', medcert=''";
			}
			if(($param['entry']['docpresc']==''||$param['entry']['docpresc']==NULL||$param['entry']['docpresc']==0)&&($param['entry']['temp_docprescfile']!=''||$param['entry']['temp_docprescfile']!=null)){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['temp_docprescfile'];
				if( file_exists($folder.$filename) ){
					unlink( $folder.$filename );
				}
				$Qry->selected 	= $Qry->selected.",docprescfile='', docpresc=''";
			}
			if(($param['entry']['ormeddoc']==''||$param['entry']['ormeddoc']==NULL||$param['entry']['ormeddoc']==0)&&($param['entry']['temp_ormeddocfile']!=''||$param['entry']['temp_ormeddocfile']!=null)){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['temp_ormeddocfile'];
				if( file_exists($folder.$filename) ){
					unlink( $folder.$filename );
				}
				$Qry->selected 	= $Qry->selected.",ormeddocfile='', ormeddoc=''";
			}
			if(($param['entry']['assessform']==''||$param['entry']['assessform']==NULL||$param['entry']['assessform']==0)&&($param['entry']['temp_assessformfile']!=''||$param['entry']['temp_assessformfile']!=null)){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['temp_assessformfile'];
				if( file_exists($folder.$filename) ){
					unlink( $folder.$filename );
				}
				$Qry->selected 	= $Qry->selected.",assessformfile='', assessform=''";
			}
			if(($param['entry']['billstate']==''||$param['entry']['billstate']==NULL||$param['entry']['billstate']==0)&&($param['entry']['temp_billstatefile']!=''||$param['entry']['temp_billstatefile']!=null)){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['temp_billstatefile'];
				if( file_exists($folder.$filename) ){
					unlink( $folder.$filename );
				}
				$Qry->selected 	= $Qry->selected.",billstatefile='', billstate=''";
			}
			if(($param['entry']['orsch']==''||$param['entry']['orsch']==NULL||$param['entry']['orsch']==0)&&($param['entry']['temp_orschfile']!=''||$param['entry']['temp_orschfile']!=null)){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['temp_orschfile'];
				if( file_exists($folder.$filename) ){
					unlink( $folder.$filename );
				}
				$Qry->selected 	= $Qry->selected.",orschfile='', orsch=''";
			}
			if(($param['entry']['pbsor']==''||$param['entry']['pbsor']==NULL||$param['entry']['pbsor']==0)&&($param['entry']['temp_pbsorfile']!=''||$param['entry']['temp_pbsorfile']!=null)){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['temp_pbsorfile'];
				if( file_exists($folder.$filename) ){
					unlink( $folder.$filename );
				}
				$Qry->selected 	= $Qry->selected.",pbsorfile='', pbsor=''";
			}
			if(($param['entry']['hospmedcert']==''||$param['entry']['hospmedcert']==NULL||$param['entry']['hospmedcert']==0)&&($param['entry']['temp_hospmedcertfile']!=''||$param['entry']['temp_hospmedcertfile']!=null)){
				$folder_path 	= $param['targetPath'];
				$folder = $folder_path.'/';
				$filename = $param['entry']['temp_hospmedcertfile'];
				if( file_exists($folder.$filename) ){
					unlink( $folder.$filename );
				}
				$Qry->selected 	= $Qry->selected.",hospmedcertfile='', hospmedcert=''";
			}
		}
		$Qry->fields 	= "id = '". $param['entry']['id'] ."'";
		$checkentry 	= $Qry->exe_UPDATE($con); 
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