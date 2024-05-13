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

// if( array_key_exists('idsuperior', $param) && !empty($param['idsuperior']) ){ 
// 	if( array_key_exists('units', $param) && !empty($param['units']) ){
// 		$search=$search." AND (idsuperior 	= '".$param['idsuperior']."' OR idsuperior in (".$param['units'].") ) ";  
// 	}else{
// 		$search=$search." AND idsuperior 	= '".$param['idsuperior']."' ";  
// 	}
// }
if( !empty( $param['acct'] ) ){ $search=$search." AND idacct 	= '".$param['acct']."' "; }
if( !empty( $param['business_unit'] ) ){ $search=$search." AND idunit IN    (". $ids .") "; }
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
					"empname",
					"business_unit",
                    "date_create",
					"date",
					"app_time",
					"planned_hrs",
					"remarks",
                    "ot_status");//mao ra ang mailisan na declare na sa ubos php
	$search=$search." ORDER BY ". $arrCols[$param['order'][0]['column']] ." ".$param['order'][0]['dir'];//default
}



if( (int)$param['length'] >= 0 ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' && (int)$param['length'] >= 0 ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "vw_overtime_approval";
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
		
		// $ot_s = date('Y-m-d h:i A', strtotime($row['planned_start']));
		// $arr_s = explode(" ",$row['planned_start']);
		// if( $arr_s[0] == $row['date'] ){
		// 	$ot_s = date('h:i A', strtotime($arr_s[1]));
		// }
		
		// $ot_f = date('Y-m-d h:i A', strtotime($row['planned_end']));
		// $arr_f = explode(" ",$row['planned_end']);
		// if( $arr_f[0] == $row['date'] ){
		// 	$ot_f = date('h:i A', strtotime($arr_f[1]));
		// }

        if($row['approve_hr'] == '2'){
            //$units = sprintf('%0.2f',($row['appr_actual_hrs'] / 60));
            //$units = $row['appr_actual_hrs'];
            $remarks = 'Actual unit';
        }else{
            //$units = sprintf('%0.2f',($row['planned_hrs'] / 60));
           // $units = $row['planned_hrs'];
            $remarks = 'Planned unit';
        }

        if(empty($row['planned_start']) && empty($row['planned_end']) ){
			$drange = 'Early OT';
		}else{
			$ot_s = date('Y-m-d h:i A', strtotime($row['planned_start']));
			$arr_s = explode(" ",$row['planned_start']);
			if( $arr_s[0] == $row['date'] ){
				$ot_s = date('h:i A', strtotime($arr_s[1]));
			}
			
			$ot_f = date('Y-m-d h:i A', strtotime($row['planned_end']));
			$arr_f = explode(" ",$row['planned_end']); 
			if( $arr_f[0] == $row['date'] ){
				$ot_f = date('h:i A', strtotime($arr_f[1]));
			}
			$drange = $ot_s. ' to ' .$ot_f;
        }
        
        if(empty($row['act_hrs']) || $row['act_hrs'] == '0.00'){
            $hrs = $row['planned_hrs'];
        }else{
            $hrs = $row['act_hrs'];
        }

        
        $data["data"][] = array(
            'count'				=> $count,    
            'empname'			=> $row['empname'],
            'business_unit'		=> $row['business_unit'],
            'date_create'		=> $row['date_create'],
            'date'			    => $row['date'],
            'approver1_time'	=> $row['approver1_time'],
            'date_approve'	    => $row['date_approve'],
            'app_time'			=> $drange,//$row['app_time'],
            // 'units2'			=> $row['planned_hrs'],
            'remarks'			=> $row['remarks'],
            'units2'			=> $hrs , //$row['act_hrs'],//$units ,
            'remarksunit'			=>$remarks ,
            'ot_status'			=> $row['ot_status'],           

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
	$Qry->table ="vw_overtime_approval";
	$Qry->selected ="*";
	$Qry->fields    = "date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."'".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>