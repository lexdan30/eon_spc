<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

// $param = $_POST;
$param       = json_decode(file_get_contents('php://input'));
$data  = array();
$date = SysDatePadLeft();
$pay_period = getPayPeriod($con);


$search ='';

if( !empty( $param->search_acct ) ){ $search=$search." AND idacct 	= '".$param->search_acct."' "; }

if( !empty($param->_from) && empty($param->_to)){
    $search=$search." AND work_date BETWEEN DATE('".$param->_from."') AND DATE('".$param->_from."') ";
}

if( !empty($param->_from) && !empty($param->_to) ){
    $search=$search." AND work_date BETWEEN DATE('".$param->_from."') AND DATE('".$param->_to."') ";
    
}




$dept = getIdUnit($con,$param->idsuperior);


//Get Managers Under person
$ids=0;if( !empty( $dept ) ){
    $arr_id = array();
    $arr 	= getHierarchy($con,$dept);
    array_push( $arr_id, $dept );
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

$Qry 			= new Query();	
$Qry->table     = "vw_dataemployees AS de LEFT JOIN vw_data_timesheet AS dt ON de.id = dt.empID";
$Qry->selected  = "de.id, de.empname, dt.work_date , dt.shiftin , dt.in,de.idunit";
$Qry->fields    = "idunit IN (".$ids.") AND (dt.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND (dt.in > shiftin)".$search;
$rs 			= $Qry->exe_SELECT($con);
if(mysqli_num_rows($rs)>= 1){

    $array_id =array();
   
    while($row=mysqli_fetch_array($rs)){
        
        if(!in_array($row['id'],$array_id)){
            
            $data[] = array( 
                "empname" 		=> $row['empname'],
                "getTimeshet"   => getTimeshet($con,$pay_period,$row['id']),
                "getTotalTardy" => getTotalTardy($con,$pay_period,$row['id']),
                // "pay_start" => $pay_period['pay_start'],
                // "pay_end" =>$pay_period['pay_end'],

			
            );
            array_push($array_id, $row['id']);
        }
        

        $return = json_encode($data);
    }
}else{
   $return = json_encode(array());
}


print $return;
mysqli_close($con);

function getTimeshet($con,$pay_period,$idacct){
    $data = array();
    $Qry 			= new Query();	
    $Qry->table     = "vw_dataemployees AS de LEFT JOIN vw_data_timesheet AS dt ON de.id = dt.empID";
    $Qry->selected  = "de.empname, dt.work_date , dt.shiftin , dt.in,de.idunit,dt.late";
    $Qry->fields    = "de.id='".$idacct."' AND (dt.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND (dt.in > shiftin)";
    $rs 			= $Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>= 1){
        while($row=mysqli_fetch_array($rs)){

            // $time1 = strtotime($row['in']);
            // $time2 =  strtotime($row['shiftin']);
            // $hours = ($time1 - $time2)/3600;
            // $real = floor($hours) ;
            // $real1 = ($hours-floor($real)) * 60 ;

                //Count all row 
                $countin = mysqli_num_rows($rs); 

              

                $data[] = array( 
                    "work_date"     => $row['work_date'],
                    "shiftin"       => $row['shiftin'],
                    "in"            => $row['in'],
                    "count_in"      => $countin,
                    "late"          => $row['late'],

                    // "hours"         => $real,
                    // "minutes"       => $real1,
                    // "late_hrs"          => $interval->format('%I hours'),
                
  
                );

        }
    }
    return $data;
}

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


function getTotalTardy($con,$pay_period,$idacct){
    $data= array();
    $Qry=new Query();
    $Qry->table="vw_dataemployees AS de LEFT JOIN vw_data_timesheet AS dt ON de.id = dt.empID";
    $Qry->selected="de.empname, dt.work_date , dt.shiftin , dt.in,de.idunit,SUM(dt.late) as total_tardy";
    $Qry->fields="de.id='".$idacct."' AND (dt.work_date BETWEEN '".$pay_period['pay_start']."' AND '".$pay_period['pay_end']."') AND (dt.in > shiftin)";
    $rs=$Qry->exe_SELECT($con);
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){


            $data[] = array(

                "total_tardy" => $row['total_tardy']

            );
        }
    }
    return $data;
}





?>