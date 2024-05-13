<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$date=SysDate();
$param = $_GET;
$counter = 0;
$search='';
$ids = '';

$idpayperiod = array(  
    "period"		=> getFPPeriod($con, $param),
);


if( !empty( $param['empname'] ) ){ $search=" AND pr.idacct ='". $param['empname'] ."' "; }
if( !empty( $param['bn']  ) ){ $search = $search . " AND aj.batchnum =   '". $param['bn']  ."' "; }


$Qry = new Query();	
$Qry->table     = "tblpayreg AS pr LEFT JOIN tblaccount AS a ON pr.idacct = a.id LEFT JOIN tblaccountjob as aj ON aj.idacct = a.id";
$Qry->selected  = "*";

$Qry->fields = "pr.net_amount > 0 AND pr.idpaygrp = '".$param['paygrp']."' AND pr.idpayperiod = '" . $idpayperiod['period']['id'] . "' AND pr.type = '" . $idpayperiod['period']['type'] . "'" .$search . " ORDER by a.lname ASC" ;

// $Qry->fields = $Qry->fields . "" .$search . "  ORDER BY lname";

$ta = 0;

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $counter++;
        $data[] = array(
            // 'counter'           => $counter,
            "ban" 	            => $row['idpayroll'],
            "amount" 	        => $row['net_amount'],
            "fullname" 	        => utf8_decode($row['lname']).','.$row['fname'] ,
            "remarks" 	        => '',
            
        );
        
    }

    // $myData = array('status' => 'success', 
    //         'result' => $data, 
    //         'totalItems' => getTotal($con , $param),
    //         'totalamount' => getTotalamount($con , $param),
    //         'uniqueDepartment' => getMainDepartment($con),
    //         'qry' => $Qry->fields
    // );

}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=BankRemittance'.$date.'.csv');
$output = fopen('php://output', 'w');

// fputcsv($output, array("Kajima Philippines Incorporated"));
// fputcsv($output, array("Bank Remittance Report"));
// fputcsv($output, array("Pay Period: ".$param['period']));
// fputcsv($output, array("Pay Type: ".$param['paytype']));
// fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array("New World Makati Hotel"));
fputcsv($output, array("Upload Date: ",  SysDatePadLeft() ));
fputcsv($output, array("Company Code: " , 'M9A' ));
fputcsv($output, array("Batch: ", $idpayperiod['period']['id'] ));
fputcsv($output, array('ACCOUNT #',
                        'AMOUNT',
                        'NAME',
                        'REMARKS',
                         )); 

if (isset($data)) {
    foreach ($data as $row22) {
            fputcsv($output, $row22);
    }
}

// fputcsv($output, array('Total:',
//                         getTotal($con , $param),
//                         '',
//                         getTotalamount($con , $param),                  
//                         '',
//                          )); 
	


mysqli_close($con);

function getTotal($con,$param){
    $search='';
    if( !empty( $param['empname'] ) ){ $search=" AND pr.idacct ='". $param['empname'] ."' "; }
    if( !empty( $param['bn']  ) ){ $search = $search . " AND aj.batchnum =   '". $param['bn']  ."' "; }
    
        $idpayperiod = array(  
            "period"		=> getFPPeriod($con, $param),
        );

        if( !empty( $param->filter->site ) ){
 
            $id_array = getLocationsbunits($con,$param->filter->site);
            $ids = implode(",",$id_array);
        }

            $Qry = new Query();	
            $Qry->table     = "tblpayreg AS pr LEFT JOIN tblaccount AS a ON pr.idacct = a.id LEFT JOIN tblaccountjob as aj ON aj.idacct = a.id ";
            $Qry->selected  = "pr.id,
                                a.idpayroll,
                                a.fname,
                                a.lname,
                                a.mname,
                                pr.net_amount";

            $Qry->fields = "pr.net_amount > 0 AND pr.idpayperiod = '" . $idpayperiod['period']['id'] . "' AND pr.type = '" . $idpayperiod['period']['type'] . "' " .$search . " ";


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
    $search='';
    if( !empty( $param['empname'] ) ){ $search=" AND pr.idacct ='". $param['empname'] ."' "; }
    if( !empty( $param['bn']  ) ){ $search = $search . " AND aj.batchnum =   '". $param['bn']  ."' "; }

    $idpayperiod = array(  
        "period"		=> getFPPeriod($con, $param),
    );


    
    $Qry = new Query();	
    $Qry->table     = "tblpayreg AS pr LEFT JOIN tblaccount AS a ON pr.idacct = a.id LEFT JOIN tblaccountjob as aj ON aj.idacct = a.id";
    $Qry->selected  = "SUM(pr.net_amount) as total";

    $Qry->fields = "net_amount > 0 AND idpayperiod = '" . $idpayperiod['period']['id'] . "' AND type = '" . $idpayperiod['period']['type'] . "' " .$search . " ";


    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['total'];
        }
    }
    return 0;
}

function getFPPeriod($con, $param){
    if($param['paytype']== 'Local Employee'){
        $type = 'ho';
    }
    if($param['paytype'] == 'Helper'){
        $type = 'helper';
    }
    if($param['paytype']== 'Japanese'){
        $type= 'hajap';
    }
    if($param['paytype'] == 'Japanese Conversion'){
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