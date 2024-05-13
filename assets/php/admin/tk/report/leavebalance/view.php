<?php
require_once('../../../../activation.php');
$conn = new connector();    
$con = $conn->connect();
require_once('../../../../classPhp.php'); 
 
$param = $_POST;
$param = json_decode(file_get_contents('php://input'));
$data  = array();
$date  = SysDateDan(); 
$time  = SysTime();
$pay_period = getPayPeriod($con);
$search='';
if( !empty( $param->empid  ) ){ $search=" AND de.empid =  '".$param->empid ."' "; }
if( !empty( $param->post  ) ){ $search=" AND de.post =  '".$param->post ."' "; }
if( !empty( $param->class  ) ){ $search=" AND de.business_unit =  '".$param->class ."' "; }
 
$where = $search;
 
$Qry            = new Query();  
$Qry->table     = "vw_dataemployees AS de LEFT JOIN vw_leavesummary AS al ON de.id=al.idacct";
$Qry->selected  = "de.id,de.empid, de.empname,de.business_unit,de.unittype,de.idunit, de.post, de.concat_sup_fname_lname AS manager,
SUM(CASE WHEN idleave = 1 THEN entitle ELSE '' END ) AS sl_entitlement,
SUM(CASE WHEN idleave = 1 THEN used ELSE '' END ) AS sl_used,
SUM(CASE WHEN idleave = 1 THEN balance ELSE '' END ) AS sl_balance,
SUM(CASE WHEN idleave = 1 THEN carry_over ELSE '' END ) AS sl_carry_over,
SUM(CASE WHEN idleave = 1 THEN conversion ELSE '' END ) AS sl_conversion,
SUM(CASE WHEN idleave = 2 THEN entitle ELSE '' END ) AS vl_entitlement,
SUM(CASE WHEN idleave = 2 THEN used ELSE '' END ) AS vl_used,
SUM(CASE WHEN idleave = 2 THEN balance ELSE '' END ) AS vl_balance,
SUM(CASE WHEN idleave = 2 THEN carry_over ELSE '' END ) AS vl_carry_over,
SUM(CASE WHEN idleave = 2 THEN conversion ELSE '' END ) AS vl_conversion,
SUM(CASE WHEN idleave = 3 THEN entitle ELSE '' END ) AS lwop_entitlement,
SUM(CASE WHEN idleave = 3 THEN used ELSE '' END ) AS lwop_used,
SUM(CASE WHEN idleave = 3 THEN balance ELSE '' END ) AS lwop_balance,
SUM(CASE WHEN idleave = 3 THEN carry_over ELSE '' END ) AS lwop_carry_over,
SUM(CASE WHEN idleave = 3 THEN conversion ELSE '' END ) AS lwop_conversion,
SUM(CASE WHEN idleave = 4 THEN entitle ELSE '' END ) AS solo_entitlement,
SUM(CASE WHEN idleave = 4 THEN used ELSE '' END ) AS solo_used,
SUM(CASE WHEN idleave = 4 THEN balance ELSE '' END ) AS solo_balance,
SUM(CASE WHEN idleave = 4 THEN carry_over ELSE '' END ) AS solo_carry_over,
SUM(CASE WHEN idleave = 4 THEN conversion ELSE '' END ) AS solo_conversion,
SUM(CASE WHEN idleave = 5 THEN entitle ELSE '' END ) AS paternity_entitlement,
SUM(CASE WHEN idleave = 5 THEN used ELSE '' END ) AS paternity_used,
SUM(CASE WHEN idleave = 5 THEN balance ELSE '' END ) AS paternity_balance,
SUM(CASE WHEN idleave = 5 THEN carry_over ELSE '' END ) AS paternity_carry_over,
SUM(CASE WHEN idleave = 5 THEN conversion ELSE '' END ) AS paternity_conversion,
SUM(CASE WHEN idleave = 6 THEN entitle ELSE '' END ) AS comp_entitlement,
SUM(CASE WHEN idleave = 6 THEN used ELSE '' END ) AS comp_used,
SUM(CASE WHEN idleave = 6 THEN balance ELSE '' END ) AS comp_balance,
SUM(CASE WHEN idleave = 6 THEN carry_over ELSE '' END ) AS comp_carry_over,
SUM(CASE WHEN idleave = 6 THEN conversion ELSE '' END ) AS comp_conversion,
SUM(CASE WHEN idleave = 7 THEN entitle ELSE '' END ) AS spcleave_entitlement,
SUM(CASE WHEN idleave = 7 THEN used ELSE '' END ) AS spcleave_used,
SUM(CASE WHEN idleave = 7 THEN balance ELSE '' END ) AS spcleave_balance,
SUM(CASE WHEN idleave = 7 THEN carry_over ELSE '' END ) AS spcleave_carry_over,
SUM(CASE WHEN idleave = 7 THEN conversion ELSE '' END ) AS spcleave_conversion,
SUM(CASE WHEN idleave = 8 THEN entitle ELSE '' END ) AS bday_entitlement,
SUM(CASE WHEN idleave = 8 THEN used ELSE '' END ) AS bday_used,
SUM(CASE WHEN idleave = 8 THEN balance ELSE '' END ) AS bday_balance,
SUM(CASE WHEN idleave = 8 THEN carry_over ELSE '' END ) AS bday_carry_over,
SUM(CASE WHEN idleave = 8 THEN conversion ELSE '' END ) AS bday_conversion,
SUM(CASE WHEN idleave = 9 THEN entitle ELSE '' END ) AS emer_entitlement,
SUM(CASE WHEN idleave = 9 THEN used ELSE '' END ) AS emer_used,
SUM(CASE WHEN idleave = 9 THEN balance ELSE '' END ) AS emer_balance,
SUM(CASE WHEN idleave = 9 THEN carry_over ELSE '' END ) AS emer_carry_over,
SUM(CASE WHEN idleave = 9 THEN conversion ELSE '' END ) AS emer_conversion,
SUM(CASE WHEN idleave = 10 THEN entitle ELSE '' END ) AS magna_entitlement,
SUM(CASE WHEN idleave = 10 THEN used ELSE '' END ) AS magna_used,
SUM(CASE WHEN idleave = 10 THEN balance ELSE '' END ) AS magna_balance,
SUM(CASE WHEN idleave = 10 THEN carry_over ELSE '' END ) AS magna_carry_over,
SUM(CASE WHEN idleave = 10 THEN conversion ELSE '' END ) AS magna_conversion,
SUM(CASE WHEN idleave = 11 THEN entitle ELSE '' END ) AS bereav_entitlement,
SUM(CASE WHEN idleave = 11 THEN used ELSE '' END ) AS bereav_used,
SUM(CASE WHEN idleave = 11 THEN balance ELSE '' END ) AS bereav_balance,
SUM(CASE WHEN idleave = 11 THEN carry_over ELSE '' END ) AS bereav_carry_over,
SUM(CASE WHEN idleave = 11 THEN conversion ELSE '' END ) AS bereav_conversion,
SUM(CASE WHEN idleave = 12 THEN entitle ELSE '' END ) AS mater_entitlement,
SUM(CASE WHEN idleave = 12 THEN used ELSE '' END ) AS mater_used,
SUM(CASE WHEN idleave = 12 THEN balance ELSE '' END ) AS mater_balance,
SUM(CASE WHEN idleave = 12 THEN carry_over ELSE '' END ) AS mater_carry_over,
SUM(CASE WHEN idleave = 12 THEN conversion ELSE '' END ) AS mater_conversion";
$Qry->fields    = "de.id!=1 AND al.entitle is not null ". $search . " GROUP BY de.empid order by de.empname";
$rs             = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        if($row['unittype'] !=3 && $row['unittype'] > 3){
            $dept = getDepartment($con, $row['idunit']);
        }else{
            $dept = $row['business_unit'];
        }
 
        //to solve in storedproc or proceed statements like this as case if not solved
        if( (int)$row['vl_entitlement'] < (int)$row['vl_used'] ){
             $vl='-'.$row['vl_balance'];
        }else{
            $vl=$row['vl_balance'];
        }

        $data[] = array( 
            "id"                        => $row['id'],
            "empid"                     => $row['empid'],
            "empname"                   => $row['empname'],
            'classif'                   => $row['business_unit'],
            'dept'                      => $dept,
            "post"                      => $row['post'],
            "manager"                   => $row['manager'],
 
            "sl_entitlement"            => $row['sl_entitlement'],
            "sl_used"                   =>  $row['sl_used'],
            "sl_balance"                => $row['sl_balance'],
            "sl_carry_over"             => $row['sl_carry_over'],
            "sl_conversion"             => $row['sl_conversion'],
            
            "vl_entitlement"            => $row['vl_entitlement'],
            "vl_used"                   => $row['vl_used'],
            "vl_balance"                => $vl,
            "vl_carry_over"             => $row['vl_carry_over'],
            "vl_conversion"             => $row['vl_conversion'],
 
            "lwop_entitlement"          => $row['lwop_entitlement'],
            "lwop_used"                 =>  $row['lwop_used'],
            "lwop_balance"              => $row['lwop_balance'],
            "lwop_carry_over"           => $row['lwop_carry_over'],
            "lwop_conversion"           => $row['lwop_conversion'],
 
            "solo_entitlement"          => $row['solo_entitlement'],
            "solo_used"                 => $row['solo_used'],
            "solo_balance"              => $row['solo_balance'],
            "solo_carry_over"           => $row['solo_carry_over'],
            "solo_conversion"           => $row['solo_conversion'],
 
            "paternity_entitlement"     => $row['paternity_entitlement'],
            "paternity_used"            => $row['paternity_used'],
            "paternity_balance"         => $row['paternity_balance'],
            "paternity_carry_over"      => $row['paternity_carry_over'],
            "paternity_conversion"      => $row['paternity_conversion'],
 
            "comp_entitlement"          => $row['comp_entitlement'],
            "comp_used"                 => $row['comp_used'],
            "comp_balance"              => $row['comp_balance'],
            "comp_carry_over"           => $row['comp_carry_over'],
            "comp_conversion"           => $row['comp_conversion'],
 
            "spcleave_entitlement"      => $row['spcleave_entitlement'],
            "spcleave_used"             =>  $row['spcleave_used'],
            "spcleave_balance"          => $row['spcleave_balance'],
            "spcleave_carry_over"       => $row['spcleave_carry_over'],
            "spcleave_conversion"       => $row['spcleave_conversion'],
 
            "bday_entitlement"          => $row['bday_entitlement'],
            "bday_used"                 =>  $row['bday_used'],
            "bday_balance"              => $row['bday_balance'],
            "bday_carry_over"           => $row['bday_carry_over'],
            "bday_conversion"           => $row['bday_conversion'],
 
            "emer_entitlement"          => $row['emer_entitlement'],
            "emer_used"                 => $row['emer_used'] ,
            "emer_balance"              => $row['emer_balance'],
            "emer_carry_over"           => $row['emer_carry_over'],
            "emer_conversion"           => $row['emer_conversion'],
 
            "magna_entitlement"         => $row['magna_entitlement'],
            "magna_used"                => $row['magna_used'] ,
            "magna_balance"             => $row['magna_balance'],
            "magna_carry_over"           => $row['magna_carry_over'],
            "magna_conversion"           => $row['magna_conversion'],
 
            "bereav_entitlement"        => $row['bereav_entitlement'],
            "bereav_used"               => $row['bereav_used'],
            "bereav_balance"            => $row['bereav_balance'],
            "bereav_carry_over"         => $row['bereav_carry_over'],
            "bereav_conversion"         => $row['bereav_conversion'],
 
            "mater_entitlement"         => $row['mater_entitlement'],
            "mater_used"                => $row['mater_used'],
            "mater_balance"             => $row['mater_balance'],
            "mater_carry_over"          => $row['mater_carry_over'],
            "mater_conversion"          => $row['mater_conversion'],
 
            "date"              => $date,
            "time"              => date ("H:i:s A",strtotime($time))
        );
        $return = json_encode($data);
    }
}else{
    $return = json_encode(array());
}
 
 
 
 
print $return;
mysqli_close($con);
?>
