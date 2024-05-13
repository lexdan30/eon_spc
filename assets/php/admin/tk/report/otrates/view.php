<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();
$date  = SysDateDan();
$time  = SysTime();
$year = $param->d_year;
$month = 1;
$months = array(
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July ',
    'August',
    'September',
    'October',
    'November',
    'December',
);

$Qry2 = new Query();	
$Qry2->table     = "vw_databusinessunits";
$Qry2->selected  = "vw_databusinessunits.name,id";
$Qry2->fields    = "stype = 'department'";

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
         $jann = getTimesheet($con, $ids, $year."-".str_pad(1,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(2,2,"0",STR_PAD_LEFT)."-01",$param->typeemp,$param->typeot);
         $febb = getTimesheet($con, $ids, $year."-".str_pad(2,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(3,2,"0",STR_PAD_LEFT)."-01",$param->typeemp,$param->typeot);
         $marr = getTimesheet($con, $ids, $year."-".str_pad(3,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(4,2,"0",STR_PAD_LEFT)."-01",$param->typeemp,$param->typeot);
         $aprr = getTimesheet($con, $ids, $year."-".str_pad(4,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(5,2,"0",STR_PAD_LEFT)."-01",$param->typeemp,$param->typeot);
         $mayy = getTimesheet($con, $ids, $year."-".str_pad(5,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(6,2,"0",STR_PAD_LEFT)."-01",$param->typeemp,$param->typeot);
         $junn = getTimesheet($con, $ids, $year."-".str_pad(6,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(7,2,"0",STR_PAD_LEFT)."-01",$param->typeemp,$param->typeot);
         $jull = getTimesheet($con, $ids, $year."-".str_pad(7,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(8,2,"0",STR_PAD_LEFT)."-01",$param->typeemp,$param->typeot);
         $augg = getTimesheet($con, $ids, $year."-".str_pad(8,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(9,2,"0",STR_PAD_LEFT)."-01",$param->typeemp,$param->typeot);
         $sepp = getTimesheet($con, $ids, $year."-".str_pad(9,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(10,2,"0",STR_PAD_LEFT)."-01",$param->typeemp,$param->typeot);
         $octt = getTimesheet($con, $ids, $year."-".str_pad(10,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(11,2,"0",STR_PAD_LEFT)."-01",$param->typeemp,$param->typeot);
         $novv = getTimesheet($con, $ids, $year."-".str_pad(11,2,"0",STR_PAD_LEFT)."-01",$year."-".str_pad(12,2,"0",STR_PAD_LEFT)."-01",$param->typeemp,$param->typeot);
         $decc = getTimesheet($con, $ids, $year."-12-01",((int)$year+1)."-01-01",$param->typeemp,$param->typeot);
         $total_ott = $jann + $febb + $marr + $aprr + $mayy + $junn + $jull + $augg + $sepp + $octt + $novv + $decc;


         $data[] = array( 
             "name"     => $row2['name'],
            "dept"      => $dept,
            "ids"       => $ids,
            "jan"       => $jann,
            "feb"       => $febb,
            "mar"       => $marr,
            "apr"       => $aprr,
            "may"       => $mayy,
            "jun"       => $junn,
            "jul"       => $jull,
            "aug"       => $augg,
            "sep"       => $sepp,
            "oct"       => $octt,
            "nov"       => $novv,
            "dec"       => $decc,
            "total_ot" =>$total_ott,
            "date"      => $date,
            "time"      => date ("H:i:s A",strtotime($time)),
            
        );


    } $return = json_encode($data);
}


print $return;
mysqli_close($con);

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
        $Qry->selected  = "IFNULL(SUM(de.ot_reg_amount),0)  + IFNULL(SUM(de.adj_ot_amount),0)  as sumot";
    }else if($typeot == 'Rest Day OT'){
        $Qry->selected  = "IFNULL(SUM(de.ot_rd_amount),0)  + IFNULL(SUM(de.adj_ot_rd_amount),0)  as sumot";
    }else if($typeot == 'Regular Work Day OT'){
        $Qry->selected  = "IFNULL(SUM(de.ot_sh_amount),0)  + IFNULL(SUM(de.adj_ot_sh_amount),0)  as sumot";
    }else if($typeot == 'Special Holiday Rest Day OT'){
        $Qry->selected  = "IFNULL(SUM(de.ot_shrd_amount),0)  + IFNULL(SUM(de.adj_ot_shrd_amount),0)  as sumot";
    }else if($typeot == 'Legal Holiday Work Day OT'){
        $Qry->selected  = "IFNULL(SUM(de.ot_lh_amount),0)  + IFNULL(SUM(de.adj_ot_lh_amount),0)  as sumot";
    }else if($typeot == 'Legal Holiday Rest Day OT'){
        $Qry->selected  = "IFNULL(SUM(de.ot_lhrd_amount),0)  + IFNULL(SUM(de.adj_ot_lhrd_amount),0)  as sumot";
    }else if($typeot == 'Legal Special Holiday OT'){
        $Qry->selected  = "IFNULL(SUM(de.ot_lsh_amount),0)  + IFNULL(SUM(de.adj_ot_lsh_amount),0)  as sumot";
    }else if($typeot == 'Legal Special Holiday Rest Day OT'){
        $Qry->selected  = "IFNULL(SUM(de.ot_lshrd_amount),0)  + IFNULL(SUM(de.adj_ot_lshrd_amount),0)  as sumot";
    }else if($typeot == 'Legal Special Holiday Rest Day Night Premium OT'){
        $Qry->selected  = "IFNULL(SUM(de.npot_lshrd_amount),0)  + IFNULL(SUM(de.adj_npot_lshrd_amount),0)  as sumot";
    }else if($typeot == 'Regular OT Night Premium'){
        $Qry->selected  = "IFNULL(SUM(de.npot_npot_amount),0)  + IFNULL(SUM(de.adj_npot_amount),0)  as sumot";
    }else if($typeot == 'Rest Day Night Premium OT'){
        $Qry->selected  = "IFNULL(SUM(de.npot_rd_amount),0)  + IFNULL(SUM(de.adj_npot_rd_amount),0)  as sumot";
    }else if($typeot == 'Special Holiday Night Premium OT'){
        $Qry->selected  = "IFNULL(SUM(de.npot_sh_amount),0)  + IFNULL(SUM(de.adj_npot_sh_amount),0)  as sumot";
    }else if($typeot == 'Special Holiday Rest Day Night Premium OT'){
        $Qry->selected  = "IFNULL(SUM(de.npot_shrd_amount),0)  + IFNULL(SUM(de.adj_npot_shrd_amount),0)  as sumot";
    }else if($typeot == 'Legal Holiday Night Premium OT'){
        $Qry->selected  = "IFNULL(SUM(de.npot_lh_amount),0)  + IFNULL(SUM(de.adj_npot_lh_amount),0)  as sumot";
    }else if($typeot == 'Legal Holiday Rest Day Night Premium OT'){
        $Qry->selected  = "IFNULL(SUM(de.npot_lhrd_amount),0)  + IFNULL(SUM(de.adj_npot_lhrd_amount),0)  as sumot";
    }else if($typeot == 'Legal Special Holiday Night Premium OT'){
        $Qry->selected  = "IFNULL(SUM(de.npot_lsh_amount),0)  + IFNULL(SUM(de.adj_npot_lsh_amount),0)  as sumot";
    }else{
        $Qry->selected  = "IFNULL(SUM(de.ot_reg_amount),0)  + IFNULL(SUM(de.adj_ot_amount),0) + IFNULL(SUM(de.ot_rd_amount),0)  + IFNULL(SUM(de.adj_ot_rd_amount),0) + IFNULL(SUM(de.ot_sh_amount),0)  + IFNULL(SUM(de.adj_ot_sh_amount),0) + IFNULL(SUM(de.ot_shrd_amount),0)  + IFNULL(SUM(de.adj_ot_shrd_amount),0) + IFNULL(SUM(de.ot_lh_amount),0)  + IFNULL(SUM(de.adj_ot_lh_amount),0)+IFNULL(SUM(de.ot_lhrd_amount),0)  + IFNULL(SUM(de.adj_ot_lhrd_amount),0)+ IFNULL(SUM(de.ot_lsh_amount),0)  + IFNULL(SUM(de.adj_ot_lsh_amount),0)+IFNULL(SUM(de.ot_lshrd_amount),0)  + IFNULL(SUM(de.adj_ot_lshrd_amount),0)+IFNULL(SUM(de.npot_lshrd_amount),0)  + IFNULL(SUM(de.adj_npot_lshrd_amount),0)+IFNULL(SUM(de.npot_npot_amount),0)  + IFNULL(SUM(de.adj_npot_amount),0) +IFNULL(SUM(de.npot_rd_amount),0)  + IFNULL(SUM(de.adj_npot_rd_amount),0)+IFNULL(SUM(de.npot_sh_amount),0)  + IFNULL(SUM(de.adj_npot_sh_amount),0)+ IFNULL(SUM(de.npot_shrd_amount),0)  + IFNULL(SUM(de.adj_npot_shrd_amount),0) +IFNULL(SUM(de.npot_lh_amount),0)  + IFNULL(SUM(de.adj_npot_lh_amount),0) +IFNULL(SUM(de.npot_lhrd_amount),0)  + IFNULL(SUM(de.adj_npot_lhrd_amount),0) + IFNULL(SUM(de.npot_lsh_amount),0)  + IFNULL(SUM(de.adj_npot_lsh_amount),0)  as sumot";
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