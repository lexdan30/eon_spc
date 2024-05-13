<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$search='';

if( !empty( $param->filter->docnum ) ){ $search=" AND docnum like   '%". $param->filter->docnum ."%' "; }
if( ($param->filter->type == '0' || $param->filter->type == '1') ){ $search.=" AND `transactiontype` = '". $param->filter->type ."' ";}
if( !empty($param->filter->transaction  ) ){ $search.=" AND payitemid = '". $param->filter->transaction ."' "; }

$where = $search;

$Qry = new Query();	
$Qry->table    = "tblrecurring as tr LEFT JOIN vw_classtranstype as vtr ON vtr.id = tr.payitemid";
$Qry->selected = "tr.*,vtr.transactiontype";
$Qry->fields = "tr.id>0" . $search . " LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize;

$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        $data[] = array( 
            "id" 	            => $row['id'],
            "docnum" 	        => $row['docnum'],
            "payitemid"     => $row['payitemid'],
            "total"          => !empty($row['total']) ? number_format($row['total'], 2) : 0, // $row['total'],
            "priority"             => $row['priority'],
            "sdate"            => $row['sdate'],
            "edate"           => $row['edate'],
            "rule"              => $row['rule'],
            "remarks"              => $row['remarks'],
             "type"              => $row['transactiontype'],
             "fh"              => $row['fh'],
             "sh"              => $row['sh'],
             "fp"              => $row['fp']
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
    $Qry->table    = "tblrecurring as tr LEFT JOIN vw_classtranstype as vtr ON vtr.id = tr.payitemid";
    $Qry->selected  = "count(*) as total";
    $Qry->fields = "tr.id>0".$search;
    $rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
				return $row['total'];
			}
		}
		return 0;
}
?>