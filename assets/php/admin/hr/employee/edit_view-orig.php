<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

$Qry 			= new Query();	
$Qry->table     = "tblaccount";
$Qry->selected  = "*";
$Qry->fields    = "id='".$param->id."'";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($row=mysqli_fetch_array($rs)){
		$path = 'assets/images/undefined.webp';
		if( !empty( $row['pic'] ) ){
			$path = 'assets/php/admin/hr/employee/pix/'.$row['pic'].'?'.time();
		}
		
		$otherbranch = array();
		
		$Qryaz = new Query();	
		$Qryaz->table ="tbldb";
		$Qryaz->selected ="*";
		$Qryaz->fields 	="id>0"; 
		$rsaz = $Qryaz->exe_SELECT($con);	
		if(mysqli_num_rows($rsaz)>= 1){
			while($rowaz=mysqli_fetch_array($rsaz)){
				$abled	= false;
				$checked= false;
				if( (int)$rowaz['isorig'] == 1 ){
					$abled	= true;
				}
				if( checkGlobalAcct($con, $rowaz['id'], $param->id) || (int)$rowaz['id'] == 1 ){
					$checked= true;
				}
				
				
				$otherbranch[] = array(
					"id"	 		=>	$rowaz['id'],
					"dbname" 		=>	$rowaz['dbname'],
					"company" 		=>	$rowaz['company'],
					"alias" 		=>	$rowaz['alias'],
					"isorig" 		=>	$rowaz['isorig'],
					"abled"			=>  $abled,
					"checked"		=>  $checked
				);
			}
		}
		
		$edubackground = [];
        $Qryeb = new Query();	
		$Qryeb->table ="tblaccountedubg";
		$Qryeb->selected ="*";
		$Qryeb->fields 	="idacct='".$param->id."'"; 
        $rseb = $Qryeb->exe_SELECT($con);	
        if(mysqli_num_rows($rseb)>= 1){
			while($roweb=mysqli_fetch_array($rseb)){
				$edubackground[] = array(
					"id"	 		=>	$roweb['id'],
					"attainment" 	=>	$roweb['attainment'],
					"school" 		=>	$roweb['school'],
					"dfrom" 		=>	$roweb['dfrom'],
					"dto" 	    	=>	$roweb['dto'],
				
				);
			}
        }
		
		$emphistory = [];
        $Qryeh = new Query();	
		$Qryeh->table ="tblaccountemphis";
		$Qryeh->selected ="*";
		$Qryeh->fields 	="idacct='".$param->id."'"; 
        $rseh = $Qryeh->exe_SELECT($con);	
        if(mysqli_num_rows($rseh)>= 1){
			while($roweh=mysqli_fetch_array($rseh)){
				$emphistory[]       = array(
					"id"	 		=>	$roweh['id'],
					"company"   	=>	$roweh['company'],
					"position" 		=>	$roweh['position'],
					"dfrom" 		=>	$roweh['dfrom'],
					"dto" 	    	=>	$roweh['dto'],
				
				);
			}
        }
		
		$examtaken = [];
        $Qryet = new Query();	
		$Qryet->table ="tblaccountet";
		$Qryet->selected ="*";
		$Qryet->fields 	="idacct='".$param->id."' AND type='exam'"; 
        $rset = $Qryet->exe_SELECT($con);	
        if(mysqli_num_rows($rset)>= 1){
			while($rowet=mysqli_fetch_array($rset)){
				$examtaken[]        = array(
					"id"	 		=>	$rowet['id'],
					"et"   	        =>	$rowet['et'],
					"location" 		=>	$rowet['location'],
					"date" 	    	=>	$rowet['date'],
				
				);
			}
        }
		
		$trainingtaken = [];
        $Qrytt = new Query();	
		$Qrytt->table ="tblaccountet";
		$Qrytt->selected ="*";
		$Qrytt->fields 	="idacct='".$param->id."' AND type='training'"; 
        $rstt = $Qrytt->exe_SELECT($con);	
        if(mysqli_num_rows($rstt)>= 1){
			while($rowtt=mysqli_fetch_array($rstt)){
				$trainingtaken[]        = array(
					"id"	 		=>	$rowtt['id'],
					"et"   	        =>	$rowtt['et'],
					"location" 		=>	$rowtt['location'],
					"date" 	    	=>	$rowtt['date'],
				
				);
			}
        }
		
        $data = array( 
            "id"        	=> $row['id'],
            "empid"			=> $row['empid'],
			"orig_empid"	=> $row['empid'],
			"idaccttype"	=> $row['idaccttype'],
			"idemptype" 	=> $row['idemptype'],
			"fname" 		=> $row['fname'],
			"lname" 		=> $row['lname'],
			"mname" 		=> $row['mname'],
			"suffix" 		=> $row['suffix'],
			"addr_st" 		=> $row['addr_st'],
			"addr_area" 	=> $row['addr_area'],
			"addr_city" 	=> $row['addr_city'],
			"addr_prov" 	=> $row['addr_prov'],
			"addr_code" 	=> $row['addr_code'],
			
			"per_st" 		=> $row['per_st'],
			"per_area" 		=> $row['per_area'],
			"per_city" 		=> $row['per_city'],
			"per_prov" 		=> $row['per_prov'],
			"per_code" 		=> $row['per_code'],
			
			
			"sex"			=> $row['sex'],
			"email"			=> $row['email'],
			"cnumber"		=> $row['cnumber'],
			"bdate"			=> $row['bdate'],
			"bplace"		=> $row['bplace'],
			"citizenship"	=> $row['citizenship'],
			"religion"		=> $row['religion'],
			"civilstat"		=> $row['civilstat'],
			"spouse"		=> $row['spouse'],
			"idtin"			=> $row['idtin'],
			"idsss"			=> $row['idsss'],
			"idhealth"		=> $row['idhealth'],
			"idibig"		=> $row['idibig'],
			"idpayroll"		=> $row['idpayroll'],
			"idtax"			=> $row['idtax'],
			"epicFile"		=> '',
			"eprof_pic"		=> $path,
			"empidlngth"	=> '4',
			"iscorporate"	=> $row['iscorp'],
			"dbs"			=> $otherbranch,
			"eb"            => $edubackground,
            "eh"            => $emphistory,
            "et"            => $examtaken,
            "tt"            => $trainingtaken
        );
    }
}

$return = json_encode($data);
print $return;
mysqli_close($con);
?>