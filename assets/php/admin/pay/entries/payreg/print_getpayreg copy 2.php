<?php
error_reporting(0);
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$data = array();
$search='';
$ids = '';

$idpayperiod = array(  
    "period"		=> getFPPeriod($con, $param),
);

$id_paydate = $idpayperiod['period']['id'];
$type = $idpayperiod['period']['type'];

$Qry = new Query();	
$Qry->table     = "vw_dataemployees AS de
LEFT JOIN tblbunits AS bu
ON de.idunit = bu.id
LEFT JOIN tbljoblvl AS jl
ON jl.id = de.idlvl";
$Qry->selected  = "
de.id AS idacct,jl.alias AS idlvl,de.empid,de.empname,bu.id as idunit,bu.name as classname,bu.`unittype`,bu.idunder,getPriority(bu.id) as priocount,
(SELECT COUNT(in.idunit) FROM tblaccountjob AS `in` WHERE in.idunit=bu.id) AS classcount";
$Qry->fields    = "bu.`name` is not null and de.id <> 1 GROUP BY de.empid ORDER BY getPriority(bu.id) asc";
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_assoc($rs)){
        $data[] = array( 
            "idacct"                => $row['empid'],
            "idlvl"        	        => $row['idlvl'],
            "empid"        	        => $row['empid'],
            "empname"        	    => $row['empname'],
            "idunit"        	    => $row['idunit'],
            "classname"         	=> $row['classname'],
            "unittype"        	    => $row['unittype'],
            "idunder"        	    => $row['idunder'],
            "unittype"        	    => $row['unittype'],
            "priocount"        	    => $row['priocount'],
            "classcount"        	=> $row['classcount'],

            "ntdiff"        	    => 0,
            "ntdiff_amount"        	=> 0,
            "ot"        	        => 0,
            "ot_amount"        	    => 0,
            "basic"        	        => 0,
            "gross"        	        => 0,
            "ded"        	        => 0,
            "net"        	        => 0
        );
    }

    $myData = array('status' => 'success', 
                'result' => $data,
                'Qry' => $Qry->fields
    );
    $return = json_encode($myData);
}else{
	$return = json_encode(array('status' => 'error', 'mysqli_error' => mysqli_error($con)));
}

print $return;
mysqli_close($con);

function getFPPeriod($con, $param){
    $type = 'ho';
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

session_destroy();
?>