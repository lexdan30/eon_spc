<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param 			= $_POST;
$date			= SysDate();
$time			= SysTime();
$return 		= null;	

$pay_period 	= getPayPeriod($con);
$id_paydate		= $_POST['id_paydate'];
$pay_date		= $_POST['pay_date'];
$date_process	= $date;
//check if PAY DATE is PRESENT and NOT CLOSED
if( checkPayDateInserted($con,$id_paydate) ){
	$QryDel           = new Query();
	$QryDel->table    = "tblpayroll";
	$QryDel->fields   = "id_paydate='".$id_paydate."' AND idstatus=1";
	$checkDel      	  = $QryDel->exe_DELETE($con);
}else{
	$return = json_encode(array('status'=>'closedalready'));	
	print $return;
	mysqli_close($con);
	return;
}

$testArr = array();

if( !empty( $param['accountid'] ) ){
	$Qry 			= new Query();	
	$Qry->table     = "vw_data_timesheet AS a LEFT JOIN tblaccountjob AS b ON b.idacct=a.empID LEFT JOIN tblpaygrp AS c ON c.id=b.idpaygrp";
	$Qry->selected  = "a.empID as acct_id,b.daysmonth,b.salary,b.idpaygrp,c.group AS grp";
	$Qry->fields    = "a.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' GROUP BY a.empID ORDER BY CONCAT(a.empID,'-',a.work_date) ASC";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_array($rs)){
			$id_acct		= $row['acct_id'];
			
			
			$dept_id		= getAcctDept($con, $id_acct);
			$sal	  		= sprintf('%0.2f',$row['salary']);
			$unit_amt 		= sprintf('%0.2f',(( $sal/$row['daysmonth'] )/8));
			
			if( (int)$row['idpaygrp'] == 3 ){ //MONTHLIES
				//SALARY
					$addRes	  = insertPayroll($con,$id_acct,$dept_id,1,58,$id_paydate,$date_process,'',sprintf('%0.2f',($sal/2)),sprintf('%0.2f',(($sal/2)*1)),$pay_date);
				//Get Current LATE 
					$class_id = 13;
					$trans_id = 34;				
					$units 	  = sprintf('%0.2f', getSumInfo($con,$pay_period,$row['acct_id'],"late"));
					$tot_amt  = sprintf('%0.2f',$unit_amt * $units);
					if( $units > 0 ){
						$addRes	  = insertPayroll($con,$id_acct,$dept_id,$class_id,$trans_id,$id_paydate,$date_process,$units,$unit_amt,$tot_amt,$pay_date);
					}
				//Get Current Undertime
					$class_id = 14;
					$trans_id = 40;
					$units 	  = sprintf('%0.2f', getSumInfo($con,$pay_period,$row['acct_id'],"ut")); 
					$tot_amt  = sprintf('%0.2f',$unit_amt * $units);
					if( $units > 0 ){
						$addRes	  = insertPayroll($con,$id_acct,$dept_id,$class_id,$trans_id,$id_paydate,$date_process,$units,$unit_amt,$tot_amt,$pay_date);
					}
				//Get Current Absent
					$class_id = 12;
					$trans_id = 33;
					$units 	  = sprintf('%0.2f', getSumInfo($con,$pay_period,$row['acct_id'],"absent")); 
					$tot_amt  = sprintf('%0.2f',$unit_amt * $units);
					if( $units > 0 ){
						$addRes	  = insertPayroll($con,$id_acct,$dept_id,$class_id,$trans_id,$id_paydate,$date_process,$units,$unit_amt,$tot_amt,$pay_date);
					}					
				//GET Current Night Premium
					$class_id = 35;
					$trans_id = 16;
					foreach( getRateCtr( $con,$pay_period,$row['acct_id'],"np_rate" ) as $v ){
						$units 	  = sprintf('%0.2f', getSumInfo2($con,$pay_period,$row['acct_id'],"np", "np_rate", $v['rate'],'' )); 
						$tot_amt  = sprintf('%0.2f',(($unit_amt * $units)* $v['rate']));
						if( $units > 0 ){
							$addRes	  = insertPayroll($con,$id_acct,$dept_id,$class_id,$trans_id,$id_paydate,$date_process,$units,$unit_amt,$tot_amt,$pay_date);
						}
					}					
				//Get Current OT
					$class_id = 18;
					$arrOT = getOTClassSetUp();
					$arr_ot_rates = array();
					foreach( $arrOT as $vv ){						
						foreach( getRateCtr( $con,$pay_period,$row['acct_id'], $vv["col_rate"] ) as $v ){							
							$units 	  = sprintf('%0.2f', getSumInfo2($con,$pay_period,$row['acct_id'], $vv["col_unit"], $vv["col_rate"], floatval($v['rate']), "overtime_status" )); 							
							$tot_amt  = sprintf('%0.2f',(($unit_amt * $units) * floatval($v['rate']))) ;
							if( $units > 0 ){
								array_push($arr_ot_rates, $units);
								$addRes	  = insertPayroll($con,$id_acct,$dept_id,$class_id,$vv["tans_id"],$id_paydate,$date_process,$units,$unit_amt,$tot_amt,$pay_date);
							}
						}
					}
					
					
				//Get Paid Leave
					$class_id = 19;
					$arrLeavePay = getPaidLeaveClassSetUp($con);
					foreach( $arrLeavePay as $vv ){
						$units 	  = sprintf('%0.2f', getSumLeave($con,$pay_period,$row['acct_id'],$vv['id']));
						$tot_amt  = sprintf('%0.2f',$unit_amt * $units);
						if( $units > 0 ){
							$addRes	  = insertPayroll($con,$id_acct,$dept_id,$class_id,$vv['trans_id'],$id_paydate,$date_process,$units,$unit_amt,$tot_amt,$pay_date);
						}
					}				
				//Get LateApprove 		
					$class_id = 18;
					$arrOT = getOTClassSetUp();		
					foreach( $arrOT as $vv ){						
						foreach( getRateCtrLate( $con,$pay_period,$row['acct_id'], $vv["col_rate"] ) as $v ){							
							$units 	  = sprintf('%0.2f', getSumInfoLate($con,$pay_period,$row['acct_id'], $vv["col_unit"], $vv["col_rate"], floatval($v['rate']), "overtime_status" )); 
							$tot_amt  = sprintf('%0.2f',(($unit_amt * $units) * floatval($v['rate']))) ;
							if( $units > 0 ){
								$addRes	  = insertPayroll($con,$id_acct,$dept_id,$class_id,$vv["tans_id"],$id_paydate,$date_process,$units,$unit_amt,$tot_amt,$pay_date);								
							}
						}
					}					
					$class_id = 19;
					$arrLeavePay = getPaidLeaveClassSetUp($con);
					foreach( $arrLeavePay as $vv ){
						$units 	  = sprintf('%0.2f', getSumLateLeave($con,$pay_period,$row['acct_id'],$vv['id']));
						$tot_amt  = sprintf('%0.2f',$unit_amt * $units);
						if( $units > 0 ){
							$addRes	  = insertPayroll($con,$id_acct,$dept_id,$class_id,$vv['trans_id'],$id_paydate,$date_process,$units,$unit_amt,$tot_amt,$pay_date);
						}
					}
					$class_id = 1;
					$trans_id = 67;
					$units 	  = sprintf('%0.2f', getSumInfoAALate($con,$pay_period,$row['acct_id'],"adj_hours")); 
					$tot_amt  = sprintf('%0.2f',$unit_amt * $units);
					if( $units > 0 ){
						$addRes	  = insertPayroll($con,$id_acct,$dept_id,$class_id,$trans_id,$id_paydate,$date_process,$units,$unit_amt,$tot_amt,$pay_date);
					}
					
				//Get Gross	
					$class_id = 37;
					$trans_id = 68;
					$units 	  = 1;
					$totalPay 		= getPayrollSumBase($con,$id_paydate,$id_acct);					
					$totalDeduction = getPayrollSumDeduction($con,$id_paydate,$id_acct);					
					$grossAmount	= sprintf('%0.2f', ($totalPay - $totalDeduction));	
					$unit_amt = $grossAmount;
					$tot_amt  = sprintf('%0.2f',$unit_amt * $units);
					if( $units > 0 ){
						$addRes	  = insertPayroll($con,$id_acct,$dept_id,$class_id,$trans_id,$id_paydate,$date_process,$units,$unit_amt,$tot_amt,$pay_date);
					}
				
				if( (int)$pay_period['hascontri'] == 2 ){
				//Get SSS Contribution
					$units 	  = 1.00;
					$arrSSSClassTrans = array(
						array( "class_id" => '21', "trans_id" => '61', "trans_name" => "sssemp" ),
						array( "class_id" => '22', "trans_id" => '59', "trans_name" => "ssscom" ),
						array( "class_id" => '23', "trans_id" => '60', "trans_name" => "sssecc" )
					);
					$arrSSSContData   = getSSSCont($con,$sal);
					foreach( $arrSSSClassTrans as $vv ){
						$unit_amt  = sprintf('%0.2f', $arrSSSContData[$vv['trans_name']] );
						$tot_amt  = sprintf('%0.2f',$unit_amt * $units);
						$addRes	  = insertPayroll($con,$id_acct,$dept_id,$vv['class_id'],$vv['trans_id'],$id_paydate,$date_process,'',$unit_amt,$tot_amt,$pay_date);
					}
				//Get PAGIBIG Contribution
					$units 	  = 1.00;
					$arrIBIGClassTrans = array(
						array( "class_id" => '27', "trans_id" => '56', "trans_name" => "pgibem" ),
						array( "class_id" => '28', "trans_id" => '55', "trans_name" => "pgibco" )
					);
					$arrIBIGContData   = getIBIGCont($con,$sal);
					foreach( $arrIBIGClassTrans as $vv ){
						$unit_amt  = sprintf('%0.2f', $arrIBIGContData[$vv['trans_name']] );
						$tot_amt  = sprintf('%0.2f',$unit_amt * $units);
						$addRes	  = insertPayroll($con,$id_acct,$dept_id,$vv['class_id'],$vv['trans_id'],$id_paydate,$date_process,'',$unit_amt,$tot_amt,$pay_date);
					}
				//Get PHILHEALTH Contribution
					$units 	  = 1.00;
					$arrHLTHClassTrans = array(
						array( "class_id" => '24', "trans_id" => '53', "trans_name" => "hlthemp" ),
						array( "class_id" => '25', "trans_id" => '52', "trans_name" => "hlthcom" )
					);
					$arrHLTHContData   = getHLTHCont($con,$sal);
					foreach( $arrHLTHClassTrans as $vv ){
						$unit_amt  = sprintf('%0.2f', $arrHLTHContData[$vv['trans_name']] );
						$tot_amt  = sprintf('%0.2f',$unit_amt * $units);
						$addRes	  = insertPayroll($con,$id_acct,$dept_id,$vv['class_id'],$vv['trans_id'],$id_paydate,$date_process,'',$unit_amt,$tot_amt,$pay_date);
					}
				}
				//Get TAX
					$class_id = 20;
					$trans_id = 62;
					$units 	  = 1.00;
					$grossCont= sprintf('%0.2f',($grossAmount - getPayrollSumContribution($con,$id_acct,$id_paydate)));	
					
					$testArr[$id_acct] = array( "grossCont" => $grossCont, "grossAmount"=>$grossAmount, "sumcontri" => getPayrollSumContribution($con,$id_acct,$id_paydate)  );
					if( $grossCont >=0 ){
						$unit_amt = getPayrollSumTax($con,$grossCont,'T');					
						$tot_amt  = sprintf('%0.2f',$unit_amt * $units);
						$withTaxComputed = $tot_amt;
						if( $units > 0 ){
							$addRes	  = insertPayroll($con,$id_acct,$dept_id,$class_id,$trans_id,$id_paydate,$date_process,'',$unit_amt,$tot_amt,$pay_date);
						}
				//NET PAY
						$class_id = 34;
						$trans_id = 54;
						$units 	  = 1.00;
						$unit_amt = ($grossAmount - ( getPayrollSumContribution($con,$id_acct,$id_paydate) + $withTaxComputed ) );
						$tot_amt  = sprintf('%0.2f',$unit_amt * $units);
						if( $units > 0 ){
							if( $tot_amt > 0 ){
								$addRes	  = insertPayroll($con,$id_acct,$dept_id,$class_id,$trans_id,$id_paydate,$date_process,'',$unit_amt,$tot_amt,$pay_date);
							}
						}
					}
				
			}
			
		}
	}
	//ADD TO LOGS
	$return = inserLogs($con, $param['accountid'], "Proccessed Payroll");
	$return = json_encode(array('status'=>'success','grossCont'=>$testArr, 'leavePay'=>$arrLeavePay ));
}else{
	$return = json_encode(array('status'=>'error'));
}

print $return;
mysqli_close($con);
?>