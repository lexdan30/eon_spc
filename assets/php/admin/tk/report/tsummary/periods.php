<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));

$Qry   = new Query();  
$Qry->table="vw_payperiod_all";
$Qry->selected="*";
$Qry->fields="tkstatus = 1 ORDER BY pay_date ASC,type";

$rs=$Qry->exe_SELECT($con);
//echo $Qry->fields;
if(mysqli_num_rows($rs)>=1){
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
        $data[] = $row;
    }
}

$Qry   = new Query();  
$Qry->table="vw_payperiod_all";
$Qry->selected="*";
$Qry->fields="tkstatus = 0 AND (tkprocess = 1 OR tkprocess = 0) ORDER BY pay_date ASC,TYPE LIMIT 3";

$rs=$Qry->exe_SELECT($con);
//echo $Qry->fields;
if(mysqli_num_rows($rs)>=1){
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
        $data[] = $row;
    }
}

$myData = array('status' => 'success', 'result' => $data);
$return = json_encode($myData);

print $return;
mysqli_close($con);
?>