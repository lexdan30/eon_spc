<?php
/*
NOTE: SYSTEM ACTIVATION TO DATABASE
*/
	class Query{
		
		public $table;	
		public $fields;
		public $values;
		public $selected;
		public $delimeter;

/**************************************************************************************
***************************************************************************************
*********************************INSERT QUERY STATEMENT*******************************/	
			function exe_INSERT($con){
				$quer_y = "INSERT INTO ".$this->table." (".$this->selected.") VALUES (".$this->fields.")";
				return mysqli_query($con, $quer_y);
			}
/**************************************************************************************
***************************************************************************************
*********************************SELECT QUERY STATEMENT*******************************/	
			function exe_SELECT($con){
				$quer_y = "SELECT ".$this->selected." FROM ".$this->table." WHERE ".$this->fields;
				return mysqli_query($con, $quer_y);
			}
/**************************************************************************************
***************************************************************************************
*********************************UPDATE QUERY STATEMENT*******************************/	
			function exe_UPDATE($con){
				$quer_y = "UPDATE ".$this->table." SET ".$this->selected." WHERE ".$this->fields;
				return mysqli_query($con, $quer_y);
			}
/**************************************************************************************
***************************************************************************************
*********************************DELETE QUERY STATEMENT*******************************/	
			function exe_DELETE($con){
				$quer_y = "DELETE FROM ".$this->table." WHERE ".$this->fields;
				return mysqli_query($con, $quer_y);
			}
/**************************************************************************************
***************************************************************************************
*********************************TRUNCATE QUERY STATEMENT*******************************/	
			function exe_TRUNCATE($con){
				$quer_y = "TRUNCATE TABLE ".$this->table;
				return mysqli_query($con, $quer_y);
			}			
	}
	function getCompanyContactLink(){
		return "";
	}
	function getCompanyName(){
		return "N-Pax";
	}
	function siteProtocol(){
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		//$domainName = $_SERVER['HTTP_HOST'].'/';
		return $protocol."127.0.0.1:8000/recruit/";
	}
	function SysYear(){
		date_default_timezone_set('Asia/Manila');
		$info = getdate();
		return $info['year'];
	}
	function SysDateDan(){
		date_default_timezone_set('Asia/Manila');
		$info = getdate();
		$date = $info['mday'];
		$month = $info['mon'];
		$year = $info['year'];
		$dat_e = $month."/".$date."/".$year;
		if(!empty($year) && !empty($month) && !empty($date)){
			return $dat_e;
		}else{
			return $dat_e;
		}
	}
	function SysDate(){
		date_default_timezone_set('Asia/Manila');
		$info = getdate();
		$date = $info['mday'];
		$month = $info['mon'];
		$year = $info['year'];
		$dat_e = $year."-".$month."-".$date;
		if(!empty($year) && !empty($month) && !empty($date)){
			return $dat_e;
		}else{
			return $dat_e;
		}
	}
	function SysDatePadLeft(){
		date_default_timezone_set('Asia/Manila');
		$info = getdate();
		$date = $info['mday'];
		$month = $info['mon'];
		$year = $info['year'];
		$dat_e = $year."-".str_pad($month,2,"0",STR_PAD_LEFT)."-".str_pad($date,2,"0",STR_PAD_LEFT);
		if(!empty($year) && !empty($month) && !empty($date)){
			return $dat_e;
		}else{
			return $dat_e;
		}
	}
	function SysTime(){

		date_default_timezone_set('Asia/Manila');
		$info = getdate();
		$hour = $info['hours'];
		$min = $info['minutes'];
		$sec = $info['seconds'];
		$time = $hour.":".$min.":00";
		if(!empty($hour) && !empty($min) && !empty($sec)){
			return $time;
		}else{
			return $time;
		}
	}
	function randomPassword() {
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass); //turn the array into a string
	}

	/* custom functions */
	function getLeaveTypes($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblleavestype";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0 ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"    => $row['id'],
					"type" 	=> $row['type']
				);
			}
		}

		return $data;
	}
	
	function getUnitTypes($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblbunitstype";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0 ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"    => $row['id'],
					"type" 	=> $row['type'],
					"under"	=> $row['idunder']
				);
			}
		}

		return $data;
	}

	function getUnitHeads($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblaccount";
		$Qry->selected  = "id,CONCAT(fname,' ', lname) AS name";
		$Qry->fields    = "id>0 ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"    => $row['id'],
					"name" =>  $row['name']
				);
			}
		}

		return $data;
	}
	
	function checkType($con, $utype, $id, $tbl ){
		$Qry = new Query();	
		$Qry->table     = $tbl;
		$Qry->selected  = "*";
		$Qry->fields    = "unittype = '".$utype."'";
		if( !empty( $id ) ){
			$Qry->fields= $Qry->fields." AND id not in ('".$id."') ";
		}
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}

	function checkAlias($con, $alias, $id, $tbl ){
		$Qry = new Query();	
		$Qry->table     = $tbl;
		$Qry->selected  = "*";
		$Qry->fields    = "alias = '".$alias."'";
		if( !empty( $id ) ){
			$Qry->fields= $Qry->fields." AND id not in ('".$id."') ";
		}
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}

	function checkAcctUnit($con, $idunit ){
		$Qry = new Query();	
		$Qry->table     = "tblaccountjob";
		$Qry->selected  = "*";
		$Qry->fields    = "idunit = '".$idunit."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}

	function checkAcctLabor($con, $id ){
		$Qry = new Query();	
		$Qry->table     = "tblaccountjob";
		$Qry->selected  = "*";
		$Qry->fields    = "idlabor = '".$id."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}
	
	function getCompTypes($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblcomptype";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0 ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"    => $row['id'],
					"type" 	=> $row['type'],
					"alias"	=> $row['alias']
				);
			}
		}
		return $data;
	}
	
	function getCompSize($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblcompsize";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0 ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"    => $row['id'],
					"size" 	=> $row['size'],
					"alias"	=> $row['alias']
				);
			}
		}
		return $data;
	}
	
	function getCompIndustry($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblcompind";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0 ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"industry" 	=> $row['industry'],
					"alias"	    => $row['alias']
				);
			}
		}
		return $data;
	}
	
	function getAccountType($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblaccttype";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0 ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"type" 		=> ucwords(strtolower($row['type'])),
					"alias"	    => $row['alias'],
					"orgdata"	=> $row['orgdata'],
					"empportal"	=> $row['empportal'],
					"mngrportal"=> $row['mngrportal'],
					"hrportal"	=> $row['hrportal'],
					"timeportal"=> $row['timeportal'],
					"payportal"	=> $row['payportal'],
					"admportal"	=> $row['admportal']
				);
			}
		}
		return $data;
	}
	
	function getEmployeeType($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblemptype";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0 ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"type" 		=> $row['type'],
					"alias"	    => $row['alias']					
				);
			}
		}
		return $data;
	}
	
	function getCivilStatus($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblcivilstatus";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0 ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"stat" 		=> $row['stat'],
					"alias"	    => $row['alias']					
				);
			}
		}
		return $data;
	}
	
	function checkEmpID( $con, $idemp ){
		$Qry = new Query();	
		$Qry->table     = "tblaccount";
		$Qry->selected  = "*";
		$Qry->fields    = "empid = '".$idemp."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}
	
	function getAccounts($con, $id){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "vw_dataemployees";
		$Qry->selected  = "*";
		$Qry->fields    = "etypeid=1  ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"empid" 	=> $row['empid'],
					"empname"	=> $row['empname'],
					"emplbl"	=> $row['empid']." ".$row['empname'],
					"idsuperior"=> $row['idsuperior'],
					"superior"	=> $row['superior']
				);
			}
		}
		return $data;
	}
	
	function getEmpStatus($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblempstatus";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"stat"		=> $row['stat']					
				);
			}
		}
		return $data;
	}
	
	function getJobLocation($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tbljoblocation";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"loc"		=> $row['loc']					
				);
			}
		}
		return $data;
	}
	
	function getWShifts($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "vw_datacalendar";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        	=> $row['id'],
					"name"			=> $row['name'],					
					"idsun"			=> $row['idsun'],
					"sun"			=> $row['sun'],
					"sun_in"		=> $row['sun_in'],
					"sun_out"		=> $row['sun_out'],
					"sun_brkin"		=> $row['sun_brkin'],
					"sun_brkout"	=> $row['sun_brkout'],
					"mon"			=> $row['mon'],
					"mon_in"		=> $row['mon_in'],
					"mon_out"		=> $row['mon_out'],
					"mon_brkin"		=> $row['mon_brkin'],
					"mon_brkout"	=> $row['mon_brkout'],
					"tue"			=> $row['tue'],
					"tue_in"		=> $row['tue_in'],
					"tue_out"		=> $row['tue_out'],
					"tue_brkin"		=> $row['tue_brkin'],
					"tue_brkout"	=> $row['tue_brkout'],
					"wed"			=> $row['wed'],
					"wed_in"		=> $row['wed_in'],
					"wed_out"		=> $row['wed_out'],
					"wed_brkin"		=> $row['wed_brkin'],
					"wed_brkout"	=> $row['wed_brkout'],
					"thu"			=> $row['thu'],
					"thu_in"		=> $row['thu_in'],
					"thu_out"		=> $row['thu_out'],
					"thu_brkin"		=> $row['thu_brkin'],
					"thu_brkout"	=> $row['thu_brkout'],
					"fri"			=> $row['fri'],
					"fri_in"		=> $row['fri_in'],
					"fri_out"		=> $row['fri_out'],
					"fri_brkin"		=> $row['fri_brkin'],
					"fri_brkout"	=> $row['fri_brkout'],
					"sat"			=> $row['sat'],
					"sat_in"		=> $row['sat_in'],
					"sat_out"		=> $row['sat_out'],
					"sat_brkin"		=> $row['sat_brkin'],
					"sat_brkout"	=> $row['sat_brkout']
				);
			}
		}
		return $data;
	}
	
	function getSchedType($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblschedtype";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"type"		=> $row['type']					
				);
			}
		}
		return $data;
	}
	
	function getLabors($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tbllabortype";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"type"		=> $row['type']					
				);
			}
		}
		return $data;
	}
	
	function getJobLvls($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tbljoblvl";
		$Qry->selected  = "*";
		$Qry->fields    = "isactive > 0";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"alias"     => $row['alias'],
					"lvl"		=> $row['lvl'],
					"lvllbl"	=> $row['alias']."  (".$row['lvl'].")"
				);
			}
		}
		return $data;
	}
	
	function getBusinessUnits( $con ){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "vw_data_assignbunits";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"name"		=> $row['name']					
				);
			}
		}
		return $data;
	}
	
	function getPositions($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "vw_data_assignposition";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"name"		=> $row['name'],
					"alias"		=> $row['alias']
				);
			}
		}
		return $data;
	}
	
	function getPayGroups($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblpaygrp";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"name"		=> $row['group']					
				);
			}
		}
		return $data;
	}
	
	function getPayStatus($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblpaystat";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"name"		=> $row['status']					
				);
			}
		}
		return $data;
	}
	
	function getPayRevenues($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblpayrevenue";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"name"		=> $row['revenue']					
				);
			}
		}
		return $data;
	}
	
	function getPayRelease($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblpayrelease";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"name"		=> $row['type']					
				);
			}
		}
		return $data;
	}
	
	function getAccountLeaves($con, $idacct){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblleaves AS a INNER JOIN tblleavestype AS b ON a.`idtype` = b.`id`";
		$Qry->selected  = "a.id, a.name, a.hours, a.idtype, b.type, a.isconvertible";
		$Qry->fields    = "a.id>0";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				
				$entitle	= 0;
				$used		= 0;
				$balance	= 0;		
				$idacctleave='';
				
				$Qry2 				= new Query();
				$Qry2->table     	= "tblaccountleaves";
				$Qry2->selected  	= "*";
				$Qry2->fields    	= "idleave='".$row['id']."' AND idacct='".$idacct."'";
				$rs2 = $Qry2->exe_SELECT($con);
				if(mysqli_num_rows($rs2)>= 1){
					if($row2=mysqli_fetch_array($rs2)){
						$idacctleave= $row2['id'];
						$entitle	= $row2['entitle'];
						$used		= $row2['used'];
						$balance	= $row2['balance'];
					}
				}
				
				$data[ $row['id'] ] = array( 
					"id"        	=> $row['id'],
					"name"			=> $row['name'],
					"hours"			=> $row['hours'],
					"idtype"		=> $row['idtype'],
					"isconvertible"	=> $row['isconvertible'],
					"type"			=> $row['type'],
					"idacct"		=> $idacct,
					"idleave"		=> $row['id'],
					"entitle"		=> $entitle,
					"used"			=> $used,
					"balance"		=> $balance,
					"idacctleave"	=> $idacctleave
				);
			}
		}
		return $data;
	}
	
	function checkLeaveName($con, $name, $id ){
		$Qry = new Query();	
		$Qry->table     = "tblleaves";
		$Qry->selected  = "*";
		$Qry->fields    = "name = '".$name."'";
		if( !empty($id) ){
			$Qry->fields= $Qry->fields." AND id!='".$id."'";
		}
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}
	
	function checkOvertimeName($con, $name, $id ){
		$Qry = new Query();	
		$Qry->table     = "tblovertimes";
		$Qry->selected  = "*";
		$Qry->fields    = "name = '".$name."'";
		if( !empty($id) ){
			$Qry->fields= $Qry->fields." AND id!='".$id."'";
		}
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}
	
	function checkOvertimeAlias($con, $name, $id ){
		$Qry = new Query();	
		$Qry->table     = "tblovertimes";
		$Qry->selected  = "*";
		$Qry->fields    = "alias = '".$name."'";
		if( !empty($id) ){
			$Qry->fields= $Qry->fields." AND id!='".$id."'";
		}
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}
	
	function checkShiftName($con, $name, $id ){
		$Qry = new Query();	
		$Qry->table     = "tblshift";
		$Qry->selected  = "*";
		$Qry->fields    = "name = '".$name."'";
		if( !empty($id) ){
			$Qry->fields= $Qry->fields." AND id!='".$id."'";
		}
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}
	
	function getShiftsTypes($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblshift";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"name"		=> $row['name'],
					"stime"		=> $row['stime'],
					"ftime"		=> $row['ftime'],
					"breakin"	=> $row['breakin'],
					"breakout"	=> $row['breakout']
				);
			}
		}
		return $data;
	}
	
	function getShiftTime( $con, $idshift, $idtimeleavetype ){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblshift";
		$Qry->selected  = "*";
		$Qry->fields    = "id='".$idshift."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				
				if( (int)$idtimeleavetype == 1 ){
					$stime = $row['stime'];
					$ftime = $row['ftime'];
				}elseif( (int)$idtimeleavetype == 2 ){
					$stime = $row['stime'];
					$ftime = $row['breakin'];
				}elseif( (int)$idtimeleavetype == 3 ){
					$stime = $row['breakout'];
					$ftime = $row['ftime'];
				}
				$data = array( 
					"stime"     => $stime,
					"ftime"		=> $ftime
				);
			}
		}
		return $data;
	}
	
	function checkCalendarName($con, $name, $id ){
		$Qry = new Query();	
		$Qry->table     = "tblcalendar";
		$Qry->selected  = "*";
		$Qry->fields    = "name = '".$name."'";
		if( !empty($id) ){
			$Qry->fields= $Qry->fields." AND id!='".$id."'";
		}
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}
	
	function getHolidayTypes($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblholidaytype";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"name"		=> $row['type']
				);
			}
		}
		return $data;
	}
	
	function checkHolidayName($con, $name, $id ){
		$Qry = new Query();	
		$Qry->table     = "tblholidays";
		$Qry->selected  = "*";
		$Qry->fields    = "name = '".$name."'";
		if( !empty($id) ){
			$Qry->fields= $Qry->fields." AND id!='".$id."'";
		}
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}
	
	function insertTimeKeepingRec($con, $idcalendar, $idacct){
		$calendar = getCalendarData($con, $idcalendar);
		$Qry2 			= new Query();	
		$Qry2->table	= "tbltimesched";
		$Qry2->selected = "idacct, idsun, idmon, idtue, idwed, idthu, idfri, idsat, date_create";
		$Qry2->fields 	= "'".$idacct."', '".$calendar['idsun']."', '".$calendar['idmon']."', '".$calendar['idtue']."', '".$calendar['idwed']."', '".$calendar['idthu']."', '".$calendar['idfri']."', '".$calendar['idsat']."','".SysDate()."'";
		$checke2 		= $Qry2->exe_INSERT($con);
		return $checke2;
	}
	function getCalendarData($con,$id){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblcalendar";
		$Qry->selected  = "*";
		$Qry->fields    = "id='".$id."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data = array( 
					"id"        => $row['id'],
					"name"		=> $row['name'],
					"idsun"		=> $row['shiftsun'],
					"idmon"		=> $row['shiftmon'],
					"idtue"		=> $row['shifttue'],
					"idwed"		=> $row['shiftwed'],
					"idthu"		=> $row['shiftthu'],
					"idfri"		=> $row['shiftfri'],
					"idsat"		=> $row['shiftsat']
				);
			}
		}
		return $data;
	}
	
	function getLatePayPeriod($con, $dateApprove){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblpayperiod";
		$Qry->selected  = "*";
		$Qry->fields    = "period_start <= '".$dateApprove."'  AND  period_end >= '".$dateApprove."' ORDER BY id ASC limit 1";		
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data = array( 
					"id"        => $row['id'],
					"pay_start"	=> $row['period_start'],
					"pay_end"	=> $row['period_end'],
					"pay_date"	=> $row['pay_date'],
					"grace_hour"=> $row['grace_hour'],
					"hascontri" => $row['hascontri'],
					"pay_stat"	=> $row['stat']
				);
			}
		}
		return $data;
	}
	
	function getPayPeriod( $con, $id_period=NULL ){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblpayperiod";
		$Qry->selected  = "*";
		if( empty( $id_period ) ){
			$Qry->fields    = "stat=0 ORDER BY id ASC limit 1";
		}else{
			$Qry->fields    = "id=".$id_period;
		}
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data = array( 
					"id"        => $row['id'],
					"pay_start"	=> $row['period_start'],
					"pay_end"	=> $row['period_end'],
					"pay_date"	=> $row['pay_date'],
					"grace_hour"=> $row['grace_hour'],
					"hascontri" => $row['hascontri'],
					"pay_stat"	=> $row['stat']
				);
			}
		}
		return $data;
	}
	
	function getShiftData($con, $idacct, $cols){
		$data = array();
		$col = explode(",",$cols);
		$Qry = new Query();	
		$Qry->table     = "vw_datatimesched";
		$Qry->selected  = $cols;
		$Qry->fields    = "idacct='".$idacct."' ORDER BY id DESC LIMIT 1";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				foreach($col as $key=>$v){
					array_push($data, $row[$col[$key]]);
				}
			}
		}
		return $data;
	}
	
	function getDateShiftData($con, $idacct, $cols, $date ){
		$data = array();
		$col = explode(",",$cols);
		$Qry = new Query();	
		$Qry->table     = "vw_datatimesched";
		$Qry->selected  = $cols;
		$Qry->fields    = "idacct='".$idacct."' AND date_create <= '".$date."' ORDER BY id DESC LIMIT 1";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){			
			if($row=mysqli_fetch_array($rs)){
				foreach($col as $key=>$v){
					array_push($data, $row[$col[$key]]);
				}
			}
		}else{
			$Qry2 = new Query();	
			$Qry2->table     = "vw_datatimesched";
			$Qry2->selected  = $cols;
			$Qry2->fields    = "idacct='".$idacct."' AND date_create >= '".$date."' ORDER BY id ASC LIMIT 1";
			$rs2 = $Qry2->exe_SELECT($con);
			if(mysqli_num_rows($rs2)>= 1){			
				if($row2=mysqli_fetch_array($rs2)){
					foreach($col as $key=>$v){
						array_push($data, $row2[$col[$key]]);
					}
				}
			}
		}
		return $data;
	}
	
	
	function getBeforeHoliday($con, $work_date, $acct_id){
		$Qry = new Query();	
		$Qry->table     = "vw_data_timesheet";
		$Qry->selected  = "absent";
		$Qry->fields    = "work_date < '".$work_date."' AND idshift NOT IN (4) AND holiday_id IS NULL AND empID='".$acct_id."' ORDER BY work_date DESC LIMIT 1";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return (int)$row['absent'];
			}
		}
		return 0;
	}
	
	function getAttendanceApplication($con, $work_date, $acct_id, $start, $end){
		
		$Qry = new Query();	
		$Qry->table     = "vw_attendance_application";
		$Qry->selected  = "*";
		$Qry->fields    = "date='".$work_date."' AND idacct='".$acct_id."' AND stat=1 AND date_approve BETWEEN '".$start."' AND '".$end."' ";
		$rs = $Qry->exe_SELECT($con);
		
		$data = array(
			"id"        	=> '',
			"date"			=> $work_date,
			"acct_id"		=> $acct_id,
			"start"			=> $start,
			"end"			=> $end,
			"where"			=> $Qry->fields
		);	
		
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data = array( 
					"id"        	=> $row['id'],					
					"idacct"		=> $row['idacct'],
					"empid"			=> $row['empid'],
					"empname"		=> $row['empname'],
					"date"			=> $row['date'],
					"stime"			=> $row['stime'],
					"ftime"			=> $row['ftime'],
					"hrs"			=> $row['hrs'],
					"remarks"		=> $row['remarks'],
					"date_approve"	=> $row['date_approve']
				);
			}			
		}
		return $data;
	}
	
	function getLeaveApplication($con, $work_date, $acct_id, $start, $end){
		$data = array(
			"id"        	=> ''
		);	
		$Qry = new Query();	
		$Qry->table     = "vw_leave_application";
		$Qry->selected  = "*";
		$Qry->fields    = "date='".$work_date."' AND idacct='".$acct_id."' AND stat=1 AND date_approve BETWEEN '".$start."' AND '".$end."' ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data = array( 
					"id"        	=> $row['id'],
					"idleave"		=> $row['idleave'],
					"leave_name"	=> $row['leave_name'],
					"leave_type"	=> $row['leave_type'],
					"idtype"		=> $row['idtype'],
					"idacct"		=> $row['idacct'],
					"empid"			=> $row['empid'],
					"empname"		=> $row['empname'],
					"date"			=> $row['date'],
					"stime"			=> $row['stime'],
					"ftime"			=> $row['ftime'],
					"hrs"			=> $row['hrs'],
					"remarks"		=> $row['remarks'],
					"file"			=> $row['file'],
					"stat"			=> $row['stat'],
					"leave_status"	=> $row['leave_status'],
					"date_approve"	=> $row['date_approve']
				);
			}			
		}
		return $data;
	}
	
	function getLeaves($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "vw_dataleavestype";
		$Qry->selected  = "*";
		$Qry->fields    = "idactive='Y'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        	=> $row['id'],
					"name"			=> $row['name'],
					"hours"			=> $row['hours'],
					"idtype"		=> $row['idtype'],
					"type"			=> $row['type'],
					"isconvertible"	=> $row['isconvertible'],
					"idactive"		=> $row['idactive']
				);
			}
		}
		return $data;
	}
	
	function getLeaveBalance($con, $idacct, $idleave ){
		$Qry = new Query();	
		$Qry->table     = "tblaccountleaves";
		$Qry->selected  = "balance, pending_bal";
		$Qry->fields    = "idacct='".$idacct."' AND idleave='".$idleave."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['balance'] - $row['pending_bal'];
			}
		}
		return 0;
	}
	
	function getAge( $date ){
		$startDate = new DateTime( $date ); 
		$endDate = new DateTime( SysDate() );
		$holiday = array(); 
		$interval = new DateInterval('P1D');  
		$daterange = new DatePeriod($startDate, $interval ,$endDate);
		$result = array();
		foreach($daterange as $date){
			if($date->format("N") <7 AND !in_array($date->format("Y-m-d"),$holiday)){
				$result[] = $date->format("Y-m-d");
			}
		}
		return "".count($result);
	}
	
	function getOtApplication($con, $work_date, $acct_id, $start, $end){
		$data = array(
			"id"        	=> ''
		);	
		$Qry = new Query();	
		$Qry->table     = "vw_overtime_application_2";
		$Qry->selected  = "*";
		$Qry->fields    = "date='".$work_date."' AND idacct='".$acct_id."' AND stat=1 /*AND date_approve BETWEEN '".$start."' AND '".$end."'*/ ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data = array( 
					"id"        	=> $row['id'],
					"docnumber"		=> $row['docnumber'],
					"creator"		=> $row['creator'],
					"idacct"		=> $row['idacct'],
					"name"			=> $row['name'],
					"shift_id"		=> $row['shift_id'],
					"shift_stime"	=> $row['shift_stime'],
					"shift_ftime"	=> $row['shift_ftime'],
					"breakin"		=> $row['breakin'],
					"breakout"		=> $row['breakout'],
					"date"			=> $row['date'],
					"sdate"			=> $row['sdate'],
					"fdate"			=> $row['fdate'],
					"overtime_stime"=> $row['overtime_stime'],
					"overtime_ftime"=> $row['overtime_ftime'],
					"planhrs"		=> $row['planhrs'],
					"hrs"			=> $row['hrs'],
					"remarks"		=> $row['remarks'],
					"file"			=> $row['file'],
					"stat"			=> $row['stat'],
					"date_create"	=> $row['date_create']
				);
			}			
		}
		return $data;
	}
	
	function getHolidayAlias($con,$date){
		$Qry = new Query();	
		$Qry->table     = "vw_dataholidays";
		$Qry->selected  = "ot_type";
		$Qry->fields    = "date='".$date."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['ot_type'];
			}
		}
		return 'W';
	}

	function getClassTrans($con,$ot_type){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblclasstrans";
		$Qry->selected  = "*";
		$Qry->fields    = "ottype='".$ot_type."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        	=> $row['id'],
					"idlcass"		=> $row['idlcass'],
					"alias"			=> $row['alias'],
					"name"			=> $row['name'],
					"rate"			=> $row['rate'],
					"isdef"			=> $row['isdef'],
					"ottype"		=> $row['ottype'],
					"determine"		=> $row['determine']
				);
			}
		}
		return $data;
	}

	function insertOtRate($con,$idot,$hrs,$rate,$nightpremium){
		$Qry2 			= new Query();	
		$Qry2->table	= "tblotbreakdown";
		$Qry2->selected = "idot,hrs,rate,name";
		$Qry2->fields 	= "'".$idot."','".$hrs."','".$rate."','".$nightpremium."'";
		$checke2 		= $Qry2->exe_INSERT($con);
		if($checke2){
			return 1;
		}else{
			return 0;
		}
	}

	function getNightPremium($con){
		$Qry = new Query();	
		$Qry->table     = "tblnightpremium";
		$Qry->selected  = "stime";
		$Qry->fields    = "id>0 ORDER BY id DESC LIMIT 1";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){					
				return $row['stime'];
			}
		}
		return '22:00';
	}
	
	function getAllDepartment($con){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "vw_databusinessunits";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array( 
					"id"        => $row['id'],
					"name" 		=> $row['name'],
					"alias"		=> $row['alias'],
					"idhead"    => $row['idhead'],
					"idunder" 	=> $row['idunder'],
					"unittype"	=> $row['unittype'],	
					"isactive"  => $row['isactive'],
					"stype" 	=> $row['stype'],
					"shead"		=> $row['shead'],
					"stat"		=> $row['stat']
				);
			}
		}
		return $data;
	}
	
	function checkApplication($con, $acct, $date, $tbl){
		$Qry = new Query();	
		$Qry->table     = $tbl;
		$Qry->selected  = "*";
		$Qry->fields    = "date='".$date."' AND idacct='".$acct."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return true;
			}			
		}
		return false;
	}
	
	function getHireDate($con, $acct){
		$Qry = new Query();	
		$Qry->table     = "vw_dataemployees";
		$Qry->selected  = "hdate";
		$Qry->fields    = "id='".$acct."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){					
				return $row['hdate'];
			}
		}
		return 0;
	}
	
	function getAcctDept($con, $acct){
		$Qry = new Query();	
		$Qry->table     = "vw_dataemployees";
		$Qry->selected  = "idunit";
		$Qry->fields    = "id='".$acct."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){					
				return $row['idunit'];
			}
		}
		return 0;
	}
	
	function getTimeSheetData( $con, $idacct, $work_date){
		$data = array();
		$Qry = new Query();	
		$Qry->table     = "tbltimesheet";
		$Qry->selected  = "*";
		$Qry->fields    = "idacct='".$idacct."' AND date='".$work_date."' ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				$data = array(
					"id"			=> (!empty($row['id']) ?$row['id']:''),
					"idacct"		=> (!empty($row['idacct']) ?$row['idacct']:''),
					"day"			=> (!empty($row['day']) ?$row['day']:''),
					"date"			=> (!empty($row['date']) ?$row['date']:''),
					"idshift"		=> (!empty($row['idshift']) ?$row['idshift']:''),
					"shiftin"		=> (!empty($row['shiftin']) ?$row['shiftin']:''),
					"shiftout"		=> (!empty($row['shiftout']) ?$row['shiftout']:''),
					"timein"		=> (!empty($row['timein']) ?$row['timein']:''),
					"timeout"		=> (!empty($row['timeout']) ?$row['timeout']:''),
					"late"			=> (!empty($row['late']) ?$row['late']:''),
					"ut"			=> (!empty($row['ut']) ?$row['ut']:''),
					"absent"		=> (!empty($row['absent']) ?$row['absent']:''),
					"reghrs"		=> (!empty($row['reghrs']) ?$row['reghrs']:''),
					"acthrs"		=> (!empty($row['acthrs']) ?$row['acthrs']:''),
					"excess"		=> (!empty($row['excess']) ?$row['excess']:''),
					"ot"			=> (!empty($row['ot']) ?$row['ot']:''),
					"idleavesheet"	=> (!empty($row['idleavesheet']) ?$row['idleavesheet']:''),				
					"idotsheet"		=> (!empty($row['idotsheet']) ?$row['idotsheet']:''),
					"idadjsheet"	=> (!empty($row['idadjsheet']) ?$row['idadjsheet']:''),
					"np"			=> (!empty($row['np']) ?$row['np']:''),
					"np_rate"		=> (!empty($row['np_rate']) ?$row['np_rate']:''),
					"id_payperiod"	=> (!empty($row['id_payperiod']) ?$row['id_payperiod']:'')
				);			
			}
		}			
		return $data;
	}
	
	function addToBackuptimesheet($con, $row){
		$Qry3           = new Query();
		$Qry3->table    = "tblbutimesheet";
		$Qry3->selected = "idacct,day,date,idshift,shiftin,shiftout,timein,timeout,late,ut,absent,reghrs,acthrs,excess,ot,np,idleavesheet,idotsheet,idadjsheet,np_rate,id_payperiod";
		$Qry3->fields   = "'".$row['idacct']."',
						   '".$row['day']."',
						   '".$row['date']."',
						   '".$row['idshift']."',
						   '".$row['shiftin']."',
						   '".$row['shiftout']."',
						   '".$row['timein']."',
						   '".$row['timeout']."',
						   '".$row['late']."',
						   '".$row['ut']."',
						   '".$row['absent']."',
						   '".$row['reghrs']."',
						   '".$row['acthrs']."',
						   '".$row['excess']."',
						   '".$row['ot']."',
						   '".$row['np']."',
						   '".$row['idleavesheet']."',
						   '".$row['idotsheet']."',
						   '".$row['idadjsheet']."',
						   '".$row['np_rate']."',
						   '".$row['id_payperiod']."'";
		$checke = $Qry3->exe_INSERT($con);
		if($checke){
			return  true;
		}else{
			return  false;
		}	
	}
	
	function updateTimesheetLate($con, $param, $updateShift=NULL){
		$return_me = 0;
		$pay_period = getPayPeriod($con);
		$shift_cols = array("monday"	=>"idmon,mon,mon_in,mon_out,mon_brkin,mon_brkout", 
							"tuesday"	=>"idtue,tue,tue_in,tue_out,tue_brkin,tue_brkout",
							"wednesday"	=>"idwed,wed,wed_in,wed_out,wed_brkin,wed_brkout",
							"thursday"	=>"idthu,thu,thu_in,thu_out,thu_brkin,thu_brkout",
							"friday"	=>"idfri,fri,fri_in,fri_out,fri_brkin,fri_brkout",
							"saturday"	=>"idsat,sat,sat_in,sat_out,sat_brkin,sat_brkout", 
							"sunday"	=>"idsun,sun,sun_in,sun_out,sun_brkin,sun_brkout");
		//process
		$Qry = new Query();	
		$Qry->table     = "vw_datacurrentworkdates2 as a LEFT JOIN vw_dataholidays AS b ON a.work_date = b.date";
		$Qry->selected  = "a.work_date, a.id AS acct_id, b.id AS holiday_id";
		$Qry->fields    = "a.work_date = '".$param->info->date."' and a.id='".$param->info->idacct."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				if( empty( $updateShift ) ){
					$shift_field= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];				
					$shift_info = getDateShiftData( $con, $row['acct_id'], $shift_field, $row['work_date'] );
				}else{
					$shift_info = getShiftIDData( $con, $updateShift );
				}
				
				$idacct 	= $row['acct_id'];
				$day		= date("l", strtotime($row['work_date']));
				$date		= $row['work_date'];
				
				$idshift	= $shift_info[0];
				$shiftin	= $shift_info[2];
				$shiftout	= $shift_info[3];
				$shiftbrkin	= $shift_info[4];
				$shiftbrkout= $shift_info[5];
				$in			= '';
				$out		= '';
				$date_in 	= $row['work_date'];
				$date_out 	= $row['work_date'];
				$date_shiftin 	= $row['work_date'];
				$date_shiftout 	= $row['work_date'];
				$date_shiftbrkin= $row['work_date'];
				$date_shiftbrkout= $row['work_date'];
										
				$regconti=0;
				
				if( strtotime( $date_shiftin.' '.$shiftin ) > strtotime( $date_shiftout.' '.$shiftout ) ){
					$date_shiftout 	= date('Y-m-d', strtotime("+1 day", strtotime($date_shiftout)));
				}
				if( strtotime( $date_shiftbrkin.' '.$shiftbrkin ) > strtotime( $date_shiftbrkout.' '.$shiftbrkout ) ){
					$date_shiftbrkout 	= date('Y-m-d', strtotime("+1 day", strtotime( $date_out )));
				}
				
				if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
					$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
					$regconti=1;
				}else{
					$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin))) / ( 60 * 60 ));
					$regconti=2;
				}
				
				if( strtotime($date_shiftout.' '.$shiftout) < strtotime($date_shiftin.' '.$shiftin) ){
					$date_out = date('Y-m-d', strtotime("+1 day", strtotime( $row['work_date']  )));
					$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin))) / ( 60 * 60 ));
					$regconti=99;
				}		
				
				$acthrs		= '0';
				$excess		= '0';
				$absent		= $reghrs;
				$late		= '0';
				$ut			= '0';
				$ot 		= '0';
				$np 		= '0';
				
				//Adjustment Applications
				$aa_data = getAttendanceApplication( $con, $row['work_date'], $row['acct_id'], $pay_period['pay_start'], date("Y-m-d", strtotime('+'.$pay_period['grace_hour'].' hours', strtotime($pay_period['pay_end']))));
				
				//Leave Applications
				$leave_data = getLeaveApplication( $con, $row['work_date'], $row['acct_id'],  $pay_period['pay_start'], date("Y-m-d", strtotime('+'.$pay_period['grace_hour'].' hours', strtotime($pay_period['pay_end'])))  );
				if( !empty($leave_data['id']) ){
					if( (int)$leave_data['idtype'] == 1 ){ //PAID LEAVE
						$reghrs		= $leave_data['hrs'];
						$absent		= $reghrs - $acthrs ;
					}else{
						$absent		= $leave_data['hrs'];
						$acthrs		= '0';
						$reghrs		= '0';
					}
				}
				if( !empty( $row['holiday_id'] ) ){ // DETERMINE IF HOLIDAY
					if( (int)getBeforeHoliday($con, $row['work_date'], $row['acct_id']) > 0 ){				
						$reghrs		= '0';
						if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
							$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
						}else{
							$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
						}
					}else{
						if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
							$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
							$regconti=4;
						}else{
							$reghrs		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
							$regconti=5;
						}
						$absent		= '0';
					}
				}
				
				$Qry2 = new Query();	
				$Qry2->table     = "vw_datatimelogs";
				$Qry2->selected  = "acct_id, work_date, date_in, time_in, date_out, CASE WHEN STR_TO_DATE(CONCAT(date_out,' ', time_out), \"%M %d %Y\") < STR_TO_DATE(CONCAT(date_in,' ', time_in), \"%M %d %Y\") THEN NULL ELSE time_out END AS time_out";
				$Qry2->fields    = "work_date = '".$row['work_date']."' AND acct_id='".$row['acct_id']."' ";
				$rs2 = $Qry2->exe_SELECT($con);
				if(mysqli_num_rows($rs2)>= 1){
					$filter_acthrs=0;
					if($row2=mysqli_fetch_array($rs2)){	
						$date_in	= $row2['date_in'];
						$date_out	= $row2['date_out'];
						if( !empty($aa_data['id']) ){
							if( empty( $row2['time_in'] ) ){
								$in			= $aa_data['stime'];
								$date_in	= $aa_data['date'];
							}else{
								$in			= $row2['time_in'];
								$date_in	= $row2['date_in'];
							}					
							if( empty( $row2['time_out'] ) ){
								$out		= $aa_data['ftime'];
								$date_out	= $aa_data['date'];
							}else{						
								$out		= $row2['time_out'];
								$date_out	= $row2['date_out'];						
							}
							if( !empty($out) && !empty($in) ){
								$acthrs		= sprintf('%0.2f', (strtotime($date_out.' '.$out) - strtotime($date_in.' '.$in)) / ( 60 * 60 ));
							}
							$absent		= '0';						
						}
						if( empty($leave_data['id']) &&  empty($aa_data['id']) ){	
							$in			= $row2['time_in'];
							$out		= $row2['time_out'];
							$date_in	= $row2['date_in'];
							$date_out	= $row2['date_out'];
							if( !empty($out) && !empty($in) ){
								$acthrs		= sprintf('%0.2f', (strtotime($date_out.' '.$out) - strtotime($date_in.' '.$in)) / ( 60 * 60 ));
							}
							$absent		= '0';	
						}
						if( !empty( $row['holiday_id'] ) ){ // DETERMINE IF HOLIDAY
							if( (int)getBeforeHoliday($con, $row['work_date'], $row['acct_id']) > 0 ){
								if( ( (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ) ) >= 9 ){
									$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) - (strtotime($date_shiftbrkout.' '.$shiftbrkout) - strtotime($date_shiftbrkin.' '.$shiftbrkin))) / ( 60 * 60 ));
								}else{
									$absent		= sprintf('%0.2f',((strtotime($date_shiftout.' '.$shiftout) - strtotime($date_shiftin.' '.$shiftin)) ) / ( 60 * 60 ));
								}
								$reghrs		= '0';
								$acthrs		= '0';
								$excess		= '0';						
								$late		= '0';
								$ut			= '0';
							}else{
								$absent		= '0';
								$reghrs		= '0';
								$acthrs		= '0';
								$excess		= '0';						
								$late		= '0';
								$ut			= '0';
							}
						}
						if( empty($leave_data['id'])  ){
							//LATES
							$condi=0;
							if( strtotime( $date_in.' '.$in ) > strtotime( $date_shiftin.' '.$shiftin ) && strtotime( $date_in.' '.$in ) < strtotime( $date_shiftout.' '.$shiftout ) ){ // TIME IN IS GREATER THAN SHIFT IN
								$late   = sprintf('%0.2f', (strtotime($date_in.' '.$in) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 )); $condi=1;
							}
							if( !empty($shiftbrkout) && strtotime( $date_in.' '.$in ) > strtotime( $date_shiftbrkout.' '.$shiftbrkout ) ){ // TIMEIN GREATER THAN BREAKOUT
								$late   = sprintf('%0.2f', (strtotime($date_in.' '.$in) - strtotime($date_shiftbrkout.' '.$shiftbrkout)) / ( 60 * 60 ));
								$absent = sprintf('%0.2f', (strtotime($date_shiftbrkin.' '.$shiftbrkin) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 )); $condi=2;
							}
							if( !empty($shiftbrkin) && !empty($shiftbrkout) && 
								((strtotime( $date_in.' '.$in ) >= strtotime( $date_shiftbrkin.' '.$shiftbrkin ) && strtotime( $date_in.' '.$in ) <= strtotime( $date_shiftbrkout.' '.$shiftbrkout )) || 
								(strtotime( $date_out.' '.$out ) >= strtotime( $date_shiftbrkin.' '.$shiftbrkin ) && strtotime( $date_out.' '.$out ) <= strtotime( $date_shiftbrkout.' '.$shiftbrkout )))  ){ // TIMEIN IN BETWEEN BREAK SCHEDULE
								$late	= '0'; $condi=3;
								if( strtotime( $date_in.' '.$in ) > strtotime( $date_shiftin.' '.$shiftin ) && strtotime( $date_in.' '.$in ) < strtotime( $date_shiftout.' '.$shiftout ) ){ // TIME IN IS GREATER THAN SHIFT IN
									$late   = sprintf('%0.2f', (strtotime($date_in.' '.$in) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 )); $condi=4;
								}						
								$absent = sprintf('%0.2f', (strtotime($date_shiftbrkin.' '.$shiftbrkin) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ));
							}					
							if( strtotime( $date_in.' '.$in ) >  strtotime( $date_shiftout.' '.$shiftout ) ){
								$absent = $reghrs;
								$acthrs	= '0';
								$excess	= '0';
							}				
							//UNDERTIME
							if( strtotime( $date_shiftout.' '.$shiftout ) > strtotime( $date_out.' '.$out ) ){	// TIME OUT IS LESSER THAN SHIFT OUT
								$ut   	= sprintf('%0.2f', (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_out.' '.$out)) / ( 60 * 60 ));
							}
							if( strtotime($date_out.' '.$out) >= strtotime($date_shiftbrkin.' '.$shiftbrkin) && strtotime($date_out.' '.$out) <= strtotime($date_shiftbrkout.' '.$shiftbrkout)  ){ // TIME OUT BETWEEN BREAK SCHEDULE
								$ut   	= '0';
								
							}	
							if( strtotime( $date_shiftbrkin.' '.$shiftbrkin ) > strtotime( $date_out.' '.$out ) ){	// TIME OUT IS LESSER THAN BREAK IN
								$absent = $reghrs;
								$ut		= '0';
								$late	= '0';
								$acthrs	= '0';
								$regconti=6;
							}
							if( ( strtotime( $date_in.' '.$in ) >= strtotime( $date_shiftbrkin.' '.$shiftbrkin ) && strtotime( $date_in.' '.$in ) <= strtotime( $date_shiftbrkout.' '.$shiftbrkout )) && strtotime($date_out.' '.$out) < strtotime($date_shiftout.' '.$shiftout) ){ // TIME IN BETWEEN BREAK SCHEDULE AND TIME OUT LESS THAN SHIFT SCHED
								$absent = $reghrs;
								$ut		= '0';
								$late	= '0';
								$acthrs	= '0';
								$regconti=7;
							}	
						}
					}
				}else{
					if( !empty($aa_data['id']) ){
						$in			= $aa_data['stime'];
						$out		= $aa_data['ftime'];
						$date_in	= $aa_data['date'];
						$date_out	= $aa_data['date'];
						if( !empty($out) && !empty($in) ){
							$acthrs		= sprintf('%0.2f', (strtotime($date_out.' '.$out) - strtotime($date_in.' '.$in)) / ( 60 * 60 ));
						}
						$absent		= '0';	
					}			
					if( !empty($aa_data['id']) ){
						if( strtotime( $date_in.' '.$in ) > strtotime( $date_shiftin.' '.$shiftin ) ){
							$late   = sprintf('%0.2f', (strtotime($date_in.' '.$in) - strtotime($date_shiftin.' '.$shiftin)) / ( 60 * 60 ));
						}
						if( strtotime( $date_shiftout.' '.$shiftout ) > strtotime( $date_out.' '.$out ) ){
							$ut   	= sprintf('%0.2f', (strtotime($date_shiftout.' '.$shiftout) - strtotime($date_out.' '.$out)) / ( 60 * 60 ));
						}
					}
				}
				if( strtotime( $acthrs ) > strtotime($reghrs) ){
					$excess	= sprintf('%0.2f', (strtotime($date_out.' '.$out) - strtotime($date_shiftout.' '.$shiftout)) / ( 60 * 60 ));			
				}
				if( $excess < 0 ){ $excess=0; }
				
				// REG HOURS MINUS LATE, UT, ABSENT
				if( empty($leave_data['id'])  ){
					if( $reghrs > 0 ){
						$reghrs	= $reghrs - ( $late + $ut + $absent );
					}			
					$reghrs = sprintf('%0.2f', $reghrs );
				}
				
				// NIGHT PREMIUM
				$nightp = getNightPremium($con);
				$workd  = $row['work_date'];
				$date_shift_out = $date_shiftout;
				$date_night_p   = $date_shiftin;		
				$np	= '0';
				if( !empty( $date_out ) && !empty( $date_in ) && $absent == '0'){			
					if( strtotime( $date_out. ' ' .$out ) > strtotime($date_night_p . ' ' . $nightp) ){
						if( strtotime( $date_shiftout. ' ' .$shiftout ) > strtotime($date_out. ' ' .$out) ){
							$np	= sprintf('%0.2f', ( strtotime( $date_out. ' ' .$out ) - strtotime($date_night_p . ' ' . $nightp)) / ( 60 * 60 ));
						}else{
							$np	= sprintf('%0.2f', ( strtotime( $date_shiftout. ' ' .$shiftout ) - strtotime($date_night_p . ' ' . $nightp)) / ( 60 * 60 ));
						}
					}
				}

				if( $idshift == 4  ){	// REST DAY ID
					$reghrs	= '0';
					$acthrs	= '0';
					$excess	= '0';
					$absent	= '0';
					$late	= '0';
					$ut		= '0';
					$np 	= '0';
				}
				
				// OT HOUR CALCULATION
				$ot_data = getOtApplication( $con, $row['work_date'], $row['acct_id'], $pay_period['pay_start'], date("Y-m-d", strtotime('+'.$pay_period['grace_hour'].' hours', strtotime($pay_period['pay_end'])))  );
				$ot_type = "W";
				if( !empty($ot_data['id']) ){
					$ot = $ot_data['hrs'];
					//Check Holiday in vw_dataholidays using ot_date and get ot_type
					$ot_type = getHolidayAlias($con,$ot_data['date']);
					if(  $ot_data['shift_id'] == 4 ){				
						if($ot_type == 'L'){
							$ot_type = 'A';
						}elseif($ot_type == 'S'){
							$ot_type = 'B';
						}elseif($ot_type == 'Y'){
							$ot_type = 'D';
						}else{
							$ot_type = 'R';
						}				
					}
					//CASE WHEN and get id,rate, compute ot hours in tblclasstrans
					$classtrans = getClassTrans ($con,$ot_type);

					foreach($classtrans as $key => $value){
						if ($value['determine'] == 'N') {
							//himo functio to insert in tblotbreakdown
							if($ot <= 8){
								insertOtRate($con,$ot_data['id'],$ot,$value['rate'],'N');						
							}else{
								insertOtRate($con,$ot_data['id'],8,$value['rate'],'N');						
							}					
						}

						if($value['determine'] == 'G'){
							if($ot <= 8){						
								insertOtRate ($con,$ot_data['id'],0,$value['rate'],'G');					
							}else{
								insertOtRate($con,$ot_data['id'],$ot-8,$value['rate'],'G');
							}
						}

						if($value['determine'] == 'P'){			
							$ot_start = $ot_data['sdate']." ".$ot_data['overtime_stime'];
							$ot_end   = $ot_data['fdate']." ".$ot_data['overtime_ftime'];
							$ot_mins  = ((strtotime($ot_end) - strtotime($ot_start)) / 60);
							$npStart = $ot_data['sdate']." ".$nightp;
							
							$nphours = (strtotime( date('Y-m-d H:i:s', strtotime($ot_start . " +".$ot_mins." minutes")) ) - strtotime($npStart)) / 3600;
							if( floatval($nphours) > 0 ){
								if( floatval($nphours) >= 8 ){
									$nphours = 8;
								}
							}else{
								$nphours = 0;
							}
							insertOtRate ($con,$ot_data['id'],$nphours,$value['rate'],'P');
						}			 
					}
				}
				
				$data = array();

				$data = array(
					"idacct" 		=>	$idacct, 	
					"day"			=>	$day,		
					"date"			=>	$date,		
					"idshift"		=>	$idshift,	
					"shiftin"		=>	$shiftin,	
					"shiftout"		=>	$shiftout,	
					"in"			=>	$in,			
					"out"			=>	$out,				
					"reghrs"		=>	sprintf('%0.2f',$reghrs),		
					"acthrs"		=>	sprintf('%0.2f',$acthrs),		
					"excess"		=>	sprintf('%0.2f',$excess),		
					"absent"		=>	sprintf('%0.2f',$absent),
					"late"			=>  sprintf('%0.2f',$late),
					"ut"			=>  sprintf('%0.2f',$ut),
					"ot"			=>  sprintf('%0.2f',$ot),
					"np"			=>  sprintf('%0.2f',$np),
					"id_payperiod"  =>  $pay_period['id'],
					"date_in"		=>  $date_in,
					"date_out"		=>  $date_out
				);


				
				if( checkTimeSheetExists($con, $idacct, $date ) ){			 
					$return = updateTimeSheetRec($con, $data);
					$return_me = 1;
				}
			}			
		}
		return $return_me;
	}
	
	function insertTimeSheetRec($con, $row){
		$Qry3           = new Query();
		$Qry3->table    = "tbltimesheet";
		$Qry3->selected = "idacct,day,date,idshift,shiftin,shiftout,timein,timeout,late,ut,absent,reghrs,acthrs,excess,ot,np,id_payperiod,date_in, date_out";
		$Qry3->fields   = "'".$row['idacct']."',
						   '".$row['day']."',
						   '".$row['date']."',
						   '".$row['idshift']."',
						   '".$row['shiftin']."',
						   '".$row['shiftout']."',
						   '".$row['in']."',
						   '".$row['out']."',
						   '".$row['late']."',
						   '".$row['ut']."',
						   '".$row['absent']."',
						   '".$row['reghrs']."',
						   '".$row['acthrs']."',
						   '".$row['excess']."',
						   '".$row['ot']."',
						   '".$row['np']."',
						   '".$row['id_payperiod']."',
						   '".$row['date_in']."',
						   '".$row['date_out']."'";
		$checke = $Qry3->exe_INSERT($con);
		if($checke){
			return  json_encode(array("status"=>"success","err"=>$row));
		}else{
			return  json_encode(array('status'=>'error',"err"=>mysqli_error($con)));
		}	
	}
	
	function updateTimeSheetRec($con, $row){
		$Qry3           = new Query();
		$Qry3->table    = "tbltimesheet";
		$Qry3->selected = " idacct	='".$row['idacct']."',
							day		='".$row['day']."',
							date	='".$row['date']."',
							idshift	='".$row['idshift']."',
							shiftin	='".$row['shiftin']."',
							shiftout='".$row['shiftout']."',
							timein	='".$row['in']."',
							timeout	='".$row['out']."',
							late	='".$row['late']."',
							ut		='".$row['ut']."',
							absent	='".$row['absent']."',
							reghrs	='".$row['reghrs']."',
							acthrs	='".$row['acthrs']."',
							excess	='".$row['excess']."',
							ot		='".$row['ot']."',
							np		='".$row['np']."',
							date_in ='".$row['date_in']."',
							date_out='".$row['date_out']."'";
		$Qry3->fields   = "idacct 	= '".$row['idacct']."' AND date='".$row['date']."'";	
		$checke = $Qry3->exe_UPDATE($con);
		if($checke){
			return  json_encode(array("status"=>"success","err"=>$row));
		}else{
			return  json_encode(array('status'=>'error',"err"=>mysqli_error($con)));
		}	
	}
	
	function insertLateApprove($con, $idtimesheet, $date_approve, $pay_date, $id_period){
		$Qry3           = new Query();
		$Qry3->table    = "tblapprovelate";
		$Qry3->selected = "idtimesheet,date_approve, pay_date, id_period";
		$Qry3->fields   = "'".$idtimesheet."',
						   '".$date_approve."',
						   '".$pay_date."',
						   '".$id_period."'";
		$checke = $Qry3->exe_INSERT($con);
		if($checke){
			return  json_encode(array("status"=>"success"));
		}else{
			return  json_encode(array('status'=>'error',"err"=>mysqli_error($con)));
		}	
	}

	function checkLateApproveExists( $con, $idtimesheet){
		$Qry = new Query();	
		$Qry->table     = "tblapprovelate";
		$Qry->selected  = "*";
		$Qry->fields    = "idtimesheet='".$idtimesheet."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}
	
	function checkTimeSheetExists($con, $idacct, $date){
		$Qry = new Query();	
		$Qry->table     = "tbltimesheet";
		$Qry->selected  = "*";
		$Qry->fields    = "idacct = '".$idacct."' AND date='".$date."'";	
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}

	function getTimesheetPayPeriod( $con, $date ){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tbltimesheet";
		$Qry->selected  = "id_payperiod";
		$Qry->fields    = "date='".$date."' ORDER BY id ASC limit 1";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				return $row['id_payperiod'];
			}
		}
		return 0;
	}
	
	function checkCurrentStatus($con, $tbl, $id){
		$Qry = new Query();	
		$Qry->table     = $tbl;
		$Qry->selected  = "stat";
		$Qry->fields    = "id='".$id."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['stat'];
			}
		}
		return 0;
	}
	
	function getSumInfo($con,$pay_period,$acct_id,$col){
		$Qry = new Query();	
		$Qry->table     = "vw_data_timesheet";
		$Qry->selected  = "IFNULL(SUM(".$col."),0) as ctr";
		$Qry->fields    = "empID='".$acct_id."' AND work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){ 				
				return $row['ctr'];
			}
		}
		return 0;
	}
	
	function getSumInfoAALate($con,$pay_period,$acct_id,$col){
		$Qry = new Query();	
		$Qry->table     = "vw_data_timesheet";
		$Qry->selected  = "IFNULL(SUM(".$col."),0) as ctr";
		$Qry->fields    = "empID='".$acct_id."' AND adj_stat=1 AND (adj_date_approve BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND work_date < '".$pay_period['pay_start']."' ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){ 				
				return $row['ctr'];
			}
		}
		return 0;
	}
	
	function getSumInfo2($con,$pay_period,$acct_id,$col1,$col2,$rate,$addCol){
		$Qry = new Query();	
		$Qry->table     = "vw_data_timesheet";
		$Qry->selected  = "IFNULL(SUM(".$col1."),0) as ctr";
		$Qry->fields    = "empID='".$acct_id."' AND ".$col2."=".$rate." AND (work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') ";
		if( !empty( $addCol ) ){
			$Qry->fields  = $Qry->fields." AND ".$addCol."='1' ";
		}
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				return $row['ctr'];
			}
		}
		return 0;
	}
	
	function getSumInfoLate($con,$pay_period,$acct_id,$col1,$col2,$rate,$addCol=NULL){
		$Qry = new Query();	
		$Qry->table     = "vw_data_timesheet";
		$Qry->selected  = "IFNULL(SUM(".$col1."),0) as ctr";
		$Qry->fields    = "empID='".$acct_id."' AND ".$col2."='".$rate."' AND (ot_date_approve BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND ot_date < '".$pay_period['pay_start']."' ";
		if( !empty( $addCol ) ){
			$Qry->fields  = $Qry->fields." AND ".$addCol."='1' ";
		}
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				return $row['ctr'];
			}
		}
		return 0;
	}
	
	function insertPayroll($con,$id_acct,$dept_id,$class_id,$trans_id,$id_paydate,$date_process,$units,$unit_amt,$tot_amt,$pay_date){
		$Qry2 			= new Query();	
		$Qry2->table	= "tblpayroll";
		$Qry2->selected = "id_acct,dept_id,class_id,trans_id,id_paydate,date_process,units,unit_amt,tot_amt,pay_date";
		$Qry2->fields 	= "'".$id_acct."','".$dept_id."','".$class_id."','".$trans_id."','".$id_paydate."','".$date_process."','".$units."','".$unit_amt."','".$tot_amt."','".$pay_date."'";
		$checke2 		= $Qry2->exe_INSERT($con);
		if($checke2){
			return 1;
		}else{
			return 0;
		}
	}
	
	function getRateCtrLate( $con,$pay_period, $id_acct, $cols ){
		$data = array();
		$Qry = new Query();	
		$Qry->table     = "vw_data_timesheet";
		$Qry->selected  = $cols;
		$Qry->fields    = "empID='".$id_acct."' AND ot_date_approve BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' GROUP BY ".$cols;
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array(
					"rate" => $row[$cols]
				);
			}
		}
		return $data;
	}
	
	function getRateCtr( $con,$pay_period, $id_acct, $cols ){
		$data = array();
		$Qry = new Query();	
		$Qry->table     = "vw_data_timesheet";
		$Qry->selected  = $cols;
		$Qry->fields    = "empID='".$id_acct."' AND work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' GROUP BY ".$cols;
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array(
					"rate" => $row[$cols] ? $row[$cols] : ''
				);
			}
		}
		return $data;
	}
	
	function getOTClassSetUp(){
		return array(
			array("col_rate" => "reg_ot_rate"		,	"col_unit" => "reg_ot"		, 	"tans_id" => '1'	),						
			array("col_rate" => "reg_ot8_rate"		,	"col_unit" => "reg_ot8"		, 	"tans_id" => '2'	),
			array("col_rate" => "reg_np_rate"		,	"col_unit" => "reg_np"		, 	"tans_id" => '65'	),						
			array("col_rate" => "rd_ot_rate"		,	"col_unit" => "rd_ot"		, 	"tans_id" => '7'	),						
			array("col_rate" => "rd_ot8_rate"		,	"col_unit" => "rd_ot8"		, 	"tans_id" => '8'	),
			array("col_rate" => "rd_np_rate"		,	"col_unit" => "rd_np"		, 	"tans_id" => '21'	),						
			array("col_rate" => "legal_hol_rate"	,	"col_unit" => "legal_hol"	, 	"tans_id" => '5'	),						
			array("col_rate" => "legal_hol8_rate"	,	"col_unit" => "legal_hol8"	, 	"tans_id" => '6'	),
			array("col_rate" => "legal_np_rate"		,	"col_unit" => "legal_np"	, 	"tans_id" => '19'	),						
			array("col_rate" => "spcl_hol_rate"		,	"col_unit" => "spcl_hol"	, 	"tans_id" => '3'	),						
			array("col_rate" => "spcl_hol8_rate"	,	"col_unit" => "spcl_hol8"	, 	"tans_id" => '4'	),						
			array("col_rate" => "spcl_np_rate"		,	"col_unit" => "spcl_np"		, 	"tans_id" => '17'	),						
			array("col_rate" => "legal_rd_rate"		,	"col_unit" => "legal_rd"	, 	"tans_id" => '9'	),						
			array("col_rate" => "legal_rd8_rate"	,	"col_unit" => "legal_rd8"	, 	"tans_id" => '10'	),
			array("col_rate" => "legal_rd_np_rate"	,	"col_unit" => "legal_rd_np"	, 	"tans_id" => '20'	),						
			array("col_rate" => "spcl_rd_rate"		,	"col_unit" => "spcl_rd"		, 	"tans_id" => '11'	),						
			array("col_rate" => "spcl_rd8_rate"		,	"col_unit" => "spcl_rd8"	, 	"tans_id" => '12'	),
			array("col_rate" => "spcl_rd_np_rate"	,	"col_unit" => "spcl_rd_np"	, 	"tans_id" => '18'	),						
			array("col_rate" => "sl_hol_rate"		,	"col_unit" => "sl_hol"		, 	"tans_id" => '13'	),						
			array("col_rate" => "sl_hol8_rate"		,	"col_unit" => "sl_hol8"		, 	"tans_id" => '14'	),
			array("col_rate" => "sl_np_rate"		,	"col_unit" => "sl_np"		, 	"tans_id" => '64'	),						
			array("col_rate" => "sl_rd_rate"		,	"col_unit" => "sl_rd"		, 	"tans_id" => '22'	),						
			array("col_rate" => "sl_rd8_rate"		,	"col_unit" => "sl_rd8"		, 	"tans_id" => '23'	),
			array("col_rate" => "sl_rd_np_rate"		,	"col_unit" => "sl_rd_np"	, 	"tans_id" => '63'	)					
		);
	}
	
	function getPaidLeaveClassSetUp($con){
		$data = array();
		$Qry = new Query();	
		$Qry->table     = "tblleaves";
		$Qry->selected  = "id,idclasstrans";
		$Qry->fields    = "active='Y' AND idtype='1'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array(
					"id" 		=> $row['id'],
					"trans_id"	=> $row['idclasstrans']
				);
			}
		}
		return $data;
	}
	
	function getSumLateLeave($con,$pay_period,$id_acct,$idleave){
		$Qry = new Query();	
		$Qry->table     = "vw_leave_application AS a";
		$Qry->selected  = "IFNULL(SUM(a.hrs),0) as ctr";
		$Qry->fields    = "idleave='".$idleave."' AND stat=1 AND idacct='".$id_acct."' AND (a.date_approve BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' ) AND id_payperiod='".$pay_period['id']."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				return $row['ctr'];
			}
		}
		return 0;
	}
	
	function getSumLeave($con,$pay_period,$id_acct,$idleave){
		$Qry = new Query();	
		$Qry->table     = "vw_leave_application AS a";
		$Qry->selected  = "IFNULL(SUM(a.hrs),0) as ctr";
		$Qry->fields    = "idleave='".$idleave."' AND stat=1 AND idacct='".$id_acct."' 
							AND ( a.date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' ) 
							AND (a.date_approve BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."' )";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				return $row['ctr'];
			}
		}
		return 0;
	}
	
	function transAddBase($con){
		return "'58','16','1','2','65','7','8','21','5','6','19','3','4','17','9','10','20','11','12','18','13','14','64','22','23','63','42','43','48'";
	}
	
	function transDeductions($con){
		return "'33','34','40'";
	}
	
	function transContribution($con){
		return "'61','56','53'";
	}
	
	function getPayrollSumBase($con,$id_paydate,$id_acct){
		$trans_id = transAddBase($con);
		
		$Qry = new Query();	
		$Qry->table     = "tblpayroll";
		$Qry->selected  = "IFNULL(SUM(tot_amt),0) as ctr";
		$Qry->fields    = "trans_id IN (".$trans_id.") AND id_acct='".$id_acct."' AND id_paydate='".$id_paydate."' /*AND idstatus=1*/";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				return $row['ctr'];
			}
		}
		return 0;
	}
	
	function getPayrollSumDeduction($con,$id_paydate,$id_acct){
		$trans_id = transDeductions($con);
		
		$Qry = new Query();	
		$Qry->table     = "tblpayroll";
		$Qry->selected  = "IFNULL(SUM(tot_amt),0) as ctr";
		$Qry->fields    = "trans_id IN (".$trans_id.") AND id_acct='".$id_acct."' AND id_paydate='".$id_paydate."' /*AND idstatus=1*/";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				return $row['ctr'];
			}
		}
		return 0;
	}
	
	function getSSSCont($con,$sal){
		$data = array();
		if( $sal < 2000 ){
			$sal = 2000;
		}
		$Qry = new Query();	
		$Qry->table     = "tblcont_sss";
		$Qry->selected  = "*";
		$Qry->fields    = "sal_credit <= ".$sal." AND yr_use='2020-01-01' ORDER BY id DESC LIMIT 1";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data = array(
					"id" 		=> $row['id'],
					"credit"	=> $row['sal_credit'],
					"sssemp"	=> $row['empcont'],
					"ssscom"	=> $row['emprcont'],
					"sssecc"	=> $row['ecc']
				);
			}
		}
		return $data;
	}
	
	function getIBIGCont($con,$sal){
		$data = array();
		if( $sal < 5000 ){
			$sal = 5000;
		}
		$Qry = new Query();	
		$Qry->table     = "tblcont_ibig";
		$Qry->selected  = "*";
		$Qry->fields    = "sal <= ".$sal." AND yr_use='2020-01-01' ORDER BY id DESC LIMIT 1";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data = array(
					"id" 		=> $row['id'],
					"credit"	=> $row['sal'],
					"pgibem"	=> ($row['sal'] * $row['ee']),
					"pgibco"	=> ($row['sal'] * $row['er'])
				);
			}
		}
		return $data;
	}
	
	function getHLTHCont($con,$sal){
		$data = array();		
		$Qry = new Query();	
		$Qry->table     = "tblcont_health";
		$Qry->selected  = "*";
		$Qry->fields    = "sal <= ".$sal." AND yr_use='2020-01-01' ORDER BY id DESC LIMIT 1";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data = array(
					"id" 		=> $row['id'],
					"credit"	=> $row['sal'],
					"hlthemp"	=> (($sal * $row['ee']) + ($row['fix_amt'])),
					"hlthcom"	=> (($sal * $row['er']) + ($row['fix_amt']))
				);
			}
		}
		return $data;
	}
	
	function getWITHTAX($con,$sal,$filter){
		$data = array();		
		$Qry = new Query();	
		$Qry->table     = "tblcont_bir";
		$Qry->selected  = "*";
		$Qry->fields    = "mini <= ".$sal." AND stat = '".$filter."' AND yr_use='2020-01-01' ORDER BY id DESC LIMIT 1";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data = array(
					"id" 		=> $row['id'],
					"mode" 		=> $row['mode'],
					"stat" 		=> $row['stat'],
					"mini" 		=> $row['mini'],
					"fix_amt"	=> $row['fix_amt'],
					"multi"		=> $row['multi'],
					"yr_use"	=> $row['yr_use']
				);
			}
		}
		return $data;
	}
	
	function getPayrollSumTax($con,$gross, $filter){
		$arrTax  = getWITHTAX($con,$gross, $filter);
		$withTax = ((( $gross - $arrTax['mini'] ) * $arrTax['multi']) + $arrTax['fix_amt']);
		return $withTax;
	}
	
	function getPayrollSumContribution($con,$id_acct,$id_paydate){
		$trans_id = transContribution($con);
		$Qry = new Query();	
		$Qry->table     = "tblpayroll";
		$Qry->selected  = "IFNULL(SUM(tot_amt),0) as ctr";
		$Qry->fields    = "trans_id IN (".$trans_id.") AND id_acct='".$id_acct."' AND id_paydate='".$id_paydate."' /*AND idstatus=1*/";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				return $row['ctr'];
			}
		}
		return 0;		
	}
	
	function checkPayDateInserted($con,$id_paydate){
		$Qry = new Query();	
		$Qry->table     = "tblpayroll";
		$Qry->selected  = "idstatus";
		$Qry->fields    = "id_paydate='".$id_paydate."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				if( (int)$row['idstatus'] == 1 ){
					return true;
				}else{
					return false;
				}
			}
		}
		return true;
	}
	
	function getWithTaxAcct($con,$param){
		$Qry = new Query();	
		$Qry->table     = "vw_eepayslip";
		$Qry->selected  = "tot_amt";
		$Qry->fields    = "id_acct='".$param->id_acct."' AND id_paydate='".$param->id_paydate."' AND trans_id=62 ORDER BY id ASC";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				return number_format(sprintf('%0.2f',$row['tot_amt']),2);
			}
		}
		return 0.00;
	}
	
	function getPaySlipEarnings($con,$param){
		$data = array();		
		$Qry = new Query();	
		$Qry->table     = "vw_eepayslip";
		$Qry->selected  = "*";
		$Qry->fields    = "id_acct='".$param->id_acct."' AND id_paydate='".$param->id_paydate."' ORDER BY id ASC";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				if( !empty( $row['units'] ) ){
					$row['units'] = sprintf('%0.2f',$row['units']);
				}
				$data[] = array(
					"id"			=> $row['id'],
					"class"			=> $row['class_id'],
					"trans"			=> $row['trans_id'],
					"alias"			=> $row['alias'],
					"description"	=> $row['description'],
					"units" 		=> $row['units'],
					"unit_amt" 		=> $row['unit_amt'],
					"tot_amt" 		=> number_format(sprintf('%0.2f',$row['tot_amt']),2)
				);
			}
		}
		return $data;
	}
	
	function getLeaveUsed($con, $idacct, $idleave){
		$Qry = new Query();	
		$Qry->table     = "tbltimeleaves";
		$Qry->selected  = "SUM(hrs) as ctr";
		$Qry->fields    = "idacct='".$idacct."' AND idleave='".$idleave."' AND stat in (1,3) ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				return $row['ctr'];
			}
		}
		return 0;
	}
	
	function getAccountLeavesInfo($con,$idleave,$idacct){
		$data= array();
		$Qry = new Query();	
		$Qry->table     = "tblaccountleaves";
		$Qry->selected  = "*";
		$Qry->fields    = "idacct='".$idacct."' AND idleave='".$idleave."' ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data = array(
					"id" 			=> $row['id'],
					"idacct" 		=> $row['idacct'],
					"idleave" 		=> $row['idleave'],
					"entitle" 		=> $row['entitle'],
					"used" 			=> $row['used'],
					"balance" 		=> $row['balance'],
					"pending_bal" 	=> $row['pending_bal']
				);
			}
		}
		return $data;
	}
	
	function getMonthEnd($con,$id_payperiod){
		$Qry = new Query();	
		$Qry->table     = "tblpayperiod";
		$Qry->selected  = "month_end";
		$Qry->fields    = "id='".$id_payperiod."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return (int)date_format(date_create($row['month_end']),"m") - 1;
			}
		}
		return 0;
	}
	
	function getSumYTD($con, $classid, $id_acct){
		$Qry 			= new Query();	
		$Qry->table     = "tblpayroll";
		$Qry->selected  = "SUM(tot_amt) as ctr";
		$Qry->fields    = "class_id IN (".$classid.") AND idstatus=2 AND id_acct = ".$id_acct;
		$rs 			= $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return sprintf('%0.2f',$row['ctr']);
			}
		}
		return 0;
	}
	
	function checkYTDExist($con, $id_acct, $transid, $pay_yr){
		$Qry 			= new Query();	
		$Qry->table     = "tblpayrolltotal";
		$Qry->selected  = "id";
		$Qry->fields    = "id_acct = '".$id_acct."' AND trans_id='".$transid."' AND pay_yr='".$pay_yr."' ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['id'];
			}
		}
		return 0;
	}
	
	function insertYTD( $con,$id_acct,$dept_id,$pay_yr,$trans_id,$cols,$amt ){
		$Qry2 			= new Query();	
		$Qry2->table	= "tblpayrolltotal";
		$Qry2->selected = "id_acct, dept_id, pay_yr, trans_id, ".$cols;
		$Qry2->fields 	= "'".$id_acct."', '".$dept_id."', '".$pay_yr."', '".$trans_id."', '".$amt."'";
		return $Qry2->exe_INSERT($con);
	}
	
	function updateYTD( $con,$idpaytot,$cols,$amt ){
		$Qry3           = new Query();
		$Qry3->table    = "tblpayrolltotal";
		$Qry3->selected = $cols."='".$amt."'";
		$Qry3->fields   = "id 	= '".$idpaytot."'";	
		return $Qry3->exe_UPDATE($con);
	}
	
	function sumYTD( $con,$id_acct, $trans_id, $pay_yr ){
		$Qry 			= new Query();	
		$Qry->table     = "tblpayrolltotal";
		$Qry->selected  = "SUM(IFNULL(janamt,0) + IFNULL(febamt,0) + IFNULL(maramt,0) + IFNULL(apramt,0) + IFNULL(mayamt,0) + IFNULL(junamt,0) + IFNULL(julamt,0) + IFNULL(augamt,0) + IFNULL(sepamt,0) + IFNULL(octamt,0) + IFNULL(novamt,0) + IFNULL(decamt,0)) as ctr";
		$Qry->fields    = "id_acct = '".$id_acct."' AND trans_id='".$trans_id."' AND pay_yr='".$pay_yr."' ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return sprintf('%0.2f',$row['ctr']);
			}
		}
		return 0;
	}
	
	function updateSumYTD($con,$id_acct, $trans_id, $pay_yr){
		$amt			= sumYTD( $con,$id_acct, $trans_id, $pay_yr );
		$Qry3           = new Query();
		$Qry3->table    = "tblpayrolltotal";
		$Qry3->selected = "ytd = '".$amt."'";
		$Qry3->fields   = "id_acct = '".$id_acct."' AND trans_id='".$trans_id."' AND pay_yr='".$pay_yr."' ";
		return $Qry3->exe_UPDATE($con);		
	}
	
	function checkApplicationTimelog( $con, $work_date, $acct_id, $table, $where=NULL ){
		$Qry 			= new Query();	
		$Qry->table     = $table;
		$Qry->selected  = "id";
		$Qry->fields    = "idacct = '".$acct_id."' AND date='".$work_date."' ".$where;
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}
	
	function getShiftIDData($con, $idshift){
		$data = array();	
		$Qry = new Query();	
		$Qry->table     = "tblshift";
		$Qry->selected  = "*";
		$Qry->fields    = "id='".$idshift."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				$data = array($row['id'],$row['name'],$row['stime'],$row['ftime'],$row['breakin'],$row['breakout']);
			}
		}
		return $data;
	}
	
	function inserLogs($con, $idacct, $tasks){
		$Qry3           = new Query();
		$Qry3->table    = "tbllogs";
		$Qry3->selected = "idacct,tasks,date_log";
		$Qry3->fields   = "'".$idacct."', '".$tasks."', '".SysDate()." ".SysTime()."'";
		return $Qry3->exe_INSERT($con);	
	}
	
	function getEmployeeName($con, $idacct){
		$Qry 			= new Query();	
		$Qry->table     = "vw_dataemployees";
		$Qry->selected  = "empname";
		$Qry->fields    = "id='".$idacct."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['empname'];
			}
		}
		return '';
	}
	
	function getSumRows($con,$search){
		$data=array();
		$Qry = new Query();	
		$Qry->table ="vw_data_timesheet";
		$Qry->selected ="SUM(late) AS late,SUM(ut) AS ut,SUM(absent) AS absent,SUM(leavehrs) AS leavehrs,SUM(reghrs) AS reg,SUM(ot) AS ot";
		$Qry->fields ="work_date is not null ".$search;
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				$data=array(
					'late'	=> sprintf('%0.2f', $row['late']),
					'ut'	=> sprintf('%0.2f', $row['ut']),
					'absent'=> sprintf('%0.2f', $row['absent']),
					'leave'	=> sprintf('%0.2f', $row['leavehrs']),
					'ot'	=> sprintf('%0.2f', $row['ot']),
					'reg'	=> sprintf('%0.2f', $row['reg'])				
				);
			}
		}else{
			$data=array(
				'late'	=> 0,
				'ut'	=> 0,
				'absent'=> 0,
				'leave'	=> 0,
				'ot'	=> 0,
				'reg'	=> 0
			);
		}
		return $data;
	}
	
	function getDBName($con){
		$Qry 			= new Query();	
		$Qry->table     = "tbldb";
		$Qry->selected  = "*";
		$Qry->fields    = "isorig='1'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return array(
					"id"		=>	$row['id'],
					"dbname"	=>	$row['dbname'],
					"company"	=>	$row['company'],
					"alias"		=>	$row['alias'],
					"iscorp"	=>	$row['iscorp']
				);
			}
		}
		return array();
	}
	
	function getDbs($con, $idacct){
		$data = array();
		$Qry 			= new Query();	
		$Qry->table     = "tbldb AS a LEFT JOIN tblacctglobal AS b ON a.id = b.iddb";
		$Qry->selected  = "a.*, b.idacct";
		$Qry->fields    = "a.id>0 AND b.idacct='".$idacct."' ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array(
					"id"		=>	$row['id'],
					"dbname"	=>	$row['dbname'],
					"company"	=>	$row['company'],
					"alias"		=>	$row['alias'],
					"iscorp"	=>	$row['iscorp']
				);
			}
		}else{
			$data = getDBName($con);
		}
		return $data;
	}
	
	function checkGlobalAcct($con, $iddb, $idacct) {
		$Qry 			= new Query();	
		$Qry->table     = "tblacctglobal";
		$Qry->selected  = "*";
		$Qry->fields    = "iddb='".$iddb."' AND idacct='".$idacct."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return true;
			}
		}
		return false;
	}
	
	function getlastaccountid($con){
		$Qry 			= new Query();	
		$Qry->table     = "tblaccount";
		$Qry->selected  = "id";
		$Qry->fields    = "id>0 ORDER BY id DESC LIMIT 1";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['id'];
			}
		}
		return '0';
	}
	
	function insertGlobalAccount( $con, $idacct, $iddb ){
		if( (int)$idacct > 0 ){
			$Qry 			= new Query();	
			$Qry->table     = "tblacctglobal";
			$Qry->selected  = "iddb,idacct";
			$Qry->fields    = "'".$iddb."','".$idacct."'";
			return $Qry->exe_INSERT($con);
		}
		return '';
	}
	
	function deleteGlobalAccount( $con, $idacct, $iddb ){
		$Qry 			= new Query();	
		$Qry->table     = "tblacctglobal";
		$Qry->fields    = "iddb='".$iddb."' AND idacct='".$idacct."'";
		return $Qry->exe_DELETE($con);
	}
	
	function getMission($con, $idcomp){
		$Qry 			= new Query();	
		$Qry->table     = "tblcompany";
		$Qry->selected  = "mission";
		$Qry->fields    = "id='".$idcomp."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['mission'];
			}
		}
		return '';
	}
	
	function getVision($con, $idcomp){
		$Qry 			= new Query();	
		$Qry->table     = "tblcompany";
		$Qry->selected  = "vision";
		$Qry->fields    = "id='".$idcomp."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['vision'];
			}
		}
		return '';
	}
	
	function getBirthDates( $con, $date_s ){
		$data = array();
		$Qry = new Query();	
		$Qry->table     = "vw_dataemployees";
		$Qry->selected  = "*";
		$Qry->fields    = "DATE_FORMAT(bdate, '%c-%d') = DATE_FORMAT('".$date_s."', '%c-%d') ORDER BY DATE_FORMAT(bdate, '%c-%d') asc";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$path = 'assets/images/undefined.webp';
				if( !empty( $row['pic'] ) ){
					$path = 'assets/php/admin/hr/employee/pix/'.$row['pic'].'?'.time();
				}
				$data[] = array( 
					"empid" => $row['empid'],
					"name" 	=> ucwords(strtolower($row['fname']). ' ' . strtolower($row['lname']) . ' ' . strtolower($row['suffix'])),
					"path"	=> $path
				);
			}
		}
		return $data;
	}
	
	function getCtrFormApprover( $con, $formid ){
		$Qry 			= new Query();	
		$Qry->table     = "tblforms";
		$Qry->selected  = "ctr_approver";
		$Qry->fields    = "id='".$formid."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return (int)$row['ctr_approver'];
			}
		}
		return '1';
	}
	
	function getSuperiorEmail( $con,$idacct ){
		$Qry 			= new Query();	
		$Qry->table     = "vw_dataemployees";
		$Qry->selected  = "superior_email";
		$Qry->fields    = "id = '".$idacct."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['superior_email'];
			}
		}
		return '';
	}
	
	function getAccountName( $con, $idacct ){
		$Qry 			= new Query();	
		$Qry->table     = "vw_dataemployees";
		$Qry->selected  = "empname";
		$Qry->fields    = "id = '".$idacct."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['empname'];
			}
		}
		return '';
	}
	
	function getAccountEmail( $con, $idacct ){
		$Qry 			= new Query();	
		$Qry->table     = "vw_dataemployees";
		$Qry->selected  = "email";
		$Qry->fields    = "id = '".$idacct."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['email'];
			}
		}
		return '';
	}
	
	function chkpendingForm( $con, $tbl, $idacct ){
		$Qry 			= new Query();	
		$Qry->table     = $tbl;
		$Qry->selected  = "id";
		$Qry->fields    = "idstatus=3 AND requestor = '".$idacct."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}
	
	function getLatestRequest( $con, $tbl, $idacct  ){
		$Qry 			= new Query();	
		$Qry->table     = $tbl;
		$Qry->selected  = "*";
		$Qry->fields    = "idstatus=1 AND requestor = '".$idacct."' ORDER BY id DESC LIMIT 1";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return array(
					"currentdeptname"	=> $row['currentdeptname'],
					"newiddept"			=> $row['newiddept'],
					"currentsection"	=> $row['currentsection'],
					"newidsection"		=> $row['newidsection']
				);
			}
		}
		return array();
	}
	
	function checkFormProgress( $con, $formid, $id ){
		$Qry 			= new Query();	
		$Qry->table     = "tblforms01";
		$Qry->selected  = "*";
		$Qry->fields    = "approver1_status is not null and id='".$id."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}
	
	function getapprover( $con, $idunder ){
		$data = array();
		$Qry 			= new Query();	
		$Qry->table     = "tblbunits";
		$Qry->selected  = "*";
		$Qry->fields    = "id='".$idunder."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				return array(
					'id' 			=> $row['id'],
					'unit'			=> $row['name'],
					'alias'			=> $row['alias'],
					'costcenter'	=> $row['costcenter'],
					'idhead'		=> $row['idhead'],
					'deputy1'		=> $row['deputy1'],
					'deputy2'		=> $row['deputy2'],
					'idunder'		=> $row['idunder'],
					'unittype'		=> $row['unittype']
				);
			}
		}
		return $data;
	}
	
	function getapprover2( $con, $idunder ){
		$data = array();
		$Qry 			= new Query();	
		$Qry->table     = "vw_databusinessunits";
		$Qry->selected  = "*";
		$Qry->fields    = "id='".$idunder."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				return array(
					'id' 			=> $row['id'],
					'unit'			=> $row['name'],
					'alias'			=> $row['alias'],
					'idhead'		=> $row['idhead'],
					'idunder'		=> $row['idunder'],
					'unittype'		=> $row['unittype'],
					'isactive'		=> $row['isactive'],
					'stype'			=> $row['stype'],
					'shead'			=> $row['shead'],
					'stat'			=> $row['stat']
				);
			}
		}
		return $data;
	}
	
	function getLatestRequestWageIncrease( $con, $tbl, $idacct  ){
		$Qry 			= new Query();	
		$Qry->table     = $tbl;
		$Qry->selected  = "*";
		$Qry->fields    = "idstatus=1 AND requestor = '".$idacct."' ORDER BY id DESC LIMIT 1";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return array(
					"currentbasepay"			=> $row['currentbasepay'],
					"newbasepay"				=> $row['newbasepay'],
					"currentriceallowance"		=> $row['currentriceallowance'],
					"newriceallowance"			=> $row['newriceallowance'],
					"currentclothingallowance"	=> $row['currentclothingallowance'],
					"newclothingallowance"		=> $row['newclothingallowance']
				);
			}
		}
		return array();
	}
	
	function getCurrentData1( $con, $id ){
		$Qry 			= new Query();	
		$Qry->table     = "vw_data_lateraltransfer";
		$Qry->selected  = "*";
		$Qry->fields    = "id='".$id."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				
				$idunit = $row['newiddept'];
				if( !empty( $row['newidsection'] ) ){
					$idunit = $row['newidsection'];
				}
				
				
				return array(
					'idacct'		=>	$row['requestor'],
					'idpos' 		=>  getIdInfo( $con, $row['newpositiontitle'], "tblposition", "name" ),
					'idlvl' 		=>  getIdInfo( $con, $row['newjoblevel'], "tbljoblvl", "lvl" ),
					'idunit'		=>  $idunit,
					'idlabor'	 	=>  getIdInfo( $con, $row['newlabortype'], "tbllabortype", "type" ),
					'idsuperior' 	=>  $row['newidsuperior'],
					'empstat'	 	=>  getIdInfo( $con, $row['newempstatus'], "tblempstatus", "stat" ),
					'idpaygrp'	 	=>  getIdInfo( $con, $row['newpaygroup'], "tblpaygrp", "group" )
				);
			}
		}
		return array();
	}
	
	function getIdInfo( $con, $val, $tbl, $col ){
		$Qry 			= new Query();	
		$Qry->table     = $tbl . " as a";
		$Qry->selected  = "a.id";
		$Qry->fields    = "a.". $col ."= '". $val ."' ";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['id'];
			}
		}
		return '';
	}
	
	function updateaccountjob( $con, $data ){
		$Qrye 			= new Query();	
		$Qrye->table 	= "tblaccountjob";
		$Qrye->selected = " idpos		=	'".$data['idpos']."',						
							idlvl		=	'".$data['idlvl']."',
							idunit		=	'".$data['idunit']."',
							idlabor		=	'".$data['idlabor']."',
							idsuperior	=	'".$data['idsuperior']."',
							empstat		=	'".$data['empstat']."',
							idpaygrp	=	'".$data['idpaygrp']."'";
		$Qrye->fields 	= "idacct='".$data['idacct']."'";
		return $Qrye->exe_UPDATE($con);
	}
	
	function getdbUrl($con,$iddb){
		$Qry 			= new Query();	
		$Qry->table     = "tbldb";
		$Qry->selected  = "url";
		$Qry->fields    = "id='".$iddb."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['url'];
			}
		}
		return '';
	}
	
	function checkFormProgressWageIncrease( $con, $formid, $id ){
		$Qry 			= new Query();	
		$Qry->table     = "tblforms02";
		$Qry->selected  = "*";
		$Qry->fields    = "approver1_status is not null AND id='".$id."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}
	
	function getCurrentData2( $con, $id ){
		$Qry 			= new Query();	
		$Qry->table     = "vw_data_wageincrease";
		$Qry->selected  = "*";
		$Qry->fields    = "id='".$id."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return array(
					'idacct'			=>	$row['requestor'],
					'salary' 			=>  $row['newbasepay'],
					'riceallowance' 	=>  $row['newriceallowance'],
					'clothingallowance' =>  $row['newclothingallowance'],
					'laundryallowance' 	=>  $row['newlaundryallowance']
				);
			}
		}
		return array();
	}

	function getCurrentData3( $con, $id ){
		$Qry 			= new Query();	
		$Qry->table     = "vw_data_promotion";
		$Qry->selected  = "*";
		$Qry->fields    = "id='".$id."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				$idunit = $row['newiddept'];
				if( !empty( $row['newidsection'] ) ){
					$idunit = $row['newidsection'];
				}
				return array(
					'idacct'			=>	$row['requestor'],
					'idpos' 		=>  getIdInfo( $con, $row['newpositiontitle'], "tblposition", "name" ),
					'idlvl' 		=>  getIdInfo( $con, $row['newjoblevel'], "tbljoblvl", "lvl" ),
					'idunit'		=>  $idunit,
					'idlabor'	 	=>  getIdInfo( $con, $row['newlabortype'], "tbllabortype", "type" ),
					'idsuperior' 	=>  $row['newidsuperior'],
					'empstat'	 	=>  getIdInfo( $con, $row['newempstatus'], "tblempstatus", "stat" ),
					'idpaygrp'	 	=>  getIdInfo( $con, $row['newpaygroup'], "tblpaygrp", "group" ),

					'salary' 			=>  $row['newbasepay'],
					'riceallowance' 	=>  $row['newriceallowance'],
					'clothingallowance' =>  $row['newclothingallowance'],
					'laundryallowance' 	=>  $row['newlaundryallowance']
				);
			}
		}
		return array();
	}
	
	function updateaccountjob1( $con, $data ){
		$Qrye 			= new Query();	
		$Qrye->table 	= "tblaccountjob";
		$Qrye->selected = " salary				=	'".$data['salary']."',						
							riceallowance		=	'".$data['riceallowance']."',
							clothingallowance	=	'".$data['clothingallowance']."',
							laundryallowance	=	'".$data['laundryallowance']."'";
		$Qrye->fields 	= "idacct='".$data['idacct']."'";
		return $Qrye->exe_UPDATE($con);
	}

	function updateaccountjob2( $con, $data ){
		$Qrye 			= new Query();	
		$Qrye->table 	= "tblaccountjob";
		$Qrye->selected = " idpos				=	'".$data['idpos']."',						
							idlvl				=	'".$data['idlvl']."',
							idunit				=	'".$data['idunit']."',
							idlabor				=	'".$data['idlabor']."',
							idsuperior			=	'".$data['idsuperior']."',
							empstat				=	'".$data['empstat']."',
							idpaygrp			=	'".$data['idpaygrp']."',
							salary				=	'".$data['salary']."',						
							riceallowance		=	'".$data['riceallowance']."',
							clothingallowance	=	'".$data['clothingallowance']."',
							laundryallowance	=	'".$data['laundryallowance']."'";
		$Qrye->fields 	= "idacct='".$data['idacct']."'";
		return $Qrye->exe_UPDATE($con);
	}
	
	function getFullName( $con, $idacct ){
		$Qry 			= new Query();	
		$Qry->table     = "tblaccount as a";
		$Qry->selected  = "CONCAT(`a`.`lname`,IFNULL(CONCAT(' ',`a`.`suffix`),''),', ',`a`.`fname`,' ',SUBSTR(`a`.`mname`,1,1),'. ') AS `empname`";
		$Qry->fields    = "a.id = '".$idacct."'";
		$rs 			= $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['empname'];
			}
		}
	}
	
	function getHierarchy( $con, $idunit ){
		$data			= array();
		$Qry 			= new Query();	
		$Qry->table     = "tblbunits";
		$Qry->selected  = "*";
		$Qry->fields    = "id = '".$idunit."'";
		$rs 			= $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				$data = array(
					"id"		=>	$row['id'],
					"name"		=>	$row['name'],
					"nodechild"	=>  getNodeChild($con, $row['id'])
				);
			}
		}
		return $data;
	}
	
	function getNodeChild( $con, $idunit ){
		$data			= array();
		$Qry 			= new Query();	
		$Qry->table     = "tblbunits";
		$Qry->selected  = "*";
		$Qry->fields    = "idunder = '".$idunit."'";
		$rs 			= $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){
				$data[] = array(
					"id"		=>	$row['id'],
					"name"		=>	$row['name'],
					"nodechild"	=>  getNodeChild($con, $row['id'])
				);
			}
		}
		return $data;
	}
	
	function getChildNode( &$arr_id, $arr ){
		foreach( $arr as $vv ){
			array_push( $arr_id, $vv["id"] );
			if( !empty( $vv["nodechild"] ) ){
				getChildNode( $arr_id, $vv["nodechild"] );
			}
		}
	}
	
	function getLatestRequestPromotion( $con, $tbl, $idacct  ){
		$Qry 			= new Query();	
		$Qry->table     = $tbl;
		$Qry->selected  = "*";
		$Qry->fields    = "idstatus=1 AND requestor = '".$idacct."' ORDER BY id DESC LIMIT 1";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return array(
					"currentdeptname"			=> $row['currentdeptname'],
					"newiddept"					=> $row['newiddept'],
					"currentsection"			=> $row['currentsection'],
					"newidsection"				=> $row['newidsection'],
					"currentbasepay"			=> $row['currentbasepay'],
					"newbasepay"				=> $row['newbasepay'],
					"currentriceallowance"		=> $row['currentriceallowance'],
					"newriceallowance"			=> $row['newriceallowance'],
					"currentclothingallowance"	=> $row['currentclothingallowance'],
					"newclothingallowance"		=> $row['newclothingallowance']
				);
			}
		}
		return array();
	}
	
	function checkFormProgressPromotion( $con, $formid, $id ){
		$Qry 			= new Query();	
		$Qry->table     = "tblforms03";
		$Qry->selected  = "*";
		$Qry->fields    = "approver1_status is not null AND id='".$id."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			return true;
		}
		return false;
	}
	
	
?>

