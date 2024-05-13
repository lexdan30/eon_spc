<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date			= SysDate();
$time			= SysTime();
$return 		= null;	
$ytdArray		= array("janamt","febamt","maramt","apramt","mayamt","junamt","julamt","augamt","sepamt","octamt","novamt","decamt");
$pay_period 	= getPayPeriod($con,$param->id_payperiod);

if( !empty($param->accountid) ){
	if( !empty($param->id_payperiod) ){			
	
		$Qry3           = new Query();
		$Qry3->table    = "tblpayperiod";
		$Qry3->selected = "stat='1'";
		$Qry3->fields   = "id='".$param->id_payperiod."'";                    
		$checke = $Qry3->exe_UPDATE($con);
		if($checke){	
			$pay_yr			= SysYear();
			$index			= getMonthEnd($con,$param->id_payperiod);
			
			$Qry 			= new Query();	
			$Qry->table     = "vw_data_timesheet AS a LEFT JOIN tblaccountjob AS b ON b.idacct=a.empID LEFT JOIN tblpaygrp AS c ON c.id=b.idpaygrp";
			$Qry->selected  = "a.empID as acct_id,b.daysmonth,b.salary,b.idpaygrp,c.group AS grp";
			$Qry->fields    = "a.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' GROUP BY a.empID ORDER BY CONCAT(a.empID,'-',a.work_date) ASC";
			$rs = $Qry->exe_SELECT($con);
			if(mysqli_num_rows($rs)>= 1){
				while($row=mysqli_fetch_array($rs)){					
					$Qry4           = new Query();
					$Qry4->table    = "tblpayroll";
					$Qry4->selected = "idstatus='2'";
					$Qry4->fields   = "id_paydate='".$param->id_payperiod."' AND id_acct='".$row['acct_id']."' ";                    
					$checke4 = $Qry4->exe_UPDATE($con);
					if($checke4){			
						$dept_id		= getAcctDept($con, $row['acct_id']);						
						//TAXINC
							$transid 	= 69;
							$classid	= "1,2,3,6,12,13,14,18,19";
							$amt		= getSumYTD($con, $classid, $row['acct_id']);
							$idpaytot	= checkYTDExist($con, $row['acct_id'], $transid, $pay_yr);
							if( $idpaytot == 0 ){
								insertYTD( $con,$row['acct_id'],$dept_id,$pay_yr,$transid,$ytdArray[$index],$amt );
							}else{
								updateYTD( $con,$idpaytot,$ytdArray[$index],$amt );
							}
							updateSumYTD($con,$row['acct_id'], $transid, $pay_yr);
						//ADDEXC
							$transid 	= 70;
							$classid	= "21,27,24";
							$amt		= getSumYTD($con, $classid, $row['acct_id']);
							$idpaytot	= checkYTDExist($con, $row['acct_id'], $transid, $pay_yr);
							if( $idpaytot == 0 ){
								insertYTD( $con,$row['acct_id'],$dept_id,$pay_yr,$transid,$ytdArray[$index],$amt );
							}else{
								updateYTD( $con,$idpaytot,$ytdArray[$index],$amt );
							}
							updateSumYTD($con,$row['acct_id'], $transid, $pay_yr);
						//BONUSN
							$transid 	= 80;
							$classid	= "7";
							$amt		= getSumYTD($con, $classid, $row['acct_id']);
							$idpaytot	= checkYTDExist($con, $row['acct_id'], $transid, $pay_yr);
							if( $idpaytot == 0 ){
								insertYTD( $con,$row['acct_id'],$dept_id,$pay_yr,$transid,$ytdArray[$index],$amt );
							}else{
								updateYTD( $con,$idpaytot,$ytdArray[$index],$amt );
							}
							updateSumYTD($con,$row['acct_id'], $transid, $pay_yr);
						//BONUST
							$transid 	= 81;
							$classid	= "6";
							$amt		= getSumYTD($con, $classid, $row['acct_id']);
							$idpaytot	= checkYTDExist($con, $row['acct_id'], $transid, $pay_yr);
							if( $idpaytot == 0 ){
								insertYTD( $con,$row['acct_id'],$dept_id,$pay_yr,$transid,$ytdArray[$index],$amt );
							}else{
								updateYTD( $con,$idpaytot,$ytdArray[$index],$amt );
							}
							updateSumYTD($con,$row['acct_id'], $transid, $pay_yr);
						//BENNS
							$transid 	= 73;
							$classid	= "9";
							$amt		= getSumYTD($con, $classid, $row['acct_id']);
							$idpaytot	= checkYTDExist($con, $row['acct_id'], $transid, $pay_yr);
							if( $idpaytot == 0 ){
								insertYTD( $con,$row['acct_id'],$dept_id,$pay_yr,$transid,$ytdArray[$index],$amt );
							}else{
								updateYTD( $con,$idpaytot,$ytdArray[$index],$amt );
							}
							updateSumYTD($con,$row['acct_id'], $transid, $pay_yr);
						//BENST
							$transid 	= 72;
							$classid	= "8";
							$amt		= getSumYTD($con, $classid, $row['acct_id']);
							$idpaytot	= checkYTDExist($con, $row['acct_id'], $transid, $pay_yr);
							if( $idpaytot == 0 ){
								insertYTD( $con,$row['acct_id'],$dept_id,$pay_yr,$transid,$ytdArray[$index],$amt );
							}else{
								updateYTD( $con,$idpaytot,$ytdArray[$index],$amt );
							}
							updateSumYTD($con,$row['acct_id'], $transid, $pay_yr);
						//ALLWNS
							$transid 	= 75;
							$classid	= "3";
							$amt		= getSumYTD($con, $classid, $row['acct_id']);
							$idpaytot	= checkYTDExist($con, $row['acct_id'], $transid, $pay_yr);
							if( $idpaytot == 0 ){
								insertYTD( $con,$row['acct_id'],$dept_id,$pay_yr,$transid,$ytdArray[$index],$amt );
							}else{
								updateYTD( $con,$idpaytot,$ytdArray[$index],$amt );
							}
							updateSumYTD($con,$row['acct_id'], $transid, $pay_yr);
						//ALLWST
							$transid 	= 74;
							$classid	= "2";
							$amt		= getSumYTD($con, $classid, $row['acct_id']);
							$idpaytot	= checkYTDExist($con, $row['acct_id'], $transid, $pay_yr);
							if( $idpaytot == 0 ){
								insertYTD( $con,$row['acct_id'],$dept_id,$pay_yr,$transid,$ytdArray[$index],$amt );
							}else{
								updateYTD( $con,$idpaytot,$ytdArray[$index],$amt );
							}
							updateSumYTD($con,$row['acct_id'], $transid, $pay_yr);
						//OVRTME
							$transid 	= 77;
							$classid	= "18";
							$amt		= getSumYTD($con, $classid, $row['acct_id']);
							$idpaytot	= checkYTDExist($con, $row['acct_id'], $transid, $pay_yr);
							if( $idpaytot == 0 ){
								insertYTD( $con,$row['acct_id'],$dept_id,$pay_yr,$transid,$ytdArray[$index],$amt );
							}else{
								updateYTD( $con,$idpaytot,$ytdArray[$index],$amt );
							}
							updateSumYTD($con,$row['acct_id'], $transid, $pay_yr);
						//SALARY
							$transid 	= 79;
							$classid	= "1,12,13,14";
							$amt		= getSumYTD($con, $classid, $row['acct_id']);
							$idpaytot	= checkYTDExist($con, $row['acct_id'], $transid, $pay_yr);
							if( $idpaytot == 0 ){
								insertYTD( $con,$row['acct_id'],$dept_id,$pay_yr,$transid,$ytdArray[$index],$amt );
							}else{
								updateYTD( $con,$idpaytot,$ytdArray[$index],$amt );
							}
							updateSumYTD($con,$row['acct_id'], $transid, $pay_yr);
						
					}else{
						$return = json_encode(array("status"=>"error", "err"=>mysqli_error($con)));
					}					
				}
				//ADD TO LOGS
				$return = inserLogs($con, $param->accountid, "Closed Payroll for paydate: ".$pay_period['pay_date']);
				$return = json_encode(array("status"=>"success"));
			}			
		}else{
			$return = json_encode(array("status"=>"error", "err"=>mysqli_error($con)));
		}
		
	}else{
		$return = json_encode(array("status"=>"id_payperiod"));
	}	
}else{
	$return = json_encode(array("status"=>"notloggedin"));
}


print $return;
mysqli_close($con);
?>