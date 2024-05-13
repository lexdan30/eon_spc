<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

    $param = json_decode(file_get_contents('php://input'));

    $Qry=new Query();
    $Qry->table="tblcompany LEFT JOIN vw_dataemployees ON tblcompany.id = vw_dataemployees.idcompany";
    $Qry->selected="*, tblcompany.idsss AS comp_idsss, tblcompany.cnumber AS comp_cnumber, tblcompany.name as compname, tblcompany.addr_code AS comp_addr_code, tblcompany.addr_city AS comp_addr_city";
    $Qry->fields="vw_dataemployees.id = '".$param->accountid."' ";
    $rs=$Qry->exe_SELECT($con);
    //echo $Qry->fields;
    if(mysqli_num_rows($rs)>=1){
            $dash='-';
            $postion='2';
            $postion2='10';
        while($row=mysqli_fetch_array($rs)){   
            $data = array(
                'company_name'   =>$row['compname'],
                'address1'       =>$row['addr_bldg'].' '. $row['addr_street'],
                'address2'       =>$row['addr_brgy'].' '.$row['comp_addr_city'],
                'comp_zipcode'   =>$row['comp_addr_code'],
                'comp_cnumber'   =>$row['comp_cnumber'],
                'comp_idsss'     =>substr_replace(substr_replace($row['comp_idsss'],$dash,$postion,0),$dash,$postion2,0) 
            );
        }
        $return = json_encode($data);
    }else{
        $return = json_encode(array('status'=>'empty'));
    }
    
print $return;
mysqli_close($con);
?>