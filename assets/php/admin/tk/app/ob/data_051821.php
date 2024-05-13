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
if( !empty( $param['appstat'] ) ){ $search=$search." AND stat = '".$param['appstat']."' "; }
if( !empty( $param['from'] ) && !empty( $param['to'] ) ){ $search=$search." AND (date between '".$param['from']."' AND '".$param['to']."')"; }

$where = $search;

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


if( (int)$param['length'] >= 0 ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' && (int)$param['length'] >= 0 ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "vw_ob_application";
$Qry->selected  = "*";
$Qry->fields    = "((approver1_id = '" . $param['idsuperior'] ."') OR 
                    (approver2_id = '" . $param['idsuperior'] ."'))
                    ".$search;
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
    while($row=mysqli_fetch_array($rs)){		
        $action = 0;
        $pending = 1;

        if($param['idsuperior'] == $row["approver1_id"]){
            $action = 1;
        }

        if(!empty($row['approver1_stat'])){
            $action = 0;
            $pending++;

            if($param['idsuperior'] == $row["approver2_id"]){
                $action = 1;
            }
        }

        $approvercount = 2;
       
        if ($action == 1) {
            if($row['stat'] == 1 || $row['stat'] == 2){
                $btn = '';
            }else{
                $btn = '<input ng-disabled="isSaving" type="checkbox" class="circlCheck2"
                value="' . $row["id"] . '"
                data-approvercount ="'. $approvercount .'" 
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
			"remarks"           =>  $row["remarks"],         
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
			"reason"           	=>  $row["reason"],          
			"stat"             	=>  $row["stat"],            
			"ob_status"        	=>  ucwords(strtolower($row["ob_status"])),       
			"id_payperiod"      =>  $row["id_payperiod"],    
            "date_create"       =>  $row["date_create"],
            "btn"               =>  $btn,
            
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
	$Qry->selected ="*";
	$Qry->fields ="id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>