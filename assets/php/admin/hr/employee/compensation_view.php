<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = $_POST;
$data  = array();
$date  = SysDateDan();
$time  = SysTime();


$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "*";
$Qry->fields    = "id > 0";
$rs 			= $Qry->exe_SELECT($con);
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
            "empname" 		        => $row['empname'],
            "post" 		            => ucwords($row['post']),
            "job_lvl" 		        => ucwords(strtolower($row['joblvl'])),
            "pay_grp" 		        => ucwords($row['pay_grp']),
            "labor_type" 		    => ucwords($row['labor_type']),
            "hire_date"             => date_format($hired_date_format,"m/d/Y"),  
            "reg_date" 		        => $reg_date_format,
            "dept" 		            => ucwords(strtolower($row['business_unit'])),
            // "section" 		        => ucwords(strtolower($row['section'])),
            "salary"                => number_format($row['salary'], 2, '.', ','),
            "clothing"              => number_format($row['clothingallowance'], 2, '.', ','),
            "laundry"               => number_format($row['laundryallowance'], 2, '.', ','),
            "rice"                  => number_format($row['riceallowance'], 2, '.', ','),
            "total_compensation"    => $row['tot_compensation'],
            "annual_gross_income"   => $row['annual_gross'],
            "date"                  => $date,
            "time"                  => date ("H:i:s A",strtotime($time)),
            "getCompAllowance"      => getCompAllowance($con, $row['id'])
            // "clothing" 		    => ucwords(strtolower($row['clothing'])),
            // "totalcomp" 		    => ucwords(strtolower($row['totalcomp'])),
            // "annualgross" 		=> ucwords(strtolower($row['annualgross'])),

			
        );
        $return = json_encode($data);
    }
}else{
    $return = json_encode(array('status'=>'error'));
}


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