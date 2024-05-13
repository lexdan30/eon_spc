<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$search='';

if( !empty( $param->filter->docnum ) ){ $search=" AND docnum like   '%". $param->filter->docnum ."%' "; }
if( !empty($param->filter->paydate  ) ){ $search.=" AND paydate = '". $param->filter->paydate ."' "; }
if( ($param->filter->type == '0' || $param->filter->type == '1') ){ $search.=" AND `type` = '". $param->filter->type ."' ";}
if( !empty($param->filter->transaction  ) ){ $search.=" AND transactionid = '". $param->filter->transaction ."' "; }

$where = $search;



$Qry = new Query();	
$Qry->table     = "tblbatchentries";
$Qry->selected  = "*";
$Qry->fields = "id>0" . $search . " LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize;

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id" 	            => $row['id'],
            "docnum" 	        => $row['docnum'],
            "transactionid"     => $row['transactionid'],
            "total"             => !empty($row['total']) ? number_format($row['total'], 2) : 0,
            "count"             => $row['count'],
            "priority"          => $row['priority'],
            "paydate"           => $row['paydate'],
            "remarks"           => $row['remarks'],
            "type"              => $row['type']
        );
    }


    $myData = array('status' => 'success', 'result' => $data, 'totalItems' => getTotal($con , $where));
	$return = json_encode($myData);
}else{
	$return = json_encode(array());
	
}
print $return;
mysqli_close($con);


function getTotal($con,$search){
    $Qry = new Query();	
    $Qry->table     = "tblbatchentries";
    $Qry->selected  = "count(*) as total";
    $Qry->fields = "id>0".$search;
    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['total'];
			}
		}
		return 0;
}
?>