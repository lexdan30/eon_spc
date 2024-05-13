<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();

$search ='';

if( !empty( $param->empid ) ){ $search=$search." AND de.empid like 	'%".$param->empid."%' "; }

//Search Department
if( !empty( $param->department ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$param->department);
    array_push( $arr_id, $param->department );
    if( !empty( $arr["nodechild"] ) ){
        $a = getChildNode($arr_id, $arr["nodechild"]);
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
    $search.=" AND idunit in (".$ids.") "; 
}

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees AS de LEFT JOIN tblaccountleaves AS al ON de.id=al.idacct";
$Qry->selected  = "de.id,de.empid, de.empname, de.post, de.concat_sup_fname_lname AS manager,
SUM(CASE WHEN idleave = 1 THEN entitle ELSE '' END ) AS sl_entitlement,
SUM(CASE WHEN idleave = 1 THEN used ELSE '' END ) AS sl_used,
SUM(CASE WHEN idleave = 1 THEN balance ELSE '' END ) AS sl_balance,
SUM(CASE WHEN idleave = 2 THEN entitle ELSE '' END ) AS vl_entitlement,
SUM(CASE WHEN idleave = 2 THEN used ELSE '' END ) AS vl_used,
SUM(CASE WHEN idleave = 2 THEN balance ELSE '' END ) AS vl_balance,
SUM(CASE WHEN idleave = 3 THEN entitle ELSE '' END ) AS lwop_entitlement,
SUM(CASE WHEN idleave = 3 THEN used ELSE '' END ) AS lwop_used,
SUM(CASE WHEN idleave = 3 THEN balance ELSE '' END ) AS lwop_balance,
SUM(CASE WHEN idleave = 4 THEN entitle ELSE '' END ) AS solo_entitlement,
SUM(CASE WHEN idleave = 4 THEN used ELSE '' END ) AS solo_used,
SUM(CASE WHEN idleave = 4 THEN balance ELSE '' END ) AS solo_balance,
SUM(CASE WHEN idleave = 5 THEN entitle ELSE '' END ) AS paternity_entitlement,
SUM(CASE WHEN idleave = 5 THEN used ELSE '' END ) AS paternity_used,
SUM(CASE WHEN idleave = 5 THEN balance ELSE '' END ) AS paternity_balance,
SUM(CASE WHEN idleave = 6 THEN entitle ELSE '' END ) AS comp_entitlement,
SUM(CASE WHEN idleave = 6 THEN used ELSE '' END ) AS comp_used,
SUM(CASE WHEN idleave = 6 THEN balance ELSE '' END ) AS comp_balance,
SUM(CASE WHEN idleave = 7 THEN entitle ELSE '' END ) AS spcleave_entitlement,
SUM(CASE WHEN idleave = 7 THEN used ELSE '' END ) AS spcleave_used,
SUM(CASE WHEN idleave = 7 THEN balance ELSE '' END ) AS spcleave_balance,
SUM(CASE WHEN idleave = 8 THEN entitle ELSE '' END ) AS bday_entitlement,
SUM(CASE WHEN idleave = 8 THEN used ELSE '' END ) AS bday_used,
SUM(CASE WHEN idleave = 8 THEN balance ELSE '' END ) AS bday_balance,
SUM(CASE WHEN idleave = 9 THEN entitle ELSE '' END ) AS emer_entitlement,
SUM(CASE WHEN idleave = 9 THEN used ELSE '' END ) AS emer_used,
SUM(CASE WHEN idleave = 9 THEN balance ELSE '' END ) AS emer_balance,
SUM(CASE WHEN idleave = 10 THEN entitle ELSE '' END ) AS magna_entitlement,
SUM(CASE WHEN idleave = 10 THEN used ELSE '' END ) AS magna_used,
SUM(CASE WHEN idleave = 10 THEN balance ELSE '' END ) AS magna_balance,
SUM(CASE WHEN idleave = 11 THEN entitle ELSE '' END ) AS bereav_entitlement,
SUM(CASE WHEN idleave = 11 THEN used ELSE '' END ) AS bereav_used,
SUM(CASE WHEN idleave = 11 THEN balance ELSE '' END ) AS bereav_balance,
SUM(CASE WHEN idleave = 12 THEN entitle ELSE '' END ) AS mater_entitlement,
SUM(CASE WHEN idleave = 12 THEN used ELSE '' END ) AS mater_used,
SUM(CASE WHEN idleave = 12 THEN balance ELSE '' END ) AS mater_balance";
$Qry->fields    = "al.entitle is not null ".$search." GROUP BY de.empid order by de.empname";
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        $data[] = array( 
            "id"        	            => $row['id'],
            "empid"			            => $row['empid'],
            "empname" 		            => $row['empname'],
            "post" 		                => $row['post'],
            "manager" 		            => $row['manager'],

            "sl_entitlement" 	        => $row['sl_entitlement'],
            "sl_used" 		            => $row['sl_used'],
            "sl_balance" 		        => $row['sl_balance'],

            "vl_entitlement" 	        => $row['vl_entitlement'],
            "vl_used" 		            => $row['vl_used'],
            "vl_balance" 		        => $row['vl_balance'],

            "lwop_entitlement" 	        => $row['lwop_entitlement'],
            "lwop_used" 		        => $row['lwop_used'],
            "lwop_balance" 		        => $row['lwop_balance'],

            "solo_entitlement" 	        => $row['solo_entitlement'],
            "solo_used" 		        => $row['solo_used'],
            "solo_balance" 		        => $row['solo_balance'],

            "paternity_entitlement" 	=> $row['paternity_entitlement'],
            "paternity_used" 		    => $row['paternity_used'],
            "paternity_balance" 		=> $row['paternity_balance'],

            "comp_entitlement" 	        => $row['comp_entitlement'],
            "comp_used" 		        => $row['comp_used'],
            "comp_balance" 		        => $row['comp_balance'],

            "spcleave_entitlement" 	    => $row['spcleave_entitlement'],
            "spcleave_used" 		    => $row['spcleave_used'],
            "spcleave_balance" 		    => $row['spcleave_balance'],

            "bday_entitlement" 	        => $row['bday_entitlement'],
            "bday_used" 		        => $row['bday_used'],
            "bday_balance" 		        => $row['bday_balance'],

            "emer_entitlement" 	        => $row['emer_entitlement'],
            "emer_used" 		        => $row['emer_used'],
            "emer_balance" 		        => $row['emer_balance'],

            "magna_entitlement" 	    => $row['magna_entitlement'],
            "magna_used" 		        => $row['magna_used'],
            "magna_balance" 		    => $row['magna_balance'],

            "bereav_entitlement" 	    => $row['bereav_entitlement'],
            "bereav_used" 		        => $row['bereav_used'],
            "bereav_balance" 		    => $row['bereav_balance'],

            "mater_entitlement" 	    => $row['mater_entitlement'],
            "mater_used" 		        => $row['mater_used'],
            "mater_balance" 		    => $row['mater_balance'],



        );
    }

    $return = json_encode($data);

}
else {
    $return = json_encode(array());
}

print $return;
mysqli_close($con);
?>