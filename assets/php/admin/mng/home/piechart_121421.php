<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$dept = getIdUnit($con,$param->accountid);
$pay_period = getPayPeriod($con);





$search='';
if( array_key_exists('accountid', $param) && !empty($param->accountid) ){
	if( array_key_exists('units', $param) && !empty($param->units) ){
		$search=$search." AND (idsuperior 	= '".$param->accountid."' OR idsuperior in (".$param->units.") ) "; 
	}else{
		$search=$search." AND idsuperior 	= '".$param->accountid."' "; 
	}
}


//Search Department
$ids=0;if( !empty( $dept ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$dept);
    array_push( $arr_id, 0 );
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
}

$pie_data	= array(getCS($con, $search, $param->accountid),getAA($con, $search, $param->accountid),getOT($con, $search, $param->accountid),getLV($con, $search, $param->accountid),getOB($con,$search, $param->accountid));
$pie_colour = array('#b0beec','#ebab76','#a29f9d9e','#ece193','#5faad682');
$pie_labels	= array('Change Shift','Attendance Adjustment','Overtime','Leaves','Official Business Trips');
$pie_href	= array('#/admin/mng/app/timekeeping/changeshiftapp','#/admin/mng/app/timekeeping/adjustapp','#/admin/mng/app/timekeeping/overtimeapp','#/admin/mng/app/timekeeping/leaveapp','#/admin/mng/app/timekeeping/obapp');

// $data = array();
// $data = array( 
//     "ot"    => getOT($con, $ids),
//     "cs"    => getCS($con, $ids),
//     "aa" 	=> getAA($con, $ids),
//     "lv" 	=> getLV($con, $ids),
//     "ob" 	=> 0, 
//     "sum"	=> (int)array_sum($pie_data)
// );

$data = array(
    "lbl" 	=> $pie_labels,
    "ctr"	=> $pie_data,
    "colour"=> $pie_colour,
    "sum"	=> (int)array_sum($pie_data),
    "pay_start"  => $pay_period['pay_start'],
    "pay_end"    => $pay_period['pay_end'],
    "href" 	     => $pie_href
);

$return = json_encode($data);



print $return;
mysqli_close($con);

function getIdUnit($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['idunit'];
        }
    }
    return null;
}


function getOT($con,$pay_period, $idacct){
    $pay_period = getPayPeriod($con);
    $Qry=new Query();
    $Qry->table="vw_overtime_application";
    $Qry->selected="COUNT(*) as ctr";
    //$Qry->fields="id>0 AND (date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND idacct !='".$idacct."' AND stat=3";
    $Qry->fields="id>0 AND idacct !='".$idacct."' AND stat=3";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['ctr'];
        }
    }
    return 0;
}
// function getOT($con, $idunit, $idacct){
//     $pay_period = getPayPeriod($con);
//     $Qry=new Query();
//     $Qry->table="vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID = de.id";
//     $Qry->selected="COUNT(*) as ctr";
//     $Qry->fields="(dt.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND dt.otid IS NOT NULL AND overtime_status = 'PENDING' AND (de.idunit IN (".$idunit.") OR de.idsuperior='".$idacct."')";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
//             return $row['ctr'];
//         }
//     }
//     return 0;
// }

// function getOT($con, $idunit){
//     $Qry=new Query();
//     $Qry->table="vw_data_timesheet LEFT JOIN vw_dataemployees ON vw_data_timesheet.empID = vw_dataemployees.id";
//     $Qry->selected="COUNT(*) as ctr";
//     $Qry->fields="otid IS NOT NULL AND overtime_status = 3 AND (vw_data_timesheet.work_date BETWEEN '".$pay_period['pay_start']."' AND vw_data_timesheet.work_date BETWEEN '".$pay_period['pay_end']."') AND vw_dataemployees.idunit in (".$idunit.")";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
//             return $row['ctr'];
//         }
//     }
//     return 0;
// }


function getCS($con,$pay_period, $idacct){
    $pay_period = getPayPeriod($con);
    $Qry=new Query();
    $Qry->table="vw_shift_application";
    $Qry->selected="COUNT(*) as ctr";
    //$Qry->fields="id>0 AND (date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND idacct !='".$idacct."' AND stat=3";
    $Qry->fields="id>0 AND idacct !='".$idacct."' AND stat=3";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['ctr'];
        }
    }
    return 0;
}

// function getCS($con, $idunit, $idacct){
//     $pay_period = getPayPeriod($con);
//     $Qry=new Query();
//     $Qry->table="vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID = de.id";
//     $Qry->selected="COUNT(*) as ctr";
//     $Qry->fields="(dt.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."')  AND dt.csid IS NOT NULL AND changeshift_stat = 'PENDING' AND (de.idunit IN (".$idunit.") OR de.idsuperior='".$idacct."')";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
//             return $row['ctr'];
//         }
//     }
//     return 0;
// }

// function getCS($con, $idunit){
//     $Qry=new Query();
//     $Qry->table="vw_data_timesheet LEFT JOIN vw_dataemployees ON vw_data_timesheet.empID = vw_dataemployees.id";
//     $Qry->selected="COUNT(*) as ctr";
//     $Qry->fields="csid IS NOT NULL AND changeshift_stat = 3 AND vw_dataemployees.idunit in (".$idunit.")";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
//             return $row['ctr'];
//         }
//     }
//     return 0;
// }

function getAA($con,$pay_period, $idacct){
    $pay_period = getPayPeriod($con);
    $Qry=new Query();
    $Qry->table="vw_attendance_application";
    $Qry->selected="COUNT(*) as ctr";
    //$Qry->fields="id>0 AND (date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."')  AND idacct !='".$idacct."' AND stat=3";
    $Qry->fields="id>0 AND idacct !='".$idacct."' AND stat=3";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['ctr'];
        }
    }
    return 0;
}


// function getAA($con, $idunit, $idacct){
//     $pay_period = getPayPeriod($con);
//     $Qry=new Query();
//     $Qry->table="vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID = de.id";
//     $Qry->selected="COUNT(*) as ctr";
//     $Qry->fields="(dt.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."')  AND dt.aaid IS NOT NULL AND adj_status = 'PENDING' AND (de.idunit IN (".$idunit.") OR de.idsuperior='".$idacct."' )";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
//             return $row['ctr'];
//         }
//     }
//     return 0;
// }
// function getAA($con, $idunit){
//     $Qry=new Query();
//     $Qry->table="vw_data_timesheet LEFT JOIN vw_dataemployees ON vw_data_timesheet.empID = vw_dataemployees.id";
//     $Qry->selected="COUNT(*) as ctr";
//     $Qry->fields="aaid IS NOT NULL AND adj_status = 'PENDING' AND vw_dataemployees.idunit in (".$idunit.")";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
//             return $row['ctr'];
//         }
//     }
//     return 0;
// }


function getOB($con,$pay_period, $idacct){
    $pay_period = getPayPeriod($con);
    $Qry=new Query();
    $Qry->table="vw_ob_application";
    $Qry->selected="COUNT(*) as ctr";
    // $Qry->fields="((approver1_id = '" .$idacct."') OR 
    //               (approver2_id = '" .$idacct."')) AND (date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND idacct!='".$idacct."'AND stat=3 ";
     $Qry->fields="((approver1_id = '" .$idacct."') OR 
                   (approver2_id = '" .$idacct."')) AND idacct!='".$idacct."'AND stat=3 ";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['ctr'];
        }
    }
    return 0;
}


function getLV($con, $pay_period, $idacct){
    $pay_period = getPayPeriod($con);
    $accountidd = getSuperiorid($con, $idacct);
    $Qry=new Query();
    $Qry->table="vw_leave_application";
    $Qry->selected="COUNT(*) as ctr";
    // $Qry->fields="((  find_in_set('".$idacct."',approver1) AND idacct != '".$idacct."' AND idacct != '".$accountidd."') OR 
    //                 (  find_in_set('".$idacct."',approver2) AND idacct != '".$idacct."' AND idacct != '".$accountidd."') OR
    //                 (  find_in_set('".$idacct."',approver3) AND idacct != '".$idacct."' AND idacct != '".$accountidd."') OR
    //                 (  find_in_set('".$idacct."',approver4) AND idacct != '".$idacct."' AND idacct != '".$accountidd."')
    //                 ) AND (date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND stat=3 AND idacct !='".$idacct."'";

      $Qry->fields="((  find_in_set('".$idacct."',approver1) AND idacct != '".$idacct."' AND idacct != '".$accountidd."') OR 
                    (  find_in_set('".$idacct."',approver2) AND idacct != '".$idacct."' AND idacct != '".$accountidd."') OR
                    (  find_in_set('".$idacct."',approver3) AND idacct != '".$idacct."' AND idacct != '".$accountidd."') OR
                    (  find_in_set('".$idacct."',approver4) AND idacct != '".$idacct."' AND idacct != '".$accountidd."')

                    ) AND stat=3 AND idacct !='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['ctr'];
        }
    }
    return 0;
}


function getSuperiorid( $con,$idacct ){
    $Qry 			= new Query();	
    $Qry->table     = "vw_dataemployees";
    $Qry->selected  = "idsuperior";
    $Qry->fields    = "id = '".$idacct."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['idsuperior'];
        }
    }
    return '';
}
// function getLV($con, $idunit, $idacct){
//     $pay_period = getPayPeriod($con);
//     $Qry=new Query();
//     $Qry->table="vw_data_timesheet AS dt LEFT JOIN vw_dataemployees AS de ON dt.empID = de.id";
//     $Qry->selected="COUNT(*) as ctr";
//     $Qry->fields="(dt.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND dt.lvid IS NOT NULL AND leave_status = 'PENDING' AND (de.idunit IN (".$idunit.") OR de.idsuperior='".$idacct."' )";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
//             return $row['ctr'];
//         }
//     }
//     return 0;
// }

// function getLV($con, $idunit){
//     $Qry=new Query();
//     $Qry->table="vw_data_timesheet LEFT JOIN vw_dataemployees ON vw_data_timesheet.empID = vw_dataemployees.id";
//     $Qry->selected="COUNT(*) as ctr";
//     $Qry->fields="lvid IS NOT NULL AND leave_status = 3 AND vw_dataemployees.idunit in (".$idunit.")";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         while($row=mysqli_fetch_array($rs)){
//             return $row['ctr'];
//         }
//     }
//     return 0;
// }




?>