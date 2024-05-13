<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	

$search='';

$where = $search;

//Sort specific column
if( $param['order'][0]['column'] !='' ){//default 
    $arrCols = array("a.isconvertible","a.name","a.idtype");
    $search=$search." ORDER BY ". $arrCols[$param['order'][0]['column']] ." ".$param['order'][0]['dir'];//default
}

if( $param['length'] !='' ){
    //$search=$search." LIMIT 12 ";	
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "tblleaves a LEFT JOIN tblleaves_conversion b ON a.id = b.idleave ";
$Qry->selected  = "a.id, a.idclasstrans,a.name,a.hours,a.idtype,a.isconvertible,a.active, a.imgicon, a.color, a.alias, b.conversion_type,b.conversion_unit,b.transaction_id,b.release_date, b.reset, b.porfeit, b.stat ";
$Qry->fields    = "a.id>0 ".$search;
$rs = $Qry->exe_SELECT($con);
$recFiltered = getTotalRows($con,$where);
if(mysqli_num_rows($rs)>= 1){
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs),
        "recordsFiltered"=> $recFiltered,
        "qry"=>$Qry->fields,
        "data"=>array()
    );
    $count=1;
    while($row=mysqli_fetch_array($rs)){

        $data["data"][] = array(
            'id'            => (int)$row['id'],
            'count'         => $count,
            "name"          => $row['name'],
            "isconvertible" => $row['conversion_type'],
            "idtype"        => $row['idtype'],
            "color"         => $row['color'],
            "units"         => $row['conversion_unit'],
            "transid"       => $row['transaction_id'],
            "date"          => $row['release_date'],
            "reset"         => $row['reset'],
            "porfeit"       => $row['porfeit'],
            "stat"          => $row['stat']
        );
    $count++;
    }
    $return =  json_encode($data);
}else{
    $data = array( 
        "draw"=> $param['draw'],
        "recordsTotal"=> mysqli_num_rows($rs),
        "recordsFiltered"=> mysqli_num_rows($rs),
        "data"=>array()
    );
    $return =  json_encode($data);
}

print $return;
mysqli_close($con);

function getTotalRows($con,$search){
	$Qry = new Query();	
	$Qry->table ="tblleaves a LEFT JOIN tblleaves_conversion b ON a.id = b.idleave ";
	$Qry->selected ="a.id, a.idclasstrans,a.name,a.hours,a.idtype,a.isconvertible,a.active, a.imgicon, a.color, a.alias, b.conversion_type,b.conversion_unit,b.transaction_id,b.release_date, b.reset, b.porfeit,b.stat ";
	$Qry->fields ="a.id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}
?>