<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	

$search='';

//if( array_key_exists('idsuperior', $param) && !empty($param['idsuperior']) ){ $search=$search." AND idsuperior 	= '".$param['idsuperior']."' ";  }
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
//     if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search; }
//     if( !empty( $param['from'] ) && empty( $param['to'] ) ){ $search=$search; }
// }
// else{
//     if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['to']."')"; }
//     if( !empty( $param['from'] ) && empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['from']."')"; }
    
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

//$where = $search;

//sort nga funtion sa table
if( $param['order'][0]['column'] !='' ){//default 
	$arrCols = array("",
					"empname",
					"date",
					"",
					"",
					"ob_status");//mao ra ang mailisan na declare na sa ubos php
	$search=$search." ORDER BY ". $arrCols[$param['order'][0]['column']] ." ".$param['order'][0]['dir'];//default
}

$where =  "((idsuperior = '" . $param['idsuperior'] ."') OR 
            (approver2_id = '" . $param['idsuperior'] ."') OR
            (approver3_id = '" . $param['idsuperior'] ."'))
            ".$search;

if( (int)$param['length'] >= 0 ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' && (int)$param['length'] >= 0 ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "vw_ob_application";
$Qry->selected  = "
id, docnumber, idacct, creator, creator_name,
empname, idsuperior, `date`, location, remarks,
cancelby, cancelby_name, cancel_date, cancel_time, approver1_id,
approver1, approver1_date, approver1_time, approver1_reason, approver1_stat,
approver2_id, approver2, approver2_date, approver2_time, approver2_reason,
approver2_stat, approver3_id, approver3, approver3_date, approver3_time,
approver3_reason, approver3_stat, approver4_id, approver4, approver4_date,
approver4_time, approver4_reason, approver4_stat, reason, stat, ob_status, id_payperiod, date_create, remarks_2";
 
// $Qry->fields    = "((idsuperior = '" . $param['idsuperior'] ."') OR 
//                     (approver2_id = '" . $param['idsuperior'] ."') OR
//                      (approver3_id = '" . $param['idsuperior'] ."') OR
//                      (approver4_id LIKE '%" . $param['idsuperior'] ."%'))
//                     ".$search;
$Qry->fields    = "((approver1_id LIKE '%" . $param['idsuperior'] ."%') OR 
                    (approver2_id LIKE '%" . $param['idsuperior'] ."%') OR
                    (approver3_id LIKE '%" . $param['idsuperior'] ."%') OR
                    (approver4_id LIKE '%" . $param['idsuperior'] ."%'))
                    ".$search;
                    
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
        $action = 0;
        $pending = 1;
        
        $applevel='';
        $approvername='';
        $isApprover=false;

        if(empty($row['approver1_stat'])){
            $heads_app1 = $row["approver1_id"];

            $approver1_count = count($heads_app1);
            if ($approver1_count > 1) {
                if(strpos( $row["approver1_id"], $param['idsuperior'])){
                    $action = 1; 
                }
                $applevel='Level 1';
                $approvername=getAcctNames($con, $row['approver1_id']);
                $isApprover = in_array($param['idsuperior'], $heads_app1);
            } else {
                if($row['approver1_id']== $param['idsuperior']){
                    $action = 1;
                }
                $applevel='Level 1';
                $approvername=getAcctName($con, $row['approver1_id']);
                $isApprover = ($param['idsuperior'] == $row['approver1_id']);
            }
        }


        if(!empty($row['approver1_stat'])){
            $action = 0;
            $pending++;

            $heads = $row["approver2_id"];

            $approver2_count = count($heads);
            if ($approver2_count > 1) {
                if(strpos( $row["approver2_id"], $param['idsuperior'])){
                    $action = 1; 
                }
                $applevel='Level 2';
                $approvername=getAcctNames($con, $row['approver2_id']);
                $isApprover = in_array($param['idsuperior'], $heads);
            } else {
                if( $param['idsuperior'] == $row["approver2_id"]){
                    $action = 1; 
                }
                $applevel='Level 2';
                $approvername=getAcctName($con, $row['approver2_id']);
                $isApprover = ($param['idsuperior'] == $row['approver2_id']);
            }
 
        }

        if(!empty($row['approver2_stat'])){
            $action = 0;
            $pending++;

            $heads = $row["approver3_id"];
 

            $approver3_count = count($heads);
            if ($approver3_count > 1) {
                $action = 1;
                $applevel='Level 3';
                $approvername=getAcctNames($con, $row['approver3_id']);
                $isApprover = in_array($param['idsuperior'], $heads);
            } else {
                $action = 1;
                $applevel='Level 3';
                $approvername=getAcctName($con, $row['approver3_id']);
                $isApprover = ($param['idsuperior'] == $row['approver3_id']);
            }
        }

        if(!empty($row['approver3_stat'])){
            $action = 0;
            $pending++;

            $heads = $row["approver4_id"];
 
            $approver4_count = count($heads);
            if ($approver4_count > 1) {
                $action = 1;
                $applevel='Level 4';
                $approvername=getAcctNames($con, $row['approver4_id']);
                $isApprover = in_array($param['idsuperior'], $heads);
            } else {
                $action = 1;
                $applevel='Level 4';
                $approvername=getAcctName($con, $row['approver4_id']);
                $isApprover = ($param['idsuperior'] == $row['approver4_id']);
            }
        }



        $approvercount = 0;
        if($row['approver1']){
            $approvercount++;
        }

        if($row['approver2']){
            $approvercount++;
        }

        if(empty($row['approver2']) && $row['approver3']){
            $approvercount++;
        }

        if($row['approver3']){
            $approvercount++;
        }

        if($row['approver4']){
            $approvercount++;
        }
       
        if ($action == 1) {
            if($row['stat'] == 1 || $row['stat'] == 2){
                $btn = '';
            }else{
                $btn = '<input ng-disabled="isSaving" type="checkbox" class="circlCheck2"
                value="' . $row["id"] . '"
                data-approvercount ="'. $approvercount .'" 
                data-date ="'. $row["date"] .'" 
                data-secapp ="'. $row["approver2"] .'"
                data-approver="' . $pending . '" ">';
            }
         
        } else {
            $btn = '';
        }
    
        $data["data"][] = array(
			"id"    			=>  $row["id"],    			       
			"docnumber"         =>  $row["docnumber"],       
			"creator"           =>  $row["creator"],         
			"creator_name"      =>  $row["creator_name"],         
			"idacct"            =>  $row["idacct"],          
			"empname"           =>  $row["empname"],   
			"idsuperior"		=>  $row["idsuperior"],   	
			"date"              =>  $row["date"],            
			"location"          =>  $row["location"],        
			"remarks"           =>  $row["remarks"].' - '.$row["remarks_2"],         
			"cancelby"          =>  $row["cancelby"],        
			"cancelby_name"     =>  $row["cancelby_name"],         
			"cancel_date"       =>  $row["cancel_date"],     
			"cancel_time"       =>  $row["cancel_time"],     
			"approver1_id"      =>  $row["approver1_id"],       
			"approver1"         =>  $row["approver1"],         
			"approver1_date"    =>  $row["approver1_date"],  
			"approver1_time"    =>  $row["approver1_time"],  
			"approver1_reason"  =>  $row["approver1_reason"],
			"approver1_stat"    =>  $row["approver1_stat"],  
			"approver2_id"      =>  $row["approver2_id"],       
			"approver2"         =>  $row["approver2"],         
			"approver2_date"    =>  $row["approver2_date"],  
			"approver2_time"    =>  $row["approver2_time"],  
			"approver2_reason"  =>  $row["approver2_reason"],
            "approver2_stat"    =>  $row["approver2_stat"],
            "approver3_id"      =>  $row["approver3_id"],       
			"approver3"         =>  $row["approver3"],         
			"approver3_date"    =>  $row["approver3_date"],  
			"approver3_time"    =>  $row["approver3_time"],  
			"approver3_reason"  =>  $row["approver3_reason"],
			"approver3_stat"    =>  $row["approver3_stat"], 
            "approver4"         =>  $row["approver4"],         
			"approver4_date"    =>  $row["approver4_date"],  
			"approver4_time"    =>  $row["approver4_time"],  
			"approver4_reason"  =>  $row["approver4_reason"],
			"approver4_stat"    =>  $row["approver4_stat"],    
			"reason"           	=>  $row["reason"],          
			"stat"             	=>  $row["stat"],            
			"ob_status"        	=>  ucwords(strtolower($row["ob_status"])),       
			"id_payperiod"      =>  $row["id_payperiod"],    
            "date_create"       =>  $row["date_create"],
            "btn"               =>  $btn,
            'applevel'			=> $applevel,  
            'approvername'		=> $approvername,
            'isApprover'        => $isApprover
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
	$Qry->table ="vw_ob_application";
	$Qry->selected ="id";
	$Qry->fields =$search;
	$rs = $Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getTotalRows');
	return mysqli_num_rows($rs);
}

function getAcctName($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="empname";
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

function explodedStringIdstoArray($con,$idacct,$ids){
    $explodedidacct =  explode(', ', $ids);
    if (in_array($idacct, $explodedidacct)){
        print_r($explodedidacct);
        return true;
    }else{
        print_r('no result');
        return false;
    }
}

?>