<?php
require_once('../../../../logger.php');
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$str =  $param->accountid;
$search ='';

// if( !empty( $param->deppt ) ){ $search=$search." AND idunit = '".$param->deppt."' "; }
if( !empty( $param->costcenter ) ){ $search=$search." AND costcenter = '".$param->costcenter."' "; }
if( !empty( $param->jobloc ) ){ $search=$search." AND idloc = '".$param->jobloc."' "; }

$year = date("Y");

$dept = getIdUnit($con,$param->accountid);
$ids=0;
//Get Managers Under person
if( !empty( $dept ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$dept);
    if( !empty( $arr["nodechild"] ) ){     
        $ids = join(',', flatten($arr['nodechild']));
    } else {
        $ids = '0';
    }
}


$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees";
$Qry->selected  = "GROUP_CONCAT(id) as idacct";
$Qry->fields    = "(idunit IN (".$ids.") OR idsuperior='".$param->accountid."') AND id != '".$param->accountid."'";
$rs 			= $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    $str = mysqli_fetch_assoc($rs)['idacct'];
}

// $date_arr=array();
// $Qry = new Query();	
// $Qry->table     = "vw_leave_application";
// $Qry->selected  = "id,idunit,costcenter,idloc,empname,MIN(DATE) AS min_date, MAX(DATE) AS max_date";
// $Qry->fields    = "idacct IN (".$str.") ".$search." GROUP BY MONTH(DATE)";
// $rs = $Qry->exe_SELECT($con);
// if(mysqli_num_rows($rs)>= 1){
//     while($row=mysqli_fetch_array($rs)){
//         //Get the difference between 2 dates
//         $earlier = new DateTime($row['min_date']);
//         $later = new DateTime($row['max_date']);
//         $diff = $later->diff($earlier)->format("%a")+1;

//         $data[] = array( 
//             "empname"        => $row['empname'],
//             "sdate"          => $row['min_date'],
//             "edate"          => $row['max_date'],
//             "no_days"        => $diff, 
//             "costcenter"     => $row['costcenter'],
//             "getEmpInfo"     =>getEmpInfo($con, $str),
//             "getEmpjobLoc"   =>getEmpjobLoc($con, $str),
//         );
//     }
//     $return = json_encode($data);
// }else{
//     $return = json_encode(array('q'=>$Qry->fields));
// }
$date_arr=array();
$Qry = new Query();	
$Qry->table     = "vw_leave_application";
$Qry->selected  = "id,idunit,costcenter,idloc,empname,MIN(DATE) AS min_date, MAX(DATE) AS max_date,COUNT(*) as diff";
$Qry->fields    = "idacct IN (".$str.") AND YEAR(`date`)='".$year."' AND stat = 1 ".$search." GROUP BY empname";
$rs = $Qry->exe_SELECT($con);
Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs));
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        //Get the difference between 2 dates
        $earlier = new DateTime($row['min_date']);
        $later = new DateTime($row['max_date']);
        $diff = $later->diff($earlier)->format("%a")+1;

        $data[] = array( 
            "empname"        => $row['empname'],
            "sdate"          => $row['min_date'],
            "edate"          => $row['max_date'],
            "no_days"        => $row['diff'], //$diff,
            "costcenter"     => $row['costcenter'],
            "getEmpInfo"     =>getEmpInfo($con, $str),
            "getEmpjobLoc"   =>getEmpjobLoc($con, $str),
        );
    }
    $return = json_encode($data);
}else{
    $return = json_encode(array('q'=>$Qry->fields));
}
print $return;
mysqli_close($con);


function getIdUnit($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getIdUnit');
    if(mysqli_num_rows($rs)>=1){
        return mysqli_fetch_assoc($rs)['idunit'];
    }
    return null;
}


function getEmpInfo($con, $ids){
    $data = array();
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="DISTINCT(costcenter)";
    $Qry->fields="id IN (".$ids.")";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getEmpInfo');
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
			
			$data[] = array(
				"costcenter" => $row['costcenter'],

			);

        }
    }
    return $data;
}

function getEmpjobLoc($con, $ids){
    $data = array();
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="DISTINCT(job_loc)";
    $Qry->fields="id IN (".$ids.")";
    $rs=$Qry->exe_SELECT($con);
    Log::v(strlen($con->error) > 0 ? " Err: ".$con->error : ""." num_count: ".mysqli_num_rows($rs), 'getEmpjobLoc');
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_assoc($rs)){
			
			$data[] = array(
				"job_loc" => $row['job_loc'],

			);

        }
    }
    return $data;
}


?>