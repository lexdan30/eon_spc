<?php 
 	require_once('../../../activation.php');
    require_once('../../../classPhp.php');
    $conn = new connector();
    $con = $conn->connect();
	// $param = $_GET;
	$param = json_decode(file_get_contents('php://input'));
	$date=SysDate();

    if(!empty($param->from) && !empty($param->to)){
        $Qry 			= new Query();	
        $Qry->table     = "vw_dataemployees AS a";
        $Qry->selected  = "a.business_unit,(SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.hdate<='".$param->from."' AND b.business_unit=a.business_unit) - (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.sdate<='".$param->from."' AND b.business_unit=a.business_unit ) AS headcount_from,
        (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.hdate<='".$param->to."' AND b.business_unit=a.business_unit) - (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.sdate<='".$param->to."' AND b.business_unit=a.business_unit ) AS headcount_to,
        ((SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.hdate<='".$param->to."' AND b.business_unit=a.business_unit ) - (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.hdate<='".$param->from."' AND b.business_unit=a.business_unit ) ) + ((SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.sdate<='".$param->to."' AND b.business_unit=a.business_unit ) - (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.sdate<='".$param->from."' AND b.business_unit=a.business_unit ) ) AS variance_comp,
        ((SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.hdate<='".$param->to."' AND b.business_unit=a.business_unit ) - (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.hdate<='".$param->from."' AND b.business_unit=a.business_unit ) ) AS variance_hired,
        ((SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.sdate<='".$param->to."' AND b.business_unit=a.business_unit ) - (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.sdate<='".$param->from."' AND b.business_unit=a.business_unit ) ) AS variance_rsgn,
        (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.hdate<='".$param->to."' AND b.business_unit=a.business_unit) - (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.sdate<='".$param->to."' AND b.business_unit=a.business_unit ) AS total_headcount,
        (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.hdate<='".$param->to."' AND b.business_unit=a.business_unit AND b.idlabor='4') - (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.sdate<='".$param->to."' AND b.business_unit=a.business_unit AND b.idlabor='4') AS total_GA,
        (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.hdate<='".$param->to."' AND b.business_unit=a.business_unit AND b.idlabor='3') - (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.sdate<='".$param->to."' AND b.business_unit=a.business_unit AND b.idlabor='3') AS total_IL,
        (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.hdate<='".$param->to."' AND b.business_unit=a.business_unit AND b.idlabor='2') - (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.sdate<='".$param->to."' AND b.business_unit=a.business_unit AND b.idlabor='2') AS total_DL,
        (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.hdate<='".$param->to."' AND b.business_unit=a.business_unit AND b.idlabor='5') - (SELECT COUNT(b.id) FROM vw_dataemployees AS b WHERE b.sdate<='".$param->to."' AND b.business_unit=a.business_unit AND b.idlabor='5') AS total_FI";
        $Qry->fields    = "a.business_unit  IS NOT NULL GROUP BY a.business_unit ORDER BY a.business_unit ASC";
        $rs 			= $Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>= 1){
            while($row=mysqli_fetch_array($rs)){
        
                $data[] = array( 
                    "Classification"        	            => $row['business_unit'],
                    "Headcount as of '".$param->from."'"	=> $row['headcount_from'],
                    "Headcount as of '".$param->to."'" 		=> $row['headcount_to'],
                    "Variance"        	                    => $row['variance_comp'],
                    "Hired"			                        => $row['variance_hired'],
                    "Resigned" 		                        => $row['variance_rsgn'],
                    "Total Headcount"			            => $row['total_headcount'],
                    "Labor_Type_GA" 		                => $row['total_GA'],
                    "Labor_Type_IL"        	                => $row['total_IL'],
                    "Labor_Type_DL"			                => $row['total_DL'],
                    "Labor_Type_FI" 		                => $row['total_FI']
                );
                $return = json_encode($data);
            }
        }else{
            $return = json_encode(array('status'=>'error'));
        }
    }else{
        $return = json_encode(array('status'=>'empty'));
    }
	
print $return;	
mysqli_close($con);


?>