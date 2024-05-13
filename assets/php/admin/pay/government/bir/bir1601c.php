<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data[] = '';

$paydates = getallPayperiod($con,$param);
$fourteen =0;
$fifteen = 0;
$sixteen = 0;
$seventeen = 0;
$eighteen = 0;
$nineteen = 0;
$twenty = 0;

foreach($paydates as $key=>$value){
    $Qry = new Query();	
    $Qry->table     = "tblpayreg AS p
                        LEFT JOIN tblaccountjob AS aj 
                        ON aj.idacct = p.idacct
                        LEFT JOIN tblregion AS r
                        ON r.regCode = aj.regcode
                        ";

    $Qry->selected  = "SUM(p.gross_amount) as fourteen,
                        SUM(p.ee + p.m_ee + p.p_ee + p.ph_ee) as nineteen,
                        SUM(
                            (CASE
                                WHEN ((p.salary ) + (SELECT SUM(IF( pa.transactionid = 58, pa.amount,0 )) FROM tblpayregadditional AS pa WHERE payregid = p.id) / aj.daysmonth) <= r.min_wage 
                                THEN p.salary  + (SELECT SUM(IF( pa.transactionid = 58, pa.amount,0 )) FROM tblpayregadditional AS pa WHERE payregid = p.id) 
                                ELSE 0
                            END)
                        ) AS fifteen,
                        SUM(
                            (CASE
                                WHEN ( (p.salary ) + (SELECT SUM(IF( pa.transactionid = 58, pa.amount,0 )) FROM tblpayregadditional AS pa WHERE payregid = p.id)  / aj.daysmonth) <= r.min_wage 
                                THEN tc_rd_amount + h_sh_amount + h_shrd_amount + h_lh_amount + h_lhrd_amount + h_lsh_amount + h_lshrd_amount + ot_reg_amount + ot_rd_amount + ot_sh_amount + ot_shrd_amount + ot_lh_amount + ot_lhrd_amount + ot_lsh_amount + ot_lshrd_amount + np_npreg_amount + np_rd_amount + np_sh_amount + np_shrd_amount + np_lh_amount + np_lhrd_amount + np_lsh_amount + np_lshrd_amount + npot_npot_amount + npot_rd_amount + npot_sh_amount + npot_shrd_amount + npot_lh_amount + npot_lhrd_amount + npot_lsh_amount + npot_lshrd_amount + adj_late_rd_amount + adj_late_sh_amount + adj_late_shrd_amount + adj_late_lh_amount + adj_late_lhrd_amount + adj_late_lsh_amount + adj_late_lshrd_amount + adj_ut_rd_amount + adj_ut_sh_amount + adj_ut_shrd_amount + adj_ut_lh_amount + adj_ut_lhrd_amount + adj_ut_lsh_amount + adj_ut_lshrd_amount + adj_absent_rd_amount + adj_absent_sh_amount + adj_absent_shrd_amount + adj_absent_lh_amount + adj_absent_lhrd_amount + adj_absent_lsh_amount + adj_absent_lshrd_amount + adj_ot_amount + adj_ot_rd_amount + adj_ot_sh_amount + adj_ot_shrd_amount + adj_ot_lh_amount + adj_ot_lhrd_amount + adj_ot_lsh_amount + adj_ot_lshrd_amount + adj_np_amount + adj_np_rd_amount + adj_np_sh_amount + adj_np_shrd_amount + adj_np_lh_amount + adj_np_lhrd_amount + adj_np_lsh_amount + adj_np_lshrd_amount + adj_npot_amount + adj_npot_rd_amount + adj_npot_sh_amount + adj_npot_shrd_amount + adj_npot_lh_amount + adj_npot_lhrd_amount + adj_npot_lsh_amount + adj_npot_lshrd_amount                          
                                ELSE 0
                            END)
                        ) AS sixteen,
                        SUM((SELECT SUM(IF( pa.transactionid = 158, pa.amount,0 )) FROM tblpayregadditional AS pa WHERE payregid = p.id)
                        ) AS seventeen,
                        0 as eighteen,
                        0 AS twenty";

    $Qry->fields = "p.idpayperiod = '" . $value['id'] . "' AND p.type = '" . $value['type'] . "'";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
            $fourteen = $fourteen  + $row['fourteen'];
            $fifteen = $fifteen  + $row['fifteen'];
            $sixteen = $sixteen  + $row['sixteen'];
            $seventeen = $seventeen  + $row['seventeen'];
            $eighteen = $eighteen  + $row['eighteen'];
            $nineteen = $nineteen  + $row['nineteen'];
            $twenty = $twenty  + $row['twenty'];
        }
    }else{
        $fourteen = $fourteen  + 0;
        $fifteen = $fifteen  + 0;
        $sixteen = $sixteen  + 0;
        $seventeen = $seventeen  + 0;
        $eighteen = $eighteen  + 0;
        $nineteen = $nineteen  + 0;
        $twenty = $twenty  + 0;
    }
}



$time = strtotime($param->month);
$data['parameter'] = date('Y-m-d',$time);
$data['fourteen'] =  $fourteen;
$data['fifteen'] =  $fifteen;
$data['sixteen'] =  $sixteen;
$data['seventeen'] =  $seventeen;
$data['eighteen'] =  $eighteen;
$data['nineteen'] =  $nineteen;
$data['twenty'] =  $twenty;

$myData = array('status' => 'success', 'result' => $data, 'company' => getcompany($con) );
$return = json_encode($myData);

print $return;
mysqli_close($con);

function getallPayperiod($con,$param){
    $time = strtotime($param->month);
    $param->month = date('Y-m-d',$time);
    
    $Qry = new Query();	
    $Qry->table     = "vw_payperiod_all";
    $Qry->selected  = "id,`type`";
    $Qry->fields = "YEAR(pay_date) = YEAR('" . $param->month . "') AND MONTH(pay_date) = MONTH('" . $param->month . "')";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){
            if($row['type']== 'ho'){
                $row['type'] = 'Local Employee';
            }
            if($row['type'] == 'helper'){
                $row['type'] = 'Helper';
            }
            if($row['type']== 'hajap'){
                $row['type']= 'Japanese';
            }
            if($row['type'] == 'hajapc'){
                $row['type'] = 'Japanese Conversion';
            }
            $data[] = array(
                "id" 	            => $row['id'],
                "type" 	            => $row['type']
           
            );
        }
    }else{
        $data[] = array();
    }
    return $data;
}


function getcompany($con){
    $Qry = new Query();	
    $Qry->table     = "tblcompany";
    $Qry->selected  = "*";
    $Qry->fields = "id>0";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            $data = $row;
        }
    }else{
        $data = '';
    }
    return $data;
}

?>