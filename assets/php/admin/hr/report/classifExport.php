<?php 

// session_start();
// $auth = isset($_SESSION['isAuth']) ? $_SESSION['isAuth']: false;
// if($auth){

 	require_once('../../../activation.php');
    require_once('../../../classPhp.php');
    $conn = new connector();
	$con = $conn->connect();
	
	$date=SysDate();
	$param = json_decode(file_get_contents('php://input'));
	if( !empty($param->accountid)){

	$Qry = new Query();	
	$Qry->table     = "vw_dataemployees AS de
	LEFT JOIN tbljoblvl AS jl
	ON de.idlvl = jl.id";
	$Qry->selected  = "de.empid AS `Employee ID`,de.empname AS `Employee Name`,de.post AS `Position`,de.joblvl AS `Job Level`,jl.alias AS `Job Code`,CONCAT(de.joblvl,' ',jl.alias) AS `Job Name`,de.business_unit AS Classification,de.superior AS Superior";
	$Qry->fields    = "de.id!=1 AND de.idemptype = 1 ORDER BY de.empname ASC";
	$rs = $Qry->exe_SELECT($con);
	$where = $Qry->fields;	
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		while($row=mysqli_fetch_array($rs)){
            $data[] = array(
							//   "" => $row['id'],
							  "EMPLOYEE ID" 	=> $row['Employee ID'],
							  "EMPLOYEE NAME"   => ucwords(strtolower($row['Employee Name'])),
							  "POSITION"  		=> ucwords(strtolower($row['Position'])), 
							  "JOB NAME"  		=> ucwords(strtolower($row['Job Name'])), 
							  "CLASSIFICATION"  => ucwords(strtolower($row['Classification'])), 
							  "SUPERIOR" =>  ucwords(strtolower($row['Superior'])), 
                    );
		}
		
		$return = json_encode($data);
	}else{
		$return = json_encode(array('status'=>'empty'));
	}

print $return;	
mysqli_close($con);
}

?>