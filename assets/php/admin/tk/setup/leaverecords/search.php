<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param       = json_decode(file_get_contents('php://input'));
$data  = array();

$search='';
if( !empty( $param->search_acct ) ){ $search=$search." AND idacct 	= '".$param->search_acct."' "; }
//Search Department
if( !empty( $param->search_dept ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$param->search_dept);
    array_push( $arr_id, $param->search_dept );
    if( !empty( $arr["nodechild"] ) ){
        $a = getChildNode($arr_id, $arr["nodechild"]);
        if( !empty($a) ){
            foreach( $a as $v ){
                array_push( $arr_id, $v );
            }
        }
    }
    if( count($arr_id) == 1 ){
        $ids 			= $arr_id[0];
    }else{
        $ids 			= implode(",",$arr_id);
    }
    $search.=" AND idunit in (".$ids.") "; 
}


$Qry 			= new Query();	
$Qry->table     = "vw_leavesummary AS al LEFT JOIN vw_dataemployees AS de ON al.idacct = de.id";
$Qry->selected  = "de.id,de.empid, al.idacct,de.empname,de.business_unit, al.idleave , al.entitle, al.used, al.pending, al.balance";
$Qry->fields    = "de.id>0 ".$search." order by de.empname";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){

    $array_id =array();

    while($row=mysqli_fetch_array($rs)){

        if(!in_array($row['id'],$array_id)){

            $leave_type = getLeaveType($con, $row['id']);
            $entitlees = sprintf('%0.2f',($row['entitle']));
            $usedd = sprintf('%0.2f',($row['used']));
            $pending_ball = sprintf('%0.2f',($row['pending']));
            $balancee = sprintf('%0.2f',($row['balance']));
            $data[] = array( 
                'id'    	=> $row['id'],
                'empid'    	=> $row['empid'],
                'empname'  	=> $row['empname'],
                'dept'		=> $row['business_unit'],
                'leave_type' => $leave_type,

            );
            array_push($array_id, $row['id']);
        }
        $return = json_encode($data);
    }
}else{
    $return = json_encode(array('status'=>'error'));
}


print $return;
mysqli_close($con);

function getLeaveType($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_leavesummary AS al LEFT JOIN vw_dataemployees AS de ON al.idacct = de.id LEFT JOIN tblleaves AS tl ON al.idleave = tl.id";
    $Qry->selected="al.idacct,tl.name,al.entitle, al.used,al.balance,al.pending";
    $Qry->fields="idacct='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            $balancee='';
            $balance = sprintf('%0.2f',($row['balance']));
            $entitlees = sprintf('%0.2f',($row['entitle']));
            $usedd = sprintf('%0.2f',($row['used']));
            $pending_ball = sprintf('%0.2f',($row['pending']));
            
            if(empty($row['used']) || $row['used'] ==0){
                $balancee = $balance;
            }else{
                $balancee = $entitlees - $usedd;
                $balancee = sprintf('%0.2f',( $balancee));
            }
            
            
            $data[] = array(
                'name'          =>$row['name'],
                'entitle'	=> $entitlees,
                'used'		=> $usedd,
                'pending_bal'=> $pending_ball,
                'balance'	 => $balancee,
                'earned' =>'0.00',
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