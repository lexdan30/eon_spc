<?php
require_once('../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$data  = array();
// $date  = '2020-08-11';
// $date1  = '2020-08-25';

$pay_period = getPayPeriod($con);
// $time  = SysTime();


$dept = getIdUnit($con,$param->accountid);
$ids=0;

//Get Managers Under person
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

/*
$Qry 			= new Query();	
$Qry->table     = "vw_data_timesheet LEFT JOIN vw_dataemployees ON vw_dataemployees.id = vw_data_timesheet.idacct";
$Qry->selected  = "vw_data_timesheet.*,vw_dataemployees.empname";
$Qry->fields    = "(vw_data_timesheet.work_date >= '".$pay_period['pay_start']."' AND vw_data_timesheet.work_date >= '".$pay_period['pay_end']."') AND ( vw_dataemployees.idsuperior='".$param->accountid."' OR vw_dataemployees.idunit IN (".$ids.") ) ";
$rs 			= $Qry->exe_SELECT($con);
*/

$Qry 			= new Query();	
$Qry->table     = "vw_resocenter";
$Qry->selected  = "*";
//$Qry->fields    = "(reso_date >= '".$pay_period['pay_start']."' AND reso_date >= '".$pay_period['pay_end']."') AND ( idsuperior='".$param->accountid."' OR idunit IN (".$ids.") ) ";
$Qry->fields    = "(reso_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND ( idsuperior='".$param->accountid."' OR idunit IN (".$ids.") )  ";
$rs 			= $Qry->exe_SELECT($con);


if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){ 

        

        $no_in='';
        $no_out='';
        $late='';
        
		/*
        if(strtotime($row['in']) == '' || strtotime($row['in']) == null){
            $no_in = 'No Time In';
        }else{
            $no_in='';
        }

        if(strtotime($row['out']) == '' || strtotime($row['out']) == null){
            $no_out = 'No Time OUT';
        }else{
            $no_out='';
        }
        
		if( !empty($no_in) && !empty($no_out) ){
			$no_in = 'Absent';
			$no_out='';
		}
		
        if(strtotime($row['shiftin']) < strtotime($row['in'])){
            $late = 'Late';
            $late = strtotime($row['in']) - strtotime($row['shiftin']);
            $late = ($late/60);
        }else{
            $late = '';
        }
        */

        // "no_in" 		    => $no_in,
        // "late" 		        => $late,
        // "no_out" 		    => $no_out,
		
		
        $data[] = array(             
            "id"			    => $row['id'],
            "name" 		        => $row['empname'],
            "work_date" 		=> $row['reso_date'],
            "reso_txt" 		    => $row['reso_txt'],
            "pic" 		    	=> $row['empid'],
            "pay_start"         => $pay_period['pay_start'],
            "pay_end"           => $pay_period['pay_end']
        );
        $return = json_encode($data);
    }
}else{
    // $return = json_encode(array('status'=>'error'));
    $return = json_encode(array(array("pay_start"         => $pay_period['pay_start'], "pay_end"           => $pay_period['pay_end'])));
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