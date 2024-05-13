<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

    $search ='';

    if( !empty( $param->empid ) ){ $search=$search." AND empid like 	'%".$param->empid."%' "; }
    if( !empty( $param->position_title ) ){ $search=$search." AND post like   '%".$param->position_title."%' "; }
    if( !empty( $param->pay_group ) ){ $search=$search." AND pay_grp like   '%".$param->pay_group."%' "; }
    if( !empty( $param->job_level ) ){ $search=$search." AND idlvl =   '".$param->job_level."' "; }
	if( !empty( $param->search_labor_type ) ){ $search=$search." AND labor_type = '".$param->search_labor_type."' "; }
    //HIRED SEARCH
    if( !empty($param->search_hired_date_from) && empty($param->search_hired_date_to)){
        $search=$search." AND hdate BETWEEN DATE('".$param->search_hired_date_from."') AND DATE('".$param->search_hired_date_from."') ";
    }
    
    if( !empty($param->search_hired_date_from) && !empty($param->search_hired_date_to) ){
        $search=$search." AND hdate BETWEEN DATE('".$param->search_hired_date_from."') AND DATE('".$param->search_hired_date_to."') ";
        
    }
    //REGULARIZATION SEARCH
    if( !empty($param->search_reg_date_from) && empty($param->search_reg_date_to)){
        $search=$search." AND rdate BETWEEN DATE('".$param->search_reg_date_from."') AND DATE('".$param->search_reg_date_from."') ";
    }
    
    if( !empty($param->search_reg_date_from) && !empty($param->search_reg_date_to) ){
        $search=$search." AND rdate BETWEEN DATE('".$param->search_reg_date_from."') AND DATE('".$param->search_reg_date_to."') ";
        
    }
    
    //Search Department
    if( !empty( $param->department ) ){
        $arr_id = array();
        $arr 	= getHierarchy($con,$param->department);
        array_push( $arr_id, $param->department );
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



    $Qry = new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "*";
    $Qry->fields    = "id>0".$search;
    $rs = $Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>= 1){ 
        while($row=mysqli_fetch_array($rs)){

            //Format date for display
			$hired_date_format=date_create($row['hdate']);

			if(!empty($row['rdate'])){
				$reg_date_format=date_create($row['rdate']);
				$reg_date_format=date_format($reg_date_format,"m/d/Y ");
			}else{
				$reg_date_format = '';
			}

            $data[] = array( 
            "id"        	        => $row['id'],
            "empid"			        => $row['empid'],
            "empname" 		        => (($row['empname'])),
            "post" 		            => ucwords($row['post']),
            "job_lvl" 		        => ucwords(strtolower($row['joblvl'])),
            "pay_grp" 		        => ucwords($row['pay_grp']),
            "labor_type" 		    => ucwords($row['labor_type']),
            "hire_date" 		    => date_format($hired_date_format,"m/d/Y"), 
            "reg_date" 		        => $reg_date_format,
            "dept" 		            => ucwords(strtolower($row['business_unit'])),
            // "section" 		    => ucwords(strtolower($row['section'])),
            "salary"                => number_format($row['salary'], 2, '.', ','),
            "clothing"              => number_format($row['clothingallowance'], 2, '.', ','),
            "laundry"               => number_format($row['laundryallowance'], 2, '.', ','),
            "rice"                  => number_format($row['riceallowance'], 2, '.', ','),
			"total_compensation" 	=> $row['tot_compensation'],
			"annual_gross_income" 	=> $row['annual_gross'],
			"getCompAllowance"      => getCompAllowance($con, $row['id'])
            // "clothing" 		    => ucwords(strtolower($row['clothing'])),
            // "totalcomp" 		    => ucwords(strtolower($row['totalcomp'])),
            // "annualgross" 		=> ucwords(strtolower($row['annualgross'])),




            );
        }

        $return = json_encode($data);

    }
    else {

        $return = json_encode(array());
    }



$return = json_encode($data);
print $return;
mysqli_close($con);


function getCompAllowance($con, $accountid){
	$Qry=new Query(); 
	$data=array();
    $Qry->table="tblacctallowance LEFT JOIN tblallowance ON tblacctallowance.idallowance = tblallowance.id";
    $Qry->selected="*";
    $Qry->fields="tblacctallowance.id>0  AND idacct='".$accountid."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){

			$data[] = array( 
				"id"            => $row['id'],
                "idacct"        => $row['idacct'],
                "description"   => $row['description'],
                // "idmethod"      => $row['idmethod'] ? $row['idmethod'] : '1',
                "amt"           => $row['amt'] ? number_format($row['amt'],2) : '0.00',
			);
        }
    }
    return $data;
}



?>