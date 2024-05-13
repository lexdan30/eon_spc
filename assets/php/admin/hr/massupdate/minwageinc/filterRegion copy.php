<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

    $param = json_decode(file_get_contents('php://input'));
    $return = null;

    if(!empty($param->accountid)){

		$data			= array();
		$Qry 			= new Query();	
		$Qry->table     = "vw_dataemployees";
		$Qry->selected  = "*";
		$Qry->fields    = "id>0 AND cur_region='".$param->region."' AND idlvl in (6,7,8,9) AND etypeid=1 AND mwidailyrate<=".$param->minwage." ORDER BY lname ASC ";
		$rs 			= $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			while($row=mysqli_fetch_array($rs)){

                // $rate = 0;

                // if($row['idpaygrp']==3){
                //     $rate = $row['salary'] / $row['daysmonth'];
                //     if($rate <= getOldMinWage($con, $param->region)){

                //     }
                // }else if($row['idpaygrp']==5){
                //     $rate = $row['salary'];
                // }

				$data[] = array(
                    "idacct"                =>  $row['id'],
					"id"			        =>	$row['empid'],
					"lname"		            =>	$row['lname'],
                    "fname"		            =>  $row['fname'],
                    "mname"		            =>  substr($row['mname'], 0, 1),
                    "id_unit"               =>  $row['idunit'],
                    "idpaygrp"              =>  $row['idpaygrp'], 
                    "daysmonth"             =>  $row['daysmonth'],
                    "business_unit"		    =>  $row['business_unit'],
                    "job_region"		    =>  $row['cur_region'],
                    "salary"		        =>  $row['mwidailyrate']
				);
			}
        }else{
            $return = json_encode($data);
        }
        
        $return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'error'));
    }

print $return;
mysqli_close($con);

// function getOldMinWage($con, $region){
//     $Qry=new Query();
//     $Qry->table="tbljobregion";
//     $Qry->selected="*";
//     $Qry->fields="id>0 AND region='".$region."'";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
//             return $row['min_wage'];
//         }
//     }
//     return null;
// }

?>