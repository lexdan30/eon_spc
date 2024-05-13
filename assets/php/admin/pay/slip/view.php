<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$data = array();		
$Qry = new Query();	
$Qry->table     = "vw_eepayslip";
$Qry->selected  = "*";
$Qry->fields    = "id_acct='".$param->id_acct."' AND id_paydate='".$param->id_paydate."' GROUP BY id_acct,id_paydate";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
	while($row=mysqli_fetch_array($rs)){
		
		$tot_deduct = (getPayrollSumContribution($con,$param->id_acct,$param->id_paydate) + getWithTaxAcct($con,$param));
		if( $tot_deduct == 0 ){
			$tot_deduct = '0.00';
		}
		
		$totalPay		= getPayrollSumBase($con,$param->id_paydate,$param->id_acct);
		$totalDeduction = getPayrollSumDeduction($con,$param->id_paydate,$param->id_acct);
		$grossAmount	= sprintf('%0.2f', ($totalPay - $totalDeduction));
		if( $grossAmount == 0 ){
			$grossAmount = '0.00';
		}
		
		$data = array(
			"id" 			=> $row['id'],
			"id_acct" 		=> $row['id_acct'],
			"empid" 		=> $row['empid'],
			"empname" 		=> $row['empname'],
			"idtin"			=> $row['idtin'],
			"idsss"			=> $row['idsss'],
			"idhealth"		=> $row['idhealth'],
			"idibig"		=> $row['idibig'],
			"salary" 		=> number_format($row['salary'],2),
			"pay_grp" 		=> $row['pay_grp'],
			"company" 		=> $row['company'],
			"dept" 			=> $row['dept'],			
			"period_start"	=> date_format(date_create($row['period_start']),"M. d, Y"),
			"period_end"	=> date_format(date_create($row['period_end']),"M. d, Y"),			
			"pay_date" 		=> date_format(date_create($row['pay_date']),"M. d, Y"),	
			"earnings"		=> getPaySlipEarnings($con,$param),
			"tot_deduct"	=> $tot_deduct,		
			"gross"			=> number_format($grossAmount,2),
			"idstatus" 		=> $row['idstatus'],
			"payroll_stat"	=> $row['payroll_stat'],
			"totpay"		=> $totalPay,
			"totded"		=> $totalDeduction,
			"id_paydate"	=> $param->id_paydate
		);
	}
}

$return =  json_encode($data);
print $return;
mysqli_close($con);
?>