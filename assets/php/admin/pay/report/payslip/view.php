<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 
if(!isset($_SESSION)) 
{ 
    session_start(); 
}
$param = json_decode(file_get_contents('php://input'));
$_SESSION['earnings']=[];
$_SESSION['deductions']=[];
$data = array();		
$Qry = new Query();	
$Qry->table     = "tblpayreg";
$Qry->selected  = "*";
$Qry->fields    = "idacct ='".$param->id_acct."' AND idpayperiod = '".$param->id_paydate."' AND type = '".$param->type."' ";
$rs = $Qry->exe_SELECT($con);

echo mysqli_error($con);
if(mysqli_num_rows($rs)>= 1){
	while($row=mysqli_fetch_assoc($rs)){

        $header = getEmployeeinfo($con,$param->id_acct,$row['id']);
        $payinfo = getPayinfo($con, $param);

        if($param->type== 'Japanese' || $param->type == 'Japanese Conversion'){
            $basic = array("");
        }else{
            $basic = array("salary");
        }
       

        $negative = array("late", "ut", "abs");
        $leaves = array("tc_vl", "tc_sl", "tc_sil", "tc_cl", "tc_ml", "tc_mc", "tc_sp", "tc_pl", "tc_el", "tc_bl", "tc_bdl", "tc_spl", "tc_rd");
        $leavesnegative = array("tc_lwop");

        $holidays = array("h_sh", "h_shrd", "h_lh", "h_lhrd", "h_lsh", "h_lshrd");
        $np = array("np_npreg", "np_rd", "np_sh", "np_shrd", "np_lh", "np_lhrd", "np_lsh", "np_lshrd");
        $ot = array("ot_reg", "ot_rd", "ot_sh", "ot_shrd", "ot_lh", "ot_lhrd", "ot_lsh", "ot_lshrd");
        $npot = array("npot_npot", "npot_rd", "npot_sh", "npot_shrd", "npot_lh", "npot_lhrd", "npot_lsh", "npot_lshrd");

        $kajimaallowance = array("otallowance","mallowance","hallowance","wallowance","shallowance","swallowance");

        if($param->type== 'Japanese' || $param->type == 'Japanese Conversion'){
            $negativeadditionals = array("ee", "m_ee", "p_ee","ph_ee");
        }else{
            $negativeadditionals = array("ee", "m_ee", "p_ee","ph_ee","w_tax");
        }
       

        foreach ($row as $key => $value) {
       
            if($value != '0.00'){
                if (in_array($key, $basic)) {
                    if($row['idpaygrp'] == 11){
                       $_SESSION['earnings'][] = array(
                            'description'	=> getTranstype($con,$key),
                            'unit'		=> $row['whrs'],
                            'amount'	=> $row['whrs_amount'],
                        );
                    }else{
                       $_SESSION['earnings'][] = array(
                            'description'	=> getTranstype($con,$key),
                            'unit'		=> '',
                            'amount'	=> $value / 2,
                        );
                    }
                   
                }
                if (in_array($key, $negative)) {
                   $_SESSION['earnings'][] = array(
                        'description'	=> getTranstype($con,$key),
                        'unit'		=> $value,
                        'amount'	=> $row[$key.'_amount']  * -1
                    );
                }
                if (in_array($key, $leaves)) {
                   $_SESSION['earnings'][] = array(
                        'description'	=> getTranstype($con,$key),
                        'unit'		=> $value,
                        'amount'	=> $row[$key.'_amount']
                    );
                }
                if (in_array($key, $leavesnegative)) {
                    $_SESSION['earnings'][] = array(
                         'description'	=> getTranstype($con,$key),
                         'unit'		=> $value,
                         'amount'	=> $row[$key.'_amount'] * -1
                     );
                 }
                if (in_array($key, $holidays)) {
                   $_SESSION['earnings'][] = array(
                        'description'	=> getTranstype($con,$key),
                        'unit'		=> $value,
                        'amount'	=> $row[$key.'_amount']
                    );
                }
                if (in_array($key, $np)) {
                   $_SESSION['earnings'][] = array(
                        'description'	=> getTranstype($con,$key),
                        'unit'		=> $value,
                        'amount'	=> $row[$key.'_amount']
                    );
                }
                if (in_array($key, $ot)) {
                   $_SESSION['earnings'][] = array(
                        'description'	=> getTranstype($con,$key),
                        'unit'		=> $value,
                        'amount'	=> $row[$key.'_amount']
                    );
                }
                if (in_array($key, $npot)) {
                   $_SESSION['earnings'][] = array(
                        'description'	=> getTranstype($con,$key),
                        'unit'		=> $value,
                        'amount'	=> $row[$key.'_amount']
                    );
                }
                
                if (in_array($key, $kajimaallowance)) {
                    $_SESSION['earnings'][] = array(
                         'description'	=> getTranstype($con,$key),
                         'unit'		=> $value,
                         'amount'	=> $row[$key.'_amount']
                     );
                 }

                if (in_array($key, $negativeadditionals)) {
                    $_SESSION['deductions'][] = array(
                         'description'	=> getTranstype($con,$key),
                         'unit'		=> '',
                         'amount'	=> $value
                     );
                 }
            }
        }

       getpayregAdditionalearnings($con, $row['id'],$param);
       getpayregAdditionaldeductions($con, $row['id'],$param);


        $net = $row['net_amount'];
        $gross = $row['gross_amount'];

        if($param->type== 'Japanese' || $param->type == 'Japanese Conversion'){
            $total_ded = $row['total_ded'] - $row['w_tax'];;
        }else{
            $total_ded = $row['total_ded'];
        }
        $data = array('header' => $header,
                        'payinfo' => $payinfo,
                        'earning' => $_SESSION['earnings'],
                        'deductions' => $_SESSION['deductions'],
                        'netpay' => $net,
                        'total_ded' => $total_ded,
                        'ytd' => ytd($con, $param->id_acct, $param->pay_date),
                        'gross' => $gross,
                    );
	}
}

$return =  json_encode($data);
print $return;
mysqli_close($con);


function getEmployeeinfo($con,$id,$payregid){
    $Qry = new Query();	
    $Qry->table     = "tblaccount AS a LEFT JOIN tblaccountjob AS b ON a.id = b.idacct 
                        LEFT JOIN tblcompany AS c ON c.id = a.idcomp
                        LEFT JOIN tblpaygrp AS d ON d.id = b.idpaygrp
                        LEFT JOIN tblpayreg AS e ON e.idacct = a.id AND e.id = '".$payregid."'";
    $Qry->selected  = "CONCAT(`a`.`lname`,IFNULL(CONCAT(' ',`a`.`suffix`),''),', ',`a`.`fname`,' ',SUBSTR(`a`.`mname`,1,1),'. ') AS `empname`,
                            c.name,
                            a.empid,
                            a.idtin,
                            a.idsss,
                            a.idhealth,
                            a.idibig,
                            d.group,
                            IF(b.idpaygrp = 11
                            , ROUND((e.salary  / 26.0833),2)
                            , ROUND((e.salary ),2) )AS payrate";
    $Qry->fields    = "a.id ='".$id."'";
    $rs = $Qry->exe_SELECT($con);
    
    echo mysqli_error($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row;
        }
    }
}

function getPayinfo($con,$param){
    $Qry = new Query();	


    if($param->type == 'Local Employee'){
        $Qry->table     = "tblpayperiod";
    }
    if($param->type == 'Helper'){
        $Qry->table     = "tblpayperiod_helper";
    }
    if($param->type== 'Japanese'){
        $Qry->table     = "tblpayperiod_japanese";
    }
    if($param->type == 'Japanese Conversion'){
        $Qry->table     = "tblpayperiod_japaneseconversion";
    }


    $Qry->selected  = "*";
    $Qry->fields    = "id ='".$param->id_paydate."'";
    $rs = $Qry->exe_SELECT($con);
    
    echo mysqli_error($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row;
        }
    }
}

function getTranstype($con,$key){
    $id = 100000;

    if($key == 'salary'){
        $id = 58;
    }
    if($key == 'late'){
        $id = 34;
    }
    if($key == 'ut'){
        $id = 40;
    }
    if($key == 'abs'){
        $id = 33;
    }
    if($key == 'tc_vl'){
        $id = 42;
    }
    if($key == 'tc_sl'){
        $id = 43;
    }
    if($key == 'tc_sil'){
        $id = 139;
    }
    if($key == 'tc_cl'){
        $id = 44;
    }
    if($key == 'tc_ml'){
        $id = 90;
    }
    if($key == 'tc_mc'){
        $id = 86;
    }
    if($key == 'tc_sp'){
        $id = 46;
    }
    if($key == 'tc_pl'){
        $id = 45;
    }
    if($key == 'tc_el'){
        $id = 83;
    }
    if($key == 'tc_bl'){
        $id = 87;
    }
    if($key == 'tc_bdl'){
        $id = 82;
    }  
    if($key == 'tc_spl'){
        $id = 48;
    }
    if($key == 'tc_lwop'){
        $id = 47;
    }
    if($key == 'tc_rd'){
        $id = 129;
    }
    if($key == 'h_sh'){
        $id = 130;
    }
    if($key == 'h_shrd'){
        $id = 131;
    }
    if($key == 'h_lh'){
        $id = 132;
    }
    if($key == 'h_lhrd'){
        $id = 133;
    }
    if($key == 'h_lsh'){
        $id = 134;
    }
    if($key == 'h_lshrd'){
        $id = 22;
    }
    if($key == 'np_npreg'){
        $id = 16;
    }
    if($key == 'np_rd'){
        $id = 102;
    }
    if($key == 'np_sh'){
        $id = 17;
    }
    if($key == 'np_shrd'){
        $id = 18;
    }
    if($key == 'np_lh'){
        $id = 19;
    }
    if($key == 'np_lhrd'){
        $id = 20;
    }
    if($key == 'np_lsh'){
        $id = 64;
    }
    if($key == 'np_lshrd'){
        $id = 63;
    }
    if($key == 'ot_reg'){
        $id = 1;
    }
    if($key == 'ot_rd'){
        $id = 7;
    }
    if($key == 'ot_sh'){
        $id = 3;
    }
    if($key == 'ot_shrd'){
        $id = 11;
    }
    if($key == 'ot_lh'){
        $id = 5;
    }
    if($key == 'ot_lhrd'){
        $id = 9;
    }
    if($key == 'ot_lsh'){
        $id = 13;
    }
    if($key == 'ot_lshrd'){
        $id = 128;
    }
    if($key == 'npot_npot'){
        $id = 65;
    }
    if($key == 'npot_rd'){
        $id = 21;
    }
    if($key == 'npot_sh'){
        $id = 122;
    }
    if($key == 'npot_shrd'){
        $id = 123;
    }
    if($key == 'npot_lh'){
        $id = 124;
    }
    if($key == 'npot_lhrd'){
        $id = 125;
    }
    if($key == 'npot_lsh'){
        $id = 126;
    }
    if($key == 'npot_lshrd'){
        $id = 127;
    }
    if($key == 'ee'){
        $id = 61;
    }
    if($key == 'm_ee'){
        $id = 99;
    }
    if($key == 'p_ee'){
        $id = 56;
    }
    if($key == 'ph_ee'){
        $id = 53;
    }
    if($key == 'w_tax'){
        $id = 62;
    }

    if($key == 'otallowance'){
        $id = 151;
    }
    if($key == 'mallowance'){
        $id = 146;
    }
    if($key == 'hallowance'){
        $id = 147;
    }
    if($key == 'wallowance'){
        $id = 148;
    }
    if($key == 'shallowance'){
        $id = 150;
    }
    if($key == 'swallowance'){
        $id = 149;
    }
    
    

    $Qry = new Query();	
    $Qry->table     = "tblclasstrans";
    $Qry->selected  = "name";
    $Qry->fields    = "id ='".$id."'";
    $rs = $Qry->exe_SELECT($con);
    
    mysqli_error($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row['name'];
        }
    }
}

function getTranstypeAdditional($con,$id){
    $Qry = new Query();	
    $Qry->table     = "tblclasstrans";
    $Qry->selected  = "name";
    $Qry->fields    = "id ='".$id."'";
    $rs = $Qry->exe_SELECT($con);
    
    echo mysqli_error($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row['name'];
        }
    }
}

function  getpayregAdditionalearnings($con, $payregid,$param){
    $Qry = new Query();	
    $Qry->table     = "tblpayregadditional";
    $Qry->selected  = "*";
    $Qry->fields    = "payregid ='".$payregid."' AND type = 1";
    $rs = $Qry->exe_SELECT($con);
    
    echo mysqli_error($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
           $_SESSION['earnings'][] = array(
                'description' => getTranstypeAdditional($con,$row['transactionid']),
                'unit'		 => '',
                'amount'	 => $row['amount']
            );
        }
    }

}

function  getpayregAdditionaldeductions($con, $payregid,$param){
    $Qry = new Query();	
    $Qry->table     = "tblpayregadditional";
    $Qry->selected  = "*";
    $Qry->fields    = "payregid ='".$payregid."' AND type = 0";
    $rs = $Qry->exe_SELECT($con);
    
    echo mysqli_error($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_assoc($rs)){
           $_SESSION['deductions'][] = array(
                'description' => getTranstypeAdditional($con,$row['transactionid']),
                'unit'		 => '',
                'amount'	 => $row['amount']
            );
        }
    }

}

function ytd($con,$idacct,$paydate){
    $Qry = new Query();	
    $Qry->table     = "tblpayreg";
    $Qry->selected  = "SUM(gross_amount) as ytdgross,
                        SUM(ee + m_ee + p_ee + ph_ee ) as ytdgovcon,
                        SUM( (gross_amount) - (ee + m_ee + p_ee + ph_ee) ) as ytdtaxable,
                        SUM( w_tax ) as ytdwtax";

    $Qry->fields    = "idacct ='".$idacct."' AND 
                    idpayperiod IN (SELECT id  FROM tblpayperiod 
                                    WHERE YEAR(pay_date) = YEAR('".$paydate."')
                                )";

    $rs = $Qry->exe_SELECT($con);
    
    echo mysqli_error($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_assoc($rs)){
            return $row;
        }
    }
}
session_destroy();
?>