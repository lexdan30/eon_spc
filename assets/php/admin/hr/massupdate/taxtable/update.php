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
	
    if(!empty($param['accountid'])){
		
        $Qry 			= new Query();	
        $Qry->table 	= "tbltt";
        $Qry->selected 	= "idupdatedby, dateupdated, timeupdated, idpaygrp, paygrp, idnewpayrev, newpayrev";
        $Qry->fields 	= " '".$param['accountid']."',
							'".SysDate()."',
							'".SysTime()."',
                            '".$param['entry']['paygrp']."',
							'".getStrPayGroup($con, $param['entry']['paygrp'])."',
                            '".$param['entry']['ir']."',
                            '".getStrPayRev($con, $param['entry']['ir'])."'";
        $checkentry 	= $Qry->exe_INSERT($con); 
		if( $checkentry ){
			$lastid = mysqli_insert_id($con);
			foreach($param['employees'] as $key=>$value){
				$Qrya 			= new Query();	
				$Qrya->table 	= "tblttlogs";
				$Qrya->selected = "idtbltt, empid, lname, fname, mi, idunit, business_unit, idpaygrp, paygrp, idoldpayrev, oldpayrev, idnewpayrev, newpayrev";
				$Qrya->fields 	= " '".$lastid."',
									'".$param['employees'][$key]['id']."',
									'".$param['employees'][$key]['lname']."',
									'".$param['employees'][$key]['fname']."',
									'".$param['employees'][$key]['mname']."',
									'".$param['employees'][$key]['id_unit']."',
									'".$param['employees'][$key]['business_unit']."',
									'".$param['employees'][$key]['idpaygrp']."',
									'".$param['employees'][$key]['paygroup']."',
									'".$param['employees'][$key]['idrevenue']."',
									'".$param['employees'][$key]['internalrev']."',
									'".$param['entry']['ir']."',
									'".getStrPayRev($con, $param['entry']['ir'])."'";
				
				$checkentrya 	= $Qrya->exe_INSERT($con); 
				if($checkentrya){
					//Update to 201 tblaccountjob column idrevenue
					$Qryb 			= new Query();	
					$Qryb->table 	= "tblaccountjob";
					$Qryb->selected = "idrevenue	= '".$param['entry']['ir']."'";
					$Qryb->fields 	= "idacct = '".$param['employees'][$key]['idacct']."'";
					$checkentryb 	= $Qryb->exe_UPDATE($con); 
				}
			}

			$return = json_encode(array('status'=>'success'));
			
		}else{
			$return = json_encode(array('status'=>'error'));
		}
    }else{
        $return = json_encode(array('status'=>'notloggedin'));
    }

print $return;
mysqli_close($con);



function getReqCtr($con){
    $Qry=new Query();
    $Qry->table="tblforms01";
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

function getStrPayGroup($con, $idpaygrp){
    $Qry=new Query();
    $Qry->table="tblpaygrp as a";
    $Qry->selected="a.group";
    $Qry->fields="a.id='".$idpaygrp."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['group'];
        }
    }
    return null;
}

function getStrPayRev($con, $ir){
    $Qry=new Query();
    $Qry->table="tblpayrevenue";
    $Qry->selected="revenue";
    $Qry->fields="id='".$ir."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['revenue'];
        }
    }
    return null;
}

?>