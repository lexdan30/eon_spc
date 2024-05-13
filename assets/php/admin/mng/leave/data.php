<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$return = null;	

$search='';
//if( array_key_exists('idsuperior', $param) && !empty($param['idsuperior']) ){ $search=$search." AND idsuperior 	= '".$param['idsuperior']."' ";  }
if( !empty( $param['acct'] ) ){ $search=$search." AND idacct 	= '".$param['acct']."' "; }
if( !empty( $param['leave'] ) ){ $search=$search." AND idleave 	= '".$param['leave']."' "; }
if( !empty( $param['docu'] ) ){ $search=$search." AND docnumber like '%".$param['docu']."%' "; }
if( !empty( $param['appstat'] ) ){ $search=$search." AND stat = '".$param['appstat']."' "; }
if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['to']."')"; }

$where = $search;

if( $param['length'] !='' ){
    $search=$search." ORDER BY date ASC LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "vw_leave_application";
$Qry->selected  = "*";
$Qry->fields    = "stat = 3 AND idleave = '" . $param['type'] . "' AND 
                    ((approver1_stat IS NULL OR approver1_stat != 1 AND approver1 = '" . $param['idsuperior'] . "') OR 
                    ((approver2_stat IS NULL OR approver2_stat != 1) AND approver2 = '" . $param['idsuperior'] . "') OR
                    ((approver3_stat IS NULL OR approver3_stat != 1) AND approver3 = '" . $param['idsuperior'] . "') OR
                    ((approver4_stat IS NULL OR approver4_stat != 1) AND approver4 = '" . $param['idsuperior'] . "')
                   )" . $search;

$rs = $Qry->exe_SELECT($con);
$recFiltered = getTotalRows($con, $param['type'], $param['idsuperior'] ,$where);
if(mysqli_num_rows($rs)>= 1){
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs),
        "recordsFiltered"=> $recFiltered,
        "qry"=>$Qry->fields,
        "data"=>array()
    );

    while($row=mysqli_fetch_array($rs)){
        $pending = 1;
        $action = 0;

        $row['leave_status'] = 'Pending Approver 1 - ' . getApproverName($con, $row['approver1']);
        if($row['approver1'] ==  $param['idsuperior']){
            $action = 1;
        }
  
        if(!empty($row['approver1_stat'])){
            $pending++;
            $action = 0;
            $row['leave_status'] = 'Pending Approver 2 - ' . getApproverName($con, $row['approver2']);
            if($row['approver2'] ==  $param['idsuperior']){
                $action = 1;
            }
               
        }
         if(!empty($row['approver2_stat'])){
            $pending++;
            $action = 0;
            $row['leave_status'] ='Pending Approver 3 - ' . getApproverName($con, $row['approver3']);
            if($row['approver3'] ==  $param['idsuperior']){
                $action = 1;
            }
        }
         if(!empty($row['approver3_stat'])){
            $pending++;
            $action = 0;
            $row['leave_status'] = 'Pending Approver 4 - ' . getApproverName($con, $row['approver4']);
            if($row['approver4'] ==  $param['idsuperior']){
                $action = 1;
            }
        }

        $approvercount = 0;
        if($row['approver1']){
            $approvercount++;
        }
        if($row['approver2']){
            $approvercount++;
        }
         if($row['approver3']){
            $approvercount++;
        }
        if($row['approver4']){
            $approvercount++;
        }

      
           
        if ($action == 1) {
            $btn = '<input type="checkbox" class="circlCheck2"
                    value="' . $row['id'] . '"
                    data-approvercount ="'. $approvercount .'" 
                    data-approver="' . $pending . '" 
                    data-hours="' .  $row['hrs'] . '"  
                    data-appid="' .  $row['idacct'] . '"
                    data-period_start="' .  $row['period_start'] . '" 
                    data-period_end="' .  $row['period_end'] . '"
                    data-grace_hour="' .  $row['grace_hour'] . '"  
                    data-date="' .  $row['date'] . '"  >';
        } else {
            $btn = '';
        }
    

        $data["data"][] = array(
            'id'        		=> (int)$row['id'],
			'docnumber'			=> $row['docnumber'],	
            'idleave'     		=> $row['idleave'],
            'remarks'   		=> $row['remarks'],
			'idtype'			=> $row['idtype'],
			'idacct'			=> $row['idacct'],
			'empid'				=> $row['empid'],
			'empname'			=> $row['empname'],
			'date'				=> $row['date'],
			'time_in'			=> $row['stime'],
			'time_out'			=> $row['ftime'],
			'hrs'				=> $row['hrs'],
			'remarks'			=> $row['remarks'],
			'file'				=> $row['file'],
			'leave_status'		=> $row['leave_status'],
            'date_approve'		=> $row['date_approve'],
            'action'            => $action,
            'checkbox'          => $btn,
        );
    }
    $return =  json_encode($data);
}else{
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs),
        "recordsFiltered"=> mysqli_num_rows($rs),
		"qry"=>$Qry->fields,
        "data"=>array()
    );
    $return =  json_encode($data);
}

print $return;
mysqli_close($con);

function getTotalRows($con, $id, $idacct ,$search){
	$Qry = new Query();	
	$Qry->table ="vw_leave_application";
	$Qry->selected ="*";
	$Qry->fields ="stat = 3 AND idleave = '" . $id . "' AND 
                        ((approver1_stat IS NULL OR approver1_stat != 1 AND approver1 = '" . $idacct . "') OR 
                        ((approver2_stat IS NULL OR approver2_stat != 1) AND approver2 = '" . $idacct . "') OR
                        ((approver3_stat IS NULL OR approver3_stat != 1) AND approver3 = '" . $idacct . "') OR
                        ((approver4_stat IS NULL OR approver4_stat != 1) AND approver4 = '" . $idacct . "'))" . $search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

function getApproverName($con,$idacct){
	$Qry = new Query();	
	$Qry->table ="vw_dataemployees";
	$Qry->selected ="empname";
    $Qry->fields ="id = '" . $idacct . "'";
    $rs            = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1) {
        if ($row = mysqli_fetch_array($rs)) {
            return $row['empname'];
        }
    }
}

?>