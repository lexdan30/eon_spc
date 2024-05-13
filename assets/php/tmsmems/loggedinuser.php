<?php 
require_once('../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../classPhp.php');

$param = json_decode(file_get_contents('php://input'));

if(!empty($param->username)){
	$Qry = new Query();	
	$Qry->table ="users";
	$Qry->selected ="*";
	$Qry->fields ="username = '".$param->username."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
		if($row=mysqli_fetch_array($rs)){     
			if( !file_exists( "../admin/hr/employee/pix/".$row['pic'] ) || empty($row['pic']) ){
				$img_prof = "assets/images/undefined.webp?".time();
			}else{
				$img_prof = "assets/php/admin/hr/employee/pix/".$row['pic']."?".time();
			} 

			$logo = "assets/images/npax_slogo.png?".time();   
			
			$data = array(
				'id'=>$row['id'],
				'empid'=>$row['empid'],
				'fname'=>ucfirst($row['fname']),
				'lname'=>ucfirst($row['lname']),
				'mname'=>ucfirst($row['mname']), 
				'empname'=>ucfirst($row['fname']).' '.ucfirst($row['mname']).' '.ucfirst($row['lname']),
				"position"		=> $row['position'],
				"joblvl"		=> $row['joblvl'],
				"user_type"		=> $row['user_type'],				
				'pic'=>$img_prof,
				'logo'=>$logo,
				'company'=>'NPAX CEBU PHILS'
			);
			$return = json_encode($data);
				
		}
	}else{
		$return = json_encode(array('status'=>'error','w'=>1));
	}
}else{
	$return = json_encode(array('status'=>'error','w'=>2));
}
print $return;	
mysqli_close($con); 

?>