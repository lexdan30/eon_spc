<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = $_POST;
$return = null;	

$search='';

if( !empty( $param['description'] ) ){ $search.=" AND (remarks like '%".$param['description']."%' OR alias like '%".$param['description']."%' OR prefname like '%".$param['description']."%' OR value like '%".$param['description']."%')"; }

if( !empty( $param['idmeasure'] ) ){ $search.=" AND idmeasure = '".$param['idmeasure']."'"; }

if($param['flags'] == "" || $param['flags'] == '1'){
    $search.= "AND flags  =  1 order by prefname asc";
}
if($param['flags'] == '2'){
    $search.= "  order by prefname asc";
}
if($param['flags'] == '0'){
    $search.= "AND flags  =  0 order by prefname asc";
}

$where = $search;

if( $param['length'] !='' ){
    $search=$search." LIMIT ".$param['length'];	
}
if( $param['start'] !='' ){
    $search=$search." OFFSET ".$param['start'];
}

$Qry = new Query();	
$Qry->table     = "tblpreference";
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
        switch ($row['idmeasure']) {
            case 1:
              $idmeasure = 'Minutes';
              break;
            case 2:
                $idmeasure = 'Hours';
              break;
            case 3:
                $idmeasure = 'Days';
              break;
            case 4:
                $idmeasure = 'Years';
            break;
            case 5:
                $idmeasure = 'Organization';
                break;
            case 6:
                $idmeasure = 'Department';
              break;
            case 7:
                $idmeasure = 'Section';
              break;
            case 8:
                $idmeasure = 'Sub Section';
            break;
            case 9:
                $idmeasure = 'Position';
            break;
            case 10:
                $idmeasure = 'Job Level';
            break;
            case 11:
                $idmeasure = 'Employee Name';
            break;
            case 12:
                $idmeasure = 'Employment Type';
            break;
            case 13:
                $idmeasure = 'Location';
            break;
            case 14:
                $idmeasure = 'Labor Type';
            break;
            case 15:
                $idmeasure = 'Hire Date';
            break;
            case 16: 
                $idmeasure = 'Regularization Date';
            break;
            case 17:
                $idmeasure = 'Pay Group';
            break;
            case 18:
                $idmeasure = 'Per Payout';
            break;
            default:
                $idmeasure = '';
          }
          if($idmeasure == 'Employee Name'){
            $val = explode(",",$row['value']);
          }else{
            $val = $row['value'];
          }
        $data["data"][] = array(
            'id'            => (int)$row['id'],
            'alias'         => $row['alias'],
            'prefname'      => $row['prefname'],
            'remarks'       => $row['remarks'],
            'idmeasure'     => $idmeasure,
            'value'         => $val,
            "flags"	        => $stats,
            "accounts"      => getAccounts($con,'')
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
	$Qry->table ="tblpreference";
	$Qry->selected ="*";
	$Qry->fields ="id > 0 ".$search;
	$rs = $Qry->exe_SELECT($con);
	return mysqli_num_rows($rs);
}

?>