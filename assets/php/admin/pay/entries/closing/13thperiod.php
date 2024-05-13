<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "tblbonuses";
$Qry->selected  = "*";
$Qry->fields = "type = 13 AND prclose is null";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array(
            "id" 	            => $row['id'],
            "paydate" 	        => $row['releasedate'],
            "cutoff" 	        => $row['start'] . ' to ' . $row['end'],
        );
    }
    $myData = array('status' => 'success', 
                'result' => $data, 
                'closedpayroll' => getclosedpayroll($con),
        );

	$return = json_encode($myData);
}else{
    $myData = array('status' => 'success', 
        'result' => '', 
        'closedpayroll' => getclosedpayroll($con),
    );
	$return = json_encode($myData);
}
print $return;
mysqli_close($con);

function getclosedpayroll($con){
    $Qry = new Query();	
    $Qry->table     = "tblbonuses";
    $Qry->selected  = "*";
    $Qry->fields = "type = 13 AND prclose is not null ORDER BY releasedate DESC";
    
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            $data[] = array(
                "id" 	            => $row['id'],
                "cutoff" 	        => $row['start'] . ' to ' . $row['end'],
                "paydate" 	        => $row['releasedate'],
                "period" 	        => $row['period'],
                "tdate" 	        => '',
                "tby" 	            => '',
                "pdate" 	        => $row['prclose'],
                "pby" 	            => getEmployeeName($con, $row['pidby']),
            );
        }
        return $data; 
    }
    return null;
}


?>