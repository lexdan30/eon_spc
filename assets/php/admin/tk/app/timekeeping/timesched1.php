<?php
require_once('../../../../activation.php');
$conn = new connector();	
$con = $conn->connect();
require_once('../../../../classPhp.php'); 

$param = json_decode(file_get_contents('php://input'));
$return = null;	

$shift_cols = array("monday"	=>"mon", 
					"tuesday"	=>"tue",
					"wednesday"	=>"wed",
					"thursday"	=>"thu",
					"friday"	=>"fri",
					"saturday"	=>"sat", 
                    "sunday"	=>"sun");
               
if($param->date == ''){
    $param->date =date('Y-m-01');
}

$Qry = new Query();	
$Qry->table     = "vw_data_timesheet";
$Qry->selected  = "*";
$Qry->fields    = "empID = '".$param->accountid."' AND ( work_date >= '".date("Y-m-01", strtotime($param->date) )."' AND work_date <= '".date('Y-m-t', strtotime($param->date) )."' ) ORDER BY work_date ASC";

$rs = $Qry->exe_SELECT($con);

if(mysqli_num_rows($rs)>= 1){
    while($row=mysqli_fetch_array($rs)){

        if( !empty($row['holiday_id']) ){
			$row['shift_status'] = ucwords(strtolower($row['holiday_type'])).' Holiday';
		}
		
		if( empty($row['shift_status']) ){
			$shift_field			= "".$shift_cols[  strtolower(''.date("l", strtotime($row['work_date']) )) ]; //result is day ex:Monday or to Friday to lower
			$shift_info 			= getDateShiftData( $con, $row['empID'], $shift_field, $row['work_date'] ); //resulting to time schedule
			$row['shift_status']	= $shift_info[0];
		}

        if($row['idshift']  != 4){ // color of each title
            $title = $row['shift_status'];

            if($title == 'Rest Day'){
				$backgroundColor = '#00b050';
			}else if($row['holiday_id']){
				$backgroundColor = '#f00404';
			}else{
				$backgroundColor = '#f39c12';
			}
          
            $data[] = array( 
                "title"             => $title ,
                "start"             =>  $row['work_date'],
                "end" 	            =>  $row['work_date'],
                "backgroundColor"   => $backgroundColor,
                "sort"              => 1
            );
            
        }

        if($row['idshift']  == 4){
            $title = $row['shift_status'];
            $backgroundColor = '#00AF50';
            
            // $backgroundColorRD = '#ff1a1a';

            $data[] = array( 
                "id" 	=> $row['id'],
                "title" => $title,
                "start" =>  $row['work_date'],
                "end" 	=>  $row['work_date'],
                "backgroundColor"   => $backgroundColor,
                "sort"  => 4
            );
        }
    }
    $return =  json_encode($data);
}else{
    $return = json_encode(array("sort2"  => $Qry->fields));
}

print $return;
mysqli_close($con);
?>