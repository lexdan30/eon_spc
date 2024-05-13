<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));

$search='';

if( !empty( $param->filter->description ) ){ $search=" AND description like   '%". $param->filter->description ."%' "; }

$where = $search;

$Qry = new Query();	
$Qry->table     = "tblcont_bir";
$Qry->selected  = "*";
$Qry->fields = "id>0" . $search . " LIMIT " .$param->pagination->pageSize. " OFFSET " . ($param->pagination->currentPage - 1) * $param->pagination->pageSize;
$rs = $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){
        
        $data[] = array(
            "id" 	        => $row['id'],
            "description" 	=> $row['description'],
            "mini" 	        => $row['mini'],
            "fix_amt" 	    => $row['fix_amt'],
            "multi" 	    => $row['multi']
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
    $Qry->table     = "tblcont_bir";
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