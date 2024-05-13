<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$ga                 = getTotal($con,'G & A');
$directlabor        = getTotal($con, 'Direct Labor');
$indirectlabor      = getTotal($con,'Indirect Labor');
$fixedindirect      = getTotal($con,'Fixed Indirect');
$executive          = getExecutives($con);
$dailies            = getPaygroup($con,'Dailies');
$monthlies            = getPaygroup($con,'Monthlies');
$piecerate            = getPaygroup($con,'Piece Rate');


$data = array(  
                "ga"	        => $ga,
                "directlabor"	=> $directlabor,
                "indirectlabor"	=> $indirectlabor,
                "fixedindirect"	=> $fixedindirect,
                "executive"	    => $executive,
                 "dailies"	    => $dailies,
                 "monthlies"	    => $monthlies,
                 "piecerate"	    => $piecerate,
            );
$return = json_encode($data);


function getPaygroup($con,$type){
    $data = array();	
    $Qry = new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "COUNT(*) AS count";
    $Qry->fields    = "pay_grp = '".$type."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $data = array( 
                "count"        => $row['count']
            );
        }
    }
    return $data;
}
function getExecutives($con){
    $data = array();	
    $Qry = new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "COUNT(*) AS count";
    $Qry->fields    = "joblvl = 'Executive'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $data = array( 
                "count"        => $row['count']
            );
        }
    }
    return $data;
}
function getTotal($con,$lt){
    $data = array();	
    $Qry = new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "COUNT(*) AS count";
    $Qry->fields    = "labor_type = '".$lt."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $data = array( 
                "count"        => $row['count']
            );
        }
    }
    return $data;
}
print $return;
mysqli_close($con);
?>