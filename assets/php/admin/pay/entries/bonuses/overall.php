<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$search = '';
$currdate =  SysDate();
$curmonthyear = date("F Y",strtotime($currdate)); 

if( !empty( $param->month ) ){ 
    if($curmonthyear !=  $param->month){
        $time = strtotime($param->month);
        $newformat = date('Y-m-d',$time);
        $search=" AND YEAR(releasedate) = YEAR('" . $newformat . "') AND MONTH(releasedate) = MONTH('" . $newformat . "') "; 
    }
}

$Qry = new Query();	
$Qry->table     = "tblbonuses";
$Qry->selected  = "*";
// $Qry->fields = "type = 'B'";
$Qry->fields = "id > 0". $search;

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id" 	         => $row['id'],
            "docnum" 	     => $row['docnum'],
            "amount" 	     => $row['unitamount'],
            "description" 	 => $row['description'],
            "period" 	     => $row['period'],
            "start" 	     => $row['start'],
            "end" 	         => $row['end'],
            "releasedate" 	 => $row['releasedate'],
            "mode" 	         => $row['mode']
        );
    }
	$return = json_encode($data);
}else{
	$return = json_encode(array("err"=>mysqli_error($con)));
	
}
print $return;
mysqli_close($con);
?>