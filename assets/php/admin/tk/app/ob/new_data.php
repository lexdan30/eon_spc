<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	
$pay_period = getPayPeriod($con);

// if( !empty( $param['search_depts'] ) ){
//     $id_array = getLocationsbunits($con,$param['search_depts']);
//     $ids = implode(",",$id_array);
// }
$arr_id = array();
if( !empty( $param['business_unit'] ) ){ 
 

   $ids=0;
   $dept =  $param['business_unit'];
   if( !empty($dept) ){
       $arr 	= getHierarchy($con,$dept);
       array_push( $arr_id, $dept );

       if( !empty( $arr["nodechild"] ) ){
           $a = getChildNodes($arr_id, $arr["nodechild"]);

           if( !empty($a) ){
               foreach( $a AS $v ){
                   array_push( $arr_id, $v );
               }
           }
       }
       if( count($arr_id) == 1 ){
           $ids 			= $arr_id[0];
       }else{
           $ids 			= implode(",",$arr_id);
       }
   }
}

$search='';

if( !empty( $param['search_acct'] ) ){ $search=$search." AND idacct 	= '".$param['search_acct']."' "; }
if( !empty( $param['business_unit'])){ $search=$search. " AND idunit IN       (". $ids .") ";}
if( !empty( $param['search_depts'] ) ){ $search = $search . " AND idunit IN   (". $ids .") "; }

// if( !empty($param['dfrom']) && empty($param['dto'])){
//     $search=$search." AND date BETWEEN DATE('".$param['dfrom']."') AND DATE('".$param['dfrom']."') ";
// }
// if( !empty($param['dfrom']) && !empty($param['dto']) ){
//     $search=$search." AND date BETWEEN DATE('".$param['dfrom']."') AND DATE('".$param['dto']."') ";   
// }
if(!empty($param['dfrom'])){
    if( !empty($param['dfrom']) && empty($param['dto'])){
        $search=$search." AND date BETWEEN DATE('".$param['dfrom']."') AND DATE('".$param['dfrom']."') ";
    }
    if( !empty($param['dfrom']) && !empty($param['dto']) ){
        $search=$search." AND date BETWEEN DATE('".$param['dfrom']."') AND DATE('".$param['dto']."') ";   
    }
}else{
    $data = array( 
		"period"		=> getPayPeriodts($con),
    );
    
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
					"empname",
					"business_unit",
                    "date_create",
					"date",
					"location",
					"remarks",
                    "ob_status");//mao ra ang mailisan na declare na sa ubos php
	$search=$search." ORDER BY ". $arrCols[$param['order'][0]['column']] ." ".$param['order'][0]['dir'];//default
}



if( (int)$param['length'] >= 0 ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' && (int)$param['length'] >= 0 ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "vw_ob_application";
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

        $applevel='';
        $approvername='';
        $pendinginfo='';

        if(empty($row['approver1_stat'])){
            $approver1 = explode(',',$row['approver1_id']);
            $approver1_count = count($approver1);
            if ($approver1_count > 1) { 
                $applevel='Level 1';
                $approvername=getAcctNames($con, $row['approver1_id']); 
            } else { 
                $applevel='Level 1';
                $approvername=getApproverName($con, $row['approver1_id']); 
            }
            $pendinginfo = ' '. $applevel . ': ' . $approvername;
        }

        if(!empty($row['approver2_stat'])){ 
            $approver2 = explode(',',$row['approver2_id']); 
            $approver2_count = count($approver2);
            if ($approver2_count > 1) { 
                $applevel='Level 2';
                $approvername=getAcctNames($con, $row['approver2_id']); 
            } else { 
                $applevel='Level 2';
                $approvername=getApproverName($con, $row['approver2_id']); 
            } 
            $pendinginfo = ' '. $applevel . ': ' . $approvername;
        }

        if(!empty($row['approver3_stat'])){
            $approver3 = explode(',',$row['approver3_id']); 
            $approver3_count = count($approver3);
            if ($approver3_count > 1) { 
                $applevel='Level 3';
                $approvername=getAcctNames($con, $row['approver3_id']); 
            } else { 
                $applevel='Level 3';
                $approvername=getApproverName($con, $row['approver3_id']); 
            }
            $pendinginfo = ' '. $applevel . ': ' . $approvername;
        }
        
        if(!empty($row['approver4_stat'])){ 
            $approver4 = explode(',',$row['approver4_id']); 
            $approver4_count = count($approver4);
            if ($approver4_count > 1) { 
                $applevel='Level 4';
                $approvername=getAcctNames($con, $row['approver4_id']); 
            } else { 
                $applevel='Level 4';
                $approvername=getApproverName($con, $row['approver4_id']); 
            }
            $pendinginfo = ' '. $applevel . ': ' . $approvername;
        }

        $data["data"][] = array(
            'count'			=> $count,
            'empname'			=> $row['empname'],
            'business_unit'		=> $row['business_unit'],
            'date_create'		=> $row['date_create'],
            'date'			    => $row['date'],
            'approver1_time'	=> $row['approver1_time'],
            'approver1_date'    => $row['approver1_date'],
            'location'			=> $row['location'],
            'remarks'			=> $row['remarks'],
            'ob_status'			=> $row['ob_status'],
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
	$Qry->table ="vw_ob_application";
	$Qry->selected ="*";
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