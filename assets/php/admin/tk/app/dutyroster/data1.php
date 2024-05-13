<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$date = getPayPeriod($con);

$idpayperiod = $date['id'];

$start = $date['pay_start'];
$end = $date['pay_end'];

$nend = new DateTime($date['pay_end']);
$nend->modify('+1 day');
$nend = $nend->format('Y-m-d');


$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod(new DateTime($start), $interval,  new DateTime($nend));

foreach ($period as $dt) {
    $workdate[] = $dt->format("Y-m-d");
}

$Qry=new Query();
$Qry->table="vw_databusinessunits";
$Qry->selected="*";
$Qry->fields="isactive = 1 AND unittype = 3";
$rs=$Qry->exe_SELECT($con);
//echo $Qry->fields;
if(mysqli_num_rows($rs)>=1){
    while($row=mysqli_fetch_array($rs)){
        $arr_id = array();
        $dept = $row['id'];
        if( !empty( $dept ) ){
            $arr 	= getHierarchy($con,$dept);
            array_push( $arr_id, $dept );
            if( !empty( $arr["nodechild"] ) ){
                $a = getChildNode($arr_id, $arr["nodechild"]);
                if( !empty($a) ){
                    foreach( $a AS $v ){
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

        $emps = [];
        $sched =array();
        $Qry2=new Query();
        $Qry2->table="vw_dataemployees";
        $Qry2->selected="*";
        $Qry2->fields="(idunit IN (".$ids.")) AND (empstat NOT IN (6,7) )";
        $rs2=$Qry2->exe_SELECT($con);
        //echo $Qry->fields;
        if(mysqli_num_rows($rs2)>=1){
            while($row2=mysqli_fetch_array($rs2)){
                if($row2['suffix']!=null){
                    $fullname = $row2['lname']. ' ' .$row2['suffix']. ', ' .$row2['fname']. ' ' .substr($row2['mname'], 0, 1). '.';
                }else{
                    $fullname = $row2['lname']. ', ' .$row2['fname']. ' ' .substr($row2['mname'], 0, 1). '.';
                }

                array_push($sched,getSched( $con, $row2['id'], $start, $end ));

                $emps[] = array(
                    'status'=>'success',
                    'id'        =>$row2['id'],
                    'empid'     =>$row2['empid'],
                    'fullname'  =>trim($row2['empname']),
                );
            }
        }


        $Qry3=new Query();
        $Qry3->table="tbldutyrosterstat";
        $Qry3->selected="*";
        $Qry3->fields="id_payperiod = '". $idpayperiod ."' AND id_department = '". 	$row['id'] ."'";
        $rs3=$Qry3->exe_SELECT($con);
      
        if(mysqli_num_rows($rs3)>=1){
            while($row3=mysqli_fetch_array($rs3)){
                $drlock = $row3['status'];
            }
        }else{
              $drlock = 0;
        }

        


        $data[] = array(
            'status'		=>	'success',
            'id'			=>	$row['id'],
            'idpayperiod'	=>	$idpayperiod,
            'departmentname'=>	$row['name'],
            'employees'     =>	$emps,
            'workdate'      =>	$workdate,
            'sched'         =>	$sched,
            'drlock'        =>	$drlock,
        );
    }
    $return = json_encode($data);
}else{
    $return = json_encode(array('status'=>'empty'));
}



$return =  json_encode($data);
print $return;
mysqli_close($con);



function getSched($con, $idacct, $start, $end){
    $shift_cols = array("monday"	=>"mon", 
    "tuesday"	=>"tue",
    "wednesday"	=>"wed",
    "thursday"	=>"thu",
    "friday"	=>"fri",
    "saturday"	=>"sat", 
    "sunday"	=>"sun");


    $Qry=new Query();
    $Qry->table="vw_data_timesheet AS a LEFT JOIN vw_dataemployees AS b ON a.empID = b.id";
    $Qry->selected="a.idacct,a.empID,b.lname,b.fname,b.mname,b.idunit,b.business_unit,a.idshift,a.shift_status,a.work_date,a.holiday_id,a.holiday_type";
    $Qry->fields="a.empID = '".$idacct."' AND (a.work_date BETWEEN '".$start."' AND '".$end."') ORDER BY CONCAT(a.work_date,b.lname) ASC";
    $rs=$Qry->exe_SELECT($con);
    //echo $Qry->fields;
    if(mysqli_num_rows($rs)>=1){
        while($row=mysqli_fetch_array($rs)){
            $holiday = '';
            
            if( !empty($row['holiday_id']) ){
                $row['shift_status'] = ucwords(strtolower($row['holiday_type'])).' Holiday';
                $holiday = $row['shift_status'];
            }

            if( empty($row['shift_status']) ){
                $shift_field			= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ];
                $shift_info 			= getDateShiftData( $con, $row['empID'], $shift_field, $row['work_date'] );
                $row['shift_status']	= $shift_info[0];
            }
            
            if($row['shift_status'] == 'Rest Day'){
                $backgroundColor = '#00b050';
            }else{
                $backgroundColor = '#f39c12';  
            }

            $data[] = array(
                'status'=>'success',
                'bg'    => $backgroundColor,
                'shift_status'  => $row['shift_status'],
                'work_date' =>$row['work_date'],
                'holiday' =>$holiday,
            );
        }
        return $data;
    }else{
        $return = json_encode(array('status'=>'empty'));

        return $return;
    }
}
?>