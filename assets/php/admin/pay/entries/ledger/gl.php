<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$search ='';
$param = json_decode(file_get_contents('php://input'));

if( !empty( $param->filter->sapgl ) ){ $search=" AND AccountCode =   '". $param->filter->sapgl ."' "; }
if( !empty( $param->filter->gldesc ) ){ $search.="  AND GL_Description =   '". $param->filter->gldesc ."'  "; }

$idpayperiod = array(  
    "period"		=> getFPPeriod($con, $param),
);

$where = $search;
$Qry = new Query();	

$Qry 			= new Query();	
if( $idpayperiod['period']['type'] == 'Helper'){
    $Qry->table     = "vw_sapdata_helper";
}else if( $idpayperiod['period']['type'] == 'Japanese'){
    $Qry->table     = "vw_sapdata_jap";
}else if( $idpayperiod['period']['type'] == 'Japanese Conversion'){
    $Qry->table     = "vw_sapdata_japc";
}else{
    $Qry->table     = "vw_sapdata";
}


$Qry->selected  = "*";
$Qry->fields = "idpayperiod = '" .  $idpayperiod['period']['id'] . "' ";
$Qry->fields = $Qry->fields .$search. "AND (Debit != ''  AND Debit != '0' OR Credit != '' AND Credit != '0') ORDER BY batchnum ASC,Credit ASC,id ASC LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize ."";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        $data[] = $row;
    }

    $myData = array('status' => 'success', 
            'result' => $data,
            'totalItems' => getTotal($con ,  $param),
            'grandtotal' => getTotalamount($con , $param)
           
    );



    $myDatas = array('status' => 'success', 'result' => $myData, 'totalItems' => getTotalpage($con , $where, $idpayperiod ));
    $return = json_encode($myDatas);
}else{
	$return = json_encode(array("err"=>mysqli_error($con)));
	
}
print $return;
mysqli_close($con);

function getTotalpage($con,$search,$idpayperiod ){
	$Qry 			= new Query();	
    if( $idpayperiod['period']['type'] == 'Helper'){
        $Qry->table     = "vw_sapdata_helper";
    }else if( $idpayperiod['period']['type'] == 'Japanese'){
        $Qry->table     = "vw_sapdata_jap";
    }else if( $idpayperiod['period']['type'] == 'Japanese Conversion'){
        $Qry->table     = "vw_sapdata_japc";
    }else{
        $Qry->table     = "vw_sapdata";
    }
	$Qry->selected  = "*";
    $Qry->fields = "idpayperiod = '" .  $idpayperiod['period']['id'] . "' ";
    $Qry->fields = $Qry->fields .$search. "AND (Debit != ''  AND Debit != '0' OR Credit != '' AND Credit != '0') ORDER BY batchnum ASC,Credit ASC,id ASC";
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

function getTotal($con,$param){
    $idpayperiod = array(  
        "period"		=> getFPPeriod($con, $param),
    );

    $Qry = new Query();	
   
    if( $idpayperiod['period']['type'] == 'Helper'){
        $Qry->table     = "vw_sapdata_helper";
    }else if( $idpayperiod['period']['type'] == 'Japanese'){
        $Qry->table     = "vw_sapdata_jap";
    }else if( $idpayperiod['period']['type'] == 'Japanese Conversion'){
        $Qry->table     = "vw_sapdata_japc";
    }else{
        $Qry->table     = "vw_sapdata";
    }

    $Qry->selected  = "*";
    $Qry->fields = "idpayperiod = '" . $idpayperiod['period']['id']  . "' AND (Debit != ''  AND Debit != '0' OR Credit != '' AND Credit != '0') ORDER BY batchnum ASC,Credit ASC,id";

    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
                $rowcount=mysqli_num_rows($rs);
				return $rowcount;
			}
		}
		return 0;
}
function getTotalamount($con,$param){
    $idpayperiod = array(  
        "period"		=> getFPPeriod($con, $param),
    );

    $Qry = new Query();		
    if( $idpayperiod['period']['type'] == 'Helper'){
        $Qry->table     = "vw_sapdata_helper";
    }else if( $idpayperiod['period']['type'] == 'Japanese'){
        $Qry->table     = "vw_sapdata_jap";
    }else if( $idpayperiod['period']['type'] == 'Japanese Conversion'){
        $Qry->table     = "vw_sapdata_japc";
    }else{
        $Qry->table     = "vw_sapdata";
    }
    $Qry->selected  = "SUM(Debit) as totaldebit,
                        SUM(Credit) as totalcredit";
    $Qry->fields = "idpayperiod = '" .  $idpayperiod['period']['id']  . "'  AND (Debit != ''  AND Debit != '0' OR Credit != '' AND Credit != '0') ORDER BY batchnum ASC,Credit ASC,id";


    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row;
        }
    }
    return 0;
}

function getFPPeriod($con, $param){
    if($param->data->paytype == 'Local Employee'){
        $type = 'ho';
    }
    if($param->data->paytype == 'Helper'){
        $type = 'helper';
    }
    if($param->data->paytype== 'Japanese'){
        $type= 'hajap';
    }
    if($param->data->paytype == 'Japanese Conversion'){
        $type = 'hajapc';
    }


    $data = array();	
    $Qry = new Query();	
    $Qry->table     = "vw_payperiod_all";
    $Qry->selected  = "*";
    $Qry->fields   = "pay_date='".$param->data->paydate."' AND type='".$type."'";      
    
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            if($row['type'] == 'ho'){
                $row['type'] = 'Local Employee';
            }
            if($row['type'] == 'helper'){
                $row['type'] = 'Helper';
            }
            if($row['type'] == 'hajap'){
                $row['type'] = 'Japanese';
            }
            if($row['type'] == 'hajapc'){
                $row['type'] = 'Japanese Conversion';
            }

            $data = array( 
                "id"        	=> $row['id'],
                "pay_start"		=> $row['period_start'],
                "pay_end"		=> $row['period_end'],
                "pay_date"		=> $row['pay_date'],
                "hascontri" 	=> $row['hascontri'],
                "pay_stat"		=> $row['stat'],
                "tkstatus"		=> $row['tkstatus'],
                "period_type" 	=> $row['pay_period'],
                "type" 			=> $row['type'],
                "tkprocess" 	=> $row['tkprocess'],
                "payprocess" 	=> $row['payprocess'],
            );
        }
    }
    return $data;
}

?>