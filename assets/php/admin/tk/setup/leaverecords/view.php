<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$data  = array();
 
$Qry 			= new Query();	
$Qry->table     = "vw_leavesummary AS al LEFT JOIN vw_dataemployees AS de ON al.idacct = de.id";
$Qry->selected  = "de.id,de.empid, al.idacct,de.empname,de.business_unit, al.idleave , al.entitle, al.used, al.pending_bal, al.balance";
$Qry->fields    = "de.id!=1 order by de.empname";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){

    $array_id =array();

    while($row=mysqli_fetch_array($rs)){
 
        if(!in_array($row['id'],$array_id)){

            $leave_type = getLeaveType($con, $row['id']);
            // $entitlees = sprintf('%0.2f',($row['entitle']));
            // $usedd = sprintf('%0.2f',($row['used']));
            // $pending_ball = sprintf('%0.2f',($row['pending_bal']));
            // $balancee = sprintf('%0.2f',($row['balance']));
            $data[] = array( 
                
                'empid'    	=> $row['empid'],
                'empname'  	=> $row['empname'],
                'dept'		=> $row['business_unit'],
                'leave_type' => $leave_type,                

            );
            array_push($array_id, $row['id']);
        }
        $return = json_encode($data);
    }
}else if(mysqli_num_rows($rs)== 0){
    $return = json_encode(array('status'=>'norec'));
}else{
    $return = json_encode(array('status'=>'error'));
}


print $return;
mysqli_close($con);

function getLeaveType($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_leavesummary AS al LEFT JOIN vw_dataemployees AS de ON al.idacct = de.id LEFT JOIN tblleaves AS tl ON al.idleave = tl.id";
    $Qry->selected="al.id,al.idacct,tl.name,al.entitle, al.used,al.balance,al.pending,al.carry_over,al.conversion";
    $Qry->fields="idacct='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            $balancee='';
            $balance = sprintf('%0.2f',($row['balance']));
            $entitlees = sprintf('%0.2f',($row['entitle']));
            $usedd = sprintf('%0.2f',($row['used']));
            $pending_ball = sprintf('%0.2f',($row['pending']));
            $carryover = sprintf('%0.2f',($row['carry_over']));
            $converted = sprintf('%0.2f',($row['conversion']));
            
            if(empty($row['used']) || $row['used'] ==0){
                $balancee = $balance;
            }else{
                $balancee = $entitlees - $usedd;
                $balancee = sprintf('%0.2f',( $balancee));
            }
            
            
            $data[] = array(
                'id'          =>$row['id'],
                'name'          =>$row['name'],
                'entitle'	=> $entitlees,
                'used'		=> $usedd,
                'pending_bal'=> $pending_ball,
                'balance'	 => $balancee,
                'earned' =>'0.00',
                'carryover' =>$carryover,
                'converted' => $converted,
                // 'entitle'       =>$row['entitle'],
                // 'used'          =>$row['used'],
                // 'balance'       =>$row['balance'],
                // 'pending_bal'   =>$row['pending_bal'],

            );
        }
    }
    return $data;
}


?>