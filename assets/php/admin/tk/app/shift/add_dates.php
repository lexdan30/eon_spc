<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

$shift_cols = array("monday"	=>"idmon,mon,mon_in,mon_out,mon_brk", 
					"tuesday"	=>"idtue,tue,tue_in,tue_out,tue_brk",
					"wednesday"	=>"idwed,wed,wed_in,wed_out,wed_brk",
					"thursday"	=>"idthu,thu,thu_in,thu_out,thu_brk",
					"friday"	=>"idfri,fri,fri_in,fri_out,fri_brk",
					"saturday"	=>"idsat,sat,sat_in,sat_out,sat_brk", 
					"sunday"	=>"idsun,sun,sun_in,sun_out,sun_brk");
					

if( !empty($param->acct) ){
		if( !empty($param->from) ){
			if( !empty($param->to) ){
				
				$Qry = new Query();	
				$Qry->table     = "(SELECT id, work_date, cs, idshift, csstatus, holidaytype, alias, timein, timeout FROM vw_timesheet) as a
				LEFT JOIN (SELECT munid, provcode, idacct FROM tblaccountjob) AS w ON (w.idacct = a.id)
				LEFT JOIN (SELECT provcode, munid, `date` FROM tblholidays) AS g ON (g.date = a.work_date)
				";
				$Qry->selected  = "
				a.id,
				a.work_date,
				a.cs,
				a.idshift,
				a.csstatus,
				a.holidaytype,
				a.alias,
				a.timein,
				a.timeout,
				g.provcode AS holidayProvid,
				g.munid AS holidayMunid,
				w.munid AS employeeMunid,
				w.provcode As employeeProvid";
				$Qry->fields    = "a.id = '".$param->acct."' AND a.work_date BETWEEN '".$param->from."' AND '".$param->to."' ORDER BY a.work_date ASC";
				$rs = $Qry->exe_SELECT($con);
				if(mysqli_num_rows($rs)>= 1){					
					while($row=mysqli_fetch_assoc($rs)){
						
						$shift_field			= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];
						$shift_info 			= getDateShiftData( $con, $row['id'], $shift_field, $row['work_date'] );
						if(!empty($row['idshift'])){
							$shift_info			= getShiftIDData($con,$row['idshift']);
						}
						
						$idshift				= $shift_info[0];
						$shiftin				= $shift_info[2];
						$shiftout				= $shift_info[3];
						$shiftbrk				= $shift_info[4];
						$reghrs					= sprintf('%0.2f',((strtotime($shiftout) - strtotime($shiftin)) ) / ( 60 * 60 ) - $shiftbrk);
						$hol = false;
						if( !empty($row['holidaytype']) ){
							if(!empty($row['holidayProvid'])){
								if(empty($row['holidayMunid']) && $row['employeeProvid'] == $row['holidayProvid'])
								{
									$shift_info[1] = ucwords(strtolower($row['alias'])).'H ' .$shift_info[1];
									$hol = true;
								}elseif($row['holidayMunid'] == $row['employeeMunid']){
									$shift_info[1] = ucwords(strtolower($row['alias'])).'H ' .$shift_info[1];
									$hol = true;
								}
							}else{
								$shift_info[1] = ucwords(strtolower($row['alias'])).'H ' .$shift_info[1];
								$hol = true;
							}
						}
						$drop = array();
						$val = '';
					
						$drop[] = array(
							"id" 	=> '',
							"valz"	=> ''
						);

						$Qry2 = new Query();	
						$Qry2->table     = 'tblshift';
						$Qry2->selected  = "id, `name`";
						$Qry2->fields    = "id != '".$idshift."' ";
						$rs2 = $Qry2->exe_SELECT($con);
						if(mysqli_num_rows($rs2)>= 1){
							while($row2=mysqli_fetch_assoc($rs2)){
								$drop[] = array(
									"id" 	=> $row2['id'],
									"valz"	=> $row2['name']
								);
								$val = '1';
							}
						}
                        
                        
				
						if( strtotime($row['work_date']) != strtotime(SysDate()) ){
							if( strtotime($row['work_date']) < strtotime(SysDate()) ){
								// $val = '';
								// $drop = array();
								// $shift_info[1] = "Can't apply late";
								// $reghrs=0;
							}elseif( !empty($row['in']) && !empty($row['out']) ){
								$val = '';
								$drop = array();
								$shift_info[1] = "Has In/Out data";
								$reghrs=0;
							}elseif( getHireDate($con, $param->acct) != '0' && strtotime($row['work_date']) < strtotime(getHireDate($con, $param->acct)) ){
								$val = '';
								$drop = array();
								$shift_info[1] = "Before Hire Date";
								$reghrs=0;
							}elseif(checkApplications($con, $param->acct, $row['work_date'], "vw_leave_application")){
								$val = '';
								$drop = array();
								$shift_info[1] = checkApplications($con, $param->acct, $row['work_date'], "vw_leave_application");
								$reghrs=0;
							}
						}
						
						$data[] = array(
							"date" 	        => $row['work_date'],
                            "empID"	        => $row['id'],
							"hol"	 =>  $hol,
							"shift"	        => $shift_info[1],		
                            "drop"	        => $drop,
                            "val"  	        => $val,
							"oldshiftid"	=> $idshift,
                            "newshiftid"	=> ''
						);
					}
				}
				$return = json_encode($data);
			}else{
				$return = json_encode(array('status'=>'error'));
			}
		}else{
			$return = json_encode(array('status'=>'error'));
		}
	
}else{
	$return = json_encode(array('status'=>'error'));
}

print $return;
mysqli_close($con);


function checkApplications($con, $acct, $date, $tbl){
    $Qry = new Query();	
    $Qry->table     = $tbl;
    $Qry->selected  = "leave_name, cancel_reason, stat";
    $Qry->fields    = "date='".$date."' AND idacct='".$acct."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            if($row['stat'] == 1 ){
                return 'Already have an approved ' . $row['leave_name'];
            }else if($row['stat'] == 3 && $row['cancel_reason'] == null){
                return 'Has pending ' . $row['leave_name'];
            }else{
                return false;
            }
            
        }			
    }
    return false;
}

?>