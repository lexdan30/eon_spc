<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

$Qry = new Query();	
$Qry->table     = "tblchangereq";
$Qry->selected  = "*";
$Qry->fields    = "id_status = 3";

$rs = $Qry->exe_SELECT($con);

while($row=mysqli_fetch_array($rs)){

    $req ='';

    if($row['new_fname'] != $row['current_fname'] && ($row['new_fname']!=''||$row['new_fname']!=null)){
        if( $req != '' ){
            $req=$req. ' And First Name ';
        }else{
            $req = 'Change First Name ';
        }
        // $req = 'Change First Name';
    }
    if($row['new_mname'] != $row['current_mname'] && ($row['new_mname']!=''||$row['new_mname']!=null)){
        if( $req != '' ){
            $req=$req. ' And Middle Name ';
        }else{
            $req = 'Change Middle Name ';
        }
    }
    if($row['new_lname'] != $row['current_lname'] && ($row['new_lname']!=''||$row['new_lname']!=null)){
        if( $req != '' ){
            $req=$req. ' And Last Name ';
        }else{
            $req = 'Change Last Name ';
        }
        // $req = 'Change Last Name';
    }
    if($row['new_suffix'] != $row['current_suffix'] && ($row['new_suffix']!=''||$row['new_suffix']!=null)){
        if( $req != '' ){
            $req=$req. ' And Suffix ';
        }else{
            $req = 'Change Suffix ';
        }
        // $req = 'Change Suffix';
    }
    if($row['new_nickname'] != $row['current_nickname'] && ($row['new_nickname']!=''||$row['new_nickname']!=null)){
        if( $req != '' ){
            $req=$req. ' And Nickname ';
        }else{
            $req = 'Change Nickname ';
        }
        // $req = 'Change Nickname';
    }
    if($row['new_mari_stat'] != $row['current_mari_stat'] && ($row['new_mari_stat']!=''||$row['new_mari_stat']!=null)){
        if( $req != '' ){
            $req=$req. ' And Marital Status ';
        }else{
            $req = 'Change Marital Status ';
        }
        // $req = 'Change Marital Status';
    }
    if($row['new_emer_name'] != $row['current_emer_name'] && ($row['new_emer_name']!=''||$row['new_emer_name']!=null)){
        if( $req != '' ){
            $req=$req. ' And Emergency Contact Name ';
        }else{
            $req = 'Change Emergency Contact Name ';
        }
        // $req = 'Change Emergency Contact Name';
    }
    if($row['new_emer_cont'] != $row['current_emer_cont'] && ($row['new_emer_cont']!=''||$row['new_emer_cont']!=null)){
        if( $req != '' ){
            $req=$req. ' And Emergency Contact Number ';
        }else{
            $req = 'Change Emergency Contact Number ';
        }
        // $req = 'Change Emergency Contact Number';
    }



    

    if($row['new_add_st'] != $row['current_add_st'] && ($row['new_add_st']!=''||$row['new_add_st']!=null)){
        if( $req != '' ){
            $req=$req. ' And Address St ';
        }else{
            $req = 'Change Address St ';
        }
        // $req = 'Change Home Address';
    }
    if($row['new_add_area'] != $row['current_add_area'] && ($row['new_add_area']!=''||$row['new_add_area']!=null)){
        if( $req != '' ){
            $req=$req. ' And Address Area ';
        }else{
            $req = 'Change Address Area ';
        }
    }
    if($row['new_add_city'] != $row['current_add_city'] && ($row['new_add_city']!=''||$row['new_add_city']!=null)){
        if( $req != '' ){
            $req=$req. ' And Address City ';
        }else{
            $req = 'Change Address City ';
        }
    }
    if($row['new_add_prov'] != $row['current_add_prov'] && ($row['new_add_prov']!=''||$row['new_add_prov']!=null)){
        if( $req != '' ){
            $req=$req. ' And Address Province ';
        }else{
            $req = 'Change Address Province ';
        }
    }
    if($row['new_add_code'] != $row['current_add_code'] && ($row['new_add_code']!=''||$row['new_add_code']!=null)){
        if( $req != '' ){
            $req=$req. ' And Address Code ';
        }else{
            $req = 'Change Address Code ';
        }
    }




    if($row['new_pnum'] != $row['current_pnum'] && ($row['new_pnum']!=''||$row['new_pnum']!=null) ){
        if( $req != '' ){
            $req=$req. ' And Phone Number ';
        }else{
            $req = 'Change Phone Number ';
        }
        // $req = 'Change Phone Number';
    }
    if(($row['new_fax_num'] != $row['current_fax_num']) && ($row['new_fax_num']!=''||$row['new_fax_num']!=null)){
        if( $req != '' ){
            $req=$req. ' And Fax Number ';
        }else{
            $req = 'Change Fax Number ';
        }
        // $req = 'Change Fax Number';
    }
    if(($row['new_mnum'] != $row['current_mnum']) && ($row['new_mnum']!='' || $row['new_mnum']!=null)){
        if( $req != '' ){
            $req=$req. ' And Mobile Number ';
        }else{
            $req = 'Change Mobile Number ';
        }
        // $req = 'Change Mobile Number';
    }
    if(isApprover($con, $row['idacct'], $row['ref_num'])==true ){
        if( $req != '' ){
            $req=$req. ' And Dependent ';
        }else{
            $req = 'Change Dependent ';
        }
        // $req = 'Change Mobile Number';
    }

    $data[] = array( 
        "idreq"             => $row['id'],
        "id" 			    => $row['empid'],
        "name" 			    => getName($con, $row['idacct']),
        "req" 	            => $req
    );
}

$return = json_encode($data);

print $return;
mysqli_close($con);


function getName($con, $accountid){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="empname";
    $Qry->fields="id='".$accountid."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
			return $row['empname'];
        }
    }
    return '';
}


function isApprover($con, $idacct, $ref){
    $Qry=new Query();
    $Qry->table="tbldependent";
    $Qry->selected="id";
    $Qry->fields="idacct='".$idacct."' AND status=3 AND ref_num='".$ref."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        return true;
    }else{
        return false;
    }
}


?>