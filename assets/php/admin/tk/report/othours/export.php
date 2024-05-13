<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_GET;
$data  = array();
$date  = SysDateDan();
$time  = SysTime();
$year = $param['d_year'];
$search = '';
if( !empty( $param['department'] ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$param['department']);
    array_push( $arr_id, $param['department'] );
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
    $search.=" AND id in (".$ids.") "; 
}
$Qry2 = new Query();	
$Qry2->table     = "vw_databusinessunits";
$Qry2->selected  = "vw_databusinessunits.name,id";
$Qry2->fields    = "stype = 'department'".$search;

$rs2 = $Qry2->exe_SELECT($con);
if(mysqli_num_rows($rs2)>= 1){
    while($row2=mysqli_fetch_array($rs2)){

        $dept = $row2['id'];
        
        //Get 
        $ids=0;if( !empty( $dept ) ){
            $arr_id = array();
            $arr 	= getHierarchy($con,$dept);
            array_push( $arr_id, $dept );
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
        }

         //OT
         $jann = getTimesheet($con, $ids, $year."-".str_pad(1,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(2,2,"0",STR_PAD_LEFT)."-01",$param['typeemp'],$param['typeot']);
         $febb = getTimesheet($con, $ids, $year."-".str_pad(2,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(3,2,"0",STR_PAD_LEFT)."-01",$param['typeemp'],$param['typeot']);
         $marr = getTimesheet($con, $ids, $year."-".str_pad(3,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(4,2,"0",STR_PAD_LEFT)."-01",$param['typeemp'],$param['typeot']);
         $aprr = getTimesheet($con, $ids, $year."-".str_pad(4,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(5,2,"0",STR_PAD_LEFT)."-01",$param['typeemp'],$param['typeot']);
         $mayy = getTimesheet($con, $ids, $year."-".str_pad(5,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(6,2,"0",STR_PAD_LEFT)."-01",$param['typeemp'],$param['typeot']);
         $junn = getTimesheet($con, $ids, $year."-".str_pad(6,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(7,2,"0",STR_PAD_LEFT)."-01",$param['typeemp'],$param['typeot']);
         $jull = getTimesheet($con, $ids, $year."-".str_pad(7,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(8,2,"0",STR_PAD_LEFT)."-01",$param['typeemp'],$param['typeot']);
         $augg = getTimesheet($con, $ids, $year."-".str_pad(8,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(9,2,"0",STR_PAD_LEFT)."-01",$param['typeemp'],$param['typeot']);
         $sepp = getTimesheet($con, $ids, $year."-".str_pad(9,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(10,2,"0",STR_PAD_LEFT)."-01",$param['typeemp'],$param['typeot']);
         $octt = getTimesheet($con, $ids, $year."-".str_pad(10,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(11,2,"0",STR_PAD_LEFT)."-01",$param['typeemp'],$param['typeot']);
         $novv = getTimesheet($con, $ids, $year."-".str_pad(11,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(12,2,"0",STR_PAD_LEFT)."-01",$param['typeemp'],$param['typeot']);
         $decc = getTimesheet($con, $ids, $year."-12-01",((int)$year+1)."-01-01",$param['typeemp'],$param['typeot']);
         $total_ott = $jann + $febb + $marr + $aprr + $mayy + $junn + $jull + $augg + $sepp + $octt + $novv + $decc;


         $name23[] = array( 
            $row2['name'],
          // $dept,
          //  $ids,
            $jann,
           $febb,
           $marr,
            $aprr,
             $mayy,
           $junn,
            $jull,
            $augg,
         $sepp,
             $octt,
            $novv,
          $decc,
           $total_ott,
           
            
        );


    } $return = json_encode($data);
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Overtimehours_'.$date.'.csv');
$output = fopen('php://output', 'w');
//fputcsv($output, array($param['company']));
fputcsv($output, array("New World Makati Hotel"));
fputcsv($output, array("Overtime Hours Report"));
fputcsv($output, array("Export Generated on " . SysDatePadLeft() .' '.SysTime() ));
fputcsv($output, array('Department',
						'January',
						'February',
						'March',
                        'April',
                        'May',
                        'June',
                        'July',
                        'August',
                        'September',
                        'October',
                        'November',
                        'December',
                        'Grand Total')); 
 
if (count($name23) > 0) {
	foreach ($name23 as $row23) {
		fputcsv($output, $row23);
	}
}



function getTimesheet($con, $ids, $dfrom, $dto,$typeemp,$typeot){
  
    $Qry=new Query();
    
    if($typeemp == 'Local Employee'){
        $Qry->table     = "tblpayreg AS de LEFT JOIN tblpayperiod AS dt ON de.idpayperiod=dt.period";
    }else if($typeemp == 'Japanese'){
        $Qry->table     = "tblpayreg AS de LEFT JOIN tblpayperiod_japanese AS dt ON de.idpayperiod=dt.period";
    }else if($typeemp == 'Helper'){
        $Qry->table     = "tblpayreg AS de LEFT JOIN tblpayperiod_helper AS dt ON de.idpayperiod=dt.period";
    }else{
        $Qry->table     = "tblpayreg AS de LEFT JOIN tblpayperiod AS dt ON de.idpayperiod=dt.period";
    }




    if($typeot == 'Regular Work Day OT'){
        $Qry->selected  = "IFNULL(SUM(de.ot_reg),0)  + IFNULL(SUM(de.adj_ot),0)  as sumot";
    }else if($typeot == 'Rest Day OT'){
        $Qry->selected  = "IFNULL(SUM(de.ot_rd),0)  + IFNULL(SUM(de.adj_ot_rd),0)  as sumot";
    }else if($typeot == 'Regular Work Day OT'){
        $Qry->selected  = "IFNULL(SUM(de.ot_sh),0)  + IFNULL(SUM(de.adj_ot_sh),0)  as sumot";
    }else if($typeot == 'Special Holiday Rest Day OT'){
        $Qry->selected  = "IFNULL(SUM(de.ot_shrd),0)  + IFNULL(SUM(de.adj_ot_shrd),0)  as sumot";
    }else if($typeot == 'Legal Holiday Work Day OT'){
        $Qry->selected  = "IFNULL(SUM(de.ot_lh),0)  + IFNULL(SUM(de.adj_ot_lh),0)  as sumot";
    }else if($typeot == 'Legal Holiday Rest Day OT'){
        $Qry->selected  = "IFNULL(SUM(de.ot_lhrd),0)  + IFNULL(SUM(de.adj_ot_lhrd),0)  as sumot";
    }else if($typeot == 'Legal Special Holiday OT'){
        $Qry->selected  = "IFNULL(SUM(de.ot_lsh),0)  + IFNULL(SUM(de.adj_ot_lsh),0)  as sumot";
    }else if($typeot == 'Legal Special Holiday Rest Day OT'){
        $Qry->selected  = "IFNULL(SUM(de.ot_lshrd),0)  + IFNULL(SUM(de.adj_ot_lshrd),0)  as sumot";
    }else if($typeot == 'Legal Special Holiday Rest Day Night Premium OT'){
        $Qry->selected  = "IFNULL(SUM(de.npot_lshrd),0)  + IFNULL(SUM(de.adj_npot_lshrd),0)  as sumot";
    }else if($typeot == 'Regular OT Night Premium'){
        $Qry->selected  = "IFNULL(SUM(de.npot_npot),0)  + IFNULL(SUM(de.adj_npot),0)  as sumot";
    }else if($typeot == 'Rest Day Night Premium OT'){
        $Qry->selected  = "IFNULL(SUM(de.npot_rd),0)  + IFNULL(SUM(de.adj_npot_rd),0)  as sumot";
    }else if($typeot == 'Special Holiday Night Premium OT'){
        $Qry->selected  = "IFNULL(SUM(de.npot_sh),0)  + IFNULL(SUM(de.adj_npot_sh),0)  as sumot";
    }else if($typeot == 'Special Holiday Rest Day Night Premium OT'){
        $Qry->selected  = "IFNULL(SUM(de.npot_shrd),0)  + IFNULL(SUM(de.adj_npot_shrd),0)  as sumot";
    }else if($typeot == 'Legal Holiday Night Premium OT'){
        $Qry->selected  = "IFNULL(SUM(de.npot_lh),0)  + IFNULL(SUM(de.adj_npot_lh),0)  as sumot";
    }else if($typeot == 'Legal Holiday Rest Day Night Premium OT'){
        $Qry->selected  = "IFNULL(SUM(de.npot_lhrd),0)  + IFNULL(SUM(de.adj_npot_lhrd),0)  as sumot";
    }else if($typeot == 'Legal Special Holiday Night Premium OT'){
        $Qry->selected  = "IFNULL(SUM(de.npot_lsh),0)  + IFNULL(SUM(de.adj_npot_lsh),0)  as sumot";
    }else{
        $Qry->selected  = "IFNULL(SUM(de.ot_reg),0)  + IFNULL(SUM(de.adj_ot),0) + IFNULL(SUM(de.ot_rd),0)  + IFNULL(SUM(de.adj_ot_rd),0) + IFNULL(SUM(de.ot_sh),0)  + IFNULL(SUM(de.adj_ot_sh),0) + IFNULL(SUM(de.ot_shrd),0)  + IFNULL(SUM(de.adj_ot_shrd),0) + IFNULL(SUM(de.ot_lh),0)  + IFNULL(SUM(de.adj_ot_lh),0)+IFNULL(SUM(de.ot_lhrd),0)  + IFNULL(SUM(de.adj_ot_lhrd),0)+ IFNULL(SUM(de.ot_lsh),0)  + IFNULL(SUM(de.adj_ot_lsh),0)+IFNULL(SUM(de.ot_lshrd),0)  + IFNULL(SUM(de.adj_ot_lshrd),0)+IFNULL(SUM(de.npot_lshrd),0)  + IFNULL(SUM(de.adj_npot_lshrd),0)+IFNULL(SUM(de.npot_npot),0)  + IFNULL(SUM(de.adj_npot),0) +IFNULL(SUM(de.npot_rd),0)  + IFNULL(SUM(de.adj_npot_rd),0)+IFNULL(SUM(de.npot_sh),0)  + IFNULL(SUM(de.adj_npot_sh),0)+ IFNULL(SUM(de.npot_shrd),0)  + IFNULL(SUM(de.adj_npot_shrd),0) +IFNULL(SUM(de.npot_lh),0)  + IFNULL(SUM(de.adj_npot_lh),0) +IFNULL(SUM(de.npot_lhrd),0)  + IFNULL(SUM(de.adj_npot_lhrd),0) + IFNULL(SUM(de.npot_lsh),0)  + IFNULL(SUM(de.adj_npot_lsh),0)  as sumot";
    }



    if(empty($typeemp) ){
        $Qry->fields    = "de.idbunit =".$ids." AND ( dt.pay_date >= '".$dfrom."' AND dt.pay_date < '".$dto."' )";
    }else{
        $Qry->fields    = "de.idbunit =".$ids." AND type = '".$typeemp."' AND ( dt.pay_date >= '".$dfrom."' AND dt.pay_date < '".$dto."' )";
    }





   


  
    $rs=$Qry->exe_SELECT($con);
    if( mysqli_num_rows($rs) > 0 ){
		if($row=mysqli_fetch_array($rs)){
			return $row['sumot'];
		}
	}
    return  0;
}



?>