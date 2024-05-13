<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$year = date("Y");
$search ='';

if( !empty( $param->deppt ) ){ $search=$search." AND idunit = '".$param->deppt."' "; }
if( !empty( $param->costcenter ) ){ $search=$search." AND costcenter = '".$param->costcenter."' "; }
if( !empty( $param->jobloc ) ){ $search=$search." AND idloc = '".$param->jobloc."' "; }

// $date_arr=array();
// $Qry = new Query();	
// $Qry->table     = "vw_leave_application";
// $Qry->selected  = "id,idunit,costcenter,idloc,empname,MIN(DATE) AS min_date, MAX(DATE) AS max_date";
// $Qry->fields    = "id>0 AND stat = 1 ".$search." GROUP BY MONTH(DATE)";
// $rs = $Qry->exe_SELECT($con);
// if(mysqli_num_rows($rs)>= 1){
//     while($row=mysqli_fetch_array($rs)){

//         $earlier = new DateTime($row['min_date']);
//         $later = new DateTime($row['max_date']);
        
//         $diff = $later->diff($earlier)->format("%a")+1;

//         $data[] = array( 
//             "empname"        => $row['empname'],
//             "sdate"          => $row['min_date'],
//             "edate"          => $row['max_date'],
//             "no_days"        => $diff,
//             "costcenter"          => $row['costcenter'],
//         );
//     }
//     $return = json_encode($data);
// }else{
//     $return = json_encode(array('q'=>$Qry->fields));
// }

$date_arr=array();
$Qry = new Query();	
$Qry->table     = "vw_leave_application";
$Qry->selected  = "id,idunit,idacct,costcenter,idloc,empname,MIN(DATE) AS min_date, MAX(DATE) AS max_date,COUNT(*) as diff";
$Qry->fields    = "id>0 AND stat = 1  AND YEAR(`date`) = '".$year."' ".$search." GROUP BY idacct";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){ 

        $earlier = new DateTime($row['min_date']);
        $later = new DateTime($row['max_date']);
        $diff = $later->diff($earlier)->format("%a")+1;
        $data[] = array( 
            "empname"        => $row['empname'],
            "sdate"          => $row['min_date'],
            "edate"          => $row['max_date'],
            "no_days"        => $row['diff'], //$diff,
            "costcenter"          => $row['costcenter'],
        );
    }
    $return = json_encode($data);
}else{
    $return = json_encode(array('q'=>$Qry->fields));
}


print $return;
mysqli_close($con);



?>