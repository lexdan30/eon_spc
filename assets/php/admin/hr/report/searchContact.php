<?php

// session_start();
// $auth = isset($_SESSION['isAuth']) ? $_SESSION['isAuth']: false;
// if($auth){

require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
if( !empty($param->accountid)){
$data  = array();

    $search ='';

    if( !empty( $param->empid ) ){ $search=$search." AND empid like 	'%".$param->empid."%' "; }
    // if( !empty( $param->department ) ){ $search=$search." AND business_unit like   '%".$param->department."%' "; }
    //HIRED SEARCH
   

   


    $Qry = new Query();	
    $Qry->table     = "vw_dataemployees AS de
                        LEFT JOIN tbljoblvl AS jl
                        ON de.idlvl = jl.id";
    $Qry->selected  = "de.empid,de.empname ,de.business_unit AS Department,CONCAT(de.joblvl,' ',jl.alias) AS `Rank/Level`,de.cnumber,de.addr_st";
    $Qry->fields    = "de.id>0".$search;
    $rs = $Qry->exe_SELECT($con);
        if(mysqli_num_rows($rs)>= 1){ 
        while($row=mysqli_fetch_array($rs)){

           

            $data[] = array( 
                "empid"			        => $row['empid'],
                "empname" 		        => (($row['empname'])),
                "contact"               => $row['cnumber'],
                "joblvl"               => $row['Rank/Level'],
                "department"            => $row['Department'],
                "address"               => $row['addr_st'],
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
}

?>