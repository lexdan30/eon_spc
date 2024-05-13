<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$time = strtotime($param->month);
$newformat = date('Y-m-d',$time);

$Qry = new Query();	
$Qry->table     = "tblpayreg as pr 
                    LEFT JOIN tblpayperiod as pp ON pr.idpayperiod = pp.id
                    LEFT JOIN tblaccount as a ON a.id = pr.idacct";
$Qry->selected  = "pr.id,
                    fname,
                    lname,
                    mname,
                    idhealth,
                    SUM(ph_ee) as ph_ee,
                    SUM(ph_er) as ph_er,
                    SUM(ph_er + ph_ee) as totalcon";
                  
$Qry->fields = "YEAR(pp.pay_date) = YEAR('" . $newformat . "') AND MONTH(pp.pay_date) = MONTH('" . $newformat . "') GROUP BY pr.idacct ORDER BY lname LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize ."";



$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array(
            "id" 	            => $row['id'],
            "last" 	            => $row['lname'],
            "first" 	        => $row['fname'],
            "mi" 	            => $row['mname'][0],
            "idibig" 	        => $row['idhealth'],
            "ph_ee" 	            => $row['ph_ee'],
            "ph_er" 	            => $row['ph_er'],
            "total" 	        => $row['totalcon']
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
    $Qry->table     = "tblpayreg as pr 
                        LEFT JOIN tblpayperiod as pp ON pr.idpayperiod = pp.id
                        LEFT JOIN tblaccount as a ON a.id = pr.idacct";
   $Qry->selected  = "pr.id,
                        fname,
                        lname,
                        mname,
                        idhealth,
                        SUM(ph_ee) as ph_ee,
                        SUM(ph_er) as ph_er,
                        SUM(ph_er + ph_ee) as totalcon";
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
    $Qry->table     = "tblpayreg as pr 
                        LEFT JOIN tblpayperiod as pp ON pr.idpayperiod = pp.id
                        LEFT JOIN tblaccount as a ON a.id = pr.idacct";
    $Qry->selected  = "pr.id,
                        fname,
                        lname,
                        mname,
                        idhealth,
                        SUM(ph_ee) as ph_ee,
                        SUM(ph_er) as ph_er,
                        SUM(ph_er + ph_ee) as totalcon";
    $Qry->fields = "YEAR(pp.pay_date) = YEAR('" . $newformat . "') AND MONTH(pp.pay_date) = MONTH('" . $newformat . "')";

    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_assoc($rs)){
                $row['grandtotal'] = $row['ph_ee'] + $row['ph_er'];
                return $row;
			}
		}
		return 0;
}
?>