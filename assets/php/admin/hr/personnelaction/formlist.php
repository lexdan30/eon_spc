<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

	$param = json_decode(file_get_contents('php://input'));
	
	if(!empty($param->accountid)){
		$Qry = new Query();	
		$Qry->table ="tblformsetup INNER JOIN tblforms ON tblformsetup.idform=tblforms.id";
		$Qry->selected ="tblforms.frm_name,
						 tblforms.icon_img,
						 tblformsetup.idform";
		$Qry->fields ="tblformsetup.id > 0 ORDER BY tblformsetup.id ASC";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>0)
		{
			while($row=mysqli_fetch_array($rs)){
				$AcctType = checkAccountType($con,$param->type);

				if($AcctType=="SYSADMIN" && $row['idform']=='1'){
					$url = "lateral-transfer---current---";                                                                       
				}elseif($AcctType=="SYSADMIN" && $row['idform']=='2'){
					$url = "promotion---current---";                                                                     
				}elseif($AcctType=="SYSADMIN" && $row['idform']=='3'){
					$url = "wage-increase---current---";                                                                           
				}else{
					$url = "";
				}
				$data[] = array(
					'frm_name'	=>$row['frm_name'],
					'icon_img'	=>$row['icon_img'],
					'url'		=>$url,
					'idform'	=>$row['idform']
				);
			}
			$return = json_encode($data);
		}else{
			$return = json_encode(array('status'=>'empty'));
		}
	}else{
		$return = json_encode(array('status'=>'error'));
	}
	print $return;
	
mysqli_close($con);

function validBoard($con, $form, $param){
	$Qry = new Query();	
	$Qry->table = "tblformsetup";
	$Qry->selected = "approver_type_a";
	$Qry->fields = "branch='".$param->accountbranch."' AND idform='".$form."' LIMIT 1";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			if( strtolower($row['approver_type_a']) == "select department" ){
				$Qry11 = new Query();	
				$Qry11->table = "tblformsetup";
				$Qry11->selected = "id";
				$Qry11->fields = "branch='".$param->accountbranch."' AND idform='".$form."' AND (approver1='".$param->accountid."' OR approver2='".$param->accountid."' OR approver3='".$param->accountid."' OR approver4='".$param->accountid."' OR approver5='".$param->accountid."' OR approver6='".$param->accountid."')";
				$rs11 = $Qry11->exe_SELECT($con);
				if(mysqli_num_rows($rs11)>= 1){
					if( checkDepartment($con,$param->accountid) == 18 && ( $form="form3" || $form="form7" ) ){
						return false;
					}
					return true;
				}
			}else{
				$Qry1 = new Query();	
				$Qry1->table = "tblforms_accountinformation";
				$Qry1->selected = "rankType";
				$Qry1->fields = "linkid='".$param->accountid."' AND rankType='Department Head' ";
				$rs1 = $Qry1->exe_SELECT($con);
				if(mysqli_num_rows($rs1)>= 1){
					if( checkDepartment($con,$param->accountid) == 18 && ( $form="form3" || $form="form7" ) ){
						return false;
					}
					return true;
				}
			}
		}
	}
	return false;
}

function checkDepartment( $con, $linkid ){
	$Qry1 = new Query();	
	$Qry1->table = "tblforms_accountinformation";
	$Qry1->selected = "department";
	$Qry1->fields = "linkid='".$linkid."' ";
	$rs1 = $Qry1->exe_SELECT($con);
	if(mysqli_num_rows($rs1)>= 1){
		if($row=mysqli_fetch_array($rs1)){
			return $row['department'];
		}
	}
	return '0';
}

function checkAccountType( $con, $linkid ){
	$Qry1 = new Query();	
	$Qry1->table = "tblaccttype";
	$Qry1->selected = "type";
	$Qry1->fields = "id='".$linkid."' ";
	$rs1 = $Qry1->exe_SELECT($con);
	if(mysqli_num_rows($rs1)>= 1){
		if($row=mysqli_fetch_array($rs1)){
			return $row['type'];
		}
	}
	return '0';
}

?>