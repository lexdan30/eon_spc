<?php
require_once('../../../activation.php');
$param = $_POST;
$conn = new connector();	

if( (int)$param['conn'] == 1 ){	
	$con = $conn->connect();
}else{
	$varcon = "connect".(int)$param['conn'];
	$con = $conn->$varcon();
}


require_once('../../../classPhp.php');  


$return = null;	

$search='';
if( !empty( $param['alias'] ) ){ $search=" AND a.alias like   '%".$param['alias']."%' "; }
if( !empty( $param['name'] ) ){ $search.=" AND a.name like   '%".$param['name']."%' "; }
if( strlen($param['stat']) > 0  ){ $search.=" AND a.isactive  =  '".$param['stat']."' ";    }


$where = $search;

/* //SORTING
if( $param['order'][0]['column'] !=''  ){		
    $cols = array('id','fname','lname','email','type','stat');
    $search=$search." ORDER BY ".$cols[ $param['order'][0]['column'] ]." ".$param['order'][0]['dir'];		
}*/
if( $param['length'] !='' ){
    $search=$search." ORDER BY a.name ASC LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}


$Qry = new Query();	
$Qry->table     = "tblposition AS a LEFT JOIN tblbunits AS b ON a.idunit = b.id";
$Qry->selected  = "a.*";
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
    while($row=mysqli_fetch_array($rs)){
        $stat = "Inactive";
        if( (int)$row['isactive'] == 1 ){
            $stat = "Active";
        }
        $data["data"][] = array(
            'id'        => (int)$row['id'],
            'name'      => $row['name'],
            'alias'     => $row['alias'],
			'idunit'	=> $row['idunit'],
            'isactive'  => $row['isactive'],
            "idunit"	=> explode(",",$row['idunit']),
            'stat'      => $stat,
            "departments"	=> getAllDepartment($con)
        );
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
	$Qry->table ="tblposition AS a LEFT JOIN tblbunits AS b ON a.idunit = b.id";
	$Qry->selected ="a.*";
	$Qry->fields ="a.id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>