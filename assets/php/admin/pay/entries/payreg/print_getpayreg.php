<?php
error_reporting(0);
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$datenow = Sysdate();
$timenow = Systime();
$data = array();
$search='';
$ids = '';

$idpayperiod = array(  
    "period"		=> getFPPeriod($con, $param),
);

$id_paydate = $idpayperiod['period']['id'];
$type = $idpayperiod['period']['type'];

$Qry = new Query();	
$Qry->table     = "
tblpayreg AS pr 
LEFT JOIN 
vw_dataemployees AS de 
ON de.id = pr.idacct 
LEFT JOIN tblbunits AS bu 
ON de.idunit = bu.id 
LEFT JOIN tbljoblvl AS jl 
ON jl.id = de.idlvl 
";
$Qry->selected  = "
pr.*,de.post,de.id AS idacct,jl.alias AS idlvl,de.empid,de.empname,bu.id AS idunit,bu.name AS classname,bu.`unittype`,bu.idunder,getPriority(bu.id) AS priocount, 
(SELECT COUNT(in.idunit) FROM tblaccountjob AS `in` WHERE in.idunit=bu.id) AS classcount, 
(np_npreg+np_rd+np_sh+np_shrd+np_lh+np_lhrd+ 
np_lsh+np_lshrd) AS `NT DIFF`,'0.00' AS `OVER-03`, 
'0.00' AS `OVER-04`,'0.00' AS `OVER-08`, 
'0.00' AS `OVER-04A`,'0.00' AS `OVER-09`, 
'0.00' AS `OVER-05`,'0.00' AS `OVER-10`, 
tc_sl AS tc_sl, 
tc_sl_amount AS tc_sl_amount, 
tc_vl AS tc_vl, 
tc_vl_amount AS tc_vl_amount, 
gross_amount AS gross_amount,  
total_ded AS total_ded, 
net_amount AS net_amount
";
$Qry->fields    = "bu.`name` IS NOT NULL AND de.id <> 1 AND pr.idpayperiod = '" .$id_paydate. "' GROUP BY de.empid ORDER BY getPriority(bu.id) ASC,de.post";

$Qry->table = str_replace("\r\n",'', $Qry->table);
$Qry->table = str_replace("\t",'', $Qry->table);
$Qry->selected = str_replace("\r\n",'', $Qry->selected);
$Qry->selected = str_replace("\t",'', $Qry->selected);
$Qry->fields = str_replace("\r\n",'', $Qry->fields);
$Qry->fields = str_replace("\t",'', $Qry->fields);

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    $prevpos = '';  
    $prevbunit = '';  
    $bunitcount = 0;
    $poscount = 0;
    while($row=mysqli_fetch_assoc($rs)){
        if($prevpos != $row['post']){
            $position = $row['post'];
            $poscount++;
        }else{
            $position = '';
        }

        if($prevbunit != $row['idunit']){
            $bunit = $row['idunit'];
            $bunitcount++;
        }else{
            $bunit = '';
        }

        

        if($poscount == 2){
            $poscount = 1;
            $posdata = gettotalpos($con,$prevpos,$id_paydate);
            $data[] = $posdata;
            $gettotalpos[] = $posdata;
        }

        if($bunitcount == 2){
            $bunitcount = 1;
            $bunitdata = gettotalclassif($con,$prevbunit,$id_paydate);
            $data[] = $bunitdata;
            $gettotalbunit[] = $bunitdata;
        }
         
        $data[] = array( 
            "idacct"                => $row['empid'],
            "idlvl"        	        => $row['idlvl'],
            "empid"        	        => $row['empid'],
            "empname"        	    => $row['empname'],
            "position"        	    => $position,
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
            "basic"        	        => empty($row['salary']) ? 0 : $row['salary'],
            "gross"        	        =>  empty($row['gross_amount']) ? 0 : $row['gross_amount'],
            "ded"        	        => $row['total_ded'],
            "net"        	        => $row['net_amount']
        );

        // if($prevpos != $row['post']){
        //     $posdata = gettotalpos($con,$row['post'],$id_paydate);
        //     $data[] = $posdata;
        //     $gettotalpos[] = $posdata;
        // }

        // if($prevbunit != $row['idunit']){
        //     $bunitdata = gettotalclassif($con,$row['idunit'],$id_paydate);
        //     $data[] = $bunitdata;
        //     $gettotalbunit[] = $bunitdata;
        // }

        $prevpos = $row['post'];
        $prevbunit = $row['idunit'];
    }

    $myData = array('status' => 'success', 
                'result' => $data,
                'totalpos' => $gettotalpos,
                'totalbunit' => $gettotalbunit,
                'date' => $datenow,
                'time' => $timenow,
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

function gettotalpos($con,$pos,$id_paydate){
    $data = array();
    $Qry = new Query();	
    $Qry->table     = "
    tblpayreg AS pr 
    LEFT JOIN 
    vw_dataemployees AS de 
    ON de.id = pr.idacct 
    LEFT JOIN tblbunits AS bu 
    ON de.idunit = bu.id 
    LEFT JOIN tbljoblvl AS jl 
    ON jl.id = de.idlvl 
    ";
    $Qry->selected  = "
    de.post,de.id AS idacct,SUM(1) AS classcount,
    SUM(pr.np_npreg+pr.np_rd+pr.np_sh+pr.np_shrd+pr.np_lh+pr.np_lhrd+ 
    pr.np_lsh+pr.np_lshrd) AS `NT DIFF`,'0.00' AS `OVER-03`, 
    '0.00' AS `OVER-04`,'0.00' AS `OVER-08`, 
    '0.00' AS `OVER-04A`,'0.00' AS `OVER-09`, 
    '0.00' AS `OVER-05`,'0.00' AS `OVER-10`, 
    SUM(pr.tc_sl) AS tc_sl, 
    SUM(pr.tc_sl_amount) AS tc_sl_amount, 
    SUM(pr.tc_vl) AS tc_vl, 
    SUM(pr.tc_vl_amount) AS tc_vl_amount, 
    SUM(pr.gross_amount) AS gross_amount,  
    SUM(pr.total_ded) AS total_ded, 
    SUM(pr.net_amount) AS net_amount
    ";
    $Qry->fields    = "bu.`name` IS NOT NULL AND de.id <> 1 AND pr.idpayperiod = '" .$id_paydate. "' AND de.post = '".$pos."'";
    
    $Qry->table = str_replace("\r\n",'', $Qry->table);
    $Qry->table = str_replace("\t",'', $Qry->table);
    $Qry->selected = str_replace("\r\n",'', $Qry->selected);
    $Qry->selected = str_replace("\t",'', $Qry->selected);
    $Qry->fields = str_replace("\r\n",'', $Qry->fields);
    $Qry->fields = str_replace("\t",'', $Qry->fields);
    
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            $data = array( 
                "idacct"                => '',
                "idlvl"        	        => '',
                "empid"        	        => '',
                "empname"        	    => 'pos',
                "position"        	    => $row['post'],
                "idunit"        	    => '',
                "classname"         	=> '',
                "unittype"        	    => '',
                "idunder"        	    => '',
                "unittype"        	    => '',
                "priocount"        	    => '',
                "classcount"        	=> $row['classcount'],
                "ntdiff"        	    => 0,
                "ntdiff_amount"        	=> 0,
                "ot"        	        => 0,
                "ot_amount"        	    => 0,
                "basic"        	        => empty($row['salary']) ? 0 : $row['salary'],
                "gross"        	        => empty($row['gross_amount']) ? 0 : $row['gross_amount'],
                "ded"        	        => $row['total_ded'],
                "net"        	        => $row['net_amount']
            );
        }
    }

    return $data;
}

function gettotalclassif($con,$idunit,$id_paydate){
    $data = array();
    $Qry = new Query();	
    $Qry->table     = "
    tblpayreg AS pr 
    LEFT JOIN 
    vw_dataemployees AS de 
    ON de.id = pr.idacct 
    LEFT JOIN tblbunits AS bu 
    ON de.idunit = bu.id 
    LEFT JOIN tbljoblvl AS jl 
    ON jl.id = de.idlvl 
    ";
    $Qry->selected  = "
    de.post,de.id AS idacct,bu.id AS idunit,SUM(1) AS classcount,bu.name AS classname,
    SUM(pr.np_npreg+pr.np_rd+pr.np_sh+pr.np_shrd+pr.np_lh+pr.np_lhrd+ 
    pr.np_lsh+pr.np_lshrd) AS `NT DIFF`,'0.00' AS `OVER-03`, 
    '0.00' AS `OVER-04`,'0.00' AS `OVER-08`, 
    '0.00' AS `OVER-04A`,'0.00' AS `OVER-09`, 
    '0.00' AS `OVER-05`,'0.00' AS `OVER-10`, 
    SUM(pr.tc_sl) AS tc_sl, 
    SUM(pr.tc_sl_amount) AS tc_sl_amount, 
    SUM(pr.tc_vl) AS tc_vl, 
    SUM(pr.tc_vl_amount) AS tc_vl_amount, 
    SUM(pr.gross_amount) AS gross_amount,  
    SUM(pr.total_ded) AS total_ded, 
    SUM(pr.net_amount) AS net_amount
    ";
    $Qry->fields    = "bu.`name` IS NOT NULL AND de.id <> 1 AND pr.idpayperiod = '" .$id_paydate. "' AND bu.id = '".$idunit."'";
    
    $Qry->table = str_replace("\r\n",'', $Qry->table);
    $Qry->table = str_replace("\t",'', $Qry->table);
    $Qry->selected = str_replace("\r\n",'', $Qry->selected);
    $Qry->selected = str_replace("\t",'', $Qry->selected);
    $Qry->fields = str_replace("\r\n",'', $Qry->fields);
    $Qry->fields = str_replace("\t",'', $Qry->fields);
    
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            $data = array( 
                "idacct"                => '',
                "idlvl"        	        => '',
                "empid"        	        => '',
                "empname"        	    => 'unit',
                "position"        	    => '',
                "idunit"        	    => $row['classname'],
                "classname"         	=> '',
                "unittype"        	    => '',
                "idunder"        	    => '',
                "unittype"        	    => '',
                "priocount"        	    => '',
                "classcount"        	=> $row['classcount'], 
                "ntdiff"        	    => 0,
                "ntdiff_amount"        	=> 0,
                "ot"        	        => 0,
                "ot_amount"        	    => 0,
                "basic"        	        => empty($row['salary']) ? 0 : $row['salary'],
                "gross"        	        => empty($row['gross_amount']) ? 0 : $row['gross_amount'],
                "ded"        	        => $row['total_ded'],
                "net"        	        => $row['net_amount']
            );
        }
    }

    return $data;
}

session_destroy();
?>