<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php');  

$idunit = $_GET["id"];


//print_r($newID);

   // $param = json_decode(file_get_contents('php://input'));

        //print_r("Hi");
		$Qry = new Query();	
        $data = array();
		$Qry->table     = "tblbunits";
		$Qry->selected  = "*";
		$Qry->fields    = "name='". $idunit."'";
		$rs = $Qry->exe_SELECT($con);
		if(mysqli_num_rows($rs)>= 1){
			if($row=mysqli_fetch_array($rs)){
                $data[] = array(
                    'status'    =>'success',
                    'name'        => $row['name'],
                    'children'    => getstaff($con, $row['id'])
               );
			}
            $return = json_encode($data);
        }else{
            $return = json_encode(array('status'=>'empty'));
        }

        print $return;
        mysqli_close($con);


function getstaff($con, $idunit){
    $Qry=new Query();
    $Qry->table="vw_dataemployees";
    $Qry->selected="*";
    $Qry->fields="id>0 AND joblvl <> 'Managers' AND idunit='".$idunit."'";
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
                'status'    =>'success',
                'id'        =>$row['id'],
                'empid'     =>$row['empid'],
                'lname'     =>$row['lname'],
                'suffix'    =>$row['suffix'],
                'fname'     =>$row['fname'],
                'mname'     =>$row['mname'],
                'fullname'  =>trim($row['empname']),
                'post'      =>trim($row['post']),
                'pic'       =>"assets/php/admin/hr/employee/pix/".$row['pic']
            );
        }
        return $data;
        print $return;
    }

    return '';
}

?>