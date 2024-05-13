<?php
	require_once('../../../../activation.php');
	$conn = new connector();	
	$con = $conn->connect();
	require_once('../../../../classPhp.php'); 


	$param = json_decode(file_get_contents('php://input'));
	$Qry=new Query();
    $Qry->table="tblwage_increase";
    $Qry->selected="*";
    $Qry->fields="id='".$param->id."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            $data[] = array(
                'status'                		=>  'success',
                'id'                    		=>  $row['id'],
                'refcode'          		        =>  $row['refcode'],
                'idacct'          		        =>  $row['idacct'],
                'empid'                 		=>  $row['empid'],
                'empname'               		=>  $row['empname'],
                'effectivedate'         		=>  $row['effectivedate'],
                'fromempname'        		    =>  $row['fromempname'],
                'subject'       		        =>  $row['subject'],
                'remarks'    		            =>  $row['remarks'],
                'from_allowance' 	            =>  $row['from_allowance'],
                'to_allowance' 	                =>  $row['to_allowance'],
				'from_salary' 				    =>  $row['from_salary'],
				'to_salary' 				    =>  $row['to_salary'],
				'from_contract' 				=>  $row['from_contract'],
				'to_contract' 				    =>  $row['to_contract'],
				'from_status' 			        =>  $row['from_status'],
				'to_status' 				    =>  $row['to_status'],
                'from_project' 				    =>  $row['from_project'],
                'to_project' 				    =>  $row['to_project'],
                'from_supervisor' 				=>  $row['from_supervisor'],
                'to_supervisor' 			    =>  $row['to_supervisor'],
				'from_corporate' 				=>  $row['from_corporate'],
                'to_corporate'               	=>  $row['to_corporate'],
                'from_iso'            		    =>  $row['from_iso'],
                'to_iso'             		    =>  $row['to_iso'],
                'asignatory'        		    =>  $row['asignatory'],
                'date_created'        		    =>  $row['date_created'],
                'time_created'        		    =>  $row['time_created']
            );
        }
        $return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'empty'));
    }
print $return;
mysqli_close($con);

// function getApproverForm($con, $id, $pending, $idform){
//     $Qry=new Query();
//     $Qry->table="tblaccount";
//     $Qry->selected="id";
//     $Qry->fields="id='".$id."'";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
//             $QryA=new Query();
//             $QryA->table="tblformsetup";
//             $QryA->selected="id";
//             $QryA->fields="idform='".$idform."' AND (approver_".$pending."a= '".$row['id']."' OR approver_".$pending."b='".$row['id']."')";
//             $rsA=$QryA->exe_SELECT($con);
//             if(mysqli_num_rows($rsA)>=1){
//                 return true;
//             }
//         }
        
//     }
//     return false;
// }

// function getPosition($con, $accountid){
//     $Qry=new Query();
//     $Qry->table="vw_dataemployees";
//     $Qry->selected="post";
//     $Qry->fields="id='".$accountid."'";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
// 			return $row['post'];
//         }
//     }
//     return '';
// }

// function getEmail($con, $accountid){
//     $Qry=new Query();
//     $Qry->table="vw_dataemployees";
//     $Qry->selected="email";
//     $Qry->fields="id='".$accountid."'";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
// 			return $row['email'];
//         }
//     }
//     return '';
// }

// function getSumAllowance( $con, $idacct ){
// 	$Qry=new Query();
// 	$Qry->table="tblacctallowance LEFT JOIN tblallowance ON tblacctallowance.idallowance = tblallowance.id";
// 	$Qry->selected="SUM(tblacctallowance.amt) AS tot";
// 	$Qry->fields="idacct='".$idacct."'";
// 	$rs=$Qry->exe_SELECT($con);
// 	if(mysqli_num_rows($rs)>0){
// 		if($row=mysqli_fetch_array($rs)){
// 			return floatval($row['tot']);
// 		}
// 	}
// 	return '';
// }
?>