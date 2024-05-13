<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$Qry = new Query();	
$Qry->table     = "vw_payperiod_all";
$Qry->selected  = "*";
$Qry->fields = "stat = 0 AND tkstatus = 1 AND tkprocess = 1 AND payprocess = 1  ORDER BY pay_date ASC,type DESC Limit 1";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        if($row['type'] == 'ho'){
            $row['type'] = 'Local Employee';
        }
        if($row['type'] == 'helper'){
            $row['type'] = 'Helper';
        }
        if($row['type'] == 'hajap'){
            $row['type'] = 'Japanese';
        }
        if($row['type'] == 'hajapc'){
            $row['type'] = 'Japanese Conversion';
        }

        $data = $row;
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
    $Qry->table     = "vw_payperiod_all";
    $Qry->selected  = "*";
    $Qry->fields = "tkstatus = 1  ORDER BY pay_date ASC,type DESC";
    
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            if($row['type'] == 'ho'){
                $row['type'] = 'Local Employee';
            }
            if($row['type'] == 'helper'){
                $row['type'] = 'Helper';
            }
            if($row['type'] == 'hajap'){
                $row['type'] = 'Japanese';
            }
            if($row['type'] == 'hajapc'){
                $row['type'] = 'Japanese Conversion';
            }
            $data[] =  $row;
            
        }
        return $data; 
    }
    return null;
}


?>