<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	

$search='';


$where = $search;

if( $param['length'] !='' ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}


$Qry = new Query();	
$Qry->table     = "tblnightpremium";
$Qry->selected  = "*";
$Qry->fields    = "id>0".$search;
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
        if($row['checkstime'] == 'Y'){
            $nextin = '<input type="checkbox" name="nextin'.$row['id'].'[]" checked>';
        }else{
            $nextin = '<input type="checkbox" name="nextin'.$row['id'].'[]">';
        }

        if($row['checkftime'] == 'Y'){
            $nightout = '<input type="checkbox" name="nightout'.$row['id'].'[]" checked>';
       }else{
            $nightout = '<input type="checkbox" name="nightout'.$row['id'].'[]">';
       }

       if($row['auto'] == 'Y'){
            $auto = '<input type="checkbox" name="auto'.$row['id'].'[]" checked>';
        }else{
            $auto = '<input type="checkbox" name="auto'.$row['id'].'[]">';
        }

    
        $data["data"][] = array(
            'id'        => $row['id'],
            'nightin'   => ( is_null($row['stime']) ? '' : date("h:i:s A",strtotime($row['stime'])) ),
            'nextin'   	=> $nextin,
            'nextout'   => ( is_null($row['ftime']) ? '' : date("h:i:s A",strtotime($row['ftime'])) ),
            'nightout'  => $nightout,
            'auto'      => $auto,
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
	$Qry->table ="tblnightpremium";
	$Qry->selected ="*";
	$Qry->fields ="id>0".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>