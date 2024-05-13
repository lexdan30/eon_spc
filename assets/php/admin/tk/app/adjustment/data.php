<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	

$search='';
if( array_key_exists('idsuperior', $param) && !empty($param['idsuperior']) ){ 
 
		//$search=$search." AND idsuperior 	= '".$param['idsuperior']."' ";  
    
        $search=$search." AND (((approver1 LIKE '%" . $param['idsuperior'] ."%') OR 
        (approver2 LIKE '%" . $param['idsuperior'] ."%') OR
        (approver3 LIKE '%" . $param['idsuperior'] ."%') OR
        (approver4 LIKE '%" . $param['idsuperior'] ."%'))) ";  

        
}
if( !empty( $param['acct'] ) ){ $search=$search." AND idacct 	= '".$param['acct']."' "; }
if( !empty( $param['docu'] ) ){ $search=$search." AND docnumber like '%".$param['docu']."%' "; }
// if( !empty( $param['appstat'] ) ){ $search=$search." AND stat = '".$param['appstat']."' "; }
if( $param['appstat'] == '3' || empty($param['appstat']) ){
	$search=$search." AND stat = '3' "; 
}elseif( $param['appstat'] == '4' ){
	$search=$search." AND stat IN (1,2,3) "; 
}else{
	$search=$search." AND stat = '".$param['appstat']."' "; 
}

// if(( $param['flag']) == '1'){
// 	if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search; }
// }
// else{
// 	if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['to']."')"; }
// }
if(!empty($param['from'])){
	if(( $param['flag']) == '1'){
		if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search; }
		if( !empty( $param['from'] ) && empty( $param['to'] ) ){ $search=$search; } 
	} 
	else{
		if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['to']."')"; }
		if( !empty( $param['from'] ) && empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['from']."')"; }
	}
}else{
	$data = array( 
		"period"		=> getPayPeriodts($con),
	);

	if(( $param['flag']) == '1'){
		// if( !empty($data['period']['pay_start'] ) && !empty( $data['period']['pay_end'] ) ){ $search=$search; }
		// if( !empty($data['period']['pay_start'] ) && empty( $data['period']['pay_end'] ) ){ $search=$search; }
		if( !empty( $data['period']['pay_start'] ) && !empty( $data['period']['pay_end'] ) ){ $search=$search." AND (date between '".$data['period']['pay_start']."' AND '".$data['period']['pay_end']."')"; }
		if( !empty( $data['period']['pay_start'] ) && empty($data['period']['pay_end'] ) ){ $search=$search." AND (date between '".$data['period']['pay_start']."' AND '".$data['period']['pay_start']."')"; }
	} 
	else{
		if( !empty( $data['period']['pay_start'] ) && !empty( $data['period']['pay_end'] ) ){ $search=$search." AND (date between '".$data['period']['pay_start']."' AND '".$data['period']['pay_end']."')"; }
		if( !empty( $data['period']['pay_start'] ) && empty($data['period']['pay_end'] ) ){ $search=$search." AND (date between '".$data['period']['pay_start']."' AND '".$data['period']['pay_start']."')"; }
	}
}



$where = $search;

//sort nga funtion sa table
if( $param['order'][0]['column'] !='' ){//default 
	$arrCols = array("",
					"empname",
					"date",
					"app_type",
					"app_time",
					"remarks",
					"file",
					"adj_status");//mao ra ang mailisan na declare na sa ubos php
	$search=$search." ORDER BY ". $arrCols[$param['order'][0]['column']] ." ".$param['order'][0]['dir'];//default
}


if( (int)$param['length'] >= 0 ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' && (int)$param['length'] >= 0 ){
    $search=$search." OFFSET ".$param['start'];
}
//print_r($search);
$Qry = new Query();	
$Qry->table     = "vw_attendance_application";
$Qry->selected  = "
file, approver1, approver2, approver3, approver4, approver1_name, approver2_name, approver3_name, approver4_name,
approver1_stat, approver2_stat, approver3_stat, adj_status, id,
docnumber, idacct, empid, empname, `date`,
stime, ftime, hrs, remarks, app_type, app_time, date_approve";
$Qry->fields    = "id>0 ".$search;
$Qry->fields = str_replace("\r\n",'', $Qry->fields);
$Qry->fields = str_replace("\t",'', $Qry->fields);
$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
$recFiltered = getTotalRows($con,$where);
if(mysqli_num_rows($rs)>= 1){
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs),
        "recordsFiltered"=> $recFiltered,
        "qry"=>$Qry->fields,
        "data"=>array()
    );
    while($row=mysqli_fetch_assoc($rs)){
		if(empty($row['file'])){
			$file='';
		}else{
			$file=$row['file'];
		}

		$pending = 1;
        $action = 0;

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

		$applevel='';
        $approvername=''; 
        $isApprover=false;

		$approver1_1 = explode(',',$row['approver1']);
		$approver1_count = count($approver1_1);

        if ($approver1_count > 1) {
            $action = 1;
            $applevel='Level 1';
            $approvername=getAcctNames($con, $row['approver1']);
            $isApprover = in_array($param['idsuperior'], $approver1_1);
        } else {
            $action = 1;
            $applevel='Level 1';
            $approvername=getAcctName($con, $row['approver1']);
            $isApprover = ($param['idsuperior'] == $row['approver1']);
        }

		if(!empty($row['approver1_stat'])){
            $pending++;
            $action = 0;

            $approver2_1 = explode(',',$row['approver2']);
			$approver2_count = count($approver2_1);
            if ($approver2_count > 1) {
                $action = 1;
                $applevel='Level 2';
                $approvername=getAcctNames($con, $row['approver2']);
                $isApprover = in_array($param['idsuperior'], $approver2_1);
            } else {
                $action = 1;
                $applevel='Level 2';
                $approvername=getAcctName($con, $row['approver2']);
                $isApprover = ($param['idsuperior'] == $row['approver2']);
            }
        }

		if(!empty($row['approver2_stat'])){
            $pending++;
            $action = 0;
           
           $approver3_1 = explode(',',$row['approver3']);
			$approver3_count = count($approver3_1);
            if ($approver3_count > 1) {
                $action = 1;
                $applevel='Level 3';
                $approvername=getAcctNames($con, $row['approver3']);
                $isApprover = in_array($param['idsuperior'], $approver3_1);
            } else {
                $action = 1;
                $applevel='Level 3';
                $approvername=getAcctName($con, $row['approver3']);
                $isApprover = ($param['idsuperior'] == $row['approver3']);
            } 
        }

		if(!empty($row['approver3_stat'])){
            $pending++;
            $action = 0;

            $approver4_1 = explode(',',$row['approver4']);
			$approver4_count = count($approver4_1);
            if ($approver4_count > 1) {
                $action = 1;
                $applevel='Level 4';
                $approvername=getAcctNames($con, $row['approver4']);
                $isApprover = in_array($param['idsuperior'], $approver4_1);
            } else {
                $action = 1;
                $applevel='Level 4';
                $approvername=getAcctName($con, $row['approver4']);
                $isApprover = ($param['idsuperior'] == $row['approver4']);
            }
        }

		if($action == 1){
			if(ucwords(strtolower($row['adj_status'])) == 'Pending'){
				$adj_statuscheck = 'Pending';
            }else{
				$adj_statuscheck = '';
			}
		} else {
            $adj_statuscheck = '';
        }

        $data["data"][] = array(
            'id'        		=> (int)$row['id'],
			'docnumber'			=> $row['docnumber'],	            
			'idacct'			=> $row['idacct'],
			'empid'				=> $row['empid'],
			'empname'			=> $row['empname'],
			'date'				=> $row['date'],
			'time_in'			=> $row['stime'],
			'time_out'			=> $row['ftime'],
			'hrs'				=> $row['hrs'],
			'remarks'			=> $row['remarks'],
			'file'				=> $file,
			'adj_statuscheck'	=> ucwords(strtolower($adj_statuscheck)),
			'adj_status'		=> ucwords(strtolower($row['adj_status'])),
			'date_approve'		=> $row['date_approve'],
			'app_type'			=> $row['app_type'],
			'app_time'			=> $row['app_time'],
            'lock'			    => checktklock($con,$row['date']),
			'isApprover'        => $isApprover,
            'applevel'			=> $applevel, 
            'approvername'		=> $approvername
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

function getTotalRows($con,$search){
	$Qry = new Query();	
	$Qry->table ="vw_attendance_application";
	$Qry->selected ="id";
	$Qry->fields ="id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTotalRows');
	return mysqli_num_rows($rs);
}

function getAcctName($con, $idacct){
    $Qry=new Query();
    $Qry->table="tblaccount";
    $Qry->selected="CONCAT(
        `lname`,
       IFNULL(CONCAT(' ',  `suffix`), ''),
       ', ',
        `fname`,
       ' ',
       SUBSTR( `mname`, 1, 1),
       '. '
     ) AS empname";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['empname'];
    }
    return '';
}


function getAcctNames($con, $idaccts){
    $names = array();
    $Qry=new Query();
    $Qry->table="tblaccount";
    $Qry->selected="CONCAT(
        `lname`,
       IFNULL(CONCAT(' ',  `suffix`), ''),
       ', ',
        `fname`,
       ' ',
       SUBSTR( `mname`, 1, 1),
       '. '
     ) AS empname";
    $Qry->fields="id IN (".$idaccts.")";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){ 
            array_push($names, $row['empname']);
        }
    }
    return join(",", $names);
}

?>