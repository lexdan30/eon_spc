<?php
	require_once('../../../../activation.php');
	$conn = new connector();	
	$con = $conn->connect();
	require_once('../../../../classPhp.php'); 


	$param = json_decode(file_get_contents('php://input'));
	$Qry=new Query();
    $Qry->table="vw_data_promotion";
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
            if(!empty($row['approver6_status'])){
                $pending = 7;
			}
			
            $isApprover = getApproverForm($con, $param->accountid, $pending, $row['idform']);

            /*Created By Picture*/
            $createdByPic = '';
            if(!empty($row['createdbyid'])){
                if(file_exists("../../../../../../assets/images/Signatures/sign-".$row['createdbyid'].".webp")){
                    $createdByPic = '<div class="img-container"><img src="assets/images/Signatures/sign-'.$row['createdbyid'].'.webp" style="width: 70px;height: 40px;"></div>';
                }else{
                    $createdByPic = '<div class="img-container"><img src="assets/images/Signatures/default.webp" style="width: 70px;height: 40px;"></div>';
                }
            }

            /*Approver 1 Sign*/
            $approver1sign = '';
            if(!empty($row['approver1'])){
                if(file_exists("../../../../../../assets/images/Signatures/sign-".$row['approver1'].".webp")){
                    $approver1sign = '<div class="img-container"><img src="assets/images/Signatures/sign-'.$row['approver1'].'.webp" style="width: 70px;height: 40px;"></div>';
                }else{
                    $approver1sign = '<div class="img-container"><img src="assets/images/Signatures/default.webp" style="width: 70px;height: 40px;"></div>';
                }
            }

            /*Approver 2 Sign*/
            $approver2sign = '';
            if(!empty($row['approver2'])){
                if(file_exists("../../../../../../assets/images/Signatures/sign-".$row['approver2'].".webp")){
                    $approver2sign = '<div class="img-container"><img src="assets/images/Signatures/sign-'.$row['approver2'].'.webp" style="width: 70px;height: 40px;"></div>';
                }else{
                    $approver2sign = '<div class="img-container"><img src="assets/images/Signatures/default.webp" style="width: 70px;height: 40px;"></div>';
                }
            }

            /*Approver 3 Sign*/
            $approver3sign = '';
            if(!empty($row['approver3'])){
                if(file_exists("../../../../../../assets/images/Signatures/sign-".$row['approver3'].".webp")){
                    $approver3sign = '<div class="img-container"><img src="assets/images/Signatures/sign-'.$row['approver3'].'.webp" style="width: 70px;height: 40px;"></div>';
                }else{
                    $approver3sign = '<div class="img-container"><img src="assets/images/Signatures/default.webp" style="width: 70px;height: 40px;"></div>';
                }
            }

            /*Approver 4 Sign*/
            $approver4sign = '';
            if(!empty($row['approver4'])){
                if(file_exists("../../../../../../assets/images/Signatures/sign-".$row['approver4'].".webp")){
                    $approver4sign = '<div class="img-container"><img src="assets/images/Signatures/sign-'.$row['approver4'].'.webp" style="width: 70px;height: 40px;"></div>';
                }else{
                    $approver4sign = '<div class="img-container"><img src="assets/images/Signatures/default.webp" style="width: 70px;height: 40px;"></div>';
                }
            }

            /*Approver 5 Sign*/
            $approver5sign = '';
            if(!empty($row['approver5'])){
                if(file_exists("../../../../../assets/images/Signatures/sign-".$row['approver5'].".webp")){
                    $approver5sign = '<div class="img-container"><img src="assets/images/Signatures/sign-'.$row['approver5'].'.webp" style="width: 70px;height: 40px;"></div>';
                }else{
                    $approver5sign = '<div class="img-container"><img src="assets/images/Signatures/default.webp" style="width: 70px;height: 40px;"></div>';
                }
            }

            /*Approver 6 Sign*/
            $approver6sign = '';
            if(!empty($row['approver6'])){
                if(file_exists("../../../../../../assets/images/Signatures/sign-".$row['approver6'].".webp")){
                    $approver6sign = '<div class="img-container"><img src="assets/images/Signatures/sign-'.$row['approver6'].'.webp" style="width: 70px;height: 40px;"></div>';
                }else{
                    $approver6sign = '<div class="img-container"><img src="assets/images/Signatures/default.webp" style="width: 70px;height: 40px;"></div>';
                }
			}
			
            /*Approver 7 Sign*/
            $approver7sign = '';
            if(!empty($row['approver7'])){
                if(file_exists("../../../../../../assets/images/Signatures/sign-".$row['approver7'].".webp")){
                    $approver7sign = '<div class="img-container"><img src="assets/images/Signatures/sign-'.$row['approver7'].'.webp" style="width: 70px;height: 40px;"></div>';
                }else{
                    $approver7sign = '<div class="img-container"><img src="assets/images/Signatures/default.webp" style="width: 70px;height: 40px;"></div>';
                }
            }

            $data[] = array(
                
                'id'                    		=>  $row['id'],
                'refferenceno'          		=>  $row['refferenceno'],
                'empid'                 		=>  $row['empid'],
                'empname'               		=>  $row['empname'],
                'effectivedate'         		=>  $row['effectivedate'],
				'idacct'						=>  $row['requestor'],
                'actiontaken'        		    =>  $row['empactiontaken'],
                
				'currentdeptname'       		=>  $row['currentdeptname'],
                'currentdeptmanager'    		=>  $row['currentdeptmanager'],
				'currentimmediatesupervisor' 	=>  $row['currentimmediatesupervisor'],
				'currentsection' 				=>  $row['currentsection'],
				'currentempstatus' 				=>  $row['currentempstatus'],
				'currentjobcode' 				=>  $row['currentjobcode'],
				'currentjoblevel' 				=>  $row['currentjoblevel'],
				'currentpositiontitle' 			=>  $row['currentpositiontitle'],
				'currentpaygroup' 				=>  $row['currentpaygroup'],
				'currentlabortype' 				=>  $row['currentlabortype'],
				
				'newdeptname'           		=>  $row['newiddept'],
                'newdeptmanager'    			=>  $row['newidmngr'],
				'newimmediatesupervisor' 		=>  $row['newidsuperior'],
				'newsection' 					=>  $row['newidsection'],
				'newempstatus' 					=>  $row['newempstatus'],
				'newjobcode' 					=>  $row['newjobcode'],
				'newjoblevel' 					=>  $row['newjoblevel'],
				'newpositiontitle' 				=>  $row['newpositiontitle'],
				'newpaygroup' 					=>  $row['newpaygroup'],
                'newlabortype' 					=>  $row['newlabortype'],
                
                'currentbasepay' 				=>  $row['currentbasepay'],
                'newbasepay' 				    =>  $row['newbasepay'],
                'currentriceallowance' 			=>  $row['currentriceallowance'],
                'newriceallowance' 				=>  $row['newriceallowance'],
                'currentclothingallowance' 		=>  $row['currentclothingallowance'],
                'newclothingallowance' 			=>  $row['newclothingallowance'],
                'currentlaundryallowance' 		=>  $row['currentlaundryallowance'],
                'newlaundryallowance' 			=>  $row['newlaundryallowance'],
                'newtotalcashcomp' 			    =>  $row['newtotalcashcomp'],
				
				'remarks' 						=>  $row['remarks'],
                'pending'               		=>  $pending,
                'isApprover'            		=>  $isApprover,
                'createdby'             		=>  ucwords($row['createdby']),
                'createdbydesig'        		=>  getPosition($con, $row['createdbyid']),
                'createdbyid'        		    =>  $row['createdbyid'],
                'createdbyemail'        		=>  getEmail($con, $row['createdbyid']),
                'datetimecreated'       		=>  ($row['date_created']. ' ' .$row['time_created'] ),
                'datecreated'           		=>  $row['date_created'],
                'timecreated'           		=>  $row['time_created'],
                'createdByPic'          		=>  $createdByPic,
				
				'jobdescdoc'				    =>	$row['jobdescdoc'],
                'jobdescfile'				    =>	$row['jobdescfile'],
                'perfapprdoc'				    =>	$row['perfapprdoc'],
                'perfapprfile'				    =>	$row['perfapprfile'],
                'promdoc'				        =>	$row['promdoc'],
                'promfile'				        =>	$row['promfile'],
                'picFile'						=>  array(),

                "allowance"						=>	getAllowance($con, $row['requestor'], $row['refferenceno']),
                "currenttotalcashcomp"			=>	floatval($row['currentbasepay']) + getSumAllowance($con, $row['requestor']),
				
                'approver1_id'					=>  $row['approver1'],
				'approver1_name'				=>  $row['approver1_name'],
				'approver1_date'				=>  $row['approver1_date'],
                'approver1_time'				=>  $row['approver1_time'],
                'approver1sign'                 =>  $approver1sign,
                'approver1desig'        		=>  getPosition($con, $row['approver1']),
				
				'approver2_id'					=>  $row['approver2'],
				'approver2_name'				=>  $row['approver2_name'],
				'approver2_date'				=>  $row['approver2_date'],
                'approver2_time'				=>  $row['approver2_time'],
                'approver2sign'                 =>  $approver2sign,
                'approver2desig'        		=>  getPosition($con, $row['approver2']),
				
				'approver3_id'					=>  $row['approver3'],
				'approver3_name'				=>  $row['approver3_name'],
				'approver3_date'				=>  $row['approver3_date'],
                'approver3_time'				=>  $row['approver3_time'],
                'approver3sign'                 =>  $approver3sign,
                'approver3desig'        		=>  getPosition($con, $row['approver3']),
				
				'approver4_id'					=>  $row['approver4'],
				'approver4_name'				=>  $row['approver4_name'],
				'approver4_date'				=>  $row['approver4_date'],
                'approver4_time'				=>  $row['approver4_time'],
                'approver4sign'                 =>  $approver4sign,
                'approver4desig'        		=>  getPosition($con, $row['approver4']),
				
				'approver5_id'					=>  $row['approver5'],
				'approver5_name'				=>  $row['approver5_name'],
				'approver5_date'				=>  $row['approver5_date'],
                'approver5_time'				=>  $row['approver5_time'],
                'approver5sign'                 =>  $approver5sign,
                'approver5desig'        		=>  getPosition($con, $row['approver5']),
				
				'approver6_id'					=>  $row['approver6'],
				'approver6_name'				=>  $row['approver6_name'],
				'approver6_date'				=>  $row['approver6_date'],
                'approver6_time'				=>  $row['approver6_time'],
                'approver6sign'                 =>  $approver6sign,
                'approver6desig'        		=>  getPosition($con, $row['approver6']),
				
                'approver7_id'					=>  $row['approver7'],
				'approver7_name'				=>  $row['approver7_name'],
				'approver7_date'				=>  $row['approver7_date'],
                'approver7_time'				=>  $row['approver7_time'],
                'approver7sign'                 =>  $approver7sign,
                'approver7desig'        		=>  getPosition($con, $row['approver7']),
				
				
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

function getSumAllowance( $con, $idacct ){
	$Qry=new Query();
	$Qry->table="tblacctallowance LEFT JOIN tblallowance ON tblacctallowance.idallowance = tblallowance.id";
	$Qry->selected="SUM(tblacctallowance.amt) AS tot";
	$Qry->fields="idacct='".$idacct."'";
	$rs=$Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>0){
		if($row=mysqli_fetch_array($rs)){
			return floatval($row['tot']);
		}
	}
	return '';
}

?>