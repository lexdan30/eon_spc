<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date = SysDatePadLeft();
// $date = '1990-06-10';
$date2 = date('Y-m-d');
//$date2 = '2020-01-10';

$dept = getIdUnit($con,$param->accountid);
$ids=0;
//Search Department
// print_r($dept);
$ids=0;if( !empty( $dept ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$dept);
    //print_r($arr);
    array_push( $arr_id, 0 );
    if( !empty( $arr["nodechild"] ) ){
        $a = getChildNodes($arr_id, $arr["nodechild"]);
        if( !empty($a) ){
            foreach( $a as $v ){
                array_push( $arr_id, $v );
            }
        }
    }
    if( count($arr_id) == 1 ){ 
        $ids 			= $arr_id[0];
    }else{
        $ids 			= implode(",",$arr_id);
    }
}


$data = array();
$Qry = new Query();	
$Qry->table     = "tblforms01 LEFT JOIN vw_dataemployees ON tblforms01.empid = vw_dataemployees.empid";
$Qry->selected  = "tblforms01.empid, tblforms01.empname, tblforms01.empactiontaken";
$Qry->fields    = "tblforms01.idstatus = 3 AND (vw_dataemployees.idunit in (".$ids.") OR vw_dataemployees.idsuperior='".$param->accountid."' )";

$rs = $Qry->exe_SELECT($con);

while($row=mysqli_fetch_array($rs)){
    $data[] = array( 
        "id" 			    => $row['empid'],
        "name" 			    => $row['empname'],
        "empactiontaken" 	=> 'Pending ' . $row['empactiontaken'], 
    );
}

$Qry1 = new Query();	
$Qry1->table     = "tblforms02 LEFT JOIN vw_dataemployees ON tblforms02.empid = vw_dataemployees.empid";
$Qry1->selected  = "tblforms02.empid, tblforms02.empname, tblforms02.empactiontaken";
$Qry1->fields    = "idstatus = 3 AND (vw_dataemployees.idunit in (".$ids.") OR vw_dataemployees.idsuperior='".$param->accountid."') ";

$rs1 = $Qry1->exe_SELECT($con);

while($row=mysqli_fetch_array($rs1)){
    $data[] = array( 
        "id" 			    => $row['empid'],
        "name" 			    => $row['empname'],
        "empactiontaken" 	=> 'Pending ' . $row['empactiontaken'],
    );
}

$Qry2 = new Query();	
$Qry2->table     = "tblforms03 LEFT JOIN vw_dataemployees ON tblforms03.empid = vw_dataemployees.empid";
$Qry2->selected  = "tblforms03.empid, tblforms03.empname, tblforms03.empactiontaken";
$Qry2->fields    = "idstatus = 3 AND (vw_dataemployees.idunit in (".$ids.") OR vw_dataemployees.idsuperior='".$param->accountid."') ";

$rs2 = $Qry2->exe_SELECT($con);

while($row=mysqli_fetch_array($rs2)){
    $data[] = array( 
        "id" 			    => $row['empid'],
        "name" 			    => $row['empname'],
        "empactiontaken" 	=> 'Pending ' . $row['empactiontaken'],
    ); 
}


$Qry8 = new Query();	
$Qry8->table     = "tblaccountjob AS a LEFT JOIN tblaccount AS b ON b.id = a.idacct";
$Qry8->selected  = "b.id, b.empid, CONCAT(b.lname,', ',b.fname,' ',SUBSTR(`b`.`mname`,1,1),'. ') AS empname, a.hdate, DATE_SUB(DATE_ADD(a.hdate, INTERVAL 6 MONTH) - INTERVAL 1 DAY, INTERVAL 45 DAY) AS startdate, DATE_ADD(a.hdate, INTERVAL 6 MONTH) - INTERVAL 1 DAY AS regdate";
$Qry8->fields    = "(a.idsuperior='".$param->accountid."' or a.idunit IN (".$ids.")) AND ('".$date2."' BETWEEN DATE_SUB(DATE_ADD(a.hdate, INTERVAL 6 MONTH) - INTERVAL 1 DAY, INTERVAL 45 DAY) AND DATE_ADD(a.hdate, INTERVAL 6 MONTH) - INTERVAL 1 DAY)";
$rs8 = $Qry8->exe_SELECT($con);

while($row=mysqli_fetch_array($rs8)){
        $data[] = array( 
            "id" 			    => $row['empid'],
            "name" 			    => $row['empname'],
            "empactiontaken" 	=> 'Contract is about to end',
        );
}

$Qry9 = new Query();	
$Qry9->table     = "tblaccountjob AS a LEFT JOIN tblaccount AS b ON b.id = a.idacct";
$Qry9->selected  = "b.id, b.empid, CONCAT(b.lname,', ',b.fname,' ',SUBSTR(`b`.`mname`,1,1),'. ') AS empname,a.empstat, a.hdate, a.contract_fdate, DATE_SUB(DATE_ADD(a.hdate, INTERVAL 6 MONTH) - INTERVAL 1 DAY, INTERVAL 45 DAY) AS startdate, DATE_ADD(a.hdate, INTERVAL 6 MONTH) - INTERVAL 1 DAY AS regdate";
$Qry9->fields    = "(a.idsuperior='".$param->accountid."' or a.idunit IN (".$ids.")) AND ('".$date2."' BETWEEN DATE_ADD(a.contract_fdate,INTERVAL -45 DAY) AND DATE_ADD(a.contract_fdate,INTERVAL 1 DAY)) AND a.contract_fdate IS NOT NULL";
$rs9 = $Qry9->exe_SELECT($con);

while($row=mysqli_fetch_array($rs9)){
    $data[] = array( 
        "id" 			    => $row['empid'],
        "name" 			    => $row['empname'],
        "empactiontaken" 	=> 'Project based contract is about to end.',
    );
}


$Qry7 = new Query();	
$Qry7->table     = "vw_dataemployees vd LEFT JOIN vw_data_timesheet vt ON vt.idacct = vd.id";
$Qry7->selected  = "vd.empname,vd.empid,vt.temp";
$Qry7->fields    = "(idunit IN (".$ids.") OR idsuperior='".$param->accountid."') AND vt.work_date = CURDATE() ";
$rs7 = $Qry7->exe_SELECT($con);

while($row=mysqli_fetch_array($rs7)){
    if($row['temp'] > 37.9){
        $data[] = array( 
            "id" 			    => $row['empid'],
            "name" 			    => $row['empname'],
            "empactiontaken" 	=> '<p style="color : red">High Temperature : ' . $row['temp'] .  ' &#8451;</p>',
        );
    }
}



$Qry3 = new Query();	
$Qry3->table     = "tblforms04 LEFT JOIN vw_dataemployees ON tblforms04.empid = vw_dataemployees.empid";
$Qry3->selected  = "tblforms04.empid, tblforms04.empname, tblforms04.explanation";
$Qry3->fields    = "idstatus = 3 AND (vw_dataemployees.idunit in (".$ids.") OR vw_dataemployees.idsuperior='".$param->accountid."') ";

$rs3 = $Qry3->exe_SELECT($con);

while($row=mysqli_fetch_array($rs3)){
    $data[] = array( 
        "id" 			    => $row['empid'],
        "name" 			    => $row['empname'],
        "empactiontaken" 	=> 'Pending ' . $row['explanation'],
    );
}

$Qry4 = new Query();	
$Qry4->table     = "tblchangereq LEFT JOIN vw_dataemployees ON tblchangereq.empid = vw_dataemployees.empid";
$Qry4->selected  = "tblchangereq.*,vw_dataemployees.empname,vw_dataemployees.idunit,vw_dataemployees.bdate";
$Qry4->fields    = " id_status = 3 AND (vw_dataemployees.idunit in (".$ids.") OR vw_dataemployees.idsuperior='".$param->accountid."') ";

$rs4 = $Qry4->exe_SELECT($con);
$array=array();
while($row=mysqli_fetch_array($rs4)){

    $day = $row['bdate'];
    $date1 = date("d", strtotime($date));
    $date2 = date("m", strtotime($date));
    $bdateday = date("d", strtotime($day));
    $bdatemonth =date("m",strtotime($day));
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
    if($bdateday == $date1 && $bdatemonth == $date2){
        if( $req != '' ){
            $req=$req. ' And Birthday Celebrant ';
        }else{
            $req = 'Birthday Celebrant ';
        }

        array_push($array,$row['empid']);
    }

    $data[] = array( 
        "name"             => $row['empname'],
        "id" 			    => $row['empid'],
        "req" 	            => $req
    );
}

$Qry5 = new Query();	
$Qry5->table     = "vw_dataemployees";
$Qry5->selected  = "id,empname,bdate,idunit,empid";
$Qry5->fields    = "DAY(bdate) = DAY(NOW()) AND MONTH(bdate) = MONTH(NOW()) AND (idunit IN (".$ids.") OR idsuperior='".$param->accountid."') ORDER BY DATE_FORMAT(bdate, '%c-%d') asc ";

$rs5 = $Qry5->exe_SELECT($con);

while($row=mysqli_fetch_array($rs5)){

    $day = $row['bdate'];
    $date1 = date("d", strtotime($date));
    $date2 = date("m", strtotime($date));
    $bdateday = date("d", strtotime($day));
    $bdatemonth =date("m",strtotime($day));

    $req1='';

    if($bdateday == $date1 && $bdatemonth == $date2){
        $req1='Birthday Celebrant';
    }

    
    if(!in_array($row['empid'],$array)){
        $data[] = array( 
            "id" 			    => $row['empid'],
            "name" 			    => $row['empname'],
            "bday" 			    => $row['bdate'],
            "bdate" 	        => $req1,
        );
    }
}

$Qry6 = new Query();	
$Qry6->table     = "vw_dataemployees";
$Qry6->selected  = "empid,empname, idunit,hdate, TIMESTAMPDIFF(YEAR,hdate,CURDATE()) AS anniv";
$Qry6->fields    = "DAY(hdate) = DAY(NOW()) AND MONTH(hdate) = MONTH(NOW()) AND (idunit IN (".$ids.") OR idsuperior='".$param->accountid."') ORDER BY DATE_FORMAT(hdate, '%c-%d') ASC";
$rs6 = $Qry6->exe_SELECT($con);

while($row=mysqli_fetch_array($rs6)){

    $today = strtotime($date);  
    $hdate = strtotime($row['hdate']); 
    $years = abs($today - $hdate);  
    $anniv = floor($years / (365*60*60*24)); 
    

    $req2 ='';
	$day = $row['hdate'];
    $today_day = date("d", strtotime($date));
    $today_month = date("m", strtotime($date));
    $hired_day = date("d", strtotime($day));
    $hired_month =date("m",strtotime($day));    

    if($hired_day == $today_day && $hired_month == $today_month){

        //get st in 1st
        $j = $anniv % 10;
        $k = $anniv % 100;
        
        if ($j == 1 && $k != 11) {
            $anniv = $anniv . "st";
        }elseif ($j == 2 && $k != 12) {
            $anniv = $anniv . "nd";
        }elseif ($j == 3 && $k != 13) {
            $anniv = $anniv . "rd";
        }else{$anniv = $anniv . "th";} 

        $req2 = $anniv.' Anniversary';
        //end
    }

    if(!in_array($row['empid'],$array)){
        $data[] = array( 
            "name"      => $row['empname'],
            "id" 	    => $row['empid'],
            "hdate" 	=> $row['hdate'],
            "anniv_no" 	=> $anniv,
            "req2" 	    => $req2,
        );
    }
}




$return = json_encode($data);

print $return;
mysqli_close($con);

function getIdUnit($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['idunit'];
        }
    }
    return null;
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

// function getbdate($con, $date, $idacct){
//     $Qry=new Query();
//     $Qry->table="vw_dataemployees";
//     $Qry->selected="id,empname,bdate";
//     $Qry->fields="DAY(bdate) = DAY(NOW()) AND MONTH(bdate) = MONTH(NOW()) AND id IN ('".$idacct."') ORDER BY DATE_FORMAT(bdate, '%c-%d') asc  ";
//     $rs=$Qry->exe_SELECT($con);
//     if(mysqli_num_rows($rs)>=1){
//         return true;
//     }else{
//         return false;
//     }
// }


?>