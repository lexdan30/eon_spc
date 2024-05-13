<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param 		= json_decode(file_get_contents('php://input'));
// $Qry->fields    = " `date` BETWEEN '".$param->dfrom."' AND '".$param->dto."'";
// print "$param->dfrom ";
// print "$param->dfrom";

$totempactive =  getTotEmpActive($con);

$Qry 			= new Query();	
$Qry->table     = "vw_attendancebydept";
$Qry->selected  = "SUM(tot) as totsum"; 
$Qry->fields    = "id>0";
$rs 			= $Qry->exe_SELECT($con);
$totbydept      = getAllDept($con,$param);
$emptoday      = getPresenttoday($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[]  = array( 
            "totemp"            => $totempactive,
            "emptoday"           => $emptoday,
            "deptinfo"          => $totbydept
        );
    }
    $return = json_encode($data);
}else{
    $return = json_encode(array());
}

print $return;
mysqli_close($con);

function getTotAVEByDept($con,$param){ 
    $data2 = array();
    $Qry 			= new Query();	
    $Qry->table     = "vw_data_attendancebydept";
    $Qry->selected  = "`name`, AVG(tot) AS `average`";
    $Qry->fields    = " id>0 AND (`date` BETWEEN '".$param->dfrom."' AND '".$param->dto."') GROUP BY `name`";
    $rs 			= $Qry->exe_SELECT($con);
    
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $data2[]  = array( 
                "name"         => $row['name'],
                "tot"          => $row['average']
            );
        }
    }
    return $data2;
}

function getTotSUMByDept($con,$param){
    $data2 = array();
    $Qry 			= new Query();	
    $Qry->table     = "vw_data_attendancebydept";
    $Qry->selected  = "`name`, SUM(tot) AS `sum`";
    $Qry->fields    = " id>0 AND (`date` BETWEEN '".$param->dfrom."' AND '".$param->dto."') GROUP BY `name`";
    $rs 			= $Qry->exe_SELECT($con);
    
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $data2[]  = array( 
                "name"         => $row['name'],
                "tot"          => $row['sum']
            );
        }
    }
    return $data2;
}

function getAllDept($con,$param){
    $data2 = array();
    $Qry 			= new Query();	
    $Qry->table     = "vw_databusinessunits";
    $Qry->selected  = "*";
    $Qry->fields    = "unittype = '3'";
    $rs 			= $Qry->exe_SELECT($con);
    
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            $dept = $row['id'];
            $ids=0;
            if (!empty($dept)) {
                $arr_id = array();
                $arr    = getHierarchy($con, $dept);
                array_push($arr_id, $dept);
                if (!empty($arr["nodechild"])) {
                    $a = getChildNodes($arr_id, $arr["nodechild"]);
                    if (!empty($a)) {
                        foreach ($a as $v) {
                            array_push($arr_id, $v);
                        }
                    }
                }
                if (count($arr_id) == 1) {
                    $ids = $arr_id[0];
                } else {
                    $ids = implode(",", $arr_id);
                }
            }

            $totmanpower = getTotManInDept($con, $ids);
            $totpresent = getTotPresentInDept($con, $ids, $param);

            $data2[]  = array( 
                "id"              => $row['id'],
                "name"            => $row['name'],
                "totalmanpower"   => $totmanpower,
                "totalpresent"    =>  $totpresent 
            );
        }
    }
    return $data2;
}

function getTotManInDept($con,$ids){
    $data2 = array();
    $Qry 			= new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "COUNT(id) AS headcount";
    $Qry->fields    = " id>0 AND etypeid = '1' AND idunit IN (".$ids.")";
    $rs 			= $Qry->exe_SELECT($con);
    
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['headcount'];
        }
    }
    return 0;
}

function getTotEmpActive($con){
    $data2 = array();
    $Qry 			= new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "COUNT(id) AS headcount";
    $Qry->fields    = " id>0 AND etypeid = '1'";
    $rs 			= $Qry->exe_SELECT($con);
    
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['headcount'];
        }
    }
    return 0;
}

function getTotPresentInDept($con,$ids,$param){
    $data2 = array();
    $Qry 			= new Query();	
    $Qry->table     = "vw_data_attendancebydept";
    $Qry->selected  = "AVG(tot) AS headcount";
    $Qry->fields    = " id>0 AND idunit IN (".$ids.") AND (`date` BETWEEN '".$param->dfrom."' AND '".$param->dto."')";
    $rs 			= $Qry->exe_SELECT($con);
    
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['headcount']? round($row['headcount']): '0';
        }
    }
    return 0;
}

function getPresenttoday($con){
    $data2 = array();
    $Qry 			= new Query();	
    $Qry->table     = "vw_attendancebydept";
    $Qry->selected  = "SUM(tot) AS headcount";
    $Qry->fields    = " id>0";
    $rs 			= $Qry->exe_SELECT($con);
    
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['headcount']? $row['headcount']: '0';
        }
    }
    return 0;
}


?>