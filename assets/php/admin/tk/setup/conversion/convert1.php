<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));

$Qry1           = new Query();
$Qry1->table    = "tblleaves_conversion";
$releaseperiod = getPeriod($con,getReleaseDate($con,$param->id));
$currperiod = getPeriod($con,date("Y-m-d"));

if(empty($param->date)){
    if($param->stats != '1' && empty($param->conversion) &&  empty($param->porfeit) &&  empty($param->reset) && empty($param->unit)){
        if($releaseperiod >= $currperiod && $releaseperiod != '0' && $currperiod != '0'){
            $Qry1->selected = "stat='1'";
        }else if($releaseperiod == '0' && $currperiod == '0'){
            $return = json_encode(array('status'=>'periodwarning','leave'=>getClosedLeave($con,$param->id)));
        }else{
            $return = json_encode(array('status'=>'periodwarning','leave'=>getClosedLeave($con,$param->id)));
        }
    }elseif($param->stats == '1' && empty($param->conversion)){
        $return = json_encode(array('status'=>'notallowed','leave'=>getClosedLeave($con,$param->id)));
    }elseif(!empty($param->conversion)){
        if(getStatsOpen($con,$param->id)){
            if($param->conversion != 'Excess of' && $param->conversion != 'Maximum of'){
                $Qry1->selected = "conversion_type='".$param->conversion."',conversion_unit='0.00'";
            }else{
                $Qry1->selected = "conversion_type='".$param->conversion."'";
            }
            
        }else{
            $return = json_encode(array('status'=>'closed','leave'=>getClosedLeave($con,$param->id)));
        }
    }elseif(!empty($param->unit)){
        if(getStatsOpen($con,$param->id)){
            $Qry1->selected = "conversion_unit='".$param->unit."'";
        }else{
            $return = json_encode(array('status'=>'closed','leave'=>getClosedLeave($con,$param->id)));
        }
    }elseif(!empty($param->porfeit)){
        if(getStatsOpen($con,$param->id)){
            if($param->porfeit != 'Y'){
                $Qry1->selected = "porfeit='1'";
            }else{
                $Qry1->selected = "porfeit='0'";
            }
        }else{
            $return = json_encode(array('status'=>'closed','leave'=>getClosedLeave($con,$param->id)));
        }
    }elseif(!empty($param->reset)){
        if(getStatsOpen($con,$param->id)){
            if($param->reset != 'Y'){
                $Qry1->selected = "reset='1'";
            }else{
                $Qry1->selected = "reset='0'";
            }
        }else{
            $return = json_encode(array('status'=>'closed','leave'=>getClosedLeave($con,$param->id)));
        }
    }
}else{
    if(getStatsOpen($con,$param->id)){
        $Qry1->selected = "release_date='".$param->date."'";
    }else{
        $return = json_encode(array('status'=>'closed','leave'=>getClosedLeave($con,$param->id)));
    }
}

$Qry1->fields   = "idleave='".$param->id."'";
$checke = $Qry1->exe_UPDATE($con);

if($checke){
    $return = json_encode(array("status"=>"success"));
}

print $return;
mysqli_close($con);

function getStatsOpen($con,$id){
	$Qry = new Query();	
	$Qry->table ="tblleaves_conversion";
	$Qry->selected ="*";
	$Qry->fields ="idleave='".$id."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            if($row['stat'] != 1){
                return true;
            }else{
                return false;
            }
        }
    }
    return false;
}
function getReleaseDate($con,$id){
	$Qry = new Query();	
	$Qry->table ="tblleaves_conversion";
	$Qry->selected ="*";
	$Qry->fields ="idleave='".$id."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return $row['release_date'];
        }
    }
    return '';
}
function getClosedLeave($con,$id){
	$Qry = new Query();	
	$Qry->table ="tblleaves";
	$Qry->selected ="*";
	$Qry->fields ="id='".$id."'";
	$rs = $Qry->exe_SELECT($con);
	if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
                return $row['name'];
        }
    }
    return '';
}
function getPeriod($con, $date){
    $Qry = new Query();	
    $Qry->table     = "tblpayperiod";
    $Qry->selected  = "*";
    $Qry->fields    = "period_start <= '".$date."'  AND  period_end >= '".$date."' ORDER BY pay_date ASC limit 1";
    $rs = $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        if($row=mysqli_fetch_array($rs)){
            return (int)$row['id'];
        }
    }
    return 0;
}
?>