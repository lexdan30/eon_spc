<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	

$search='';
if( !empty( $param['alias'] ) ){ $search=" AND idclass =  '".$param['alias']."' "; }
if( !empty( $param['name'] ) ){ $search.=" AND (name like '%".$param['name']."%' OR alias like '%".$param['name']."%')"; }

if(($param['flags']) == "" || ($param['flags']) == 1){
    $search.= "AND flags  =  1";
}else if($param['flags'] == 2){
    $search.= " ";
}else{
    $search.= "AND flags  =  0";
    //$search.= "AND flags  =  0 order by name asc";
}

$where = $search;

//Sort specific column
if( $param['order'][0]['column'] !='' ){//default 
    $arrCols = array("id","alias","name","debit","credit","rate","flags");
    $search=$search." ORDER BY ". $arrCols[$param['order'][0]['column']] ." ".$param['order'][0]['dir'];//default
}

if( $param['length'] !='' ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}
// echo $search;
// return;

$Qry = new Query();	
$Qry->table     = "tblclasstrans";
$Qry->selected  = "*";
$Qry->fields    = "id>0 ".$search;
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

        if($row['flags'] == 1){
            $stats = 'ACTIVE';
        }else{
            $stats = 'INACTIVE';
        }
        $data["data"][] = array(
            'id'            => (int)$row['id'],
            "idclass"       => $row['idclass'],
            "alias"         => $row['alias'],
            "name"	        => $row['name'],
            "debit"	        => $row['debit'],
            "credit"	    => $row['credit'],
            "rate"	        => $row['rate'],
            "isdef"	        => $row['isdef'],
            "determine"	    => $row['determine'],
            "ottype"	    => $row['ottype'],
            "flags"	        => $stats
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
	$Qry->table ="tblclasstrans";
	$Qry->selected ="*";
	$Qry->fields ="id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>