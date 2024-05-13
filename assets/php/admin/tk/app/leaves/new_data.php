<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	
$pay_period = getPayPeriod($con);



if( !empty( $param['search_depts'] ) ){ 
 
    //print_r($param['search_depts']);
    $id_array = getLocationsbunits($con,$param['search_depts']);
    $ids = implode(",",$id_array);
   //print_r($ids);
}

$search='';

if( !empty( $param['business_unit'])){ $search=$search." AND `business_unit`= '".$param['business_unit']."' ";}
if( !empty( $param['search_depts'])){ $search=$search." AND id	= '".$param['search_depts']."' ";}
//if( array_key_exists('idsuperior', $param) && !empty($param['idsuperior']) ){ $search=$search." AND idsuperior 	= '".$param['idsuperior']."' ";  }
if( !empty( $param['search_acct'] ) ){ $search=$search." AND idacct 	= '".$param['search_acct']."' "; }
if( !empty( $param['search_depts'] ) ){ $search = $search . " AND idunit IN   (". $ids .") "; }
// if( !empty($param['_from']) && empty($param['_to'])){
//     $search=$search." AND date BETWEEN DATE('".$param['_from']."') AND DATE('".$param['_from']."') ";
// }
// if( !empty($param['_from']) && !empty($param['_to']) ){
//     $search=$search." OR date BETWEEN DATE('".$param['_from']."') AND DATE('".$param['_to']."') ";   
// }
if(!empty($param['_from'])){
    if( !empty($param['_from']) && empty($param['_to'])){
        $search=$search." AND date BETWEEN DATE('".$param['_from']."') AND DATE('".$param['_from']."') ";
    }
    if( !empty($param['_from']) && !empty($param['_to']) ){
        $search=$search." AND date BETWEEN DATE('".$param['_from']."') AND DATE('".$param['_to']."') ";   
    }
}else{
    $data = array( 
		"period"		=> getPayPeriodts($con),
    );
    
    // if( !empty($data['period']['pay_start']) && empty( $data['period']['pay_end'])){
    //     $search=$search." AND date BETWEEN DATE('".$data['period']['pay_start']."') AND DATE('".$data['period']['pay_start']."') ";
    // }
    // if( !empty($data['period']['pay_start']) && !empty( $data['period']['pay_end']) ){
    //     $search=$search." AND date BETWEEN DATE('".$data['period']['pay_start']."') AND DATE('". $data['period']['pay_end']."') ";   
    // }
    $search=" AND (date between '".$data['period']['pay_start']."' AND '".$data['period']['pay_end']."')";
    if( !empty($data['period']['pay_start']) && empty( $data['period']['pay_end'])){
        $search=$search." AND (date between '".$data['period']['pay_start']."' AND '".$data['period']['pay_end']."')";
    }
    if( !empty($data['period']['pay_start']) && empty( $data['period']['pay_end']) ){
        $search=$search." AND (date between '".$data['period']['pay_start']."' AND '".$data['period']['pay_start']."')";
    }
}

$where = $search;

//sort nga funtion sa table
if( $param['order'][0]['column'] !='' ){//default 
	$arrCols = array("count",
                    "empid",
					"empname",
					"business_unit",
                    "date_create",
					"date",
					"leavename",
					"units",
					"remarks",
                    "leave_status");//mao ra ang mailisan na declare na sa ubos php
	$search=$search." ORDER BY ". $arrCols[$param['order'][0]['column']] ." ".$param['order'][0]['dir'];//default
}

if( (int)$param['length'] >= 0 ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' && (int)$param['length'] >= 0 ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "vw_leave_application";
$Qry->selected  = "*";
$Qry->fields    = "id > 0".$search;
$rs = $Qry->exe_SELECT($con);
$recFiltered = getTotalRows($con,$where);
if(mysqli_num_rows($rs)>= 1){
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs),
        "recordsFiltered"=> $recFiltered,
        "qry"=>$Qry->fields,
        "data"=>array()
    );
    $count=1;
    while($row=mysqli_fetch_array($rs)){
 
        $units = sprintf('%0.2f',($row['hrs'] / 8));

        $applevel='';
        $approvername='';
        $pendinginfo='';

        if(empty($row['approver1_stat'])){
            $approver1 = explode(',',$row['approver1']);
            $approver1_count = count($approver1);
            if ($approver1_count > 1) { 
                $applevel='Level 1';
                $approvername=getAcctNames($con, $row['approver1']); 
            } else { 
                $applevel='Level 1';
                $approvername=getApproverName($con, $row['approver1']); 
            }
            $pendinginfo = ' '. $applevel . ': ' . $approvername;
        }

        if(!empty($row['approver1_stat'])){ 
            $approver2 = explode(',',$row['approver2']); 
            $approver2_count = count($approver2);
            if ($approver2_count > 1) { 
                $applevel='Level 2';
                $approvername=getAcctNames($con, $row['approver2']); 
            } else { 
                $applevel='Level 2';
                $approvername=getApproverName($con, $row['approver2']); 
            } 
            $pendinginfo = ' '. $applevel . ': ' . $approvername;
        }

        if(!empty($row['approver2_stat'])){
            $approver3 = explode(',',$row['approver3']); 
            $approver3_count = count($approver3);
            if ($approver3_count > 1) { 
                $applevel='Level 3';
                $approvername=getAcctNames($con, $row['approver3']); 
            } else { 
                $applevel='Level 3';
                $approvername=getApproverName($con, $row['approver3']); 
            }
            $pendinginfo = ' '. $applevel . ': ' . $approvername;
        }
        
        if(!empty($row['approver3_stat'])){ 
            $approver4 = explode(',',$row['approver4']); 
            $approver4_count = count($approver4);
            if ($approver4_count > 1) { 
                $applevel='Level 4';
                $approvername=getAcctNames($con, $row['approver4']); 
            } else { 
                $applevel='Level 4';
                $approvername=getApproverName($con, $row['approver4']); 
            }
            $pendinginfo = ' '. $applevel . ': ' . $approvername;
        }

        $data["data"][] = array(
            'count'        		=> $count,
            'empid'			    => $row['empid'],
            'empname'			=> $row['empname'],
            'business_unit'		=> $row['business_unit'],
            'date_create'		=> $row['date_create'],
            'date_approve'		=> $row['date_approve'],
            'approver1_time'	=> '',//$row['approver1_time'],
            'work_date'			=> $row['date'],
            'leavename'			=> $row['leave_name'],
            'units'				=> $units,
            'remarks'			=> $row['remarks'],
            'leavestat'			=> $row['leave_status'],
            'pendinginfo'		=> $pendinginfo
        );
        $count++;
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
    $pay_period = getPayPeriod($con);
	$Qry = new Query();	
    $Qry->table     = "vw_leave_application";
    $Qry->selected  = "*";
    $Qry->fields    = "date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."'".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}
function getAcctNames($con, $idaccts){
    $names = array();
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="empname";
    $Qry->fields="id IN (".$idaccts.")";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){ 
            array_push($names, $row['empname']);
        }
    }
    return join(",", $names);
}

function getApproverName($con,$idacct){
	$Qry = new Query();	
	$Qry->table ="vw_dataemployees";
	$Qry->selected ="empname";
    $Qry->fields ="id = '" . $idacct . "'";
    $rs            = $Qry->exe_SELECT($con);
    if (mysqli_num_rows($rs) >= 1) {
        return mysqli_fetch_assoc($rs)['empname'];
    }
}

?>