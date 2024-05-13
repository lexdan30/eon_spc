<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

    $param = json_decode(file_get_contents('php://input'));
    $return = null;

    if(!empty($param->accountid)){
        $Qry=new Query();
        $Qry->table="vw_dataemployees";
        $Qry->selected="*";
        $Qry->fields="empname='".$param->empname."'";
        $rs=$Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>0){
            if($row=mysqli_fetch_array($rs)){
				$departmentname = "";
				$section 		= "";
				if( (int)$row['unittype'] == 3 ){
					$departmentname = $row['business_unit'];
					$manager		= $row['superior'];
				}elseif( (int)$row['unittype'] > 3 && (int)$row['unittype'] != 6 ){ //Section is under Department
					//get idunder and use to get department name
					$idunittype = $row['unittype'];
					$idunit		= $row['idunit'] ;
					
					if( (int)$idunittype == 4 ){
						$section 		= $row['business_unit'];
					}
					
					
					do {
						$idunit 	= getidunderdepartment( $con, $idunit );
						$idunittype = getunittype($con,$idunit);
						if( (int)$idunittype == 4 ){
							$section= getdepartmentName( $con,$idunit );
						}
					} while ( (int)$idunittype != 3 );
					
					$departmentname = getdepartmentName( $con,$idunit );
					$manager		= getdepartmentMngr( $con,$idunit );
				}else{
					$manager		= '';
				}

			    $data = array(
					'idacct'						=>	$row['id'],
                    'empid'							=>	$row['empid'],
                    'currentdeptname' 				=> 	$departmentname,
                    'currentimmediatesupervisor' 	=> 	$row['superior'],
                    'currentempstatus' 				=> 	$row['emp_status'],
					'currentjobcode'				=>	$row['jobcode'],
					'currentjoblevel'				=>	$row['joblvl'],
                    'currentpositiontitle' 			=> 	$row['post'],
                    'currentpaygroup' 				=> 	$row['pay_grp'],
                    'currentlabortype' 				=> 	$row['labor_type'],
					'currentdeptmanager'			=>  $manager,
					'currentsection'				=>  $section,
                    'fullname'						=>	trim($row['empname']),
					'actiontaken'					=>	"Wage Increase",
					'currentbasepay'				=>	$row['salary'],
					'currentriceallowance'			=> 	$row['riceallowance'],
					'currentclothingallowance'		=> 	$row['clothingallowance'],
					'currentlaundryallowance'		=> 	$row['laundryallowance'],
					"newbasepay"                   	=> 	'',
					"newriceallowance"              => 	'',
					"newclothingallowance"        	=> 	'',
					"newlaundryallowance"           => 	'',
					"newtotalcashcomp"              => 	'',
					"remarks"                       => 	'',
					"effectivedate"					=> 	'',
					"doc_job_desc"					=> 	'',
					"doc_perf_appr"					=> 	'',
					"doc_promotion"					=> 	'',
					"allowance"						=>	getAllowancePersonnelAction($con, $row['id']),
					"totalallowance"				=>  '0.00', //getSumAllowance($con, $row['id']),
					"currenttotalcashcomp"			=>	floatval($row['salary']) + getSumAllowance($con, $row['id'])
					// "picFile"						=> 	array()
                );
            }
            $return = json_encode($data);
        }
    }else{
        $return = json_encode(array('status'=>'error'));
    }

print $return;
mysqli_close($con);

function getidunderdepartment( $con, $idunit ){
	$Qry=new Query();
	$Qry->table="tblbunits";
	$Qry->selected="idunder";
	$Qry->fields="id='".$idunit."'";
	$rs=$Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>0){
		if($row=mysqli_fetch_array($rs)){
			return $row['idunder'];
		}
	}
	return '';
}

function getunittype( $con, $idunit ){
	$Qry=new Query();
	$Qry->table="tblbunits";
	$Qry->selected="unittype";
	$Qry->fields="id='".$idunit."'";
	$rs=$Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>0){
		if($row=mysqli_fetch_array($rs)){
			return $row['unittype'];
		}
	}
	return 0;
}

function getdepartmentName( $con, $idunit ){
	$Qry=new Query();
	$Qry->table="vw_databusinessunits";
	$Qry->selected="name";
	$Qry->fields="id='".$idunit."'";
	$rs=$Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>0){
		if($row=mysqli_fetch_array($rs)){
			return $row['name'];
		}
	}
	return '';
}

function getdepartmentMngr( $con, $idunit ){
	$Qry=new Query();
	$Qry->table="vw_databusinessunits";
	$Qry->selected="shead";
	$Qry->fields="id='".$idunit."'";
	$rs=$Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>0){
		if($row=mysqli_fetch_array($rs)){
			return $row['shead'];
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
	return '0.00';
}

function getTotalAllowancePersonnelAction($con, $idacct){
	$Qry 			= new Query();	
	$Qry->table     = "tblacctallowance";
	$Qry->selected  = "SUM(amt) AS total";
	$Qry->fields    = "idacct='".$idacct."'";
	$rs 			= $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){
			return $row["idshift"];
		}
	}
	return '0.00';
}

?>