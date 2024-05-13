<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$search='';

if( !empty($param->filter->employee  ) ){ $search.=" AND empid = '". $param->filter->employee ."' "; }
if( !empty($param->filter->type  ) ){ $search.=" AND loanid = '". $param->filter->type ."' "; }

$where = $search;

$Qry = new Query();	
$Qry->table     = "tblloans AS a 
LEFT JOIN tblloantype AS b ON a.loanid = b.id 
LEFT JOIN tblclasstrans AS c ON c.id = b.transid";
$Qry->selected  = "a.*,c.name,b.app_first,b.app_second";
$Qry->fields = "a.id>0" . $search . " LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize;


$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id" 	                    => $row['id'],
            "docnum" 	                => $row['docnumber'],
            "empid"                     => $row['empid'],
            "loanid"                    => $row['loanid'],
            "status"                    => $row['status'],
            "interest"                  => $row['interest'],
            "loandate"                  => $row['loandate'],
            "entrydate"                 => $row['entrydate'],
            "firstpaydate"              => $row['firstpaydate'],
            "noa"                       => $row['noa'],
            "begginingbalance"          => $row['begginingbalance'],
            "systemamortization"        => $row['systemamortization'],
            "useramortization"          => $row['useramortization'],
            "totalamount"               => $row['totalamount'],
            "loantype"                  => $row['name'],
            "app_first"                  => $row['app_first'],
            "app_second"                  => $row['app_second']
            
        );
    }

    $myData = array('status' => 'success', 'result' => $data, 'totalItems' => getTotal($con , $where));
	$return = json_encode($myData);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);

function getTotal($con,$search){
    $Qry = new Query();	
    $Qry->table     = "tblloans";
    $Qry->selected  = "count(*) as total";
    $Qry->fields = "id>0".$search;
    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['total'];
			}
		}
		return 0;
}
?>