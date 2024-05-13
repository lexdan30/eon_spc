<?php
	require_once('../../../activation.php');
	$conn = new connector();	
	$con = $conn->connect();
	require_once('../../../classPhp.php'); 


	$param = json_decode(file_get_contents('php://input'));
	$Qry=new Query();
    $Qry->table="tblforms04";
    $Qry->selected="*";
    $Qry->fields="id='".$param->id."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){

            $pending = 1;
            if(!empty($row['approver1_status'])){
                $pending = 2;
            }
            if(!empty($row['approver2_status'])){
                $pending = 3;
            }
            if(!empty($row['approver3_status'])){
                $pending = 4;
            }
            if(!empty($row['approver4_status'])){
                $pending = 5;
            }
            if(!empty($row['approver5_status'])){
                $pending = 6;
            }
			
            $isApprover = getApproverForm($con, $param->accountid, $pending, $param->form_id);

            /*Created By Picture*/
            $createdByPic = '';
            if(!empty($row['createdbyid'])){
                if(file_exists("../../../../assets/images/Signatures/sign-".$row['createdbyid'].".webp")){
                    $createdByPic = '<div class="img-container"><img src="assets/images/Signatures/sign-'.$row['createdbyid'].'.webp" style="width: 70px;height: 40px;"></div>';
                }else{
                    $createdByPic = '<div class="img-container"><img src="assets/images/Signatures/default.webp" style="width: 70px;height: 40px;"></div>';
                }
            }

            /*Approver 1 Sign*/
            $approver1sign = '';
            if(!empty($row['approver1'])){
                if(file_exists("../../../../assets/images/Signatures/sign-".$row['approver1'].".webp")){
                    $approver1sign = '<div class="img-container"><img src="assets/images/Signatures/sign-'.$row['approver1'].'.webp" style="width: 70px;height: 40px;"></div>';
                }else{
                    $approver1sign = '<div class="img-container"><img src="assets/images/Signatures/default.webp" style="width: 70px;height: 40px;"></div>';
                }
            }

            /*Approver 2 Sign*/
            $approver2sign = '';
            if(!empty($row['approver2'])){
                if(file_exists("../../../../assets/images/Signatures/sign-".$row['approver2'].".webp")){
                    $approver2sign = '<div class="img-container"><img src="assets/images/Signatures/sign-'.$row['approver2'].'.webp" style="width: 70px;height: 40px;"></div>';
                }else{
                    $approver2sign = '<div class="img-container"><img src="assets/images/Signatures/default.webp" style="width: 70px;height: 40px;"></div>';
                }
            }

            /*Approver 3 Sign*/
            $approver3sign = '';
            if(!empty($row['approver3'])){
                if(file_exists("../../../../assets/images/Signatures/sign-".$row['approver3'].".webp")){
                    $approver3sign = '<div class="img-container"><img src="assets/images/Signatures/sign-'.$row['approver3'].'.webp" style="width: 70px;height: 40px;"></div>';
                }else{
                    $approver3sign = '<div class="img-container"><img src="assets/images/Signatures/default.webp" style="width: 70px;height: 40px;"></div>';
                }
            }

            /*Approver 4 Sign*/
            $approver4sign = '';
            if(!empty($row['approver4'])){
                if(file_exists("../../../../assets/images/Signatures/sign-".$row['approver4'].".webp")){
                    $approver4sign = '<div class="img-container"><img src="assets/images/Signatures/sign-'.$row['approver4'].'.webp" style="width: 70px;height: 40px;"></div>';
                }else{
                    $approver4sign = '<div class="img-container"><img src="assets/images/Signatures/default.webp" style="width: 70px;height: 40px;"></div>';
                }
            }

            /*Approver 5 Sign*/
            $approver5sign = '';
            if(!empty($row['approver5'])){
                if(file_exists("../../../../assets/images/Signatures/sign-".$row['approver5'].".webp")){
                    $approver5sign = '<div class="img-container"><img src="assets/images/Signatures/sign-'.$row['approver5'].'.webp" style="width: 70px;height: 40px;"></div>';
                }else{
                    $approver5sign = '<div class="img-container"><img src="assets/images/Signatures/default.webp" style="width: 70px;height: 40px;"></div>';
                }
            }

            /*Approver 6 Sign*/
            $approver6sign = '';
            if(!empty($row['approver6'])){
                if(file_exists("../../../../assets/images/Signatures/sign-".$row['approver6'].".webp")){
                    $approver6sign = '<div class="img-container"><img src="assets/images/Signatures/sign-'.$row['approver6'].'.webp" style="width: 70px;height: 40px;"></div>';
                }else{
                    $approver6sign = '<div class="img-container"><img src="assets/images/Signatures/default.webp" style="width: 70px;height: 40px;"></div>';
                }
            }
            
            if($row['newpayabledate']=='0000-00-00'){
                $row['newpayabledate']='';
            }

            if($row['newterms']==0){
                $row['newterms']='';
            }

            $data[] = array(
                
                'id'                    		=>  $row['id'],
                'refferenceno'          		=>  $row['refferenceno'],
                'accountid'                     =>  getAcctID($con, $row['empid']),
                'empid'                 		=>  $row['empid'],
                'empname'               		=>  $row['empname'],
                'position'         		        =>  $row['position'],
                'department'        		    =>  $row['department'],
                'datehired'						=>  $row['datehired'],
                'datecreated'					=>  $row['datecreated'],
                'timecreated'					=>  $row['timecreated'],
                'reason'					    =>  $row['reason'],
                'explanation'					=>  $row['explanation'],
                'explanation_print'				=>  nl2br($row['explanation']),
                'loanamount'					=>  $row['loanamount'],
                'payabledate'					=>  $row['payabledate'],
                'terms'					        =>  $row['terms'],
                'newloanamount'					=>  $row['newloanamount'],
                'newpayabledate'				=>  $row['newpayabledate'],
                'newterms'				        =>  $row['newterms'],
                'loanbalance'				    =>  $row['loanbalance'],
				
                'pending'               		=>  $pending,
                'isApprover'            		=>  $isApprover,
				
				'medcert'				        =>	intval($row['medcert']),
                'medcertfile'				    =>	$row['medcertfile'],
                'temp_medcertfile'				=>	$row['medcertfile'],
                'docpresc'				        =>	intval($row['docpresc']),
                'docprescfile'				    =>	$row['docprescfile'],
                'temp_docprescfile'				=>	$row['docprescfile'],
                'ormeddoc'				        =>	intval($row['ormeddoc']),
                'ormeddocfile'				    =>	$row['ormeddocfile'],
                'temp_ormeddocfile'				=>	$row['ormeddocfile'],
                'assessform'				    =>	intval($row['assessform']),
                'assessformfile'				=>	$row['assessformfile'],
                'temp_assessformfile'		    =>	$row['assessformfile'],
                'billstate'				        =>	intval($row['billstate']),
                'billstatefile'				    =>	$row['billstatefile'],
                'temp_billstatefile'		    =>	$row['billstatefile'],
                'orsch'				            =>	intval($row['orsch']),
                'orschfile'				        =>	$row['orschfile'],
                'temp_orschfile'				=>	$row['orschfile'],
                'pbsor'				            =>	intval($row['pbsor']),
                'pbsorfile'				        =>	$row['pbsorfile'],
                'temp_pbsorfile'				=>	$row['pbsorfile'],
                'hospmedcert'				    =>	intval($row['hospmedcert']),
                'hospmedcertfile'				=>	$row['hospmedcertfile'],
                'temp_hospmedcertfile'			=>	$row['hospmedcertfile'],
                'aFile'						    =>  array(),
				
                'approver1_id'					=>  $row['approver1'],
				'approver1_name'				=>  getName($con, $row['approver1']),
				'approver1_date'				=>  $row['approver1_date'],
                'approver1_time'				=>  $row['approver1_time'],
                'approver1sign'                 =>  $approver1sign,
                'approver1desig'        		=>  getPosition($con, $row['approver1']),
                'approver1_status'				=>  $row['approver1_status'],
				
				'approver2_id'					=>  $row['approver2'],
				'approver2_name'				=>  getName($con, $row['approver2']),
				'approver2_date'				=>  $row['approver2_date'],
                'approver2_time'				=>  $row['approver2_time'],
                'approver2sign'                 =>  $approver2sign,
                'approver2desig'        		=>  getPosition($con, $row['approver2']),
                'approver2_status'				=>  $row['approver2_status'],
				
				'approver3_id'					=>  $row['approver3'],
				'approver3_name'				=>  getName($con, $row['approver3']),
				'approver3_date'				=>  $row['approver3_date'],
                'approver3_time'				=>  $row['approver3_time'],
                'approver3sign'                 =>  $approver3sign,
                'approver3desig'        		=>  getPosition($con, $row['approver3']),
                'approver3_status'				=>  $row['approver3_status'],
				
				'approver4_id'					=>  $row['approver4'],
				'approver4_name'				=>  getName($con, $row['approver4']),
				'approver4_date'				=>  $row['approver4_date'],
                'approver4_time'				=>  $row['approver4_time'],
                'approver4sign'                 =>  $approver4sign,
                'approver4desig'        		=>  getPosition($con, $row['approver4']),
                'approver4_status'				=>  $row['approver4_status'],
				
				'approver5_id'					=>  $row['approver5'],
				'approver5_name'				=>  getName($con, $row['approver5']),
				'approver5_date'				=>  $row['approver5_date'],
                'approver5_time'				=>  $row['approver5_time'],
                'approver5sign'                 =>  $approver5sign,
                'approver5desig'        		=>  getPosition($con, $row['approver5']),
                'approver5_status'				=>  $row['approver5_status'],
				
				'status'                		=>  'success'
            );
        }
        $return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'empty'));
    }
print $return;
mysqli_close($con);

function getApproverForm($con, $id, $pending, $idform){
    $Qry=new Query();
    $Qry->table="tblaccount";
    $Qry->selected="id";
    $Qry->fields="id='".$id."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            $QryA=new Query();
            $QryA->table="tblformsetup";
            $QryA->selected="id";
            $QryA->fields="idform='".$idform."' AND (approver_".$pending."a= '".$row['id']."' OR approver_".$pending."b='".$row['id']."')";
            $rsA=$QryA->exe_SELECT($con);
            if(mysqli_num_rows($rsA)>=1){
                return true;
            }
        }
        
    }
    return false;
}

function getPosition($con, $accountid){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="post";
    $Qry->fields="id='".$accountid."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
			return $row['post'];
        }
    }
    return '';
}

function getName($con, $accountid){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="empname";
    $Qry->fields="id='".$accountid."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
			return $row['empname'];
        }
    }
    return '';
}

function getAcctID($con, $empid){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="id";
    $Qry->fields="empid='".$empid."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
			return $row['id'];
        }
    }
    return '';
}

function getEmail($con, $accountid){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="email";
    $Qry->fields="id='".$accountid."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
			return $row['email'];
        }
    }
    return '';
}

?>