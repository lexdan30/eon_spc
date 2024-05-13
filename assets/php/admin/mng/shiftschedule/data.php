<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php');

    $param = json_decode(file_get_contents('php://input'));

    if($param->under != ''){
        $dept = $param->under;
    }else{
        $dept = getIdUnit($con,$param->accountid);
    }

    //Search Department
    $ids=0;if( !empty( $dept ) ){
        $arr_id = array();
        $arr 	= getHierarchy($con,$dept);
        array_push( $arr_id, 0 );
        if( !empty( $arr["nodechild"] ) ){
            $a = getChildNode($arr_id, $arr["nodechild"]);
            if( !empty($a) ){
                foreach( $a as $v ){
                    array_push( $arr_id, $v );
                }
            }
        }
        if( count($arr_id) == 1 ){
            $ids 			= $arr_id[0];
        }else{
            $ids 			= implode(",",$arr_id);
        }
    }

    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="*";
    $Qry->fields="id>0 AND (idunit IN (".$ids.") OR idsuperior='".$param->accountid."')";
    $rs=$Qry->exe_SELECT($con);
    //echo $Qry->fields;
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            if($row['suffix']!=null){
                $fullname = $row['lname']. ' ' .$row['suffix']. ', ' .$row['fname']. ' ' .substr($row['mname'], 0, 1). '.';
            }else{
                $fullname = $row['lname']. ', ' .$row['fname']. ' ' .substr($row['mname'], 0, 1). '.';
            }
            
            $data[] = array(
                'status'=>'success',
                'id'        =>$row['id'],
                'empid'     =>$row['empid'],
                'lname'     =>$row['lname'],
                'suffix'    =>$row['suffix'],
                'fname'     =>$row['fname'],
                'mname'     =>$row['mname'],
                'fullname'  =>trim($row['empname'])
            );
        }
        $return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'empty'));
    }
    
print $return;
mysqli_close($con);

function getIdUnit($con, $idacct){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="idunit";
    $Qry->fields="id='".$idacct."'";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            return $row['idunit'];
        }
    }
    return null;
}

?>