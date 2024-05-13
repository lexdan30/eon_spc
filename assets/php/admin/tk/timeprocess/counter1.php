<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));  
$date=SysDate(); 
$time=SysTime(); 
$return = null;	 
$data = array();
$data1 =array();

$data1 = array(  
    "period"		=> getPayPeriod($con),
);

$idpayperiod = $data1['period']['id']; 

if( !empty($param->accountid) ){
	$Qry 			= new Query();	
	$Qry->table     = "vw_dataemployees as b";
    $Qry->selected  = "b.business_unit AS unit,idunit";
	if( !empty($param->info->classi) ){
		$dept = $param->info->classi;
		$arr_id = array();
		$arr    = getHierarchy($con, $dept);
		array_push($arr_id, $dept);
		if (!empty($arr["nodechild"])) {
			$a = getChildNode($arr_id, $arr["nodechild"]);
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

        

        if($data1['period']['type'] == 'Helper'){
            $Qry->fields    = "b.idunit IN (".$ids.") AND batchnum = 6  GROUP BY b.idunit ORDER BY COUNT(b.idunit) DESC";
        }else if($data1['period']['type'] == 'Japanese' || $data1['period']['type'] == 'Japanese Conversion'){
            $Qry->fields    = "b.idunit IN (".$ids.") AND batchnum = '3,4'  GROUP BY b.idunit ORDER BY COUNT(b.idunit) DESC";
        }else{
            $Qry->fields    = "b.idunit IN (".$ids.") AND (batchnum != '3,4' OR batchnum != 6)  GROUP BY b.idunit ORDER BY COUNT(b.idunit) DESC";
        }
		
	}else{
        
        if($data1['period']['type'] == 'Helper'){
            $Qry->fields    = "idunit IS NOT NULL AND batchnum = 6  GROUP BY b.idunit ORDER BY COUNT(b.idunit) DESC";
        }else if($data1['period']['type'] == 'Japanese' || $data1['period']['type'] == 'Japanese Conversion'){
            $Qry->fields    = "idunit IS NOT NULL AND batchnum ='3,4'  GROUP BY b.idunit ORDER BY COUNT(b.idunit) DESC";
        }else{
		    $Qry->fields    = "idunit IS NOT NULL AND (batchnum != '3,4' OR batchnum != 6) GROUP BY b.idunit ORDER BY COUNT(b.idunit) DESC";
        }
	}


	$rs				= $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>=1){
        $ctr=0;
		while($row=mysqli_fetch_array($rs)){
            $ctr= $ctr + getFPbyunit($con, $row['idunit']);
			$data[] = array(
				"name" => $row['unit'],
				"ctr"  => getFPbyunit($con, $row['idunit']),
                "totalemp"  => getunittotalemp($con ,$row['idunit']),
                "processed"  => getprocessedtotal($con ,$idpayperiod ,$row['idunit']),
                "sum"  => $ctr
			);
		}
	}
}
$return = json_encode($data);

print $return;
mysqli_close($con);


function payperiod($con,$date,$date1){
    $Qry = new Query();	
    $Qry->table         = "tblpayperiod";
    $Qry->selected      = "id";
    $Qry->fields        = "period_start = '".$date."' AND period_end = '".$date1."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['id'];
        }
    }
    
}

function getFPbyunit($con, $idunit){
    $data1 = array(  
        "period"		=> getPayPeriod($con),
    );
    
    $Qry = new Query();	

    if($data1['period']['type'] == 'helper'){
        $Qry->table     = "vw_timesheetfinal_helper";
    }else if($data1['period']['type'] == 'hajap'){
        $Qry->table     = "vw_timesheetfinal_japanese";
    }else if($data1['period']['type'] == 'hajapc'){
        $Qry->table     = "vw_timesheetfinal_japanesec";
    }else{
        $Qry->table     = "vw_timesheetfinal";
    }

    $Qry->selected      = "COUNT(*) as total";
    $Qry->fields        = "idpayperiod = '".$data1['period']['id']."' AND acthrs > 0 AND idunit = '".$idunit."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['total'];
        }
    }
}

function getunittotalemp($con, $idunit){
    $data1 = array(  
        "period"		=> getPayPeriod($con),
    );
    $Qry = new Query();	
    $Qry->table         = "vw_dataemployees";
    $Qry->selected      = "COUNT(*) as total";

    if($data1['period']['type'] == 'Helper'){
        $Qry->fields        = "idunit = '".$idunit."' AND batchnum = 6 ";
    }else if($data1['period']['type'] == 'Japanese' || $data1['period']['type'] == 'Japanese Conversion'){
        $Qry->fields        = "idunit = '".$idunit."' AND batchnum = '3,4'";
    }else{
        $Qry->fields        = "idunit = '".$idunit."' AND (batchnum != '3,4' AND batchnum != 6)";
    }

    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['total'];
        }
    }
    
}


function getprocessedtotal($con ,$idpayperiod ,$idunit){
    $data1 = array(  
        "period"		=> getPayPeriod($con),
    );

    $Qry = new Query();	
    $Qry->table         = "tbltimesheetsummary";
    $Qry->selected      = "COUNT(*) as total";
    $Qry->fields        = "idbunit = '".$idunit."' AND idpayperiod = '".$data1['period']['id']."' AND type='".$data1['period']['type']."'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['total'];
        }
    } 
}
?>