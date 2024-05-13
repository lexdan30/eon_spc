<?php
require_once('../../../activation.php');
require_once('../../../classPhp.php');
$conn = new connector();	
$con = $conn->connect();

	$param = json_decode(file_get_contents('php://input'));
	$Qry=new Query();
    $Qry->table="vw_data_approversetup";
    $Qry->selected="*";
    // $Qry->fields="id > 0 AND branch='".$param->branch."'";
    $Qry->fields="id > 0 AND id='".$param->id."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            $data[] = array(
                'status'=>'success',
                'id'=>$row['id'],
                'approver1a'         =>  (empty(trim($row['approver_1a'])) ? '' : $row['approver_1a']),
                'approver1b'         =>  (empty(trim($row['approver_1b'])) ? '' : $row['approver_1b']),
                'approver2a'         =>  (empty(trim($row['approver_2a'])) ? '' : $row['approver_2a']),
                'approver2b'         =>  (empty(trim($row['approver_2b'])) ? '' : $row['approver_2b']),
                'approver3a'         =>  (empty(trim($row['approver_3a'])) ? '' : $row['approver_3a']),
                'approver3b'         =>  (empty(trim($row['approver_3b'])) ? '' : $row['approver_3b']),
                'approver4a'         =>  (empty(trim($row['approver_4a'])) ? '' : $row['approver_4a']),
                'approver4b'         =>  (empty(trim($row['approver_4b'])) ? '' : $row['approver_4b']),
                'approver5a'         =>  (empty(trim($row['approver_5a'])) ? '' : $row['approver_5a']),
                'approver5b'         =>  (empty(trim($row['approver_5b'])) ? '' : $row['approver_5b']),
				'ctr_approvers'		 =>	 $row['ctr_approver'],
                'status'             =>  $row['idstatus']
            );
        }
        $return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'empty'));
    }

print $return;
mysqli_close($con);
?>