<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$time = strtotime($param->month);
$newformat = date('Y-m-d',$time);

$Qry = new Query();	
$Qry->table     = "tblpayreg AS pr 
LEFT JOIN `vw_payperiod_all` AS pp
ON pr.idpayperiod = pp.id AND pp.type = (CASE 
                        WHEN pr.type = 'HELPER' THEN 'helper'
                        WHEN pr.type = 'Japanese' THEN 'hajap'
                        WHEN pr.type = 'Japanese Conversion' THEN 'hajapc'
                        ELSE 'ho'
                    END)
LEFT JOIN tblaccount AS a ON a.id = pr.idacct";
$Qry->selected  = "pr.id,
                    fname,
                    lname,
                    mname,
                    idtin,
                    SUM(w_tax) as w_tax";
     
$Qry->fields = "YEAR(pp.pay_date) = YEAR('" . $newformat . "') AND MONTH(pp.pay_date) = MONTH('" . $newformat . "') GROUP BY pr.idacct ORDER BY lname LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize ."";



$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array(
            "id" 	            => $row['id'],
            "last" 	            => $row['lname'],
            "first" 	        => $row['fname'],
            "mi" 	            => $row['mname'],
            "idtin" 	        => $row['idtin'],
            "w_tax" 	        => $row['w_tax'],
       
        );
    }

    $myData = array('status' => 'success', 
                    'result' => $data,
                    'totalItems' => getTotal($con , $newformat), 
                    'totals'  => getTotals($con , $newformat));

	$return = json_encode($myData);
}else{
    
    $myData = array('status' => 'success', 
                        'result' => '');
	
 $return = json_encode($myData);
	
}
print $return;
mysqli_close($con);

function getTotal($con,$newformat){
    $Qry = new Query();	
    $Qry->table     = "tblpayreg AS pr 
    LEFT JOIN `vw_payperiod_all` AS pp
    ON pr.idpayperiod = pp.id AND pp.type = (CASE 
                            WHEN pr.type = 'HELPER' THEN 'helper'
                            WHEN pr.type = 'Japanese' THEN 'hajap'
                            WHEN pr.type = 'Japanese Conversion' THEN 'hajapc'
                            ELSE 'ho'
                        END)
    LEFT JOIN tblaccount AS a ON a.id = pr.idacct";
    $Qry->selected  = "pr.id,
                        fname,
                        lname,
                        mname,
                        idtin,
                        SUM(w_tax) as w_tax";
    $Qry->fields = "YEAR(pp.pay_date) = YEAR('" . $newformat . "') AND MONTH(pp.pay_date) = MONTH('" . $newformat . "') GROUP BY pr.idacct";

    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
                $rowcount=mysqli_num_rows($rs);
				return $rowcount;
			}
		}
		return 0;
}

function getTotals($con,$newformat){
    $Qry = new Query();	
    $Qry->table     = "tblpayreg AS pr 
    LEFT JOIN `vw_payperiod_all` AS pp
    ON pr.idpayperiod = pp.id AND pp.type = (CASE 
                            WHEN pr.type = 'HELPER' THEN 'helper'
                            WHEN pr.type = 'Japanese' THEN 'hajap'
                            WHEN pr.type = 'Japanese Conversion' THEN 'hajapc'
                            ELSE 'ho'
                        END)
    LEFT JOIN tblaccount AS a ON a.id = pr.idacct";
    $Qry->selected  = "SUM(w_tax) as w_tax";
    $Qry->fields = "YEAR(pp.pay_date) = YEAR('" . $newformat . "') AND MONTH(pp.pay_date) = MONTH('" . $newformat . "')";

    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_assoc($rs)){
                $row['grandtotal'] = $row['w_tax'];
                return $row;
			}
		}
		return 0;
}
?>