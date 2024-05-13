<?php 
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php');

$param = json_decode(file_get_contents('php://input'));
if(!empty($param->accountid)){
$search='';
$counter = 0;
$data =array();
if(!empty( $param->month) ){
    $time = strtotime($param->month);
    $param->month = date('Y-m-d',$time);
}else{
    $param->month = SysDate();//'2022-01-31';//SysDate();
}



// if( !empty( $param->filter->alias ) ){ $search=" AND idclass =   '". $param->filter->alias ."' "; }
$search=" YEAR(period) = YEAR('" . $param->month . "') AND MONTH(period) = MONTH('" . $param->month . "') ";

$where = $search;

$Qry = new Query();	
$Qry->table     = "vw_fte_records";
$Qry->selected  = "*";
$Qry->fields    = $search." ORDER BY classification LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    if($param->pagination->currentPage>1){
        $counter = ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
    }
    while($row=mysqli_fetch_array($rs)){
        $counter++;
        
        $data[] = array(
            'counter'       => $counter,
            'period'        => $row['period'],
            "costcenter"    => $row['costcenter'],
            "classification" => $row['classification'],
            "unit_type"	     => $row['unit_type'],
            "no_staffs"	     => $row['no_staffs'],
            "absences"	    => $row['absences'],
            "vl"	        => $row['vl'],
            "sl"	        => $row['sl'],
            "othrs_lv"	    => $row['othrs_lv'],
            "equiv_hrs"	    => $row['equiv_hrs'],
            "total"	    => $row['total'],
            "extra_off"	    => $row['extra_off'],
            "fte_factor"	    => $row['fte_factor'],
            "fte_factor_diff"	    => $row['fte_factor_diff'],
            "net_fte"	    => $row['net_fte'],
            "ot"	    => $row['ot'],
            "backpay"	    => $row['backpay']
        );
        
    }
    // echo( $data);
    $myData = array('status' => 'success', 'result' => $data , 'totalItems' => getTotal($con , $where));
	$return = json_encode($myData);
}else{
	$return = json_encode(array('result' => $data));
    
	
}

print $return;
 mysqli_close($con);
}else{
	mysqli_close($con);
  // unAuth back to login page
}
function getTotal($con,$search){
	$Qry = new Query();	
	$Qry->table ="vw_fte_records";
	$Qry->selected ="*";
	$Qry->fields = $search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}
?>