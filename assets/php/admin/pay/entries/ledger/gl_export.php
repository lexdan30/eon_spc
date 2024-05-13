<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 
$date=SysDate();
$param = $_GET;
$data = array();
$idpayperiod = array(  
    "period"		=> getFPPeriod($con, $param)
);

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
$Qry->fields = $Qry->fields . "AND (Debit != ''  AND Debit != '0' OR Credit != '' AND Credit != '0') ORDER BY batchnum ASC,Credit ASC,id ASC ";

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        $data[] = array(
            $row['AccountCode'],
            $row['ControlAccount'],
            $row['CostingCode'],
            $row['Debit'],
            $row['Credit'],
            $row['GL_Description'],
            $row['ProjectCode']
        );
    }

    // $myData = array('status' => 'success', 
    //         'result' => $data,
    //         'totalItems' => getTotal($con ,  $param),
    //         'grandtotal' => getTotalamount($con , $param)
           
    // );
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=DistributiontoLedger'.$date.'.csv');
$output = fopen('php://output', 'w');

fputcsv($output, array("New World Makati Hotel"));
fputcsv($output, array("Distribution to Ledger"));
fputcsv($output, array("Pay Period: ".$param['period']));
fputcsv($output, array("Pay Type: ".$param['paytype']));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('SAP GL Code',
						'Control Account',
						'Costing Code',
						'Debit',
                        'Credit',
                        'GL Description',
                        'Project Code',
                         )); 

if (isset($data)) {
    foreach ($data as $row22) {
            fputcsv($output, $row22);
    }
}

fputcsv($output, array('Total:',
						'',
                        '',
                        getTotalamount($con , $param)['totaldebit'],
                        getTotalamount($con , $param)['totalcredit'],
                        '',
                        '',
                         )); 


mysqli_close($con);

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
    
    if($param['paytype'] == 'Local Employee'){
        $type = 'ho';
    }
    if($param['paytype'] == 'Helper'){
        $type = 'helper';
    }
    if($param['paytype']== 'Japanese'){
        $type = 'hajap';
    }
    if($param['paytype']== 'Japanese Conversion'){
        $type = 'hajapc';
    }

    $data = array();	
    $Qry = new Query();	
    $Qry->table     = "vw_payperiod_all";
    $Qry->selected  = "*";
    $Qry->fields   = "pay_date='".$param['period']."' AND type='".$type."'";      
    
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