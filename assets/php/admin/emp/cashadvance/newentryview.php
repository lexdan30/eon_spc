<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

    $param = json_decode(file_get_contents('php://input'));
    $return = null;

    if(!empty($param->accountid)){
        $Qry=new Query();
        $Qry->table="vw_dataemployees";
        $Qry->selected="*";
        $Qry->fields="id='".$param->accountid."'";
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
				}

			    $data = array(
                    "empid"                         => $row['empid'],
                    "empname"                       => $row['empname'],
                    "position"                           => $row['post'],
                    "department"                    => $departmentname,
					"datecreated"                   => '',
					"timecreated"                   => '',
                    "datehired"                     => $row['hdate'],
					"reason"                        => '',
					"explanation"                   => '',
                    "medcert"                       => 0,
                    "docpresc"                      => 0,
                    "ormeddoc"                      => 0,
                    "assessform"                    => 0,
                    "billstate"                     => 0,
                    "orsch"                         => 0,
                    "pbsor"                         => 0,
                    "hospmedcert"                   => 0,
                    "loanamount"                    => '',
                    "payabledate"                   => '',
					"terms"                   		=> '',
					"newloanamount"                 => '',
                    "newpayabledate"                => '',
					"newterms"                   	=> 0,
					"loanbalance"                  	=> '',
                    "aFile"						    => array()
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

?>